<!DOCTYPE html>
<html <?php language_attributes(); ?> style="margin-top: 0px !important">

<head>
	<?php $title = ucwords( strtolower ( get_the_title() ) ); ?>
	<title>Can Pep Rey <?php if ( !is_front_page() ) echo "– " . $title; ?></title>
    <meta name="description" content="">
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta property="og:url" content="<?php bloginfo('url'); ?>" />
    <meta property="og:type" content="Website" />
    <meta property="og:title" content="Can Pep Rey" />
    <meta property="og:description" content="Can Pep Rey is a wom­enswear brand based on the rel­a­tive con­cept of liv­ing space, fash­ion and art." />
    <meta property="og:image" content="<?php bloginfo("template_url"); ?>/img/can-pep-rey.png" />

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url') ?>">

	<link rel="shortcut icon" href="<?php bloginfo("template_url"); ?>/img/favicon-160x160.png">
	<link rel="apple-touch-icon" sizes="160x160" href="/favicon-160x160.png">
	
	<script>
		// Picture element HTML5 shiv
		document.createElement( "picture" );
	</script>

	<?php wp_head(); ?>
</head>

<body>