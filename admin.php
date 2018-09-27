<?
	session_start();
	if($_POST['username'] && $_POST['password']) {
		require_once('assets/lib.php');
		connectToDB();
		$theQ = "SELECT use_id FROM tbluser WHERE use_username = '".mysql_real_escape_string($_POST['username'])."' AND use_pass = '".md5($_POST['password'])."' AND use_enabled = 1";
		$theQ = mysql_query($theQ);
		if(mysql_num_rows($theQ)) {
			$_SESSION['admin'] = mysql_result($theQ,0);			
		} else {
			$error = "<p>Sorry, your username and password was not accepted</p>";
		}
	}
?>
<?
	include('assets/top.php');
?>
<h2>Admin Login</h2>
<?
	echo $error;
	if($_SESSION['admin']) {
		echo "<p>You are now logged in.</p>";
		if(is_array($plugins)) foreach($plugins as $p) if($p->position&POSITION_ADMINLOGIN) $p->adminLogin($this);
	} else {
?>
<form method="post" action="/_admin">
	<label>Username</label><input name="username" /><br /><br />

	<label>Password</label><input name="password" type="password" /><br /><br />

	<label>&nbsp;</label><input type="submit" value="Log In" />
</form>
<?
	}
?>
<?
	include('assets/bottom.php');
?>