<?
if(isset($_POST['filename'])){
	unlink("../../../../theme/user/".$_POST['filename']);
	//Do Deletion
	echo 'OK';
	die();
}
echo 'NOT OK';
?>