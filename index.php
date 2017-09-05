<?php 

global $query;
require '/custom.php';
require '/split.php';
$limit = 40000;
// $path_kb = 'www.google.com';
header( 'Cache-Control: public' );
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 86400 ) . ' GMT' );
header( "Content-type: text/xml" ); 
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	
	$files = glob('sitemap/*.xml' );	
	$totalfile = count($files);
	echo $totalfile;
		
	
	
	foreach( $files as $file ) {
		$line_count[] = lines_count( $file );
	}
	$sitemapname = "sitemap";

	$rows = array_sum( $line_count );
	$total = ceil( $rows / $limit );
	$number = 0;
foreach( $line_count as $key => $line ) {
		// for( $i = 1; $i <= ( ceil( $line / $limit ) ); $i++ ) {
		for ($i=1; $i< $totalfile; $i++ ){	
			$number++;
			// echo '
			// <sitemap>
				// <loc>' . $domain . '/sitemap/' . ( $key + 1 ) . '-' . $i . '.xml</loc>
				// <lastmod>' . date( 'c' ) . '</lastmod>
			// </sitemap>
			// ';
			$sitemap[]= '
			<sitemap>
				<loc>' . $path_kb . 'sitemap/' .$sitemapname. $i . '.xml</loc>
				<lastmod>' . date( 'c' ) . '</lastmod>
			</sitemap>';
			$namesitemap[]= '' . $path_kb . 'sitemap/' .$sitemapname. $i . '.xml';
		}
	}
	unlink('sitemapindex.xml');
	$myfile = fopen('sitemapindex.xml','a+');
	fwrite ($myfile,'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
	foreach ($sitemap as $hasil ){
		echo $hasil;
			fwrite ($myfile,$hasil);
		
	}
	$space = PHP_EOL;
	fwrite ($myfile,$space);
	fwrite ($myfile,'</sitemapindex>');
	echo '</sitemapindex>';
	
?>
