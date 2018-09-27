<?
	session_start();
	$_SESSION['admin'] = 0;
	unset($_SESSION['admin']);
	
	$_SESSION['ret_num'] = 0;
	unset($_SESSION['ret_num']);
	
	$_SESSION['pri_num'] = 0;
	unset($_SESSION['pri_num']);	
	
	$_SESSION['mem_num'] = 0;
	unset($_SESSION['mem_num']);
	
	session_destroy();
	header('Location:/');
?>