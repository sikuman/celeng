<?php

	$f = "xml"; // $_POST[format]

	$ms = 50000; // max size
	// in this (non-sensical) example the initial output is 2733 bytes long.
	// try various numbers (smaller than that, like 1000 or 2000) to see the results.
	// if you make it too small, it will not function very well... (1 record per output file, first file empty)

	// when you're done testing, take out the print statements as they may affect other output processing

	$fn = ""; // file number, starts empty, then 1, 2, etc

	$path_kb = "";


	if($f=="xml")
	{
		$prefix_string 	= "<url>\n<loc>";
		$postfix_string	= "</loc>\n<priority>$_POST[priority]</priority>\n<lastmod>$_POST[lastmodified]</lastmod>\n<changefreq>$_POST[frequency]</changefreq>\n</url>\n";
		$sitemap_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
			"<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";
		$sitemap_footer = "</urlset>\n";
	}
	elseif($f=="txt")
	{
		$prefix_string 	= "";
		$postfix_string	= "\n";
		$sitemap_header = "";
		$sitemap_footer = "";
	}
	elseif($f=="html")
	{
		$prefix_string 	= "";
		$postfix_string	= "<br>\n";
		$sitemap_header = "<html>\n".
			"<head>\n<title>Sitemap</title>\n</head>".
			"<body>\n".
			"<h1>Sitemap</h1>\n";
		$sitemap_footer .= "</body>\n</html>";
	}

	$baselen = strlen($sitemap_header) + strlen($sitemap_footer);

	$sitemap_urls_string = "";

	// process the list of standard pages

	foreach(array("","index.html","register.html","login.html","contact-us.html","about-us.html","advertise.html","success-stories.html","testimonials.html","careers.html","terms-conditions.html","privacy-policy.html","payment-options.html","sitemap.html") as $record)
	{
		// $sitemap_url_string = "$prefix_string$path_kb$record"."$postfix_string";
		// if (strlen($sitemap_urls_string) + strlen($sitemap_url_string) + $baselen > $ms)
		// {
			// writeFile($sitemap_header.$sitemap_urls_string.$sitemap_footer, $f, $fn);
			// $sitemap_urls_string = "";
		// }
		// $sitemap_urls_string .= $sitemap_url_string;
	}

	// process the entries from the database, represented here by 9 dummy records

	// foreach(array(1,2,3,4,5,6,7,8,9) as $record)
	// {
		// $sitemap_url_string = "$prefix_string$path_kb$record"."$listing_city_name-$listing_locality_name-$listing_subcat_name-$listing_com_name-$listing_id.html"."$postfix_string";
		// if (strlen($sitemap_urls_string) + strlen($sitemap_url_string) + $baselen > $ms)
		// {
			// writeFile($sitemap_header.$sitemap_urls_string.$sitemap_footer, $f, $fn);
			// $sitemap_urls_string = "";
		// }
		// $sitemap_urls_string .= $sitemap_url_string;
	// }
	writeFile($sitemap_header.$sitemap_urls_string.$sitemap_footer, $f, $fn);
	print "Total number of output files = ".$fn;


// function to avoid repetition of code

function writeFile($sitemap_string, $f, &$fn) // the new $fn is returned to the main code
{
	$result_status = "";

	$fp = @fopen("./sitemap$fn.$f", "wb"); // Create Sitemap File

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