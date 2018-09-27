<?
	session_start();
	if(!$_SESSION['admin']) {
		header('Location:/');
		die();
	}
	include('assets/top.php');

if(isset($_POST['delete'])){
	if(isset($_POST['use_id']) && is_numeric($_POST['use_id'])){
		$theQ = "DELETE FROM tbluser WHERE use_id = ".$_POST['use_id'];
		$theD = mysql_query($theQ);
		if($theD){
			$theQ = "DELETE FROM tbluserpages WHERE use_id = ".$_POST['use_id'];
			$theD = mysql_query($theQ);
			if($theD){
				echo '<h4>User Deleted Sucessfully</h4>';
			}
		}
	}
}



if(isset($_POST['insert'])){
	$error = array();
	if (isset($_POST['use_id']) && is_numeric($_POST['use_id'])){ // Updating user
		$theQ = "UPDATE tbluser SET ";
		if(isset($_POST['use_pass']) && strlen($_POST['use_pass'])>=6){ 	// Valid password has been set 
			$theQ .= "use_pass='".mysql_real_escape_string(md5($_POST['use_pass']))."',";
		} else if(isset($_POST['use_pass']) && strlen($_POST['use_pass'])>=1){	// User wants to change P/W but it is not valid
			$error['use_pass'] = "New password must be 6 characters or greater, leave blank to keep existing";
		}
	} else { // Inserting a new user
		$theQ = "INSERT INTO tbluser (use_pass,use_username,use_email,use_fname,use_lname,use_enabled,pri_id)VALUES (";
		if(isset($_POST['use_pass']) && strlen($_POST['use_pass'])>=6){		//Valid password has been set
			$theQ .= "'".mysql_real_escape_string(md5($_POST['use_pass']))."',";
	
		} else { //This is a new user/short password so they need to set a password
			$error['use_pass'] = "Please set a password, password must be 6 characters or greater";
		}
	}	
	$_POST['pri_id'] = mysql_real_escape_string($_POST['pri_id']);	
	if($_POST['use_fname'] == ""){
		$error['use_fname'] = "Please set a first name";
	} else {
		$_POST['use_fname'] = mysql_real_escape_string($_POST['use_fname']);
	}
	if($_POST['use_lname'] == ""){
		$error['use_lname'] = "Please set a last name";
	} else {
		$_POST['use_lname'] = mysql_real_escape_string($_POST['use_lname']);
	}
	if($_POST['use_username'] == ""){
		$error['use_username'] = "Please set a username";
	} else {
		$_POST['use_lname'] = mysql_real_escape_string($_POST['use_lname']);
	}
	if(check_email($_POST['use_email'])){
		$_POST['use_email'] = mysql_real_escape_string($_POST['use_email']);
	} else {
		$error['use_email'] = "Please set a valid email";
	}	
	$_POST['use_enabled']=($_POST['use_enabled']=='on')?'1':'0';
	if(empty($error)){
		if (isset($_POST['use_id'])){ //Existing User
			$theQ.= "use_username='".$_POST['use_username']."',use_email='".$_POST['use_email']."',use_fname='".$_POST['use_fname']."',use_lname='".$_POST['use_lname']."',use_enabled='".$_POST['use_enabled']."',pri_id='".$_POST['pri_id']."' WHERE use_id = '".mysql_real_escape_string($_POST['use_id'])."'";
			$theU = mysql_query($theQ);
		} else { // New User
			$theQ.= "'".$_POST['use_username']."','".$_POST['use_email']."','".$_POST['use_fname']."','".$_POST['use_lname']."','".$_POST['use_enabled']."','".$_POST['pri_id']."')";
			$theI = mysql_query($theQ);
			if(!$theI){
				echo '<h4>Unable to create user</h4>';
				
			} else {
				$_POST['use_id'] = mysql_insert_id();

				// Send the user an email with their details
				if($_POST['email_user_details']){
					$message = '<p>Hello '.$_POST['use_fname'].',</p>';
					$message .= '<p>You have been setup with a ProSite membership for the website located at <a href="'.$_CONFIG['domain'].'">'.$_CONFIG['domain'].'</a>.</p>';
					
					$message .= '<p>To get started go to <a href="'.$_CONFIG['domain'].'/admin">'.$_CONFIG['domain'].'/admin</a> and login using your username <b>'.$_POST['use_username'].'</b> and password <b>'.$_POST['use_pass'].'</b></p>
					<p>For documentation and instructions on using ProSite please visit <a href="'.$_CONFIG['domain'].'/admin/help.php">'.$_CONFIG['domain'].'/admin/help.php';	
					
					$htmlMail = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
			<title>Email</title>
			<style type="text/css">
			html, body {font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px}
			#container {width:90%; margin:0 auto}
			
			#footer {font-size:10px; color:#999999; text-align:center; padding-bottom:150px;}
			a{color:#0066CC; text-decoration:none}
			a:hover {color:#990000}
			a img {border:0}
			</style>
			</head>
			<body><div id="container">'.$message.'</container></body></html>';
			
					$textMail = str_replace("</p>","\n\n",$message);
					$textMail = str_replace("<br />","\n",$textMail);
					$textMail = strip_tags($textMail);
					
					$mail = new PHPMailer();
					$mail->From     = $_CONFIG['siteemail'];
					$mail->FromName = $_CONFIG['sitetitle'];
					$mail->Subject = "Login details for ".$_CONFIG['sitetitle'];
					$mail->IsHTML(true);
					$mail->AltBody = $textMail;
					$mail->Body = $htmlMail;
					$mail->IsSMTP(true);
					$mail->AddAddress($_POST['use_email']);
					if(!$mail->Send()) {
						echo "<p>Please contact a member of the ProSouth technical team at </p>".$_CONFIG['mailTechnical'];
					} else {
						echo "<p>Email of details sent.</p>\n";		
					}
				}
			}
		} 
	} else {
		echo "<div class=\"errorbox\">";
		foreach($error as $e) echo $e."<br />";
		echo "</div>";
	}
}

?>

	<h2>User Management</h2>
	<h3 style="text-align:center;">Edit Existing Users</h3>
	<? 
		$theQ = "SELECT * FROM tbluser";
		$theR = mysql_query($theQ);
		if(mysql_num_rows($theR)){
			while($row = mysql_fetch_row($theR)){
				$editing = $row[0]==$_POST['use_id'];
	?>
			<form class="user" method="post" action="/_users">
				<fieldset>		
					<legend>User &quot;<?=$row[1]?>&quot; Settings</legend>
					<?=($editing && $theU)?"<h4>User has been updated sucessfully</h4>":"";?>
					<?=($editing && $theI)?"<h4>User has been created sucessfully</h4>":"";?>
					<label>Username</label>
					<input name="use_username" value="<?=($_POST['use_id']==$row[0])?$_POST['use_username']:$row[1]?>" <?=$editing && $error['use_username']?'class="error"':''?> /><br /><br />
					<label>First Name</label>
					<input name="use_fname" value="<?=($_POST['use_id']==$row[0])?$_POST['use_fname']:$row[4]?>" <?=$editing && $error['use_fname']?'class="error"':''?> /><br /><br />
					<label>Last Name</label>
					<input name="use_lname" value="<?=($_POST['use_id']==$row[0])?$_POST['use_lname']:$row[5]?>" <?=$editing && $error['use_lname']?'class="error"':''?> /><br /><br />
					<label>Email</label>
					<input name="use_email" value="<?=($_POST['use_id']==$row[0])?$_POST['use_email']:$row[3]?>" <?=$editing && $error['use_email']?'class="error"':''?> /><br /><br />
					<label>Reset Password</label>
					<input id="use_pass<?=$row[0]?>" type="password" name="use_pass" <?=$editing && $error['use_pass']?'class="error"':''?> /> <span class="tip">Type in password to reset or leave blank</span><br /><br />
					<label>User Active</label>
					<input name="use_enabled" type="checkbox" <? if($_POST['use_id']==$row[0]){ echo($_POST['use_enabled'])?'checked="checked"':''; } else {echo($row[6]==true)?'checked="checked"':''; }?>  /><br /><br />
					<label>&nbsp;</label><input type="hidden" name="use_id" value="<?=$row[0]?>" />
					<input type="submit" name="delete" value="Delete" class="cancel" onclick="return confirm('Are you sure you want to delete this user?')" />
					<input type="submit" name="insert" value="Update" class="confirm" />
				</fieldset>
			</form>
		<?
			}
			$editing = $_POST['insert'] && !$_POST['use_id'];
		?>
		
		<h3 style="text-align:center;">Create New User</h3>
		<form class="user"  action="/_users" method="post">
				<fieldset>		
					<legend>User Settings</legend>
					<label>Username</label>
					<input name="use_username" value="<?=(isset($_POST['use_id']))?'':$_POST['use_username']?>" <?=$editing && $error['use_username']?'class="error"':''?> /><br /><br />
					<label>First Name</label>
					<input name="use_fname" value="<?=(isset($_POST['use_id']))?'':$_POST['use_fname']?>" <?=$editing && $error['use_fname']?'class="error"':''?> /><br /><br />
					<label>Last Name</label>
					<input name="use_lname" value="<?=(isset($_POST['use_id']))?'':$_POST['use_lname']?>" <?=$editing && $error['use_lname']?'class="error"':''?> /><br /><br />
					<label>Email</label>
					<input name="use_email" value="<?=(isset($_POST['use_id']))?'':$_POST['use_email']?>" <?=$editing && $error['use_email']?'class="error"':''?> /><br /><br />
					<label>Create Password</label>
					<input id="use_pass" type="password" name="use_pass" <?=$editing && $error['use_pass']?'class="error"':''?> /><br /><br />
					<label for="use_enabled_new">User Active</label>
					<input name="use_enabled" id="use_enabled_new" type="checkbox" <?=(!isset($_POST['use_id']) && $_POST['use_enabled'])?'checked="checked"':''?> /><br /><br />
					<label for="email_user_details">Email User Details</label>
					<input id="email_user_details" name="email_user_details" type="checkbox" <?=(!isset($_POST['use_id']) && $_POST['email_user_details'])?'checked="checked"':''?> /> <span class="tip">Send the user you are creating their username, password, details on what pages they may edit and instructions on how to edit them</span><br /><br />		
					<input type="hidden" name="insert" value="1" />
					<label>&nbsp;</label><input type="submit" value="Create User" class="confirm" />
				</fieldset>
			</form>
		
		
		
<?	}
?>
<?
	include('assets/bottom.php');
	
?>