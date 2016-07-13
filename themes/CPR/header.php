<!DOCTYPE html>
<html <?php language_attributes(); ?> style="margin-top: 0px !important">

<head>
	<?php 
	$title = ucwords( strtolower ( get_the_title() ) ); 
	?>
	<title>Can Pep Rey <?php if ( !is_front_page() ) echo "– " . $title; ?></title>
    <meta name="description" content="">
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta property="og:url" content="<?php bloginfo('url'); ?>/" />
    <meta property="og:type" content="Website" />
    <meta property="og:title" content="Can Pep Rey" />
    <meta property="og:description" content="Can Pep Rey is a wom­enswear brand based on the rel­a­tive con­cept of liv­ing space, fash­ion and art." />
    <meta property="og:image" content="http://jackandbill.com/cpr2/wp-content/uploads/2016/03/CPR-71-1024x683.jpg" />
    <meta property="og:image:width" content="200" />
	<meta property="og:image:height" content="200" />

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url') ?>">

	<link rel="shortcut icon" href="<?php bloginfo("template_url"); ?>/img/favicon-160x160.png">
	<link rel="apple-touch-icon" sizes="160x160" href="/favicon-160x160.png">
	<script src="<?php bloginfo('template_url'); ?>/js/jquery.min.js"></script>
	<script>
		// PICTURE ELEMENT HTML5 SHIV
		document.createElement( "picture" );
		// FIX IE CONSOLE ERRORS
		if (!window.console) console = {log: function() {}}; 
	</script>


	<?php wp_head(); ?>
</head>

<body>