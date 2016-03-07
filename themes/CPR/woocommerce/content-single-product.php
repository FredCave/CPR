<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php 
$all_classes = get_post_class();
$bottom_classes = array( "product-tag-shorts", "product-tag-leggings", "product-tag-skirt", "product-tag-pants" );
// echo "<pre>";
// var_dump( $all_classes );
// echo "</pre>";
$classes = array();
if( count( array_intersect($bottom_classes, $all_classes) ) > 0 ) {
     // at least one of $target is in $haystack
	$classes[] = "bottom";
}
?>

<?php if ( is_single() ) { 
	$classes[] = "single_product";
} else { 
	$classes[] = "non_single_product";
} ?>

<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>

	<div class="custom-content">

		<?php if ( is_single() ) { ?>

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

				<img class="" 
	                sizes="100vw" 
	                srcset="<?php echo $full; ?> 2000w,
	                		<?php echo $extralarge; ?> 1024w,
	                		<?php echo $large; ?> 800w,
	                        <?php echo $medium; ?> 600w,
	                        <?php echo $thumb; ?> 300w"
	                src="<?php echo $extralarge; ?>"
	                alt="Can Pep Rey — <?php the_title(); ?>"
	            />	

			</div><!-- end of #single_main_image -->

		<?php } ?>
		
		<?php /*
		<div id="" class="single_additional_images row">

			<?php if ( have_rows("product_images") ):				
				$i = 0; 
				while ( have_rows("product_images") ) : the_row();
					// FIRST IMAGE ON LEFT
					if ( $i === 0 ) {
						$position = "left";
					} else {
						$position = "right";
					}
					$i++;

					$image = get_sub_field("product_image");
					if( !empty($image) ): 
			            $thumb = $image["sizes"][ "thumbnail" ]; // 300
			            $medium = $image["sizes"][ "medium" ]; // 600
			            $large = $image["sizes"][ "large" ]; // 800
			            $extralarge = $image["sizes"][ "extra-large" ]; // 1024
			            $full = $image["url"];
			        endif; ?>

					<div class="picturefill-background position_<?php echo $position; ?>">
			        	<!-- CURRENTLY REFERRING TO WINDOW WIDTH, NOT IMAGE WIDTH ???? -->
					    <span data-src="<?php echo $thumb; ?>"></span>
					    <span data-src="<?php echo $medium; ?>" data-media="(min-width: 300px)"></span>
					    <span data-src="<?php echo $large; ?>" data-media="(min-width: 600px)"></span>
					    <span data-src="<?php echo $extralarge; ?>" data-media="(min-width: 800px)"></span>
					</div>	
			        <?php
			        
				endwhile;
			endif; 
			*/
			?>

			<?php wc_get_template_part( 'content', 'single-product-info' ); ?>

		<?php /* </div><!-- end of #single_additional_images --> */ ?>

	</div>

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
