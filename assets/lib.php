<?
	require_once('config.php');
	require_once('inc_simplecms.php');
	include_once('inc_image.php');
	include_once('inc_contactformprotect.php');
	require('lib/autoload.php');

	define('ERROR_DBCONNECTION',1);
	define('ERROR_NOPAGE',2);

	define('POSITION_BEFOREBODY',1);
	define('POSITION_AFTERBODY',2);
	define('POSITION_ADMINLOGIN',4);

	if(is_array($_CONFIG['plugins'])) foreach($_CONFIG['plugins'] as $p) {
		require_once('plugins/'.$p.'/plugin.php');
		$plugins[$p] = new $p;
	}
	require_once('phpmailer/class.custom.php');

	connectToDB();

	function connectToDB() {
		global $_CONFIG;
		$m = mysql_connect($_CONFIG['dbhost'],$_CONFIG['dbuser'],$_CONFIG['dbpass']);
		if(!$m) fail(ERROR_DBCONNECTION);
		$m = mysql_select_db($_CONFIG['dbname']);
		if(!$m) fail(ERROR_DBCONNECTION);

	}

	function fail($error) {
		echo "<div>We're sorry, there seems to have been an error.</div>";
		switch($error) {
			case ERROR_DBCONNECTION:	echo 'Could not connect to database'; break;
			case ERROR_NOPAGE:			echo 'Could not load a page to display'; break;
		}

		include('bottom.php');
		die();
	}

	function getExcerpt($str, $startPos=0, $maxLength=100) {
		if(strlen($str) > $maxLength) {
			$excerpt   = substr($str, $startPos, $maxLength-3);
			$lastSpace = strrpos($excerpt, ' ');
			$excerpt   = substr($excerpt, 0, $lastSpace);
			$excerpt  .= '...';
		} else {
			$excerpt = $str;
		}

		return $excerpt;
	}

	function check_email($email){
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
			'\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$quoted_pair = '\\x5c[\\x00-\\x7f]';
		$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
		$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
		$domain_ref = $atom;
		$sub_domain = "($domain_ref|$domain_literal)";
		$word = "($atom|$quoted_string)";
		$domain = "$sub_domain(\\x2e$sub_domain)*";
		$local_part = "$word(\\x2e$word)*";
		$addr_spec = "$local_part\\x40$domain";
		return preg_match("!^$addr_spec$!", $email) ? true : false;
	}

	function stop_spam($message) {
	   $suspicious_str = array (
		   "content-type:"
		   ,"charset="
		   ,"mime-version:"
		   ,"multipart/mixed"
		   ,"bcc:"
		);
		foreach($suspicious_str as $suspect) {
			if(eregi($suspect, strtolower($message))) {
				$message = eregi_replace($suspect, "(anti-spam-".$suspect.")", $message);
			}
		}
		return $message;
	}

	function friendlyDate($date) {
		if ($date) return date('F d, Y',strtotime($date));
		else return '';
	}

	function friendlyDateTime($date) {
		if ($date) return date('g:ia F d, Y',strtotime($date));
		else return '';
	}

	function dateField($name,$value,$hasTime=false) {
		$jsValue = $value;
		if ($hasTime) {
			$format = 'yyyy-mm-dd hr:mi';
			if (strlen($value)==19) $jsValue = substr($value,0,16);
		} else {
			$format = 'yyyy-mm-dd';
		}
		echo "<img src=\"/assets/images/cal.gif\" alt=\"Select a Date\" onclick=\"showCalendar('','".$name."','".$name."','',this,5,-60,1,'".$name."_disp','".$format."')\" style=\"vertical-align:middle; margin-top:5px;\"/>
		<input type=\"hidden\" name=\"".$name."\" value=\"".$jsValue."\" id=\"".$name."\" />
		<span id=\"".$name."_disp\">".($hasTime?friendlyDateTime($value):friendlyDate($value))."</span>";
	}

