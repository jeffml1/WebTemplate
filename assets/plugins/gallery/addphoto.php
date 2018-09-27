<?
	ob_start();
	include('../../top.php');
	ob_end_clean();
	if($_SESSION['admin'] && isset($_POST['submit'])) {
		if(!is_uploaded_file($_FILES['image']['tmp_name'])) {
?>
	<script type="text/javascript">
		parent.photoUpDone('')
		alert('Please select a file to upload')
	</script>
<?
		} else {
			switch ($_FILES['image']['type']) {
				case "image/gif" 	: $outype = 'gif'; break;
				case "image/jpeg"	: $outype = 'jpg'; break;
				case "image/pjpeg"	: $outype = 'jpg'; break;
				case "image/png"	: $outype = 'png'; break;
			}
			if(!$outype) {
?>
	<script type="text/javascript">
		parent.photoUpDone('')
		alert('Please upload JPG, GIF or PNG images only')
	</script>
<?			
			} else {
				$name = str_replace(' ','_',$_FILES['image']['name']);
				$uploadDir = '../../../upload/';
				$tempfile = $uploadDir.microtime(true).rand(1000,9999);
				move_uploaded_file($_FILES['image']['tmp_name'],$tempfile.".$outype");
				
				$size = getimagesize($tempfile.".$outype");
				
				$w = 150;
				$h = 150;
				if($size[0] > $size[1]) {
					$w = 9999;
				} else {
					$h = 9999;
				}
				
				exec('convert '.$tempfile.".$outype"." -thumbnail ${w}x${h}> -background white -gravity center -extent 150x150 ".$tempfile."t.$outype");
				
				
				$w = 958;
				$h = 400;
				if(isset($_POST['crop'])) {
					if($size[0]/$size[1] > 958/400) {
						$w = 99999;
					} else {
						$h = 99999;
					}
				}
				
				exec('convert '.$tempfile.".$outype"." -resize ${w}x${h}> -background white -gravity center -extent 958x400 ".$tempfile."f.$outype");
	
				$theQ = "INSERT INTO tblimages (cat_num, img_type, img_filename,img_name,img_width,img_height) VALUES (8,'".mysql_real_escape_string($_FILES['image']['type'])."','".mysql_real_escape_string($name)."','".mysql_real_escape_string($_POST['img_name'])."',958,400)" ;
				$theQ = mysql_query($theQ);
				
				$img_num = mysql_insert_id();
				rename($tempfile."f.$outype",$uploadDir.$img_num.'_'.$name);
				rename($tempfile."t.$outype",$uploadDir.'thumb/'.$img_num.'_'.$name);
				unlink($tempfile.".$outype");
				
				$theQ = "SELECT MAX(gal_order) FROM tblgallery WHERE pag_num = ".mysql_real_escape_string($_POST['pag_num']);
				$theQ = mysql_query($theQ);
				$gal_order = mysql_result($theQ,0)+1;
				
				$theQ = "INSERT INTO tblgallery (pag_num, img_num, gal_order, gal_caption) VALUES (".mysql_real_escape_string($_POST['pag_num']).", $img_num, $gal_order, '".mysql_real_escape_string($_POST['gal_caption'])."')";
				$theQ = mysql_query($theQ);
				$gal_num = mysql_insert_id();
?>
	<script type="text/javascript">
		parent.photoUpDone('<a href=\"?del=<?=$gal_num?>" class="deleteicon" onclick="return confirm(\'Are you sure you want to delete this image?\')" id="img_<?=$gal_num?>">[NUM]</a><img src="/upload/thumb/<?=$img_num.'_'.$name?>" ref="/upload/<?=$img_num.'_'.$name?>" alt="<?=htmlentities($_POST['gal_caption'])?>" title="<?=htmlentities($_POST['img_name'])?>" />')
	</script>
<?		
			}
		}
	}
?>