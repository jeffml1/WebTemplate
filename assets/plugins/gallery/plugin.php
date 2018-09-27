<?
	//$_CONFIG['adminmenu']['/shop'] = 'Shop Setup';
	$_CONFIG['css'][] = 	'/assets/plugins/gallery/styles.css';
	$_CONFIG['js'][] = 		'/assets/plugins/gallery/jquery.pikachoose.min.js';
	$_CONFIG['js'][] = 		'/assets/plugins/gallery/jquery.jcarousel.min.js';
	/*if($_SESSION['admin']) {*/
		$_CONFIG['js'][] =	'/assets/plugins/gallery/gallery.admin.js';
	/*}*/
	
	class gallery {
		
		function gallery() {
			$this->position = POSITION_AFTERBODY;
		}
		function afterBody($cms) {
			if(is_numeric($cms->pag_id) && !isset($_GET['edit'])) {
				if($_SESSION['admin'] && isset($_GET['del']) && is_numeric($_GET['del'])) {
					$theQ = "DELETE FROM tblgallery WHERE gal_num = ".$_GET['del'];
					$theQ = mysql_query($theQ);
				}
				$theQ = "SELECT i.img_num, i.img_filename, i.img_name, gal_caption, gal_num FROM tblgallery g LEFT JOIN tblimages i ON (g.img_num = i.img_num) WHERE pag_num = $cms->pag_id ORDER BY gal_order";
				$theQ = mysql_query($theQ);
				if(mysql_num_rows($theQ)) {
					$i = 0;
					echo "\n\n<ul id=\"pikame\" class=\"jcarousel-skin-pika\">\n";
					while ($theR = mysql_fetch_row($theQ)) {
						$i++;
						echo "\t<li>";
						if($_SESSION['admin']) {
							echo "<a href=\"/".$cms->pag_code."?del=".$theR[4]."\" class=\"deleteicon\" onclick=\"return confirm('Are you sure you want to delete this image?')\" id=\"img_".$theR[4]."\">$i</a>";
						}
						echo "<img src=\"/upload/thumb/$theR[0]_$theR[1]\" ref=\"/upload/$theR[0]_$theR[1]\" title=\"".htmlentities($theR[2])."\" alt=\"".htmlentities($theR[3])."\" /></li>\n";
					}
					echo "</ul>\n";
					if($_SESSION['admin']) {
						echo "<a href=\"#\" class=\"action\" id=\"saveorder\" style=\"display:none\" onclick=\"saveGalOrder(); return false;\">Save photo order</a><br />";
					}
				}
	if($_SESSION['admin']) {
?>
<div style="clear:both"></div>
<a href="#" onclick="addGalPhoto(true); return false" class="action" id="addphoto">Add new photos</a>
<form id="addphotodiv" method="post" enctype="multipart/form-data" action="/_gallery/addphoto" style="display:none" onsubmit="photoUpStart()" target="uploader">
	<label>File:</label> <input type="file" name="image" /><br /><br />
	<label>Name:</label> <input name="img_name" maxlength="30" /><br /><br />
	<label>Caption:</label> <input name="gal_caption" maxlength="255" style="width:400px" /><br /><br />
	<label for="crop">Crop to Fit?</label> <input name="crop" type="checkbox" id="crop" checked="checked" /><br /><br />
	<label>&nbsp;</label> <input type="button" onclick="addGalPhoto(false)" class="cancel" value="Cancel" /><input type="submit" value="Upload Photo" class="confirm" />
	<input type="hidden" name="submit" value="1" />
	<input type="hidden" name="pag_num" value="<?=$cms->pag_id?>" />
</form>
<iframe name="uploader" src="/_gallery/addphoto" style="display:none"></iframe>
<?
	}
				
				
			}
		}
	}
	
?>