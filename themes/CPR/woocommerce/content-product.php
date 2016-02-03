<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
	return;
}

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}

?>

<li <?php post_class( $classes ); ?>>

	<a href="<?php the_permalink(); ?>" class="open_single">

		<!-- LOAD TWO IMAGES FOR HOVER EFFECT -->

		<?php if ( have_rows("product_images") ) : 
			$i = 0;
			while ( have_rows("product_images") ) : the_row();
				if ( $i < 2 ) :
					$image = get_sub_field('product_image');
					if( !empty($image) ): 
			            $thumb = $image['sizes'][ "thumbnail" ]; // 300
			            $medium = $image['sizes'][ "medium" ]; // 600
			            $large = $image['sizes'][ "large" ]; // 800
			            $extralarge = $image['sizes'][ "extra-large" ]; // 1024
			        endif;
			        ?>

			        	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

				        <div class="picturefill-background">
				        	<!-- CURRENTLY REFERRING TO WINDOW WIDTH, NOT IMAGE WIDTH ???? -->
						    <span data-src="<?php echo $thumb; ?>"></span>
						    <span data-src="<?php echo $medium; ?>" data-media="(min-width: 300px)"></span>
						    <span data-src="<?php echo $large; ?>" data-media="(min-width: 600px)"></span>
						    <span data-src="<?php echo $extralarge; ?>" data-media="(min-width: 800px)"></span>
						</div>			

				<?php   
				endif;
				$i++;      
			endwhile;
		endif; ?>

		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			//do_action( 'woocommerce_before_shop_loop_item_title' );

			/* INCLUDE TITLE AND PRICE ON HOVER OVER ??? */

			/**
			 * woocommerce_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_product_title - 10
			 */
			// do_action( 'woocommerce_shop_loop_item_title' );

			/**
			 * woocommerce_after_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			// do_action( 'woocommerce_after_shop_loop_item_title' );

			/**
			 * woocommerce_after_shop_loop_item hook
			 *
			 * @hooked woocommerce_template_loop_add_to_cart - 10
			 */
			// do_action( 'woocommerce_after_shop_loop_item' );
		?>
	
	</a>

</li>

