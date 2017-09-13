<?php
	ini_set('memory_limit', '-1');
// header( "Content-type: text/xml" ); 
	$f = "xml"; // $_POST[format]
	$files_name= file('keywords/keywords.txt' );
	$priority = "0.5" ;
	$lastmodified =  date( 'c' );
	$changefreq ="daily";
	$path_kb = "http://fullhdfree.xyz/";
	$slug = "video/";
	$per_page = 10; // max size

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

		
		$counter = 0;
		
		while ($chunk = array_splice($sitemap_url_string,0,$per_page)){
			$f=fopen("out".($counter++),"w");
			fputs($f,implode("",$chunk));
			// echo $chunk;
			fclose($f);
		}
?>