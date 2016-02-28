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
/*
?>

<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class( ); ?>>

	<div class="custom-content"> */ ?>
		
		<div id="" class="single_additional_images row">

			<div class="gallery_arrow">
				<img src="<?php bloginfo('template_url'); ?>/img/gallery_arrow.svg" />
			</div>

			<?php if ( have_rows("product_images") ):				
				$i = 0; 
				while ( have_rows("product_images") ) : the_row();
					/* FIRST IMAGE ON LEFT */
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
			endif; ?>

			<div class="single_info">
				<ul>
					<!-- TITLE -->
					<li class="wrap no_break product_title"><?php the_title(); ?></li>
					<!-- SKU IF ON WHOLESALE -->
					<?php if ( is_page( "wholesale" ) ) :
						global $product; ?>
						<li class="" style="text-align:center"><?php echo $product->sku; ?></li>
			    	<?php endif; ?>
					<!-- PRODUCT DESCRIPTION -->
					<div class="product_desc_toggle">
						<p class="wrap">Info</p>
						<div class="product_desc">
							<li class="wrap no_break">
								<?php the_field("product_description"); ?>
							</li>
						</div>
					</div>
					<!-- FABRIC INFO -->
					<li class="wrap no_break">
						<?php the_field("product_info"); ?>
					</li>
			    	<!-- LINKS TO OTHER COLOURS -->
			    	<?php if ( !is_page( "wholesale" ) ) :
						echo other_colours( get_the_ID() ); 
			    	endif; ?>
			    	
					<!-- PRICES -->
					<?php echo get_prices( get_the_ID() );
					// the_ID();
					// $meta = get_post_meta( get_the_ID() );

				    // print_r( $meta );

					// $this_post = the_post();
					// $availability = $product->get_availability();
					// print_r($availability);
					// var_dump( $post );


					// if ( ! $product->is_in_stock() ) {
					//     echo "in stock";
					// } else {
					// 	echo "out of stock";
					// }

					remove_action( "woocommerce_single_product_summary", "woocommerce_template_single_title", 5 );
					remove_action( "woocommerce_single_product_summary", "woocommerce_template_single_meta", 40 );

					/**
					 * woocommerce_single_product_summary hook.
					 *
					 * @hooked woocommerce_template_single_title - 5
					 * @hooked woocommerce_template_single_rating - 10
					 * @hooked woocommerce_template_single_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 * @hooked woocommerce_template_single_add_to_cart - 30
					 * @hooked woocommerce_template_single_meta - 40
					 * @hooked woocommerce_template_single_sharing - 50
					 */
					do_action( 'woocommerce_single_product_summary' );

					?>
					
					<!-- HOW MANY IN CART -->
					<!--<li class="">
						<?php
							foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
						        if ( get_the_ID() === $values["product_id"] ) {
						        	echo "In cart: " . $values["quantity"]; 
						        }
						    } 
					    ?>
			    	</li>-->

				</ul>

				<div class="wrap">
					<?php /*woocommerce_template_single_add_to_cart();*/ ?>
				</div>

			</div><!-- end of .single_info -->	

		</div><!-- end of #single_additional_images -->

<?php /*

	</div>

</div><!-- #product-<?php the_ID(); ?> -->
*/ ?>

<?php do_action( 'woocommerce_after_single_product' ); ?>
