<?
	require_once('../../lib.php');
	if(isset($_GET['num']) && is_numeric($_GET['num'])) {
		$theQ = "SELECT pag_num, prd_num, prd_code, prd_name, prd_detail, i.img_num, i.img_filename FROM tblproduct p LEFT JOIN tblimages i ON (p.img_num = i.img_num) WHERE prd_num = ".$_GET['num'];
		$theQ = mysql_query($theQ);
		$theR = mysql_fetch_assoc($theQ);
		$_GET['pag_num'] = $theR['pag_num'];
	} else {
		header('Location:/');
		die();
	}
?>
<?
	include('../../top.php');
?>
<div class="breadcrumb">
<?
		$cms->doBreadCrumb();
		echo " &raquo; ";
		echo "<a href=\"/_shop/".$theR['prd_num']."-".preg_replace('~[^A-Za-z0-9_]~','',str_replace(' ','_',$theR['prd_name']))."\">".htmlentities($theR['prd_name'])."</a>";
?>
</div>
<h2><?=htmlentities($theR['prd_name'])?></h2>
<div style="float:right; margin-left:10px">
<?
	if($theR['img_num']) {
?>
<img src="/upload/<?=$theR['img_num']?>_<?=$theR['img_filename']?>" alt="<?=htmlentities($theR['prd_name'])?>" class="shopimage" />
<?
	}
?>
<h3>Buy Online Now</h3>
<? $plugins['shop']->displayPricing($theR['prd_num']); ?>
</div>
<?=$theR['prd_detail']?>
<?
	include('../../bottom.php');
?>