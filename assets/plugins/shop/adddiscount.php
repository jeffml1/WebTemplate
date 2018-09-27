<?
	if(isset($_GET['notitle']) || $_POST['ajax']) ob_start();
	include('../../top.php');
	if(isset($_GET['notitle']) || $_POST['ajax']) ob_end_clean();
	
	if(isset($_POST['dsc_num'])) {
		$dsc_num = $_POST['dsc_num'];
		/*if(is_numeric($_POST['prd_num'])) $prd->loadFromDB($_POST['prd_num']);
		$prd->loadFromPOST();
		$prd->saveToDB();*/
		if($_POST['ajax']) {
			/*echo "<div id=\"prodNum\">".$prd->prd_num."</div>";
			echo "<div id=\"newProduct\">";
			$plugins['shop']->displayProduct($prd->prd_num);
			echo "</div>";
?>
<script type="text/javascript">
	parent.productSaveComplete(document.getElementById('prodNum').innerHTML, document.getElementById('newProduct').innerHTML)
</script>
<?
			die();*/
		}
	}
	
	if(isset($_GET['d'])) {
		$dsc_num = $_GET['d'];
	}
	$pag_num = array();
	$prd_num = array();
	if(is_numeric($dsc_num) && $dsc_num) {
		$theQ = "SELECT dsc_name, dsc_percent, dsc_start, dsc_end, dsc_code, dsc_minspend, dsc_type FROM tbldiscount WHERE dsc_num = $dsc_num";
		$theQ = mysql_query($theQ);
		$theR = mysql_fetch_assoc($theQ);
		$subQ = "SELECT pag_num FROM tbldiscountpage WHERE dsc_num = $dsc_num";
		$subQ = mysql_query($subQ);
		while($subR = mysql_fetch_row($subQ)) {
			$pag_num[$subR[0]] = true;
		}
		$subQ = "SELECT prd_num FROM tbldiscountprod WHERE dsc_num = $dsc_num";
		$subQ = mysql_query($subQ);
		while($subR = mysql_fetch_row($subQ)) {
			$prd_num[$subR[0]] = true;
		}
	} else {
		$theR = array();
		$theR['dsc_start'] = date('Y-m-d H:i:s');
		$theR['dsc_end'] = date('Y-m-d H:i:s',time()+30*24*60*60);
	}
	
	
?>
<form action="/_shop/discounts" method="post">
<label>Name:</label><input name="dsc_name" value="<?=htmlentities($theR['dsc_name'])?>" /> <span class="tip">This is for your reference only</span><br /><br />
<label>Discount For:</label> <label class="check"><input type="radio" name="dsc_type" value="1" <?=$theR['dsc_type']==1?'checked="checked"':''?> /> Web Shop Customers</label>&nbsp;&nbsp;&nbsp;<label class="check"><input type="radio" name="dsc_type" value="2" <?=$theR['dsc_type']==2?'checked="checked"':''?> /> Retailers</label><br /><br />
<label>Start Date:</label><? dateField('dsc_start',$theR['dsc_start'],true); ?><br/><br/>
<label>End Date:</label><? dateField('dsc_end',$theR['dsc_end'],true); ?><br/><br/>
<label>Discount Amount:</label><input name="dsc_percent" value="<?=$theR['dsc_percent']?>" style="width:60px" /> <span class="tip">The <strong>percent</strong> amount you want to discount the product by, eg 10%</span><br /><br />
<label>Minimum Spend:</label><input name="dsc_minspend" value="<?=$theR['dsc_minspend']?>" style="width:60px" /> <span class="tip">Leave blank if there is no minimum spend</span><br /><br />

<label>Discount Code:</label><input name="dsc_code" value="<?=htmlentities($theR['dsc_code'])?>" /> <span class="tip">Leave blank to automatically apply discount</span><br /><br />
<label>Apply to:</label>
<div style="margin:3px 0 0 150px">
	<p>Please select the <strong>Categories</strong> (<img src="/assets/images/cat.gif" alt="Category" style="vertical-align:middle" />) or <strong>Products</strong> (<img src="/assets/images/product.png" alt="Product" style="vertical-align:middle" />) you would like to apply the discount to. Selecting a Category will apply the discount to all products within the category, or in any sub-categories under it.
<?

	$pages = array(0);
	$theQ = "SELECT DISTINCT pag_num FROM tblproduct";
	$theQ = mysql_query($theQ);
	while($theR = mysql_fetch_row($theQ)) {
		$pages[] = $theR[0];
	}
	do {
		$l = implode(',',$pages);
		$theQ = "SELECT DISTINCT pag_parent FROM tblpage WHERE pag_id IN ($l) AND pag_parent NOT IN ($l)";
		$theQ = mysql_query($theQ);
		$num = mysql_num_rows($theQ);
		if($num) while($theR = mysql_fetch_row($theQ)) {
			$pages[] = $theR[0];
		} 
	} while ($num);
	$pagelist = implode(',',$pages);
	function showDiscountList($parent) {
		global $pagelist, $pag_num, $prd_num;
		$theQ = "SELECT pag_id, pag_name FROM tblpage WHERE pag_parent = $parent AND pag_id IN ($pagelist) ORDER BY pag_order";
		$theQ = mysql_query($theQ);
		if(mysql_num_rows($theQ)) {
			echo "<ul class=\"disclist\">";
			while ($theR = mysql_fetch_row($theQ)) {
				echo "<li><label class=\"check\"> <input type=\"checkbox\" name=\"dsc_page[]\" value=\"$theR[0]\"";
				if($pag_num[$theR[0]]) echo ' checked="checked"';
				echo ">$theR[1]</label>";
				showDiscountList($theR[0]);
				echo "</li>\n";
			}
			echo "</ul>";
		}
		$theQ = "SELECT prd_num, prd_name FROM tblproduct WHERE pag_num = $parent ORDER BY prd_order";
		$theQ = mysql_query($theQ);
		if(mysql_num_rows($theQ)) {
			echo "<ul class=\"products\">";
			while ($theR = mysql_fetch_row($theQ)) {
				echo "<li><label class=\"check\"> <input type=\"checkbox\" name=\"dsc_prod[]\" value=\"$theR[0]\"";
				if($prd_num[$theR[0]]) echo ' checked="checked"';
				echo ">$theR[1]</label></li>\n";
			}
			echo "</ul>";
		}
	}
	showDiscountList(0);
?>
</div>
<label>&nbsp;</label><input type="button" value="Cancel" class="lbAction cancel" name="deactivate" /> <input type="submit" value="Save Changes" class="confirm" onclick="discountSave(this)" />
<a class="spinner"><em></em></a>
<input type="hidden" name="ajax" id="ajax" value="0" />
<input type="hidden" name="dsc_num" value="<?=$dsc_num?>" />
</form>
<iframe name="submitter" style="display:none"></iframe>
<?
	
	if(!isset($_GET['notitle']) && !$_POST['ajax']) {
		include('../../bottom.php');
	}
?>