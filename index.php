<?php
	ini_set('memory_limit', '-1');
// header( "Content-type: text/xml" ); 
	$f = ".xml"; // $_POST[format]
	$files_name= file('keywords/keywords.txt' );
	$priority = "0.5" ;
	$lastmodified =  date( 'c' );
	$changefreq ="daily";
	$path_kb = "http://fullhdfree.xyz/";
	$slug = "video/";
	$per_page = 1; // max size

	$fn = "1"; // file number, starts empty, then 1, 2, etc
	

		$prefix_string 	= "<url>\n<loc>";
		$postfix_string	= "</loc>\n<priority>$priority</priority>\n<lastmod>$lastmodified</lastmod>\n<changefreq>$changefreq</changefreq>\n</url>\n";
		$sitemap_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
			"<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\" xmlns:video=\"http://www.google.com/schemas/sitemap-video/1.1\">\n";
		$sitemap_footer = "</urlset>\n";

foreach ($files_name as $word)
		{
			$arraystrreplace= array("&"," ");
			$strreplace = str_replace($arraystrreplace,"-",trim($word)); 
			$sitemap_url_string []= "$prefix_string$path_kb$slug$strreplace"."$postfix_string";
		}	
		//delete all file in dir	
		array_map('unlink',glob("./sitemap/*"));
		
		
		$counter = 0;
		while ($chunk = array_splice($sitemap_url_string,0,$per_page)){

			$openfile=@fopen("./sitemap/sitemap".($counter++).$f,"wb");
			$celeng = implode("",$chunk);
			if($openfile){
			@fwrite($openfile,$sitemap_header.$celeng.$sitemap_footer);
			@fclose($openfile);
			}
		}
	//	
	
	//make sitemap_index start here	
	
	$files = glob('sitemap/*.xml' );	
	$totalfile = count($files);
	echo $totalfile;
	foreach( $files as $file ) {
		$line_count[] = $totalfile;
	}
	$sitemapname = "sitemap";

	$number = 0;
// foreach( $line_count as $key => $line ) {
		for ($i=0; $i < $totalfile; $i++ ){	
			// $i++;
			$sitemap[]= '
			<sitemap>
				<loc>' . $path_kb . 'sitemap/' .$sitemapname. $i . '.xml</loc>
				<lastmod>' . date( 'c' ) . '</lastmod>
			</sitemap>';
		}
			// $namesitemap[]= '' . $path_kb . 'sitemap/' .$sitemapname. $i . '.xml';
		// $i++;
	// }
	unlink('sitemapindex.xml');
	$myfile = fopen('sitemapindex.xml','wb');
	fwrite ($myfile,'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
	foreach ($sitemap as $hasil ){
		echo $hasil."<br>";
			fwrite ($myfile,$hasil);
		
	}
	$space = PHP_EOL;
	fwrite ($myfile,$space);
	fwrite ($myfile,'</sitemapindex>');
	echo '</sitemapindex>';
?>