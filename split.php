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

// echo $files_name;

$arraystrreplace= array("&"," ");
$singletxt = array($files_name);
$strreplace = str_replace($arraystrreplace,"-",$files_name); 

foreach ($strreplace as $kiwod){
	// echo $kiwod;
			$sitemap_url_string = "$prefix_string$path_kb$slug$kiwod"."$postfix_string";
	if (count($singletxt) == $per_page){
		echo "jancuuk";
	}else {
			writeFile($sitemap_header.$sitemap_url_string.$sitemap_footer, $f, $fn);
		// echo "wedus";
	}		
		}
function writeFile($sitemap_string, $f, &$fn) // the new $fn is returned to the main code
{
	$result_status = "";
	$files_name= file_get_contents('keywords/keywords.txt' );
	$singletxt = array($files_name);
echo "panggil fungsi write";
	$fp = @fopen("./sitemap/sitemap$fn.$f", "wb"); // Create Sitemap File

	if($fp) // The sitemap file opened now, let's write to it.
	{
	// if($singletxt == $per_page){	
		@fwrite($fp, $sitemap_string);
		@fclose($fp);
		$result_status = "success";
		// }
	}
	else // Failed to Open File for Writing
	{
		$result_status = "failed";
	}

	print date("Y-m-d H:i:s").", result_status: $result_status, file: sitemap$fn.$f, len = ".strlen($sitemap_string)."<br>\n";

	$fn++;
}

?>