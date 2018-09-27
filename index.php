<?
	include('assets/top.php');
?>
<?
	/*if($cms->pag_code!='Home') {
		echo "<div class=\"breadcrumb\">";
		$cms->doBreadCrumb();
		echo "</div>";
	}*/
	$cms->doBody();
?>
<?
	include('assets/bottom.php');
?>