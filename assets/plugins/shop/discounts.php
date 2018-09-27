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
<?
	if(isset($_POST['dsc_num'])) {
		if(!is_numeric($_POST['dsc_num'])) {
			$theQ = "INSERT INTO tbldiscount (dsc_name) VALUES ('')";
			$theQ = mysql_query($theQ);
			$dsc_num = mysql_insert_id();
		} else {
			$dsc_num = $_POST['dsc_num'];
			$theQ = "DELETE FROM tbldiscountpage WHERE dsc_num = $dsc_num";
			$theQ = mysql_query($theQ);
			$theQ = "DELETE FROM tbldiscountprod WHERE dsc_num = $dsc_num";
			$theQ = mysql_query($theQ);
		}
		$theQ = "UPDATE tbldiscount SET
			dsc_name = '".mysql_real_escape_string($_POST['dsc_name'])."',
			dsc_percent = '".preg_replace('~[^0-9\.]*~','',$_POST['dsc_percent'])."',
			dsc_start = '".mysql_real_escape_string($_POST['dsc_start'])."',
			dsc_end = '".mysql_real_escape_string($_POST['dsc_end'])."',
			dsc_code = '".mysql_real_escape_string($_POST['dsc_code'])."',
			dsc_minspend = '".preg_replace('~[^0-9,\.]*~','',$_POST['dsc_minspend'])."',
			dsc_type = '".preg_replace('~[^1-2]*~','',$_POST['dsc_type'])."' WHERE dsc_num = $dsc_num";
		$theQ = mysql_query($theQ);
		if(is_array($_POST['dsc_page'])) foreach ($_POST['dsc_page'] as $pag_num) if(is_numeric($pag_num)) {
			$theQ = "INSERT INTO tbldiscountpage (dsc_num, pag_num) VALUES ($dsc_num, $pag_num)";
			$theQ = mysql_query($theQ);		
		}
		if(is_array($_POST['dsc_prod'])) foreach ($_POST['dsc_prod'] as $prd_num) if(is_numeric($prd_num)) {
			$theQ = "INSERT INTO tbldiscountprod (dsc_num, prd_num) VALUES ($dsc_num, $prd_num)";
			$theQ = mysql_query($theQ);
		}
	}
?>
<h2>Discounts</h2>
<a href="/_shop/adddiscount" rel="600,800" class="lbOn action">Add New Discount</a>
<?
	$theQ = "SELECT dsc_num, dsc_name, dsc_percent, dsc_start, dsc_end, dsc_code, dsc_minspend, dsc_type FROM tbldiscount WHERE dsc_end > now()";
	$theQ = mysql_query($theQ);
	while ($theR = mysql_fetch_row($theQ)) {
		echo "<div class=\"discount\" id=\"discount_$theR[0]\">";
		echo "<a href=\"/_shop/adddiscount?d=$theR[0]\" rel=\"600,800\" class=\"lbOn action\" style=\"float:right\">Edit this Discount</a>\n";
		echo "<a href=\"/_shop/adddiscount?d=$theR[0]\" rel=\"600,800\" class=\"lbOn\"><h4>$theR[1]</h4></a>\n";
		echo "<strong>Discount Type:</strong> ".($theR[5]?'Promo Code <em>'.$theR[5].'</em>':'Listed').", ".($theR[7]==1?'Public Shop Only':'Retailers Only')."<br/>\n";
		echo "<strong>Validity:</strong> ".date('j M Y <\e\m>g:ia</\e\m>',strtotime($theR[3])).' - '.date(' j M Y <\e\m>g:ia</\e\m>',strtotime($theR[4]))."<br/>\n";
		echo "<strong>Discount Amount:</strong> ".$theR[2]."%";
		if($theR[6]) echo " on purchases over $".number_format($theR[6],2);
		$subQ = "SELECT pag_name FROM tbldiscountpage dp JOIN tblpage p ON (p.pag_id = dp.pag_num) WHERE dsc_num = $theR[0]";
		$subQ = mysql_query($subQ);
		if(mysql_num_rows($subQ)) {
			echo "<br />";
			echo "<strong>Applied to pages:</strong> ";
			$started = false;
			while ($subR = mysql_fetch_row($subQ)) {
				if($started) echo ", ";
				echo $subR[0];
				$started = true;
			}
		}
		$subQ = "SELECT prd_name, prd_code FROM tbldiscountprod dp JOIN tblproduct p ON (p.prd_num = dp.prd_num) WHERE dsc_num = $theR[0]";
		$subQ = mysql_query($subQ);
		if(mysql_num_rows($subQ)) {
			echo "<br />";
			echo "<strong>Applied to products:</strong> ";
			$started = false;
			while ($subR = mysql_fetch_row($subQ)) {
				if($started) echo ", ";
				echo $subR[0];
				$started = true;
			}
		}
		echo "</div>\n";
	}
?>
<?
	include('../..//bottom.php');
	
?>