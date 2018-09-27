<?
	if (isset($_GET['sid'])) session_id($_GET['sid']);
	session_start();
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	$_SESSION['alive'] = rand(1,999);
	echo $_SESSION['alive'].'.';
	echo isset($_SESSION['admin'])?1:0;
	echo '.'.$_GET['sid'];
?>