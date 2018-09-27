<?
	ob_start();
	include('../../top.php');
	ob_end_clean();
	
	$num = $_GET['num'];
	$name = $_GET['name'];
	
	//check num
	if(!is_numeric($num)){
		header("Status: 404 Not Found");
		die();
	}
	
	//check name
	$theQ = "SELECT file_name, file_protected, file_filename FROM tblfiles WHERE file_num = ".mysql_real_escape_string($num);
	$theQ = mysql_query($theQ);
	$theR = mysql_fetch_row($theQ);	
	if($theR[2] != $name) {
		header('Location:'.$_SERVER['DOCUMENT_ROOT'].'/upload/library/'.$num.'_'.$theR[2]);
		die();
	}
	
	//check it's unprotected OR $_SESSION['mem_num'] is set
	if($theR[1] == 0 || $_SESSION['mem_num']) {
		//Output the file
		$pos = strpos($file_filename, '.');
		$type = substr($file_filename, $pos+1);
		//echo "blah";
		
		switch ($type) {
			case '.doc':
			case '.docx':
				header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
				break;
			case '.xls':
			case '.xlsx':
				header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				break;
			case '.pps':
			case '.ppsx':
				header('Content-type: application/vnd.openxmlformats-officedocument.presentationml.slideshow');
				break;
			case '.ppt':
			case '.pptx':
				header('Content-type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
				break;
			case '.pdf':
				header('Content-type: application/pdf');
				break;
			case '.dwg':
				header('Content-type: application/acad');
				break;
			case '.gif':
				header('Content-type: image/gif');
				break;
			case '.jpe':
			case '.jpeg':
			case '.jpg':
				header('Content-type: image/jpeg');
				break;
			case '.png':
				header('Content-type: image/png');
				break;
			default:
				header('Content-type: text/plain');
		}

		// It will be called downloaded.pdf
		$file = $name.$type;
		header('Content-Disposition: attachment; filename="'.$file.'"');
		
		header('Content-length:'.filesize($_SERVER['DOCUMENT_ROOT'].'/upload/library/'.$num.'_'.$file));

		// The PDF source is in original.pdf
		readfile($_SERVER['DOCUMENT_ROOT'].'/upload/files/'.$num.'_'.$file);
		if($_SESSION['mem_num']) {
			$theQ = "INSERT INTO tbldownloads (file_num, dl_ipaddress, dl_useragent, dl_date, mem_num) VALUES (".mysql_real_escape_string($num).", '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."', NOW(), ".$_SESSION['mem_num'].")";
		}else
			$theQ = "INSERT INTO tbldownloads (file_num, dl_ipaddress, dl_useragent, dl_date) VALUES (".mysql_real_escape_string($num).", '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."', NOW())";
		$theQ = mysql_query($theQ);
	}
	else {
		//Direct to a login form
		header("Location:/_login?r=".$num.'_'.$theR[2]);
	}
	
	
?>