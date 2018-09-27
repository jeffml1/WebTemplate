<?
	include('../../../lib.php');
	connectToDB();
	
	$uploadDir = '../../../../upload/';
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ProImage Manager</title>
<style type="text/css">
.toolbar {padding:5px; border:1px solid #bbb; background:#eee; clear:both; width:608px}
#galleries { padding: 0px 5px; background:#eee; border:1px solid #ccc; border-right:0; width:200px; position:fixed}
#library {width:624px;}
a{font-size:16px;}
a img{border:0}

a{text-decoration:none;}
.container {padding:5px; background:#FFFFFF; border:1px solid #ccc; position:absolute; left:219px}
#delcat{display:none; padding:none; margin:10px 0px 0px 0px;}



</style>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/custom.js"></script>

</head>
<body>

<?

	if(isset($_POST['upload']) && isset($_FILES['img_data'])){
		if($_POST['resize']=="on"){
			if(is_numeric($_POST['i_height']) && $_POST['i_height']>0 && $_POST['i_height']<1500) $height = $_POST['i_height'];
			else $height = 9999;
			if(is_numeric($_POST['i_width']) && $_POST['i_width']>0 && $_POST['i_width']<1500) $width = $_POST['i_width'];
			else $width = 9999;
			$resize = " -resize '${width}x${height}>'";
		} else {
			$resize = '';
		}
		switch ($_FILES['img_data']['type']) {
			case "image/gif" 	:	$outype = 'gif';
									$force = '';
									break;
			case "image/jpeg"	: 
			case "image/pjpeg"	:	$outype = 'jpg';
									$force = '';
									break;
			case "image/png"	:	$outype = 'png';
									$force = 'PNG8:';
									break;
			default: $outype = 'jpg';
		}

		$name = str_replace(' ','_',$_FILES['img_data']['name']);
		$tempfile = $uploadDir.microtime(true).rand(1000,9999);
		move_uploaded_file($_FILES['img_data']['tmp_name'],$tempfile.".$outype");

		exec('convert '.$tempfile.".$outype"." -thumbnail '150x150>' -background white -gravity center -extent 150x150 $force".$tempfile."t.$outype");
		exec('convert '.$tempfile.".$outype"."$resize $force".$tempfile."f.$outype");

		if (is_numeric($_POST['cat_num']) && $_POST['cat_num']) {
			$cat_num = $_POST['cat_num'];
		} elseif (isset($_POST['cat_name'])) {
			if($_POST['cat_name'] == '' || $_POST['cat_name'] == ' '){
				$_POST['cat_name'] = 'Images';
			} 
			$theQ = "INSERT INTO tblimagecat (cat_name) VALUES ('".mysql_real_escape_string($_POST['cat_name'])."')";
			$theQ = mysql_query($theQ);
			$cat_num = mysql_insert_id();	
		} else $cat_num = 1;
		
		$size = getimagesize($tempfile."f.$outype");
		
		$theQ = "INSERT INTO tblimages (cat_num, img_type, img_filename,img_name,img_width,img_height) VALUES ($cat_num,'".mysql_real_escape_string($_FILES['img_data']['type'])."','".mysql_real_escape_string($name)."','".mysql_real_escape_string($_POST['img_name'])."',".$size[0].",".$size[1].")" ;
		$theQ = mysql_query($theQ);
		
								
		$id = mysql_insert_id();
		rename($tempfile."f.$outype",$uploadDir.$id.'_'.$name);
		rename($tempfile."t.$outype",$uploadDir.'thumb/'.$id.'_'.$name);
		unlink($tempfile.".$outype");
			
		$_GET['cat'] = $cat_num;
		echo '<script type="text/javascript"> window.onload = function() { insertimg(\''.urlencode($id.'_'.$name).'\',\''.htmlentities($_POST['img_name']).'\',\''.$_SERVER['HTTP_HOST'].'\','.$size[0].','.$size[1].')} </script>';
		echo '</body></html>';
		die();
		
	} else if(!isset($_GET['cat'])){
		echo '<script type="text/javascript"> window.onload = function() { addImage(); } </script>';
	}
	// onclick="deleteCategory('.$_GET['cat'].',\''.$catname.'\'); return false"
	if(isset($_GET['delcat']) && is_numeric($_GET['delcat'])){
		$theQ = "DELETE FROM tblimages WHERE cat_num =".mysql_real_escape_string($_GET['delcat']);
		$theR = mysql_query($theQ);
		if(!$theR){
			echo "<h2>Error deleting images</h2>";
		}
		$theQ = "DELETE FROM tblimagecat WHERE cat_num =".mysql_real_escape_string($_GET['delcat']);
		$theR = mysql_query($theQ);
		if(!$theR){
			echo "<h2>Error deleting category</h2>";
		}
	}	
	

?>

<div id="galleries">
<h2>Categories</h2>
<?	
	$num = 0;
	$theQ = "SELECT cat_num, cat_name FROM tblimagecat ORDER BY cat_name";
	$theQ = mysql_query($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		if(!$num) $num = $theR[0];
		echo '<a href="#" onclick="getPage('.$theR[0].'); return false;">'.$theR[1].'</a><br />';	
	}
?><br />
<a href="#" onclick="addImage(<?=$num?>); return false;">Add Image</a>
</div>
<div class="container" id="library">
<?
	$supress = true;
	include('getpage.php');
?>
</div>
<div class="container" id="uploader" style="display:none">
	<div style="text-align:center; width:608px" class="toolbar"><h2 style="padding:0px">Upload Image</h2></div>
<form action="image.php" method="post" enctype="multipart/form-data">

	<input type="hidden" name="upload" value="1" />
	<h4>Image</h4>
	<input name="img_data" type="file" onchange="imageName(this.value)" />
	<h4>Image Name</h4>
	<input name="img_name" id="img_name" />
	<h4>Category</h4>
	<select name="cat_num" onchange="checkCat()" id="cat_num">
<?
	$theQ = "SELECT cat_num, cat_name FROM tblimagecat ORDER BY cat_name";
	$theQ = mysql_query($theQ);
	$newcat = mysql_num_rows($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		echo "<option value=\"$theR[0]\">$theR[1]</option>";
	}
?>	
	<option value="0">Add New Category</option>
	</select>
	<input name="cat_name" style=" <?=($newcat == 0)?"":"display:none";?>" id="cat_name" />
	<h4><label>Resize Image
	<input type="checkbox" name="resize" onchange="defineDimensions(this.checked)" /></label></h4>
	<div style="display:none" id="i_div">
		Width: <input type="text" name="i_width" id="i_width" style="width:50px;" />px x 
		Height: <input type="text" name="i_height" id="i_height" style="width:50px;" />px<br /><br />
		<img src="ruler.png" /><br /><br />
		If <strong>both</strong> dimensions are defined the image will be as big as it can while maintaining its proportions.<br />
		If <strong>one</strong> dimension is defined that side will be the specified size, and the proportion is maintained
		
	</div><br />
	<input type="submit" name="upload" />
</form>

</div>
</body>
</html>

<?
?>