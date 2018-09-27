<?
	ob_start();
	include('../../top.php');
	ob_end_clean();
	if($_SESSION['admin']) {
		$i = 0;
		$g = explode(",",$_GET['l']);
		foreach ($g as $gal_num) if (is_numeric($gal_num)) {
			$i++;
			$theQ = "UPDATE tblgallery SET gal_order = $i WHERE gal_num = $gal_num";
			$theQ = mysql_query($theQ);
		}

	}
?>