<?php 

class NVC {
	public function __construct() {
		global $query, $pages, $options;
		
		if ( $query['uri'] != '/' ) {
			$exp_uri = explode( '/', $query['uri'] );
			$filter_uri = array_filter( $exp_uri );
			$has_slashes = $exp_uri;
			$not_has_slashes = false;
			
			if ( count( $has_slashes ) == count( $filter_uri ) ) {
				$not_has_slashes = true;
			} elseif ( count( $has_slashes ) > count( $filter_uri ) && count( $has_slashes ) > ( count( $filter_uri ) + 1 ) ) {
				$not_has_slashes = true;
			}
			
			if ( slug_has_extension( $query['uri'] ) ) {
				$not_has_slashes = false;
			}
			
			if ( $not_has_slashes ) {
				$url = parse_url( $query['uri'] );
				$uri_query = ( isset( $url['query'] ) ) ? $url['query'] : '';
				$redirect = implode( array_filter( $exp_uri ), '/' ) . '/';
				$redirect.= ( ! empty( $uri_query ) ) ? '?' . $uri_query : '';
				redirect( url() . '/' . $redirect );
			}
		}
		
		if ( isset( $_GET['s'] ) ) {
			redirect( search_permalink( $_GET['s'] ) );
		}
		
		if ( isset( $options['search_slug'] ) ) {
			$pages['search']['permalink'] = str_replace( '%permalink%', '([^_/]+)', $options['search_slug'] );
		}
		if ( isset( $options['download_slug'] ) ) {
			$pages['download']['permalink'] = str_replace( '%permalink%', '([^_/]+)', $options['download_slug'] );
		}
		if ( isset( $options['file_slug'] ) ) {
			$pages['file']['permalink'] = str_replace( '%permalink%', '([^_/]+)', $options['file_slug'] );
		}
		
		if ( ! isset( $options['primary_menu']['country'] ) ) {
			unset( $pages['country'] );
		}
		if ( ! isset( $options['primary_menu']['rock'] ) ) {
			unset( $pages['rock'] );
		}
		if ( ! isset( $options['primary_menu']['usa'] ) ) {
			unset( $pages['usa'] );
		}
		if ( ! isset( $options['primary_menu']['uk'] ) ) {
			unset( $pages['uk'] );
		}
		if ( ! isset( $options['primary_menu']['japan'] ) ) {
			unset( $pages['japan'] );
		}
		if ( ! isset( $options['primary_menu']['k-pop'] ) ) {
			unset( $pages['k-pop'] );
		}
		if ( ! isset( $options['primary_menu']['german'] ) ) {
			unset( $pages['german'] );
		}
		if ( ! isset( $options['primary_menu']['france'] ) ) {
			unset( $pages['france'] );
		}
		if ( ! isset( $options['primary_menu']['r-and-b'] ) ) {
			unset( $pages['r-and-b'] );
		}
		if ( ! isset( $options['primary_menu']['rap'] ) ) {
			unset( $pages['rap'] );
		}
		if ( ! isset( $options['primary_menu']['soundtracks'] ) ) {
			unset( $pages['soundtracks'] );
		}
		if ( ! isset( $options['primary_menu']['blues'] ) ) {
			unset( $pages['blues'] );
		}
		if ( ! isset( $options['primary_menu']['classical'] ) ) {
			unset( $pages['classical'] );
		}		
		if ( ! isset( $options['primary_menu']['pop'] ) ) {
			unset( $pages['pop'] );
		}		
		if ( ! isset( $options['primary_menu']['dance-electronic'] ) ) {
			unset( $pages['dance-electronic'] );
		}		
		if ( ! isset( $options['primary_menu']['latin'] ) ) {
			unset( $pages['latin'] );
		}		
		if ( ! isset( $options['primary_menu']['jazz'] ) ) {
			unset( $pages['jazz'] );
		}		
		if ( ! isset( $options['primary_menu']['new-age'] ) ) {
			unset( $pages['new-age'] );
		}		
		if ( ! isset( $options['primary_menu']['reggae'] ) ) {
			unset( $pages['reggae'] );
		}
		if ( ! isset( $options['footer_menu']['about'] ) ) {
			unset( $pages['about'] );
		}
		if ( ! isset( $options['footer_menu']['dmca'] ) ) {
			unset( $pages['dmca'] );
		}
		if ( ! isset( $options['footer_menu']['terms'] ) ) {
			unset( $pages['terms'] );
		}
		
		foreach( $pages as $now => $args ) {
			$args['permalink'].= ( ! slug_has_extension( $args['permalink'] ) && $args['permalink'] != '/' ) ? '/' : '';
			
			if ( isset( $args['pattern'] ) ) {
				$pattern = '/^' . str_replace( '/', '\/', $args['permalink'] ) . '$/';
				if ( preg_match( $pattern, $query['uri'], $params ) ) {
					array_shift( $params );
					$query['vars'] = ( count( $params ) > 1 ) ? $params : $params[0];
					$query['paged'] = ( isset( $args['paged'] ) ) ? $args['paged'] : false;
					$query['page'] = ( isset( $args['paged'] ) ) ? end( $params ) : 1;
					$query['now'] = $now;
					$file = $args['file'];
				}
			} else {
				$uri = ( $query['uri'] != '/' ) ? rtrim( $query['uri'], '/' ) : '/';
				$uri.= ( ! slug_has_extension( $args['permalink'] ) && $uri != '/' ) ? '/' : '';
				if ( $args['permalink'] == $uri ) {
					$query['now'] = $now;
					$file = $args['file'];
				}
			}
		}
		
		if ( ! isset( $file ) ) {
			$query['now'] = 'error404';
			$query['error404'] = true;
			$file = 'pages/public/404';
		}
		
		if ( isset( $query['vars'] ) && ! $this->check() ) {
			$query['now'] = 'error404';
			$query['error404'] = true;
			$file = 'pages/public/404';
		}
		
		$this->page( $file );
	}
	
	public function check() {
		global $query;
		
		if ( is_now( 'search' ) ) {
			$music['title'] = urldecode( ucwords( str_replace( '-', ' ', $query['vars'] ) ) );
			$bad_words = bad_words();
			if ( filter_badwords( strtolower( $music['title'] ), $bad_words, 0 ) ) {
				$exp_title = explode( ' ', strtolower( $music['title'] ) );
				if ( count( $exp_title ) > 1 ) {
					foreach( $bad_words as $key => $value ) {
						$exp_value = explode( ' ', $value );
						if ( count( $exp_value ) > 1 ) {
							foreach( $exp_value as $child_value ) {
								if ( ( $key = array_search( $child_value, $exp_title ) ) !== false ) {
									unset( $exp_title[$key] );
								}
							}
						} else {
							if ( ( $key = array_search( $value, $exp_title ) ) !== false ) {
								unset( $exp_title[$key] );
							}
						}
					}
				
					if ( count( $exp_title ) > 0 ) {
						$redirect = implode( $exp_title, '-' );
						redirect( search_permalink( $redirect ) );
					} else {
						redirect( url() );
					}
				} else {
					redirect( url() );
				}
			
				exit();
			}
	
			set_recent_search( $music['title'] );

			$youtube = youtube( $music['title'] );
			$soundcloud = soundcloud( $music['title'] );
			
			if ( ! isset( $youtube['error'] ) && ! isset( $soundcloud['error'] ) ) {
				$music['results'] = array_merge( $youtube['results'], $soundcloud['results'] );
			} elseif ( ! isset( $youtube['error'] ) && isset( $soundcloud['error'] ) ) {
				$music['results'] = $youtube['results'];
			} elseif ( isset( $youtube['error'] ) && ! isset( $soundcloud['error'] ) ) {
				$music['results'] = $soundcloud['results'];
			} else {
				$music['error'] = array_merge( $youtube, $soundcloud );
			}
	
			$GLOBALS['music'] = $music;
			
			return true;
		} elseif ( is_now( 'download' ) ) {
			$exp_slug = explode( '-', $query['vars'], 2 );
			$decode = base64_url_decode( $exp_slug[0] );
			$exp_decode = explode( '-', $decode, 2 );
			$source = $exp_decode[0];
			$id = $exp_decode[1];
			$info = download_info( $source, $id );
			$info['title'] = strip_tags( $info['title'] );
			
			$GLOBALS['music'] = $info;
			
			return true;
		} elseif ( is_now( 'file' ) ) {
			return true;
		} elseif ( is_now( 'sitemap' ) || is_now( 'sitemap-xml' ) ) {
			$file_number = 1;
			$page_number = 1;
			if ( isset( $query['vars'] ) ) {
				$exp_vars = explode( '-', $query['vars'] );
				$file_number = $exp_vars[0];
				$page_number = ( isset( $exp_vars[1] ) ) ? $exp_vars[1] : 0;
			}
			
			if ( is_now( 'sitemap' ) && file_exists( BASE . '/contents/keywords/keywords' . $file_number . '.txt' ) ) {
				return true;
			}
			
			$keywords = glob( BASE . '/contents/keywords/*.txt' );
			if ( $file_number > 0 && $file_number <= count( $keywords ) && $page_number > 0 ) {
				return true;
			}
		} elseif ( is_now( 'css' ) || is_now( 'js' ) ) {
			return true;
		}
		
		return false;
	}
	
	public function page( $file ) {
		require BASE . '/' . $file . '.php';
	}
}