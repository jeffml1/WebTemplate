<?
	function imgFormat($imgdata,$keep_raw=false,$f_width=false,$f_height=false,$f_bgcolor=false,$t_width=false,$t_height=false,$t_bgcolor=false){
		$images['img_data_raw'] = file_get_contents($imgdata['tmp_name']);
		if ($f_width || $f_height) {
			$images['img_data_full'] = resizeImage($images['img_data_raw'],$f_width,$f_height,$imgdata['type'],$f_bgcolor);
		} else {
			$images['img_data_full'] = file_get_contents($imgdata['tmp_name']);
		}
		if ($t_height && $t_height){
			$images['img_data_thumb'] = resizeImage($images['img_data_raw'],$t_width,$t_height,$imgdata['type'],$t_bgcolor);	
		} else {
			$images['img_data_thumb'] = "Thumbnail has not been created, please set variables when calling imgFormat function if you would like thumbnails created";
		}
		if($keep_raw){
			$images['img_data_raw'] = $images['img_data_raw'];
		} else {
			$images['img_data_raw'] = "Raw image has not been saved, please set variables when calling imgFormat function if you would like thumbnails created";
		}
		
		return $images;
	}
	
	function resizeImageMagick($imgdata,$width=false,$height=false,$type,$bgcolor=false) {
		$temp = $_SERVER['DOCUMENT_ROOT'].'/assets/temp/magick_'.microtime(true);
		switch ($type){
			case "image/gif" 	: $ext = '.gif'; break;
			case "image/jpeg"	: $ext = '.jpg'; break;
			case "image/png"	: $ext = '.png'; break;
			default				: $ext = '.jpg'; break;
		}
		file_put_contents($temp.'orig'.$type,$imgdata);
		$size = getimagesize($temp.'orig'.$type);
	
	
			$t_wd = $o_wd = $size[0];
			$t_ht = $o_ht = $size[1];
			
			if ($height) $c_ht = $height;
			else $height = $o_ht;
			if ($width) $c_wd = $width;
			else $width = $o_wd;
					
			if ($t_wd>$width) {
				$t_wd = $width;
				$t_ht = round($t_ht * $t_wd / $o_wd);
			} 
			if ($t_ht>$height) {
				$t_wd = round($t_wd * $height / $t_ht);
				$t_ht = $height;
			}
			
			if (!isset($c_ht) || !isset($c_wd) || !$bgcolor){
				$c_ht = $t_ht;
				$c_wd = $t_wd;
			}
			
			exec('convert '.$temp."orig".$type." -quality 80% -resize ".$maxwid.'x'.$maxhei."> ".$temp.'final'.$ext);
			
			
		
			/*$t_im = imagecreatetruecolor($c_wd,$c_ht);
			
			$red = hexdec(substr($bgcolor, 1, 2));
			$green = hexdec(substr($bgcolor, 3, 2));
			$blue = hexdec(substr($bgcolor, 5, 2));
			$rgbcolor = imagecolorallocate($t_im,$red,$green,$blue);
			imagefill($t_im,0,0,$rgbcolor);
			$dstY = ($c_ht-$t_ht)/2;
			$dstX = ($c_wd-$t_wd)/2;
			imagecopyresampled($t_im, $o_im, $dstX, $dstY, 0, 0, $t_wd, $t_ht, $o_wd, $o_ht);
			ob_start();		
			if($type=='image/pjpeg') $type = "image/jpeg";
			if($type=='image/x-png') $type = "image/png";
			switch ($type){
				case "image/gif" 	: imagegif($t_im); break;
				case "image/jpeg"	: imagejpeg($t_im, '', '80'); break;
				case "image/png"	: imagepng($t_im); break;
				default				: imagejpeg($t_im, '', '80'); break;
			}
			$resized = ob_get_contents();
			ob_end_clean();
			imageDestroy($o_im);
			imageDestroy($t_im);*/
			return $resized;
	/*	} else {
			echo "<strong>Error:</strong> An unknown error has occured";
			return false;
		}*/
	
	}
	
	function resizeImage($imgdata,$width=false,$height=false,$type,$bgcolor=false) {
		if ($o_im = imagecreatefromstring($imgdata)) {
			$t_wd = $o_wd = imagesx($o_im) ;
			$t_ht = $o_ht = imagesy($o_im) ;
			
			if ($height) $c_ht = $height;
			else $height = $o_ht;
			if ($width) $c_wd = $width;
			else $width = $o_wd;
					
			if ($t_wd>$width) {
				$t_wd = $width;
				$t_ht = round($t_ht * $t_wd / $o_wd);
			} 
			if ($t_ht>$height) {
				$t_wd = round($t_wd * $height / $t_ht);
				$t_ht = $height;
			}
			
			if (!isset($c_ht) || !isset($c_wd) || !$bgcolor){
				$c_ht = $t_ht;
				$c_wd = $t_wd;
			}
		
			$t_im = imagecreatetruecolor($c_wd,$c_ht);
			
			$red = hexdec(substr($bgcolor, 1, 2));
			$green = hexdec(substr($bgcolor, 3, 2));
			$blue = hexdec(substr($bgcolor, 5, 2));
			$rgbcolor = imagecolorallocate($t_im,$red,$green,$blue);
			imagefill($t_im,0,0,$rgbcolor);
			$dstY = ($c_ht-$t_ht)/2;
			$dstX = ($c_wd-$t_wd)/2;
			imagecopyresampled($t_im, $o_im, $dstX, $dstY, 0, 0, $t_wd, $t_ht, $o_wd, $o_ht);
			ob_start();		
			if($type=='image/pjpeg') $type = "image/jpeg";
			if($type=='image/x-png') $type = "image/png";
			switch ($type){
				case "image/gif" 	: imagegif($t_im); break;
				case "image/jpeg"	: imagejpeg($t_im, '', '80'); break;
				case "image/png"	: imagepng($t_im); break;
				default				: imagejpeg($t_im, '', '80'); break;
			}
			$resized = ob_get_contents();
			ob_end_clean();
			imageDestroy($o_im);
			imageDestroy($t_im);
			return $resized;
		} else {
			echo "<strong>Error:</strong> An unknown error has occured";
			return false;
		}
	}

?>