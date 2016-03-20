<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

if ( is_single() ) { 
	$classes[] = "single_product";
} else { 
	$classes[] = "non_single_product";
} 
?>

<!--<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>-->
<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>">

	<div id="single_main_image">

		<?php 
		$image = get_field("product_images")[0]["product_image"];
		if( !empty($image) ): 
            $thumb = $image["sizes"][ "thumbnail" ]; // 300
            $medium = $image["sizes"][ "medium" ]; // 600
            $large = $image["sizes"][ "large" ]; // 800
            $extralarge = $image["sizes"][ "extra-large" ]; // 1024
            $full = $image["url"];
        endif; ?>

		<picture>
			<source srcset="<?php echo $full; ?>" media="(min-width: 683px)">
			<source srcset="<?php echo $extralarge; ?>" media="(min-width: 533px)">	
			<source srcset="<?php echo $large; ?>" media="(min-width: 400px)">
			<source srcset="<?php echo $medium; ?>" media="(min-width: 200px)">
			<img srcset="<?php echo $thumb; ?>" alt="Can Pep Rey – <?php the_title(); ?>">
		</picture>

	</div><!-- end of #single_main_image -->

	<!-- PRODUCT INFO -->
	<?php wc_get_template_part( 'content', 'single-product-info' ); ?>

</div><!-- END OF PRODUCT -->







