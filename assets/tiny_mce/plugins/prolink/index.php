<?
	session_start();
	include($_SERVER['DOCUMENT_ROOT'].'/assets/lib.php');
	connectToDB();
	
	$cms = new SimpleCMS();
	//createLinks();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>ProLink Manager</title>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/custom.js"></script>
<style type="text/css">
#toolbar {padding:5px; border:1px solid #bbb; background:#eee; clear:both;}
#types { padding: 0px 5px; background:#eee; border:1px solid #ccc; border-right:0; width:200px; float:left; height:570px; overflow:auto}
a img{border:0}
a{text-decoration:none;}
.container {padding:5px; background:#FFFFFF; border:1px solid #ccc; height:560px; overflow:auto }
h1,h2,h3,h4,h5 {margin:0; padding:0px;}
#delcat{display:none; padding:none; margin:10px 0px 0px 0px;}
a, a:link, a:visited {color:#0066CC}
a:hover {color:#990000}
#single ul {margin:0 0 0 20px; padding:0;}
#types a {font-size:16px}
#single a {font-size:12px}
</style>


</head>
<body>


<div id="types">
<h2>Insert Links</h2>
	<a href="index.php?p=internal">Internal Link</a><br />		
	<p style="margin:0 0 0 2em">Insert a link to another page on this site</p>
	<a href="index.php?p=file">Link to File</a><br />
	<p style="margin:0 0 0 2em">Upload and link to a file</p>
</div>

<div class="container">
<?
	if(isset($_GET['p'])){
		include($_GET['p'].'.php');
	} else {
		include('internal.php');
	}
?>
</div>
</body>
</html>
