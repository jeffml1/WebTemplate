<?php
	include('assets/lib.php');
/////////////////////////////////////////////////
/////////////Begin Script below./////////////////
/////////////////////////////////////////////////
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);


// assign posted variables to local variables
$payment_status = $_POST['payment_status'];	//Ensure this is "Completed"
$mc_gross = $_POST['mc_gross'];				//This should match the DB
$payment_currency = $_POST['mc_currency'];	//This should be NZD
$ord_num = $_POST['custom'];				//Check this against DB entry
$receiver_email = $_POST['receiver_email'];	//Ensure this is us.
$type = $_POST['txn_type'];

ob_start();
print_r($_POST);
$vars = ob_get_contents();
ob_end_clean();

function saveStatus($subj,$vars) {
	$fh = fopen('ppstatus.txt','w+');
	fwrite($fh,date('d/m/Y h:i')."\n\n".$subj."\n\n".$vars);
	fclose($fh);
}



if (!$fp) {
	mail('ian@logicstudio.co.nz','[HG-IPN] HTTP Error',$vars);
	saveStatus('HTTP Error',$vars);
// HTTP ERROR
} else {
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) {
			if (strtolower($payment_status)=='completed' && $receiver_email = $_CONFIG['paypal']) {
				//Check all fields against DB and stored constants, then mark invoices paid.
				$theQ = "SELECT ord_total, ord_shipping FROM tblorder WHERE ord_num = '".mysql_real_escape_string($ord_num)."'";
				$theQ = mysql_query($theQ);
				if ((mysql_result($theQ,0,'ord_total')+mysql_result($theQ,0,'ord_shipping'))!=$mc_gross) {
					mail('ian@logicstudio.co.nz','[HG-IPN] Incorrect total',$vars);
					saveStatus('Incorrect total',$vars);
				} else {
					$theQ = "UPDATE tblorder SET ost_num = 4 WHERE ord_num = '".mysql_real_escape_string($ord_num)."'";
					$theQ = mysql_query($theQ);
					mail('ian@logicstudio.co.nz','[HG-IPN] Successful transaction',$vars);
					saveStatus('Successful transaction',$vars);
					
					$theQ = "SELECT ord_name, ord_email FROM tblorder WHERE ord_num = '".mysql_real_escape_string($ord_num)."'";
					$theQ = mysql_query($theQ);
					$theR = mysql_fetch_row($theQ);
					
					$mailer = new CustomMailer();
					$mailer->sendMail($theR[1],'Order Confirmation',"Hi $theR[0],<br><br>Thanks for placing an order with ".$_CONFIG['sitename'].". Your order has been received and is currently being processed.<br><br>We'll be in touch soon to let you know when your order is on its way.<br><br>Thanks again,<br><br>The team at ".$_CONFIG['sitename']);
					
					$mailer = new CustomMailer();
					$mailer->sendMail($_CONFIG['ordermail'],'New order placed and paid',"A new order has been placed and paid for by $theR[0].<br><br>Please log in to process this order.");
				}
			} else {
				mail('ian@logicstudio.co.nz','[HG-IPN] Incomplete or bad email',$vars);
				saveStatus('Incomplete or bad email',$vars);
			}
		} else if (strcmp ($res, "INVALID") == 0) {	
			//Drop an email out so we know what's going on
			mail('ian@logicstudio.co.nz','[HG-IPN] INVALID result',$vars);
			saveStatus('INVALID result',$vars);
		}
	}
	fclose ($fp);
}
?>

