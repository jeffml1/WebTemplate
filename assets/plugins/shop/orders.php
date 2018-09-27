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
	if(isset($_POST['ord_num']) && is_numeric($_POST['ord_num'])) {
		if(isset($_POST['ost_num']) && is_numeric($_POST['ost_num'])) {
			$theQ = "UPDATE tblorder SET ost_num = ".$_POST['ost_num']." WHERE ord_num = ".$_POST['ord_num'];
			$theQ = mysql_query($theQ);
			$_GET['s'] = $_POST['ost_num'];
		} elseif(isset($_POST['dispatch'])) {
			$theQ = "SELECT ost_num, ord_name, ord_email FROM tblorder WHERE ord_num = ".$_POST['ord_num'];
			$theQ = mysql_query($theQ);
			$theR = mysql_fetch_row($theQ);
			$_GET['s'] = $theR[0];
			$codes = explode("\n",$_POST['tracking']);
			$newcode = array();
			foreach($codes as $code) {
				if(trim($code)) $newcode[] = trim($code);
			}
			if(!sizeof($newcode)) $newcode[] = '';
			foreach($newcode as $code) {
				if(preg_match('~[0-9][A-Z]{3}([A-Z]{2}) ( [0-9]{8})[0-9]{2}~',$code,$m)) $code = $m[1].$m[2];
				$theQ = "INSERT INTO tblordertrack (ord_num, usr_num, trk_date, trk_code) VALUES (".$_POST['ord_num'].", ".$_SESSION['admin'].", now(), '".mysql_real_escape_string($code)."')";
				$theQ = mysql_query($theQ);
			}
			$theQ = "UPDATE tblorder SET ost_num = 10 WHERE ord_num = ".$_POST['ord_num'];
			$theQ = mysql_query($theQ);
			if(isset($_POST['sendconfirm'])) {
				$mailer = new CustomMailer();
				$message = '<p>Hi '.$theR[1].',</p><p>Your order has been dispatched</p>';
				//Need to add tracking details into the mix.
				$subQ = "SELECT trk_code, trk_date, use_fname, use_lname FROM tblordertrack o LEFT JOIN tbluser u ON (o.usr_num = u.use_id) WHERE trk_code!='' AND ord_num = ".$_POST['ord_num'];
				$subQ = mysql_query($subQ);
				if(mysql_num_rows($subQ)) {
					$message.= "<p>You can track your order using the details below</p><p>\n";
					while($subR = mysql_fetch_row($subQ)) {
						if(!$subR[0]) $subR[0]='N/A';
						$message.= "$subR[0] <em>".date('d/m/Y H:i',strtotime($subR[1]))."</em> ";
						if(strlen($subR[0])>4) $message.= $plugins['shop']->trackTrace($subR[0]);
						$message.= "<br />";
					}
					$message.= "</p>";
				}
				$message.= "<p>Kind Regards,</p><p>The Team at ".$_CONFIG['sitename']."</p>";
				$mailer->sendMail($theR[2],'Your order has been dispatched',$message);

			}
		}
	}
?>
<h2>Orders</h2>
<?
	$theQ = "SELECT os.ost_num, ost_name, COUNT(ord_num) FROM tblorder o RIGHT JOIN tblorderstatus os ON (o.ost_num = os.ost_num) GROUP BY os.ost_num";
	$theQ = mysql_query($theQ);
	$first = true;
	echo "<strong>View Orders:</strong> ";
	while($theR = mysql_fetch_row($theQ)) {
		if(!$first) {
			echo " | ";
		}
		$first = false;
		echo "<a href=\"/_shop/orders?s=$theR[0]\"";
		if($theR[0]==$_GET['s']) {
			echo ' style="font-weight:bold"';
			$title = $theR[1];
		}
		echo ">$theR[1] ($theR[2])</a>";
	}
	if(is_numeric($_GET['s'])) {
?>
<h3><?=$title?></h3>
<?
		$theQ = "SELECT ord_num, ord_name, ord_placed, ord_total FROM tblorder WHERE ost_num = ".$_GET['s']." ORDER BY ord_num DESC";
		$theQ = mysql_query($theQ);
		if(mysql_num_rows($theQ)) {
			echo "<table style=\"width:100%;\" class=\"orders\">";
			echo "<tr><th>#</th><th>Name</th><th>Order Placed</th><th>Total</th><th style=\"width:150px\"></th></tr>\n";
			$row = 1;
			while ($theR = mysql_fetch_row($theQ)) {
				$row = 1-$row;
				echo "<tr class=\"row$row\">";
				echo "<td>$theR[0]</td>";
				echo "<td>".htmlentities($theR[1])."</td>";
				echo "<td>".date('d/m/Y, g:ia',strtotime($theR[2]))."</td>";
				echo "<td>$".number_format($theR[3],2)."</td>";
				echo "<td><a href=\"/_shop/manageorder?o=$theR[0]\" class=\"lbOn action\" rel=\"800,900\">View/Edit Order</a></td>";
				echo "</tr>";
			}
			echo "</table>";
		} else {
			echo "There are no orders with this status";
		}
	}
?>
<?
	include('../..//bottom.php');
	
?>