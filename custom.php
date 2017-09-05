<?php
 
$linux_proc = array( 'i686', 'x86_64' );
$mac_proc = array( 'Intel', 'PPC', 'U; Intel', 'U; PPC' );
$lang = array( 'en-US', 'sl-SI' );
 
function search_permalink( $title ) {
	$slug = get_option( 'search_slug' );
	$has_ext = slug_has_extension( $slug );
	$permalink = permalink( $title, '-', array( 'transliterate' => true ) );
	$permalink = str_replace( '%permalink%', $permalink, get_option( 'search_slug' ) );
	$permalink.= ( ! $has_ext ) ? '/' : '';
	return url() . '/' . $permalink;
}
 
function download_permalink( $id, $title, $source ) {
	$slug = get_option( 'download_slug' );
	$has_ext = slug_has_extension( $slug );
	$permalink = base64_url_encode( $source . '-' . $id ) . '-' . permalink( $title, '-', array( 'transliterate' => true ) );
	$permalink = str_replace( '%permalink%', $permalink, get_option( 'download_slug' ) );
	$permalink.= ( ! $has_ext ) ? '/' : '';
	return url() . '/' . $permalink;
}
 
function file_permalink( $id, $source ) {
	$slug = get_option( 'file_slug' );
	$has_ext = slug_has_extension( $slug );
	$permalink = base64_url_encode( $source . '-' . $id );
	$permalink = str_replace( '%permalink%', $permalink, get_option( 'file_slug' ) );
	$permalink.= ( ! $has_ext ) ? '/' : '';
	return url() . '/' . $permalink;
}
 
function get_key( $keys ) {
	$key = '';
	if ( ! empty( $keys ) ) {
		$keys = explode( ',', $keys );
		
		foreach( $keys as $key ) {
			$keys[] = trim( $key );
		}
	
		shuffle( $keys );
		$key = $keys[0];
	}
	
	return $key;
}

function bad_words() {
	if ( file_exists( BASE . '/contents/badwords.txt' ) ) {
		$words = file( BASE . '/contents/badwords.txt' );
		$words = array_filter( explode( ',', $words[0] ) );
		return $words;
	}
	
	return null;
}
 
function music_by_category( $name ) {
	$numbers = ( get_option( 'mp3_per_page' ) != '' ) ? get_option( 'mp3_per_page' ) : 10;
	$keylastfm='b8e81a3f7d703d9f8f1f44357fbda14a';
	if ($name == 'usa'){
		$feed_url = 'http://www.billboard.com/rss/charts/billboard-200';
	}else if ($name == 'country' || $name == 'rock' || $name =='united-kingdom' || $name == 'france' || $name == 'pop' || $name == 'dance-electronic' || $name == 'latin')
	{
		$feed_url = 'http://www.billboard.com/rss/charts/'.$name . '-songs';	
	}else if ($name == 'soundtracks'){
		$feed_url = 'http://www.billboard.com/rss/charts/soundtracks';
		
	}else if ($name == 'german' || $name =='r-and-b' || $name == 'rap' || $name == 'blues' || $name == 'classical' || $name == 'jazz' || $name == 'new-age' || $name == 'reggae'){
		$feed_url = 'http://www.billboard.com/rss/charts/'.$name . '-albums';	
		
	}else if ($name == 'japan'){
		$feed_url = 'http://www.billboard.com/rss/charts/'.$name . '-hot-100';	
		
	}else if ($name == 'artis'){
		$feed_url = 'http://www.billboard.com/rss/charts/artist-100';	
		
	}else if ($name == 'popularartis'){
		$feed_url = 'http://ws.audioscrobbler.com/2.0/?method=geo.gettoptracks&limit=' . $numbers . '&country=spain&api_key=' . $keylastfm;
	}
	
	
	$music_json_file = BASE . '/contents/' . $name . '.json';
	if ( file_exists( $music_json_file ) ) {
		$music = file_get_contents( $music_json_file );
		$music = json_decode( $music, true );
		$expired = $music['expired'];
		$not_expired = ( time() > $expired ) ? false : true;
	}
		
	if ( ! isset( $not_expired ) || isset( $not_expired ) && ! $not_expired ) {
		$music = array();
		if ( $xml = @simplexml_load_file( $feed_url ) ) {
			$result["item"] = $xml->xpath( "/rss/channel/item" );
			
			$i = 1;
			foreach( $result['item'] as $item ) {
				$title = $item->chart_item_title . ' - ' . $item->artist;
				$artist = $item->artist . ' ';
				$music['results'][] = array( 'title' => $title, 'artist' => substr( $artist, 0, -1 ) );
				
				if ( $i++ == $numbers ) {
					break;
				}
			}
			
			$music['expired'] = time() + ( 60 * 60 * 12 );
			$output = json_encode( $music );
			
			if ( file_exists( $music_json_file ) ) {
				unlink( $music_json_file );
			}
			
			$music_file_update = fopen( BASE . '/contents/' . $name . '.json', 'a+' );
			fwrite( $music_file_update, $output );
			fclose( $music_file_update );
		}
	}
	return $music['results'];
}
 
function popular_music( $limit = 10 ) {
	$numbers = ( get_option( 'mp3_per_page' ) != '' ) ? get_option( 'mp3_per_page' ) : 10;
	
	$popular_music_json_file = BASE . '/contents/popular-music.json';
	if ( file_exists( $popular_music_json_file ) ) {
		$music = file_get_contents( $popular_music_json_file );
		$music = json_decode( $music, true );
		$expired = $music['expired'];
		$not_expired = ( time() > $expired ) ? false : true;
	}
	
	if ( ! isset( $not_expired ) || isset( $not_expired ) && ! $not_expired ) {
		$music = array();
		$previous_value = libxml_use_internal_errors( TRUE );
		$dom = new DOMDocument();
		if ( @$dom->load( 'https://itunes.apple.com/us/rss/topsongs/limit=' . $numbers . '/explicit=true/xml' ) ) {
			libxml_clear_errors();
			libxml_use_internal_errors( $previous_value );

			foreach ( $dom->getElementsByTagName( 'entry' ) as $node ) {
				$title = $node->getElementsByTagName( 'title' )->item(0)->nodeValue;
				$artist = $node->getElementsByTagName( 'artist' )->item(0)->nodeValue;
				$music['results'][] = array( 'title' => $title, 'artist' => $artist );
			}
		}
		
		$music['expired'] = time() + ( 60 * 60 * 12 );
		$output = json_encode( $music );
		
		if ( file_exists( $popular_music_json_file ) ) {
			unlink( $popular_music_json_file );
		}
		
		$popular_music_file_update = fopen( BASE . '/contents/popular-music.json', 'a+' );
		fwrite( $popular_music_file_update, $output );
		fclose( $popular_music_file_update );
	}
	
	return $music['results'];
}
 
function youtube( $title = '' ) {
	$key = get_key( get_option( 'youtube_api' ) );
	if ( ! empty( $key ) ) {
		$numbers = ( get_option( 'youtube_search_numbers' ) != '' ) ? get_option( 'youtube_search_numbers' ) : 5;
		
		$output = array();
		
		if ( $_SERVER['HTTP_HOST'] == 'localhost' ) {
			$data = @file_get_contents( 'https://www.googleapis.com/youtube/v3/search?part=snippet&q=' . permalink( limit_word( $title, 5, '+' ), '+', array( 'transliterate' => true ) ). '&key=' . $key . '&maxResults=' . $numbers . '&order=relevance&safeSearch=strict&type=music' );
		} else {
			$data = get_contents( 'https://www.googleapis.com/youtube/v3/search?part=snippet&q=' . permalink( limit_word( $title, 5, '+' ), '+', array( 'transliterate' => true ) ) . '&key=' . $key . '&maxResults=' . $numbers . '&order=relevance&safeSearch=strict&type=music' );
		}
		
		if ( $data ) {
			$youtube = json_decode( $data, true );
			
			$results = array();
			
			if ( isset( $youtube['items'] ) && is_array( $youtube['items'] ) ) {
				foreach( $youtube['items'] as $track ) { 
					if ( isset( $track['id']['videoId'] ) ) {
						$id = $track['id']['videoId'];
					} elseif ( isset( $track['id']['playlistId'] ) ) {
						$exp = explode( '/', $track['snippet']['thumbnails']['default']['url'] );
						$id = $exp[4];
					}
					
					if ( isset( $id ) ) {
						$title = $track['snippet']['title'];
						$thumb = $track['snippet']['thumbnails']['default']['url'];
						$source = 'youtube';
						$results[] = array( 'id' => $id, 'title' => $title, 'thumb' => $thumb, 'source' => $source );
					}
				}
			}
			
			$output['results'] = $results;
		} else {
			$output['error'] = 'Can\'t retrieving data!';
		}
	} else {
		$output['error'] = 'No Youtube API entered!';
	}
	
	return $output;
}
 
function soundcloud( $title = '' ) {
	$key = get_key( get_option( 'soundcloud_api' ) );
	if ( ! empty( $key ) ) {
		$numbers = ( get_option( 'soundcloud_search_numbers' ) != '' ) ? get_option( 'soundcloud_search_numbers' ) : 5;
		
		$output = array();
	
		if ( $_SERVER['HTTP_HOST'] == 'localhost' ) {
			$data = @file_get_contents( 'http://api.soundcloud.com/tracks.json?client_id=' . $key . '&q=' . permalink( limit_word( $title, 5, '+' ), '+', array( 'transliterate' => true )  ) . '&limit=' . $numbers );
		} else {
			$data = get_contents( 'http://api.soundcloud.com/tracks.json?client_id=' . $key . '&q=' . permalink( limit_word( $title, 5, '+' ), '+', array( 'transliterate' => true )  ) . '&limit=' . $numbers );
		}
		
		if ( $data ) {
			$soundcloud = json_decode( $data, true );
			
			if ( is_array( $soundcloud ) && ! empty( $soundcloud ) ) {
				foreach ( $soundcloud as $track ) { 
					if ( $track['id'] && $track['title'] ) {
						$id = $track['id'];
						$title = $track['title'];
						$thumb = $track['artwork_url'];
						$source = 'soundcloud';
						$results[] = array( 'id' => $id, 'title' => $title, 'thumb' => $thumb, 'source' => $source );
					}
				}
				
				$output['results'] = $results;
			} else {
				$output['error'] = 'Can\'t retrieving data!';
			}
		} else {
			$output['error'] = 'Can\'t retrieving data!';
		}
	} else {
		$output['error'] = 'No Soundcloud API entered!';
	}
	
	return $output;
}
  
function download_info( $source, $id ) {
	$info['source'] = $source;
	
	if ( $source == 'youtube' ) {
		$key = get_key( get_option( 'youtube_api' ) );
		if ( ! empty( $key ) ) {
			if ( $_SERVER['HTTP_HOST'] == 'localhost' ) {
				$data = @file_get_contents( 'https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&key=' . $key . '&part=snippet,contentDetails,statistics,status' );
			} else {
				$data = get_contents( 'https://www.googleapis.com/youtube/v3/videos?id=' . $id . '&key=' . $key . '&part=snippet,contentDetails,statistics,status' );
			}
			
			if ( $data ) {
				$youtube = json_decode( $data, true );
			
				$info['id'] = $id;
				$info['title'] = $youtube['items'][0]['snippet']['title'];
				$info['viewCount'] = $youtube['items'][0]['statistics']['viewCount'];
				$info['description'] = $youtube['items'][0]['snippet']['description'];
				$info['thumb'] = 'http://img.youtube.com/vi/' . $info['id'] . '/hqdefault.jpg';
				$info['release'] = $youtube['items'][0]['snippet']['publishedAt'];
				$info['duration'] = $youtube['items'][0]['contentDetails']['duration'];
				
				$category_id = $youtube['items'][0]['snippet']['categoryId'];
				if ( $_SERVER['HTTP_HOST'] == 'localhost' ) {
					$category_data = @file_get_contents( 'https://www.googleapis.com/youtube/v3/videoCategories?part=snippet&id=' . $category_id . '&key=' . $key );
				} else {
					$category_data = get_contents( 'https://www.googleapis.com/youtube/v3/videoCategories?part=snippet&id=' . $category_id . '&key=' . $key );
				}
				$category = json_decode( $category_data, true );
				
				$info['genre'] = ( $category['items'][0]['snippet']['title'] != '' ) ? $category['items'][0]['snippet']['title'] : 'Unknown';
			} else {
				$info['error'] = 'Can\'t retrieving data!';
			}
		} else {
			$info['error'] = 'No Youtube API entered!';
		}
	} elseif ( $source == 'soundcloud' ) {
		$key = get_key( get_option( 'soundcloud_api' ) );
		if ( ! empty( $key ) ) {
			$soundcloud = get_contents( 'http://api.soundcloud.com/tracks/' . $id . '.json?client_id=' . $key );
			$track = json_decode( $soundcloud );
			
			if ( $track && ! isset( $track->errors ) ) {
				$info['id'] = $id;
				$info['title'] = $track->title;
				$info['thumb'] = preg_replace( '/large.jpg/ms', "t500x500.jpg", $track->artwork_url );
				$info['release'] = $track->last_modified;
				$info['genre'] = ( $track->genre != null ) ? $track->genre : 'Unknown';
				$info['description'] = $track->description;
				$info['viewCount'] = $track->download_count;
			} else {
				$info['error'] = 'Can\'t retrieving data!';
			}
		} else {
			$info['error'] = 'No Soundcloud API entered!';
		}
	}
	
	return $info;
}
 
function download_soundcloud( $id ) {
	$key = get_key( get_option( 'soundcloud_api' ) );
	
	if ( $id != '' ) {
		http_response_code( 200 );
			
		$grab = json_decode( get_contents( 'http://api.soundcloud.com/tracks/' . $id . '.json?client_id=' . $key ), true );
		if ( is_array( $grab ) ) {
			$file = check_redirection( 'https://api.soundcloud.com/tracks/' . $id . '/stream?client_id=' . $key );
			$name = $grab['title'];

			header( "Content-Type:audio/mpeg" );
			if ( $file != "404" &&  $file != "401" ) {
				header( "Content-length:" . get_file_size( $file ) );
			}

			header( 'Content-Disposition:attachment; filename="' . $name . '.mp3"' );
			
			if ( get_http_response_code( 'https://api.soundcloud.com/tracks/' . $id . '/stream?client_id=' . $key ) != "404" ) {
				if ( get_http_response_code( 'https://api.soundcloud.com/tracks/' . $id . '/stream?client_id=' . $key ) != "401" ) {
					readfile( "$file" );
				}
			}
		} else {
			redirect( url() );
		}
	} else {
		redirect( url() );
	}
	
	exit();
}
 
function filter_badwords( $haystack, $needle, $offset = 0 ) {
	if ( is_array( $needle ) && ! empty( $needle ) ) {
		$needle = implode( '|', $needle );
		if ( preg_match( '~\b(' . str_replace( ' ', '|', $needle ) . ')\b~', $haystack ) ) {
    			return true;
  		}
	}
	
	return false;
}
 
function set_recent_search( $title = '' ) {
	$recent_search_file = BASE . '/contents/recent-search.json';
	
	$get_search = array();
	if ( file_exists( $recent_search_file ) ) {
		$json = file_get_contents( $recent_search_file );
		$get_search = json_decode( $json, true );
	}
	
	if ( count( $get_search ) > get_option( 'recent_search_numbers' ) ) {
		array_pop( $get_search );
	}

	if ( in_array( $title, $get_search ) ) {
		if ( ( $key = array_search( $title, $get_search ) ) !== false ) {
			unset( $get_search[$key] );
		}
	}
	
	if ( count( $get_search ) > 0 ) {
		$update = array_merge( array( $title ), $get_search );
	} else {
		$update = array( $title );
	}
	
	foreach( $update as $key => $value ) {
		if ( $value == '' ) {
			unset( $update[$key] );
		}
	}
	
	if ( file_exists( $recent_search_file ) ) {
		unlink( $recent_search_file );
	}
	
	$output = json_encode( $update );
	$recent_search_file_update = fopen( BASE . '/contents/recent-search.json', 'a+' );
	fwrite( $recent_search_file_update, $output );
	fclose( $recent_search_file_update );
}

function lines_count( $file ) {
    $f = fopen( $file, 'a+' );
    $lines = 0;

    while ( ! feof( $f ) ) {
        $lines+= substr_count( fread( $f, 8192 ), "\n" );
    }

    fclose( $f );

    return $lines;
}
 
function base64_url_encode( $data ) {
	return strtr( rtrim( base64_encode( $data ), '=' ), '+/', '-_' );
}
 
function base64_url_decode( $base64 ) {
	return base64_decode( strtr( $base64, '-_', '+/' ) );
}
 
function limit_word( $string, $word_limit ) {
	$words = explode( ' ', $string );
	return implode( ' ', array_slice( $words, 0, $word_limit ) );
}
 
function permalink( $str, $delimiter = '-', $options = array() ) {
	$str = mb_convert_encoding( ( string ) $str, 'UTF-8', mb_list_encodings() );
	
	$defaults = array(
		'delimiter' => $delimiter,
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => false,
	);
  
	$options = array_merge( $defaults, $options );
  
	$char_map = array(
		// Latin
		'ÃƒÂ€' => 'A', 'ÃƒÂ' => 'A', 'ÃƒÂ‚' => 'A', 'ÃƒÂƒ' => 'A', 'ÃƒÂ„' => 'A', 'ÃƒÂ…' => 'A', 'ÃƒÂ†' => 'AE', 'ÃƒÂ‡' => 'C', 
		'ÃƒÂˆ' => 'E', 'ÃƒÂ‰' => 'E', 'ÃƒÂŠ' => 'E', 'ÃƒÂ‹' => 'E', 'ÃƒÂŒ' => 'I', 'ÃƒÂ' => 'I', 'ÃƒÂŽ' => 'I', 'ÃƒÂ' => 'I', 
		'ÃƒÂ' => 'D', 'ÃƒÂ‘' => 'N', 'ÃƒÂ’' => 'O', 'ÃƒÂ“' => 'O', 'ÃƒÂ”' => 'O', 'ÃƒÂ•' => 'O', 'ÃƒÂ–' => 'O', 'Ã…Â' => 'O', 
		'ÃƒÂ˜' => 'O', 'ÃƒÂ™' => 'U', 'ÃƒÂš' => 'U', 'ÃƒÂ›' => 'U', 'ÃƒÂœ' => 'U', 'Ã…Â°' => 'U', 'ÃƒÂ' => 'Y', 'ÃƒÂž' => 'TH', 
		'ÃƒÂŸ' => 'ss', 
		'Ãƒ ' => 'a', 'ÃƒÂ¡' => 'a', 'ÃƒÂ¢' => 'a', 'ÃƒÂ£' => 'a', 'ÃƒÂ¤' => 'a', 'ÃƒÂ¥' => 'a', 'ÃƒÂ¦' => 'ae', 'ÃƒÂ§' => 'c', 
		'ÃƒÂ¨' => 'e', 'ÃƒÂ©' => 'e', 'ÃƒÂª' => 'e', 'ÃƒÂ«' => 'e', 'ÃƒÂ¬' => 'i', 'ÃƒÂ­' => 'i', 'ÃƒÂ®' => 'i', 'ÃƒÂ¯' => 'i', 
		'ÃƒÂ°' => 'd', 'ÃƒÂ±' => 'n', 'ÃƒÂ²' => 'o', 'ÃƒÂ³' => 'o', 'ÃƒÂ´' => 'o', 'ÃƒÂµ' => 'o', 'ÃƒÂ¶' => 'o', 'Ã…Â‘' => 'o', 
		'ÃƒÂ¸' => 'o', 'ÃƒÂ¹' => 'u', 'ÃƒÂº' => 'u', 'ÃƒÂ»' => 'u', 'ÃƒÂ¼' => 'u', 'Ã…Â±' => 'u', 'ÃƒÂ½' => 'y', 'ÃƒÂ¾' => 'th', 
		'ÃƒÂ¿' => 'y',
 
		// Latin symbols
		'Ã‚Â©' => '(c)',
 
		// Greek
		'ÃŽÂ‘' => 'A', 'ÃŽÂ’' => 'B', 'ÃŽÂ“' => 'G', 'ÃŽÂ”' => 'D', 'ÃŽÂ•' => 'E', 'ÃŽÂ–' => 'Z', 'ÃŽÂ—' => 'H', 'ÃŽÂ˜' => '8',
		'ÃŽÂ™' => 'I', 'ÃŽÂš' => 'K', 'ÃŽÂ›' => 'L', 'ÃŽÂœ' => 'M', 'ÃŽÂ' => 'N', 'ÃŽÂž' => '3', 'ÃŽÂŸ' => 'O', 'ÃŽ ' => 'P',
		'ÃŽÂ¡' => 'R', 'ÃŽÂ£' => 'S', 'ÃŽÂ¤' => 'T', 'ÃŽÂ¥' => 'Y', 'ÃŽÂ¦' => 'F', 'ÃŽÂ§' => 'X', 'ÃŽÂ¨' => 'PS', 'ÃŽÂ©' => 'W',
		'ÃŽÂ†' => 'A', 'ÃŽÂˆ' => 'E', 'ÃŽÂŠ' => 'I', 'ÃŽÂŒ' => 'O', 'ÃŽÂŽ' => 'Y', 'ÃŽÂ‰' => 'H', 'ÃŽÂ' => 'W', 'ÃŽÂª' => 'I',
		'ÃŽÂ«' => 'Y',
		'ÃŽÂ±' => 'a', 'ÃŽÂ²' => 'b', 'ÃŽÂ³' => 'g', 'ÃŽÂ´' => 'd', 'ÃŽÂµ' => 'e', 'ÃŽÂ¶' => 'z', 'ÃŽÂ·' => 'h', 'ÃŽÂ¸' => '8',
		'ÃŽÂ¹' => 'i', 'ÃŽÂº' => 'k', 'ÃŽÂ»' => 'l', 'ÃŽÂ¼' => 'm', 'ÃŽÂ½' => 'n', 'ÃŽÂ¾' => '3', 'ÃŽÂ¿' => 'o', 'ÃÂ€' => 'p',
		'ÃÂ' => 'r', 'ÃÂƒ' => 's', 'ÃÂ„' => 't', 'ÃÂ…' => 'y', 'ÃÂ†' => 'f', 'ÃÂ‡' => 'x', 'ÃÂˆ' => 'ps', 'ÃÂ‰' => 'w',
		'ÃŽÂ¬' => 'a', 'ÃŽÂ­' => 'e', 'ÃŽÂ¯' => 'i', 'ÃÂŒ' => 'o', 'ÃÂ' => 'y', 'ÃŽÂ®' => 'h', 'ÃÂŽ' => 'w', 'ÃÂ‚' => 's',
		'ÃÂŠ' => 'i', 'ÃŽÂ°' => 'y', 'ÃÂ‹' => 'y', 'ÃŽÂ' => 'i',
 
		// Turkish
		'Ã…Âž' => 'S', 'Ã„Â°' => 'I', 'ÃƒÂ‡' => 'C', 'ÃƒÂœ' => 'U', 'ÃƒÂ–' => 'O', 'Ã„Âž' => 'G',
		'Ã…ÂŸ' => 's', 'Ã„Â±' => 'i', 'ÃƒÂ§' => 'c', 'ÃƒÂ¼' => 'u', 'ÃƒÂ¶' => 'o', 'Ã„ÂŸ' => 'g', 
 
		// Russian
		'ÃÂ' => 'A', 'ÃÂ‘' => 'B', 'ÃÂ’' => 'V', 'ÃÂ“' => 'G', 'ÃÂ”' => 'D', 'ÃÂ•' => 'E', 'ÃÂ' => 'Yo', 'ÃÂ–' => 'Zh',
		'ÃÂ—' => 'Z', 'ÃÂ˜' => 'I', 'ÃÂ™' => 'J', 'ÃÂš' => 'K', 'ÃÂ›' => 'L', 'ÃÂœ' => 'M', 'ÃÂ' => 'N', 'ÃÂž' => 'O',
		'ÃÂŸ' => 'P', 'Ã ' => 'R', 'ÃÂ¡' => 'S', 'ÃÂ¢' => 'T', 'ÃÂ£' => 'U', 'ÃÂ¤' => 'F', 'ÃÂ¥' => 'H', 'ÃÂ¦' => 'C',
		'ÃÂ§' => 'Ch', 'ÃÂ¨' => 'Sh', 'ÃÂ©' => 'Sh', 'ÃÂª' => '', 'ÃÂ«' => 'Y', 'ÃÂ¬' => '', 'ÃÂ­' => 'E', 'ÃÂ®' => 'Yu',
		'ÃÂ¯' => 'Ya',
		'ÃÂ°' => 'a', 'ÃÂ±' => 'b', 'ÃÂ²' => 'v', 'ÃÂ³' => 'g', 'ÃÂ´' => 'd', 'ÃÂµ' => 'e', 'Ã‘Â‘' => 'yo', 'ÃÂ¶' => 'zh',
		'ÃÂ·' => 'z', 'ÃÂ¸' => 'i', 'ÃÂ¹' => 'j', 'ÃÂº' => 'k', 'ÃÂ»' => 'l', 'ÃÂ¼' => 'm', 'ÃÂ½' => 'n', 'ÃÂ¾' => 'o',
		'ÃÂ¿' => 'p', 'Ã‘Â€' => 'r', 'Ã‘Â' => 's', 'Ã‘Â‚' => 't', 'Ã‘Âƒ' => 'u', 'Ã‘Â„' => 'f', 'Ã‘Â…' => 'h', 'Ã‘Â†' => 'c',
		'Ã‘Â‡' => 'ch', 'Ã‘Âˆ' => 'sh', 'Ã‘Â‰' => 'sh', 'Ã‘ÂŠ' => '', 'Ã‘Â‹' => 'y', 'Ã‘ÂŒ' => '', 'Ã‘Â' => 'e', 'Ã‘ÂŽ' => 'yu',
		'Ã‘Â' => 'ya',
 
		// Ukrainian
		'ÃÂ„' => 'Ye', 'ÃÂ†' => 'I', 'ÃÂ‡' => 'Yi', 'Ã’Â' => 'G',
		'Ã‘Â”' => 'ye', 'Ã‘Â–' => 'i', 'Ã‘Â—' => 'yi', 'Ã’Â‘' => 'g',
 
		// Czech
		'Ã„ÂŒ' => 'C', 'Ã„ÂŽ' => 'D', 'Ã„Âš' => 'E', 'Ã…Â‡' => 'N', 'Ã…Â˜' => 'R', 'Ã… ' => 'S', 'Ã…Â¤' => 'T', 'Ã…Â®' => 'U', 
		'Ã…Â½' => 'Z', 
		'Ã„Â' => 'c', 'Ã„Â' => 'd', 'Ã„Â›' => 'e', 'Ã…Âˆ' => 'n', 'Ã…Â™' => 'r', 'Ã…Â¡' => 's', 'Ã…Â¥' => 't', 'Ã…Â¯' => 'u',
		'Ã…Â¾' => 'z', 
 
		// Polish
		'Ã„Â„' => 'A', 'Ã„Â†' => 'C', 'Ã„Â˜' => 'e', 'Ã…Â' => 'L', 'Ã…Âƒ' => 'N', 'ÃƒÂ“' => 'o', 'Ã…Âš' => 'S', 'Ã…Â¹' => 'Z', 
		'Ã…Â»' => 'Z', 
		'Ã„Â…' => 'a', 'Ã„Â‡' => 'c', 'Ã„Â™' => 'e', 'Ã…Â‚' => 'l', 'Ã…Â„' => 'n', 'ÃƒÂ³' => 'o', 'Ã…Â›' => 's', 'Ã…Âº' => 'z',
		'Ã…Â¼' => 'z',
 
		// Latvian
		'Ã„Â€' => 'A', 'Ã„ÂŒ' => 'C', 'Ã„Â’' => 'E', 'Ã„Â¢' => 'G', 'Ã„Âª' => 'i', 'Ã„Â¶' => 'k', 'Ã„Â»' => 'L', 'Ã…Â…' => 'N', 
		'Ã… ' => 'S', 'Ã…Âª' => 'u', 'Ã…Â½' => 'Z',
		'Ã„Â' => 'a', 'Ã„Â' => 'c', 'Ã„Â“' => 'e', 'Ã„Â£' => 'g', 'Ã„Â«' => 'i', 'Ã„Â·' => 'k', 'Ã„Â¼' => 'l', 'Ã…Â†' => 'n',
		'Ã…Â¡' => 's', 'Ã…Â«' => 'u', 'Ã…Â¾' => 'z'
	);
	
	$str = preg_replace( array_keys( $options['replacements'] ), $options['replacements'], $str );
  
	if ( $options['transliterate'] ) {
		$str = str_replace( array_keys( $char_map ), $char_map, $str );
	}
  
	$str = preg_replace( '/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	$str = preg_replace( '/(' . preg_quote( $options['delimiter'], '/') . '){2,}/', '$1', $str);
	$str = substr( $str, 0, ( $options['limit'] ? $options['limit'] : strlen( $str ) ) );
	$str = trim( $str, $options['delimiter'] );
  
	return $options['lowercase'] ? strtolower( $str ) : $str;
}
 
function get_contents( $url ) {
	if ( function_exists( 'curl_exec' ) ) { 
		$ch = curl_init();

		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0].= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: ";

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_ENCODING, "gzip,deflate" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_setopt( $ch, CURLOPT_REFERER, "http://www.facebook.com" );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt( $ch, CURLOPT_USERAGENT, user_agent() );

		$url_get_contents_data = curl_exec( $ch );
		curl_close( $ch );
		if ( $url_get_contents_data == false ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_URL, $url );
			$url_get_contents_data = curl_exec( $ch );
			curl_close( $ch );
		}
	} elseif( function_exists( 'file_get_contents' ) ){
		$url_get_contents_data = @file_get_contents( $url );
	} elseif( function_exists( 'fopen' ) && function_exists( 'stream_get_contents' ) ) {
		$handle = fopen( $url, "r" );
		$url_get_contents_data = stream_get_contents( $handle );
	} else {
		$url_get_contents_data = false;
	}
	
	return $url_get_contents_data;
}

function user_agent() {
	$x = rand( 1, 5 );
	
	switch ( $x ) {
		case 1: return "Mozilla/5.0 " . firefox(); break;
		case 2: return "Mozilla/5.0 " . safari() ; break;
		case 3: return "Mozilla/" . rand( 4, 5 ) . ".0 " . iexplorer(); break;
		case 4: return "Opera/" . rand( 8, 9 ) . '.' . rand( 10, 99 ) . ' ' . opera(); break;
		case 5: return 'Mozilla/5.0' . chrome(); break;
	}
}

function firefox() {
	global $linux_proc, $mac_proc, $lang;
	
	$ver = array(
		'Gecko/' . date( 'Ymd', rand( strtotime( '2011-1-1' ), time() ) ) . ' Firefox/' . rand( 5, 7 ) . '.0',
		'Gecko/' . date( 'Ymd', rand( strtotime( '2011-1-1' ), time() ) ) . ' Firefox/' . rand( 5, 7 ) . '.0.1',
		'Gecko/' . date( 'Ymd', rand( strtotime( '2010-1-1' ), time() ) ) . ' Firefox/3.6.' . rand( 1, 20 ),
		'Gecko/' . date( 'Ymd', rand( strtotime( '2010-1-1' ), time() ) ) . ' Firefox/3.8'
	);
	
	$platforms = array(
		'(Windows NT ' . rand( 5, 6 ) . '.' . rand( 0, 1 ) . '; ' . $lang[array_rand( $lang, 1 )] . '; rv:1.9.' . rand( 0, 2 ) . '.20) ' . $ver[array_rand( $ver, 1 )],
		'(X11; Linux ' . $linux_proc[array_rand( $linux_proc, 1 )] . '; rv:' . rand( 5, 7 ) . '.0) ' . $ver[array_rand( $ver, 1 )],
		'(Macintosh; ' . $mac_proc[array_rand( $mac_proc, 1 )] . ' Mac OS X 10_' . rand( 5, 7 ) . '_' . rand( 0, 9 ) . ' rv:' . rand( 2, 6 ) . '.0) ' . $ver[array_rand( $ver, 1 )]
	);
	
	return $platforms[array_rand( $platforms, 1 )];
}

function safari() {
	global $mac_proc, $lang;
	
	$saf = rand( 531, 535 ) . '.' . rand( 1, 50 ) . '.' . rand( 1, 7 );
	   
	if ( rand( 0, 1 ) == 0 )
		$ver = rand( 4, 5 ) . '.' . rand( 0, 1 );
	else
		$ver = rand( 4, 5 ) . '.0.' . rand( 1, 5 );
	
	$platforms = array(
		'(Windows; U; Windows NT ' . rand( 5, 6 ) . '.' . rand( 0, 1 ) . ") AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf",
		'(Macintosh; U; ' . $mac_proc[array_rand( $mac_proc, 1 )] . ' Mac OS X 10_' . rand( 5, 7 ) . '_' . rand( 0, 9 ) . ' rv:' . rand( 2, 6 ) . '.0; ' . $lang[array_rand( $lang, 1 )] . ") AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf",
		'(iPod; U; CPU iPhone OS ' . rand( 3, 4 ) . '_' . rand( 0, 3 ) . ' like Mac OS X; ' . $lang[array_rand( $lang, 1 )] . ") AppleWebKit/$saf (KHTML, like Gecko) Version/" . rand( 3, 4 ) . ".0.5 Mobile/8B" . rand( 111, 119 ) . " Safari/6$saf",
	);
	
	return $platforms[array_rand( $platforms, 1 )];
}

function iexplorer() {
	$ie_extra = array( '', '; .NET CLR 1.1.' . rand( 4320, 4325 ) . '', '; WOW64' );
	
	$platforms = array( '(compatible; MSIE ' . rand( 5, 9 ) . '.0; Windows NT ' . rand( 5, 6 ) . '.' . rand( 0, 1 ) . '; Trident/' . rand( 3, 5 ) . '.' . rand( 0, 1 ) . ')' );
	
	return $platforms[array_rand( $platforms, 1 )];
}

function opera() {
	global $linux_proc, $lang;
	
	$op_extra = array( '', '; .NET CLR 1.1.' . rand( 4320, 4325 ) . '', '; WOW64' );
	
	$platforms = array(
		'(X11; Linux ' . $linux_proc[array_rand( $linux_proc, 1 )] . '; U; ' . $lang[array_rand( $lang, 1 )] . ') Presto/2.9.' . rand( 160, 190 ) . ' Version/' . rand( 10, 12 ) . '.00',
		'(Windows NT ' . rand( 5, 6 ) . '.' . rand( 0, 1 ) . '; U; ' . $lang[array_rand( $lang, 1 )] . ') Presto/2.9.' . rand( 160, 190 ) . ' Version/' . rand( 10, 12 ) . '.00'
	);
	
	return $platforms[array_rand( $platforms, 1 )];
}

function chrome() {
	global $linux_proc, $mac_proc, $lang;
	
	$saf = rand( 531, 536 ) . rand( 0, 2 );

	$platforms = array(
		'(X11; Linux ' . $linux_proc[array_rand( $linux_proc, 1 )] . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/" . rand( 13, 15 ) . '.0.' . rand( 800, 899 ) . ".0 Safari/$saf",
		'(Windows NT ' . rand( 5, 6 ) . '.' . rand( 0, 1 ) . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/" . rand( 13, 15 ) . '.0.' . rand( 800, 899 ) . ".0 Safari/$saf",
		'(Macintosh; U; ' . $mac_proc[array_rand( $mac_proc, 1 )] . ' Mac OS X 10_' . rand( 5, 7 ) . '_' . rand( 0, 9 ) . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/" . rand( 13, 15 ) . '.0.' . rand( 800, 899 ) . ".0 Safari/$saf"
	);

	return $platforms[array_rand( $platforms, 1 )];
}
 
function check_redirection( $url ) {
	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_HEADER, true );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$data = curl_exec( $ch );
	$code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	
	if ( in_array( $code, array( 301, 302 ) ) ) {
		preg_match( '/Location:(.*?)\n/', $data, $matches );
		return trim( $matches[1] );
	}
	
	return $url;
}
 
function get_file_size( $url ) {
	$data = get_headers( $url, true );
	return ( isset( $data['Content-Length'] ) ) ? ( int ) $data['Content-Length'] : 0;
}
 
function get_http_response_code( $url ) {
	$headers = get_headers( $url );
	return substr( $headers[0], 9, 3 );
}