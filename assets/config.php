<?
	/** General Site Details **/
	$_CONFIG['sitetitle']			= 'Payless Energy';
	$_CONFIG['domain']				= 'https://paylessenergy.co.nz';			//No trailing slash!
	$_CONFIG['siteemail']			= 'enquiries@paylessenergy.co.nz';			//The "From" email address for login details etc.
	$_CONFIG['sitename']			= 'Payless Energy'; 					//The "From" name
	$_CONFIG['mailForm']			= 'matthew@logicstudio.nz';			//Where the "Mail Form" should send to
	$_CONFIG['ordermail']			= '';								//Where new order notifications should go
	$_CONFIG['plugins']				= '';
	$_CONFIG['paypal']				= '';								//Paypal username
	$_CONFIG['defaultPageText']		= "";
	$_CONFIG['skippages']			= array(1, 43, 52, 55, 57); //Pages to skip from menu

	/** CSS/JS Files to Load **/
	$_CONFIG['css'][] = 	'/assets/css/adminStyles.css';
	$_CONFIG['css'][] = 	'/assets/css/fonts.css';
	$_CONFIG['css'][] = 	'/assets/css/fontawesome.css';
	//$_CONFIG['css'][] = 	'/assets/css/animate.css';
	$_CONFIG['css'][] = 	'/assets/css/styles.css';
	//$_CONFIG['css'][] = 	'/assets/css/fancybox.css';
	$_CONFIG['css'][] = 	'/assets/css/admin.css';
	$_CONFIG['css'][] = 	'/assets/css/responsive.css';
	// $_CONFIG['css'][] = 	'/assets/css/rangeslider.css';
	//$_CONFIG['css'][] = 	'/assets/css/jquery.sidr.light.css';
	//$_CONFIG['css'][] = 	'/assets/css/calendar.css';

	$_CONFIG['js'][] = 		'/assets/scripts/jquery-1.8.2.min.js';
	//$_CONFIG['js'][] = 		'/assets/scripts/jquery.sidr.min.js';
	//$_CONFIG['js'][] = 		'/assets/scripts/jquery.fancybox.js';
	// $_CONFIG['js'][] = 		'/assets/scripts/wow.js';
	//$_CONFIG['js'][] = 		'/assets/scripts/jquery-ui-1.8.16.custom.min.js';
	//$_CONFIG['js'][] = 		'/assets/scripts/jquery.loadmask.min.js';
	//$_CONFIG['js'][] = 		'/assets/tiny_mce/tiny_mce.js';
	// $_CONFIG['js'][] = 		'/assets/scripts/rangeslider.min.js';
	$_CONFIG['js'][] = 		'/assets/scripts/admin.js';
	$_CONFIG['js'][] = 		'/assets/scripts/common.js';

	//$_CONFIG['js'][] = 		'/assets/scripts/markercluster.js';

	/** MySQL Database Connection Details **/
	$_CONFIG['dbhost'] = 	'logicstudio';//Host
	$_CONFIG['dbuser'] = 	'paylessenergy';					//Username
	$_CONFIG['dbpass'] = 	'LoUM9PAxJxNlu!6V';					//Password
	$_CONFIG['dbname'] = 	'paylessenergy';				//Database Name

	if(strpos($_SERVER['HTTP_HOST'],'payless.co.nz.')!==false) {
		$_CONFIG['mailForm'] 	= 'matthew@logicstudio.nz';
		$_CONFIG['siteemail'] 	= 'matthew@logicstudio.nz';
	}
?>
