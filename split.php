<?php
	ini_set('memory_limit', '-1');
// header( "Content-type: text/xml" ); 
	$f = "xml"; // $_POST[format]
	$per_page = 10; // max size
	$files_name= file('keywords/keywords.txt' );
	$priority = "0.5" ;
	$lastmodified =  date( 'c' );
	$changefreq ="daily";
	$path_kb = "http://fullhdfree.xyz/";
	$slug = "video/";
	// $exten =".html";
	
	// $filectime = filectime($files_name);

	$fn = "1"; // file number, starts empty, then 1, 2, etc
	

		$prefix_string 	= "<url>\n<loc>";
		$postfix_string	= "</loc>\n<priority>$priority</priority>\n<lastmod>$lastmodified</lastmod>\n<changefreq>$changefreq</changefreq>\n</url>\n";
		$sitemap_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
			"<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\" xmlns:video=\"http://www.google.com/schemas/sitemap-video/1.1\">\n";
		$sitemap_footer = "</urlset>\n";
$sitemap = array();
foreach($files_name as $url){
		$sitemap_urls_string = "";
		// if (strlen($sitemap_urls_string) + strlen($sitemap_url_string) + $baselen > $ms)
		$hitung = count($url);
		echo $hitung;
		// if ($hitung > $per_page)
		// {
		$sitemap_url_string = "$prefix_string$path_kb$slug$url"."$postfix_string";
			writeFile($sitemap_header.$sitemap_url_string.$sitemap_footer, $f, $fn);
			// $sitemap_urls_string = "";
		// }
		$sitemap_urls_string .= $sitemap_url_string;
		
		**** array di pecah dadi 10 terus di pecah meneh lagi di write **** besok coba ini
    // }
}

	$page_numbers = range(1,count($pages));
 foreach ($pages as $pg_num){
	    echo "\t<sitemap>\n";
        echo "\t\t<loc>" .htmlentities($url['loc']) . "</loc>\n";
        echo "\t\t<lastmod>$lastmodified</lastmod>\n";
        echo "\t</sitemap>\n";
// echo $pg_num ."<br>";
 }

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