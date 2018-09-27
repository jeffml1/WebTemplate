<?
//Handle Uploads
$folder = $_SERVER['DOCUMENT_ROOT'].'/upload/files/';
/*global $CONFIG;
if($CONFIG['siteMembers']){
	require $_SERVER['DOCUMENT_ROOT'].'/assets/functions/encryption.php';
	$crypt = new encryption_class;
}*/



if(isset($_FILES['newfile'])){
	$target = $folder.basename($_FILES['newfile']['name']);
	if(move_uploaded_file($_FILES['newfile']['tmp_name'],$target)){
		echo '<h3 style="float:right;">The file '.basename($_FILES['newfile']['name']).' has been uploaded</h3>';
?>
<script type="text/javascript">
	window.onload = function() {
		insertFile1('<?=basename($_FILES['newfile']['name'])?>')
	}
</script>
<?
	} else {
		echo '<h3 style="float:right;">Error uploading file, the maxium file size is 5mb</h3>';
	}
}

?>
<h2>File Browser</h2>
<p>Click on the name of the file you wish to link to. You may upload new files from the form at the bottom of the dialogue</p>

<table style="width:100%; text-align:right">
	<tr>
		<th style="text-align:left">
			Filename
		</th>
		<th style="width:100px;">
			Filesize
		</th>
		<!--<th style="width:100px;">Delete File</th>-->
	</tr>

<?
$files = array_diff(scandir($folder), array('.','..'));
$foldersize=0;

/*
$encrypt_result = $crypt->encrypt($key, $string, $stringlength);
$errors = $crypt->errors;
$decrypt_result = $crypt->decrypt($key, $encrypt_result);
$errors = $crypt->errors;
	*/
	
foreach ($files as $key => $value){
	$foldersize+=filesize($folder.$value);
	if($key%2==0){
		echo'<tr style="background:#eeeeee"><td style="text-align:left">';
	} else{
		echo'<tr><td style="text-align:left">';
	}
	echo "<a href=\"#\" onclick=\"insertFile1('$value'); return false\" />";
	echo $value;
	echo'</a></td><td>';
		echo number_format(filesize($folder.$value)/1024,0).'KB';		
	echo"</td>";
//	echo "<td><a href=\"#\" onclick=\"deleteFile('$value'); return false\"><img src=\"interface/images/cross.gif\" /></a></td>";
	echo "</tr>\n\n";
}
?>

</table>
<br />
<br />

<label for="newfile">Upload New File</label>
<form enctype="multipart/form-data" action="index.php?p=file" method="post">
	<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
	<input onchange="uploadFile(this)" type="file" name="newfile" id="newfile" />
	<input type="submit" value="Upload File" />
</form>