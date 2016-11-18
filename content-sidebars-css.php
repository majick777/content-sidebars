<?php

	// Output Header

	header("Content-type: text/css; charset: UTF-8");

	// for Direct URL Loading

	if (strstr($_SERVER['REQUEST_URI'],'content-sidebars-css.php')) {

		// Find/Require for Blog Loader

		function fcs_find_require($file,$folder=null) {
			if ($folder === null) {$folder = dirname(__FILE__);}
			$path = $folder.DIRECTORY_SEPARATOR.$file;
			if (file_exists($path)) {require($path); return $folder;}
			else {
				$upfolder = fcs_find_require($file,dirname($folder));
				if ($upfolder != '') {return $upfolder;}
			}
		}

		// Load using our friend Shorty...

		define('SHORTINIT', true);
		$wp_root_path = fcs_find_require('wp-load.php');

	}

	// Output Content Sidebars CSS

	$contentsidebars = get_option('content_sidebars');
	echo $contentsidebars['dynamic_css'];
	exit;

?>