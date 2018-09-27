<?
	//sleep(1);	
	if (!$supress) {
	include('../../../lib.php');
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		connectToDB();
	}
	if(isset($_GET['del']) && is_numeric($_GET['del'])){
		$theQ = "DELETE FROM tblimages WHERE img_num = ".mysql_real_escape_string($_GET['del']);
		$theR = mysql_query($theQ);
	}	
	?>
	
	<?
	if(isset($_GET['cat']) && is_numeric($_GET['cat'])) {
		$theQ = "SELECT cat_name FROM tblimagecat WHERE cat_num = ".$_GET['cat'];
		$theQ = mysql_query($theQ);

		$catname = mysql_result($theQ,0);
		echo '<div style="text-align:center; margin-bottom:4px;" class="toolbar"><h2>'.$catname.'</h2></div>';
		
		$theQ = "SELECT img_num, img_name, img_filename, img_name, img_width, img_height FROM tblimages WHERE cat_num = ".$_GET['cat'];
		$theQ = mysql_query($theQ);
		while ($theR = mysql_fetch_row($theQ)) {
			echo '<div style="width:150px; height:150px; border:1px #cccccc solid; margin:0 4px 4px 0; float:left; ">
						<a href="#" onclick="if(confirm(\'Are you sure you want to delete this image?\')) getPage('.$_GET['cat'].','.$theR[0].'); return false;" style="position:absolute; margin:130px 0 0 130px;">
							<img src="interface/images/cross.gif">
						</a>
					<div id="img'.$theR[0].'" style="text-align:center">		
						<a href="#" onclick="insertimg(\''.urlencode($theR[0].'_'.$theR[2]).'\',\''.$theR[3].'\',\''.$_SERVER['HTTP_HOST'].'\','.$theR[4].','.$theR[5].'); return false" style="background:url(\'/upload/thumb/'.$theR[0].'_'.$theR[2].'\') no-repeat center; width:150px; height:150px; display:block;" title="'.$theR[1].'">&nbsp;</a>	
					</div>
				</div>';
		}

		echo '<div style="text-align:right;" class="toolbar">
		<div style="float:left; text-align:left">
			<a onclick="hide(\'delcat\'); return false;" href="#">Delete Category</a>
			<p id="delcat">Are you sure?
			<a href="image.php?delcat='.$_GET['cat'].'"><img src="interface/images/tick.gif"></a>
			<a href="#" onclick="hide(\'delcat\')"><img src="interface/images/cross.gif"></a></p>
			</div>
			
			
			';
		echo '<a href="#" onclick="addImage('.$_GET['cat'].'); return false">Add Image</a>
</div>';
	}

?>
