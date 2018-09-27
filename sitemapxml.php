<?
	include('assets/lib.php');
	$cms = new simpleCMS();
	header("Content-type:text/xml");
?>
<?='<?xml version="1.0" encoding="UTF-8"?>'?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?
	function doMenu($id = "", $parent = 0, $priority) {
		global $_CONFIG;
		global $cms;
		$first = true;
		//echo "<ul".($id?" class=\"$id\"":"").">\n";
		$hasactive = false;
		if($cms->pagestructure[$parent]) foreach($cms->pagestructure[$parent] as $m) if($showhidden || !in_array($m[0],$_CONFIG['skippages'])) {
			$act = false;
			echo "<url>\n";
			echo "<loc>http://".$_SERVER['HTTP_HOST']."/".$m[2]."</loc>\n";
			echo "<changefreq>monthly</changefreq>\n";
			echo "<priority>$priority</priority>\n";
			echo "</url>\n";
			doMenu('',$m[0],$priority-0.1);
		}
	}

	doMenu('sitemap',0,1);
?>
</urlset>
