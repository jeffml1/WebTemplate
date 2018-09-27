<?
	include('../../../lib.php');
	if(isset($_GET['i']) && is_numeric($_GET['i'])) {
		connectToDB();
		$theQ = "SELECT img_data_thumb, img_type FROM tblimages WHERE img_num = ".$_GET['i'];
		$theQ = mysql_query($theQ);
		$theR = mysql_fetch_row($theQ);
		header('Content-type: '.$theR[1]);
		echo $theR[0];
	}
?>