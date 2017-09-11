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
$sitemap_urls_string ="";
$arraystrreplace= array("&"," ");
$split = array_chunk($files_name,$per_page);
//$array_slice = array_slice($files_name,0,$per_page);
$strreplace = str_replace($arraystrreplace,"-",$files_name);
$babi= count($split);
echo $babi;
$value="";
$celeng="";
for ($i =0 ;$i<count($split);$i++)
{
	 foreach ($split as $word)
	 {
		foreach ($word as $wedus ){
		 $celeng .= $prefix_string.$wedus;
		 }
	 	writeFile($sitemap_header.$celeng.$sitemap_footer, $f,$fn);
	 }

$i++;
}
//$explode =print_r($split,true);
//	 	writeFile($sitemap_header.$explode.$sitemap_footer, $f,$fn);


function writeFile($sitemap_string, $f, &$fn) // the new $fn is returned to the main code
{
	$result_status = "";
	$fp = @fopen("./sitemap/sitemap$fn.$f", "wb"); // Create Sitemap File

	if($fp) // The sitemap file opened now, let's write to it.
	{
		echo $sitemap_string."<br>";
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