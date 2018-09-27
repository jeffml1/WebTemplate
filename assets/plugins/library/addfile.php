<?
	ob_start();
	include('../../top.php');
	ob_end_clean();
	
	if($_SESSION['admin'] && isset($_POST['file_submit'])) {
		$file_filename = $_FILES["file"]["name"];
		$filename = $_POST['file_name'];
		if (isset($_POST['protected'])) 
			$protected = 1;
		else
			$protected = 0;
			
		$parentfolder = $_POST['fol_num'];
		
		if(!is_uploaded_file($_FILES['file']['tmp_name'])) {
		?>
			<script type="text/javascript">
				alert('Please select a file to upload')
			</script>
		<?
		} else {			
			$file_filename = str_replace(' ','_',$file_filename);
			$file_filename = $file_filename;
			$uploadDir = '../../../upload/library/';
			$theQ = "INSERT INTO tblfiles (fol_num, file_name, file_filename, file_order, file_protected, file_dateadded) VALUES (".mysql_real_escape_string($parentfolder).",'".mysql_real_escape_string($filename)."','".mysql_real_escape_string($file_filename)."',0,".mysql_real_escape_string($protected).",NOW())" ;
			$theQ = mysql_query($theQ);
			
			$file_num = mysql_insert_id();
			$file_filename = $file_num."_".$file_filename;		
			
			move_uploaded_file($_FILES['file']['tmp_name'],$uploadDir.$file_filename);
			$pos = strpos($file_filename, '.');
			$type = substr($file_filename, $pos+1);
?>
			<script type="text/javascript">
				parent.addfiledone('<?=$file_filename?>', '<?=$filename?>', '<?=$parentfolder?>', '<?=$type?>', '<?=$file_num?>')
			</script>
<?		
		}
	}
?>