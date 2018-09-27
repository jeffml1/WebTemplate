<?
	session_start();
	header('Content-Type: text/html; charset=utf-8');
	require_once('lib.php');
	$cms = new simpleCMS();
	ob_start();


?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" sizes="48x48"  href="/favicon.ico">
<?
	if($cms->pag_title) $title = htmlentities($cms->pag_title,ENT_COMPAT,'UTF-8');
	elseif($cms->pag_name!='Home') $title = $cms->pag_name.' - '.$_CONFIG['sitetitle'];
	else $title = $_CONFIG['sitetitle'];

?>
		<title><?=$title?></title>
		<meta name="description" content="<?=$cms->pag_metadesc?htmlentities($cms->pag_metadesc,ENT_COMPAT,'UTF-8'):''?>" />
		<? include($_SERVER['DOCUMENT_ROOT'].'/assets/css/css.php'); ?>
<?
	if(isset($_GET['edit'])) {
?>
		<script type="text/javascript" src="/assets/tiny_mce/tiny_mce.js"></script>
<?
	}
?>
	</head>

	<body class="<?=$_SERVER['SCRIPT_NAME']=='/index.php'?str_replace('/','_',$cms->pag_code):'special'?> <?= ($cms->pag_name == "Home" && $_SERVER['SCRIPT_NAME']=='/index.php') ? 'Home' : '' ?> nojs">
	<? $cms->doAdminMenu("adminnav"); ?>
		<div class="header">
			<div class="container">

				<a href="/" id="headerLogo"></a>
				<? $cms->doMenu('nav') ?>
				<a class="nav-open-button" href="#"></a>
			</div>
		<? if($cms->pag_name == "Home" && $_SERVER['SCRIPT_NAME']=='/index.php' && !isset($_GET['edit'])){ ?>
			<div class="hero">
				<div class="container">
					<div id="insightTxt">Cheaper Power. No Gimmicks.</div>
				</div>
			</div>
		<? } ?>
		</div>
		<div class="body">
			<div class="container">
