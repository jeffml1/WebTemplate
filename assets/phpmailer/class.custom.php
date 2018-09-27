<?
	include('class.phpmailer.php');
	include('class.html2text.php');
	
	class CustomMailer extends PHPMailer {
		// Set default variables for all new objects  
		var $From;
		var $FromName;
		var $Host     = "localhost";	//"smtp.webhost.co.nz";  		
		var $Mailer   = "smtp";			// Alternative to IsSMTP() 

		/*var $Host     = "smtp.webhost.co.nz";  
		var $SMTPAuth = true; // turn on SMTP authentication
		var $Username = ""; // SMTP username
		var $Password = ""; // SMTP password*/
		
		var $HTMLStart = '';
		var $HTMLEnd = '';
		
		function CustomMailer() {
			global $_CONFIG;
			$this->From     = $_CONFIG['siteemail']; 
			$this->FromName = $_CONFIG['sitename'];
			$this->HTMLStart = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title></title>
</head>

<body style="background:white; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px">';
			$this->HTMLEnd = '</body></html>';
		}

		
		function sendMail($to,$subject,$content,$file='',$filename = '') {
			$this->Body = $this->HTMLStart.$content.$this->HTMLEnd;
			$h2t = new html2text($content);
			$this->AltBody = $h2t->get_text();
			$this->Subject = $subject;
			$this->AddAddress($to);
			if ($file) $this->AddStringAttachment(file_exists($file)?file_get_contents($file):$file,$filename);
			$this->Send();
		}

	}
?>