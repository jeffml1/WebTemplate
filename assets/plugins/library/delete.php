<?
	ob_start();
	include('../../top.php');
	ob_end_clean();
	if($_SESSION['admin'] && is_numeric($_GET['num'])) {
		if($_GET['type'] == "folder") {
			$theQ = "DELETE FROM tblfolders WHERE fol_num = ".mysql_real_escape_string($_GET['num']);
			$theQ = mysql_query($theQ);
		} else {
			$theQ = "SELECT file_num, file_filename FROM tblfiles WHERE file_num = ".mysql_real_escape_string($_GET['num']);
			//echo $theQ;
			$theQ = mysql_query($theQ);
			$theR = mysql_fetch_row($theQ);
			if($theR) {
				//$file = $theR[0].'_'.$theR[1];
				$filepath = $_SERVER['DOCUMENT_ROOT'].'/upload/library/'.$theR[0].'_'.$theR[1];
				$ok = unlink($filepath);
				if($ok) {
					$subQ = "DELETE FROM tblfiles WHERE file_num = ".mysql_real_escape_string($_GET['num']);
					$subQ = mysql_query($subQ);
					echo "1";
				} else echo "0";
			} else echo "0";
		}
	}
?>