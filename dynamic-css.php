<?php

	header("Content-type: text/css; charset: UTF-8");

	$contentsidebars = get_option('content_sidebars');

	echo $contentsidebars['dynamic_css'];

	exit;

?>