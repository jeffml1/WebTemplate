<?
	session_start();
	if(!$_SESSION['admin']) {
		header('Location:/');
		die();
	}
	include('../../top.php');
?>

<h2>International Pricing Brackets</h2>
<?
	if(isset($_POST['submit']) && is_array($_POST['pri_num'])) {
		foreach($_POST['pri_num'] as $c=>$p) if(is_numeric($p) && is_numeric($_POST['shp_num'][$c])) {
			$theQ = "UPDATE tblcountry SET pri_num = $p, shp_num = ".$_POST['shp_num'][$c]." WHERE cou_code = '".mysql_real_escape_string($c)."'";
			$theQ = mysql_query($theQ);
		}
		echo "<p>Price levels have been saved</p>";
	}
	
?>
<form action="/_shop/countries" method="post">
<table id="pricingOptions">
	<tr>
		<th colspan="2">Country</th><th style="width:200px">Price Level</th><th style="width:200px">Shipping Region</th>
	</tr>
<? 
	$levels = array();
	$ships = array();
	$theQ = "SELECT pri_num, pri_name FROM tblpricelevel WHERE pri_type = 1 ORDER BY pri_num";
	$theQ = mysql_query($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		$levels[$theR[0]] = $theR[1];
	}
	$theQ = "SELECT shp_num, shp_name FROM tblshippingregion ORDER BY shp_num";
	$theQ = mysql_query($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		$ships[$theR[0]] = $theR[1];
	}
	$theQ = "SELECT cou_code, cou_name, pri_num, shp_num FROM tblcountry ORDER BY cou_name";
	$theQ = mysql_query($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		echo "<tr><td style=\"width:18px\"><img src=\"/assets/images/flags/$theR[0].png\" style=\"width:18px; height:12px\" alt=\"$theR[1] ($theR[0])\"></td><td>$theR[1] ($theR[0])</td><td><select name=\"pri_num[$theR[0]]\">";
		foreach($levels as $num=>$name) {
			echo "<option value=\"$num\"";
			if ($num==$theR[2]) echo ' selected="selected"';
			echo ">$name</option>\n";
		}
		echo "</select></td><td><select name=\"shp_num[$theR[0]]\">";
		foreach($ships as $num=>$name) {
			echo "<option value=\"$num\"";
			if ($num==$theR[3]) echo ' selected="selected"';
			echo ">$name</option>\n";
		}
		echo "</select><td></tr>\n";
	}
?>
</table><br />
<input type="hidden" name="submit" value="1" />
<input type="submit" class="confirm" value="Save Price Levels" />
</form>
<?
	include('../..//bottom.php');
	
?>