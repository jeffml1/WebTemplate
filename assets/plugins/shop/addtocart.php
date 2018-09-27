<?
	session_start();
	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$id = $_GET['id'];
		$_SESSION['shcart'][$id] += 1;
		//setcookie('shcart',serialize($_SESSION['shcart']),time()+60*60*2);
		echo $_SESSION['shcart'][$id];
	}
?>