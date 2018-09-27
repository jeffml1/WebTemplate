<?
	$qs = $_SERVER['QUERY_STRING'];
	$qs = str_replace('&notitle','',$qs);
	if(is_numeric($qs)) {
		require_once('../../lib.php');
		$theQ = "SELECT pag_num, prd_num, prd_code, prd_name, prd_detail, i.img_num, i.img_filename FROM tblproduct p LEFT JOIN tblimages i ON (p.img_num = i.img_num) WHERE prd_num = ".$qs;
		$theQ = mysql_query($theQ);
		$theR = mysql_fetch_assoc($theQ);
?>
<a href="javascript:window.close()" class="lbAction" rel="deactivate">
<img src="/upload/<?=$theR['img_num']?>_<?=$theR['img_filename']?>" alt="<?=htmlentities($theR['prd_name'])?>" />
</a>
<?
	}
?>