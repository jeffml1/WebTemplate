<?
	ob_start();
	include('../../top.php');
	ob_end_clean();
	if($_SESSION['admin']) {
		//$i = 0;
		$page = mysql_real_escape_string($_POST['page']);
		$fol = explode(",",$_POST['folders']);
		foreach ($fol as $fol_num) {
			$item = explode(":",$fol_num);
			if($item[0]) {
				$theQ = "UPDATE tblfolders SET fol_order = $item[2] ";
				if(!$item[1]) {
					$theQ .=",pag_id = $page, fol_parent = 0 ";
				} else {
					$theQ .=",pag_id = 0, fol_parent = $item[1] ";
				}
				$theQ .= "WHERE fol_num = $item[0]";
				$theQ = mysql_query($theQ);
			}
		}
		$file = explode(",",$_POST['files']);
		foreach ($file as $file_num) {
			$item = explode(":",$file_num);
			if($item[0]) {
				$theQ = "UPDATE tblfiles SET fol_num = $item[1] WHERE file_num = $item[0]";
				$theQ = mysql_query($theQ);
			}
		}
	}
?>