<?
	if(isset($_GET['notitle']) || $_POST['ajax']) ob_start();
	include('../../top.php');
	if(isset($_GET['notitle']) || $_POST['ajax']) ob_end_clean();
	$prd = new product($_GET['p']);
	if(isset($_GET['c']) && is_numeric($_GET['c'])) $prd->pag_num = $_GET['c'];
	
	if(isset($_POST['prd_num'])) {
		if(is_numeric($_POST['prd_num'])) $prd->loadFromDB($_POST['prd_num']);
		$prd->loadFromPOST();
		$prd->saveToDB();
		if($_POST['ajax']) {
			echo "<div id=\"prodNum\">".$prd->prd_num."</div>";
			echo "<div id=\"newProduct\">";
			$plugins['shop']->displayProduct($prd->prd_num);
			echo "</div>";
?>
<script type="text/javascript">
	parent.productSaveComplete(document.getElementById('prodNum').innerHTML, document.getElementById('newProduct').innerHTML)
</script>
<?
			die();
		}
	}
	
?>
<form action="/_shop/add" method="post" enctype="multipart/form-data">
<label>Name:</label><input style="width:400px" name="prd_name" value="<?=htmlentities($prd->prd_name)?>" /><br /><br />
<label>Page:</label><select name="pag_num">
					<?
						$cms->doOptions(0,0,$prd->pag_num);
					?>
					</select><br /><br />
<label>Product Code:</label><input name="prd_code" value="<?=htmlentities($prd->prd_code)?>" /><br /><br />
<label>Short Description:</label><textarea style="width:400px; height:60px" name="prd_desc"><?=htmlentities($prd->prd_desc)?></textarea><br /><br />
<label>Product Photo:</label><input type="file" name="prd_image" /><br /><br />
<label>Long Description:</label><br />
<textarea class="mceEditor" name="prd_detail"><?=htmlentities($prd->prd_detail)?></textarea><br />
<label>Pricing Options:</label>
<table id="pricingOptions" style="width:700px">
	<tr>
		<th>Name</th>
		<th>Retail Pricing</th>
		<th>Wholesale Pricing</th>
		<th>Weight (g)</th>
		<th colspan="2">Stock?</th>
	</tr>
<?
	$i = 5;
	$optrow = '<td><input name="opt_name[%1$s]" id="opt_name[%1$s]" value="%2$s" /><div class="tip">eg: &quot;50g&quot;, &quot;10 pack&quot; etc</div></td>
		<td><table>';
	$subQ = "SELECT pri_num, pri_name FROM tblpricelevel WHERE pri_type = 1";
	$subQ = mysql_query($subQ);
	while($subR = mysql_fetch_row($subQ)) {
		$optrow.= '<tr><td>'.$subR[1].'</td><td><input name="price[%1$s]['.$subR[0].']" id="price[%1$s]['.$subR[0].']" value="%'.$i.'$s" style="width:60px" /></td></tr>';
		$i++;
	}
	$optrow.= '</table></td><td><table>';
	$subQ = "SELECT pri_num, pri_name FROM tblpricelevel WHERE pri_type = 2";
	$subQ = mysql_query($subQ);
	while($subR = mysql_fetch_row($subQ)) {
		$optrow.= '<tr><td>'.$subR[1].'</td><td><input name="price[%1$s]['.$subR[0].']" id="price[%1$s]['.$subR[0].']" value="%'.$i.'$s" style="width:60px" /></td></tr>';
		$i++;
	}
		
	$optrow.= '</table></td>
		<td><input name="opt_weight[%1$s]" id="opt_weight[%1$s]" value="%3$s" style="width:60px" /></td>
		<td><input name="opt_stock[%1$s]" id="opt_stock[%1$s]" %4$s type="checkbox" value="1" /></td>
		<td style="vertical-align:bottom"><a href="#" onclick="deletePricingOption(this.parentNode.parentNode); return false"><img src="/assets/images/redcross.gif" alt="Delete this Option" style="vertical-align:middle" /></a>
		</td>';
	foreach($prd->po as $po) {
		echo "<tr>".sprintf($optrow,$po->opt_num,$po->opt_name,$po->opt_weight,$po->opt_stock?'checked="checked"':'',$po->prices[1],$po->prices[2],$po->prices[3],$po->prices[4],$po->prices[101],$po->prices[102],$po->prices[103],$po->prices[104])."</tr>";
	}
	if(!sizeof($prd->po)) echo "<tr>".sprintf($optrow,"NEW_0","","",'checked="checked"','','','','','','','','')."</tr>";
?>
		

</table>

<a href="#" onclick="addPricingOption()" style="float:right; margin-top:-16px"><img src="/assets/images/plus.gif" alt="Add a new pricing option" /></a>
<br />
<label>Product Enabled:</label> <input type="checkbox" name="prd_active" value="1" <?=$prd->prd_active?'checked="checked"':''?> /><br /><br />
<label>&nbsp;</label><input type="button" value="Cancel" class="lbAction cancel" name="deactivate" /> <input type="submit" value="Save Changes" class="confirm" onclick="productSave(this)" />
<a class="spinner"><em></em></a>
<input type="hidden" name="ajax" id="ajax" value="0" />
<input type="hidden" name="prd_num" value="<?=$prd->prd_num?>" />
</form>
<input type="hidden" id="newrow" value="<?=htmlentities(str_replace("\n","",sprintf($optrow,"~NEW~","","",'checked="checked"','','','','','','','','')));?>" />
<input type="hidden" id="numrows" value="<?=sizeof($prd->po)+(sizeof($prd->po)?0:1)?>" />
<iframe name="submitter" style="display:none"></iframe>
<?
	
	if(!isset($_GET['notitle']) && !$_POST['ajax']) {
		include('../../bottom.php');
	}
?>