<?

	$files = $_CONFIG['css'];

	$dir = $_SERVER['DOCUMENT_ROOT'];

	$change = 0;
	foreach($files as $f) {
		$time = filemtime($dir.$f);
		if($time>$change) $change = $time;
	}
	$cache = '/assets/css/cache/'.$change.'.css';
	if(!file_exists($dir.$cache)) {
		$file = '';
		foreach($files as $f) {
			$file.= file_get_contents($dir.$f);
		}
		include('CssCrush/CssCrush.php');

		$data = csscrush_string($file);

		$data = str_replace('url("/assets/images/','url("/assets/images/'.$change.'/',$data);
		$data = str_replace('url(/assets/images/','url(/assets/images/'.$change.'/',$data);

		file_put_contents($dir.$cache,$data);

		$files = scandir($dir.'/assets/css/cache');
		foreach($files as $num=>$file) {
			if($file!='.' && $file!='..' && $num<sizeof($files)-3) unlink($dir.'/assets/css/cache/'.$file);
		}
	}
	echo '<link href="'.$cache.'" rel="stylesheet" type="text/css" />'."\n";
?>
