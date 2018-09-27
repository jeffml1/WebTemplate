<?
	ob_start("ob_gzhandler");
	if($_GET['f']) {
		$_GET['f'] = str_replace(" ", "+", $_GET['f']);
		$type = explode('.',$_GET['f']);
		$type = strtolower($type[sizeof($type)-1]);
		$ok = array('png','gif','jpg','jpeg');
		switch($type) {
			case 'png' : $ct = 'image/png'; break;
			case 'jpg' :
			case 'jpeg': $ct = 'image/jpg'; break;
			case 'gif' : $ct = 'image/gif'; break;
			case 'js'  : $ct = 'text/javascript'; break;
			case 'css' : $ct = 'text/css'; break;
			case 'ico' : $ct = 'image/x-icon'; break;
			case 'eot' : $ct = 'application/vnd.bw-fontobject'; break;
			case 'svg' : $ct = 'image/svg+xml'; break;
			case 'ttf' : $ct = 'application/x-font-ttf'; break;
			case 'woff': $ct = 'application/x-woff'; break;
			case 'woff2': $ct = 'application/x-woff2'; break;
			case 'otf': $ct = 'application/x-font-opentype'; break;
			default    : die($_GET['f']);
		}
		if(!file_exists($_GET['f'])) die($_GET['f']);
		
		$mod = filemtime($_GET['f']);
		
		$etag = md5_file($_GET['f']); 
		$expires = 60*60*24*365;
		
		header('Content-type: '.$ct);
		header('Last-Modified:'.gmdate('D, d M Y H:i:s',$mod));
		header("Etag: ".$etag);
		header("Pragma: public");
		header("Cache-Control: maxage=".$expires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
		
		if(in_array($type,$ok) && isset($_GET['w'],$_GET['h']) && $_GET['w'] && $_GET['h'] && is_numeric($_GET['w'].$_GET['h'])) {
			$temp = $_SERVER['DOCUMENT_ROOT'].'/upload/'.microtime(true).rand(0,1000).'.'.$type;
			$a = exec('convert '.$_GET['f']." -resize ".$_GET['w']."x".$_GET['h']."^ -gravity center -extent ".$_GET['w']."x".$_GET['h']." ".$temp,$b,$c);
			readfile($temp);
			unlink($temp);
		} else {
			readfile($_GET['f']);
		}
	}
?>