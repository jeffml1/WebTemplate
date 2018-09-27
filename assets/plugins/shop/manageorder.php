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
	if(isset($_GET['o'])) {
		$ord_num = $_GET['o'];
	}
	if(is_numeric($ord_num)) {
		$theQ = "SELECT ord_name, ord_email, ord_phone, ord_address, cou_name, ord_placed, ost_num, ord_total, ret_code, ord_ip, dsc_code, ord_shipping FROM tblorder o LEFT JOIN tblcountry c ON (o.cou_code = c.cou_code) WHERE ord_num = $ord_num";
		$theQ = mysql_query($theQ);
		$theR = mysql_fetch_assoc($theQ);
		
		$ch = curl_init('http://api.wipmania.com/'.$theR['ord_ip'].'?'.$_SERVER['HTTP_HOST']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$cou_code = curl_exec($ch);
		curl_close($ch);
		
		if(strlen($cou_code)>2) $cou_code = '';
		
		$subQ = "SELECT cou_name FROM tblcountry WHERE cou_code = '".mysql_real_escape_string($cou_code)."'";
		$subQ = mysql_query($subQ);
		$subR = mysql_fetch_row($subQ);
	}
	
?>
<h3>Order Details</h3>
<div id="orderdetail">
<?
	if($theR['ret_code']) {
?>
	<p><label>Customer Number</label> <strong><?=$theR['ret_code']?></strong></p>
<?
	}
?>
	<p><label>Name</label> <?=htmlentities($theR['ord_name'])?></p>
	<p><label>Email</label> <a href="mailto:<?=$theR['ord_email']?>"><?=$theR['ord_email']?></a></p>
	<p><label>Phone</label> <?=htmlentities($theR['ord_phone'])?></p>
	<p><label>Address</label> <div style="margin-left:155px"><?=nl2br(htmlentities($theR['ord_address']))?><br /><?=$theR['cou_name']?></div></p>
	<p><label>Order Placed</label> <?=date('g:i:sa, l jS F, Y',strtotime($theR['ord_placed']))?></p>
	<p><label>IP Address</label> <?=$theR['ord_ip']?> (<?=$subR[0]?> <img src="/assets/images/flags/<?=$cou_code?>.png" alt="<?=$cou_code?>" style="vertical-align:middle" />)</p>
<?
	if($theR['dsc_code']) {
?>
	<p><label>Discount Code</label> <?=$theR['dsc_code']?></p>
<?
	}
?>
<h3>Order Contents</h3>
<table style="width:100%">
	<tr><th>Product</th><th>Description</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
<?
	$subQ = "SELECT prd_name, odl_desc, odl_price, odl_quantity FROM tblorderline ol LEFT JOIN tblproduct p ON (ol.prd_num = p.prd_num) WHERE ord_num = $ord_num";
	$subQ = mysql_query($subQ);
	$row = 1;
	while($subR = mysql_fetch_row($subQ)) {
		$row = 1-$row;
		echo "\t<tr class=\"row$row\">";
		echo "<td>".htmlentities($subR[0])."</td>";
		echo "<td>".htmlentities($subR[1])."</td>";
		echo "<td>$".number_format($subR[2],2)."</td>";
		echo "<td>".$subR[3]."</td>";
		echo "<td>$".number_format($subR[2]*$subR[3],2)."</td>";
		echo "</tr>\n";
	}
?>
	<tr><td colspan="4" style="text-align:right">Subotal:</td><td>$<?=number_format($theR['ord_total'],2)?></td></tr>
	<tr><td colspan="4" style="text-align:right">Shipping:</td><td>$<?=number_format($theR['ord_shipping'],2)?></td></tr>
	<tr><td colspan="4" style="text-align:right"><strong>Total:</strong></td><td>$<?=number_format($theR['ord_shipping']+$theR['ord_total'],2)?></td></tr>
</table>
</div>
<?
	$subQ = "SELECT trk_code, trk_date, use_fname, use_lname FROM tblordertrack o LEFT JOIN tbluser u ON (o.usr_num = u.use_id) WHERE ord_num = $ord_num";
	$subQ = mysql_query($subQ);
	if(mysql_num_rows($subQ)) {
		echo "<h3>Order Tracking</h3>";
		while($subR = mysql_fetch_row($subQ)) {
			if(!$subR[0]) $subR[0]='N/A';
			echo "$subR[0] <span class=\"tip\">".date('d/m/Y H:i',strtotime($subR[1]))." by $subR[2] $subR[3]</span> ";
			if(strlen($subR[0])>4) echo $plugins['shop']->trackTrace($subR[0]);
			echo "<br />";
		}
		echo "<br />";
	}
?>
<?
	if(in_array($theR['ost_num'],array(2,4,5))) {
?>
<h3>Dispatch Order</h3>
<form action="/_shop/orders" method="post">
<label>Tracking Number(s)</label><textarea cols="20" rows="3" name="tracking" id="tracking"></textarea><br /><br />
<label>Send Confirmation</label> <label class="check"><input type="checkbox" name="sendconfirm" checked="checked" /> Send confirmation email with tracking details to customer</label><br /><br />
<label>&nbsp;</label><input type="button" value="Cancel" class="lbAction cancel" name="deactivate" /> <input type="submit" value="Confirm Dispatch" class="confirm" onclick="discountSave(this)" />
<a class="spinner"><em></em></a>

<input type="hidden" name="dispatch" value="1" />
<input type="hidden" name="ord_num" value="<?=$ord_num?>" />
</form><br /><br />
<script type="text/javascript">
	$('tracking').focus()
</script>
<?
	}
?>
<h3>Change Order Status</h3>
<form action="/_shop/orders" method="post">
<label>Status</label><select name="ost_num">
<?
	$subQ = "SELECT ost_num, ost_name FROM tblorderstatus ORDER BY ost_num";
	$subQ = mysql_query($subQ);
	while($subR = mysql_fetch_row($subQ)) {
		echo "<option value=\"$subR[0]\"";
		if($subR[0]==$theR['ost_num']) echo ' selected="selected"';
		echo ">$subR[1]</option>\n";
	}
?>
</select><br /><br />
<label>&nbsp;</label><input type="button" value="Cancel" class="lbAction cancel" name="deactivate" /> <input type="submit" value="Save Status" class="confirm" onclick="discountSave(this)" />
<a class="spinner"><em></em></a>


<input type="hidden" name="ord_num" value="<?=$ord_num?>" />
</form>

<iframe name="submitter" style="display:none"></iframe>
<?
	
	if(!isset($_GET['notitle']) && !$_POST['ajax']) {
		include('../../bottom.php');
	}
?>