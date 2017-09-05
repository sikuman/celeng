<?php
// header( "Content-type: text/xml" ); 
	$f = "xml"; // $_POST[format]
	$ms = 2000000; // max size
	$files2= file('keywords/keywords.txt' );
	$priority = "0.5" ;
	$lastmodified =  date( 'c' );
	$changefreq ="daily";
	$path_kb = "http://fullhdfree.xyz/";
	$slug = "video/";
	// $exten =".html";
	

	$fn = "1"; // file number, starts empty, then 1, 2, etc


		$prefix_string 	= "<url>\n<loc>";
		$postfix_string	= "</loc>\n<priority>$priority</priority>\n<lastmod>$lastmodified</lastmod>\n<changefreq>$changefreq</changefreq>\n</url>\n";
		$sitemap_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
			"<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\" xmlns:video=\"http://www.google.com/schemas/sitemap-video/1.1\">\n";
		$sitemap_footer = "</urlset>\n";

	$baselen = strlen($sitemap_header) + strlen($sitemap_footer);

	$sitemap_urls_string = "";
	$start =0;
	$arraystrreplace= array("&"," ");
	$singletxt = array_slice($files2,$start,$ms);
	$strreplace = str_replace($arraystrreplace,"-",$singletxt); 
	foreach ($strreplace as $word){
			echo var_dump($word)."<br>";

		$sitemap_url_string = "$prefix_string$path_kb$slug$word"."$postfix_string";
		if (strlen($sitemap_urls_string) + strlen($sitemap_url_string) + $baselen > $ms)
		{
			writeFile($sitemap_header.$sitemap_urls_string.$sitemap_footer, $f, $fn);
			$sitemap_urls_string = "";
		}
		$sitemap_urls_string .= $sitemap_url_string;
	}

	writeFile($sitemap_header.$sitemap_urls_string.$sitemap_footer, $f, $fn);
	print "Total number of output files = ".$fn;


// function to avoid repetition of code

function writeFile($sitemap_string, $f, &$fn) // the new $fn is returned to the main code
{
	$result_status = "";

	$fp = @fopen("./sitemap/sitemap$fn.$f", "wb"); // Create Sitemap File

	if($fp) // The sitemap file opened now, let's write to it.
	{
		@fwrite($fp, $sitemap_string);
		@fclose($fp);
		$result_status = "success";
	}
	else // Failed to Open File for Writing
	{
		$result_status = "failed";
	}

	print date("Y-m-d H:i:s").", result_status: $result_status, file: sitemap$fn.$f, len = ".strlen($sitemap_string)."<br>\n";

	$fn++;
}

?>