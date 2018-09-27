<?
	session_start();
	if(!$_SESSION['admin']) {
		header('Location:/');
		die();
	}
?>
<?
	include('../../top.php');
?>
<h2>Shipping Prices</h2>
<?
	if(isset($_POST['shc_cost']) && is_array($_POST['shc_cost'])) foreach($_POST['shc_cost'] as $shp_num=>$arr) if(is_numeric($shp_num)) {
		if(is_array($arr)) foreach($arr as $shw_num=>$shc_cost) if(is_numeric($shp_num)) {
			$shc_cost = preg_replace('~[^0-9\.]~','',$shc_cost);
			$theQ = "REPLACE INTO tblshippingcost (shc_cost, shp_num, shw_num) VALUES ($shc_cost, $shp_num, $shw_num)";
			$theQ = mysql_query($theQ);
		}
	
	}
?>
<form action="/_shop/shipping" method="post">
<table class="shipping">
<?
	$weights = array();
	$costs = array();
	$theQ = "SELECT shw_num, shw_from, shw_to FROM tblshippingweight ORDER BY shw_num";
	$theQ = mysql_query($theQ);
	echo "<tr><th>Destination/Weight</th>";
	while ($theR = mysql_fetch_row($theQ)) {
		$weights[$theR[0]] = "$theR[1] - $theR[2]";
		echo  "<th>$theR[1]<br /> -$theR[2]</th>\n";
	}
	echo "</tr>";
	$theQ = "SELECT shp_num, shw_num, shc_cost FROM tblshippingcost";
	$theQ = mysql_query($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		$costs[$theR[0]][$theR[1]] = $theR[2];
	}
	$row = 1;
	$theQ = "SELECT shp_num, shp_name FROM tblshippingregion ORDER BY shp_num";
	$theQ = mysql_query($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		$row = 1-$row;
		echo "<tr class=\"row$row\">";
		echo "<td>$theR[1]</td>\n";
		foreach($weights as $shw_num=>$w) {
			echo "<td><input name=\"shc_cost[$theR[0]][$shw_num]\" value=\"".number_format($costs[$theR[0]][$shw_num],2)."\"/></td>\n";
		}
		echo "</tr>\n";
	}
?>
</table><br />

<input type="submit" class="confirm" value="Save Shipping Rates" />
</form>
<?
	include('../../bottom.php');
?>