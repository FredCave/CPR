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

<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class( ); ?>>

	<div class="custom-content">
		
		<div id="" class="single_additional_images row">

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
					<!-- FABRIC INFO -->
					<li class="wrap">
						<?php the_field("product_info"); ?>
					</li>
					<!-- SIZES -->
					<li class="wrap">SIZES</li>
			    	<!-- LINKS TO OTHER COLOURS -->
			    	<?php echo other_colours( get_the_ID() ); ?>
					<!-- PRICES -->
					<li class="">
						<?php /*
						global $product;
						$retail_price = $product->regular_price;
						if ( is_page( "wholesale" ) ) {
							echo "Retail Price: " . $retail_price;
						} else {
							echo "Price: " . $retail_price;
						} ?>
					</li>
					<!-- IF WHOLESALE -->
					<?php if ( is_page( "wholesale" ) ) : ?>
						<li class="">
							<?php
							echo "Wholesale Price: " . $product->price;
							?>
						</li>
					<?php endif; */ ?>

					<?php 

					global $product;

					echo "<pre>";
					print_r( $product->price );
					echo "</pre>";
					?>
					<!-- ENDIF -->	
					</li>
					<!-- HOW MANY IN CART -->
					<li class="">
						<?php
							foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
						        if ( get_the_ID() === $values["product_id"] ) {
						        	echo "In cart: " . $values["quantity"]; 
						        }
						    } 
					    ?>
			    	</li>

				</ul>

				<div class="wrap">
					<?php woocommerce_template_single_add_to_cart(); ?>
				</div>

			</div><!-- end of .single_info -->	

		</div><!-- end of #single_additional_images -->

	</div>

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
