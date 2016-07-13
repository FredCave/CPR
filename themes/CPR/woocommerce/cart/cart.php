<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div id="cart">

	<!-- IF WHOLESALE BACK TO ORDER PAGE -->
	<?php if ( is_user_logged_in() ) { ?>
		<p class="return-to-shop">
			<a class="button" href="<?php bloginfo( 'url' ); ?>/wholesale-ordering/">
				Return to Wholesale Order Page
			</a>
		</p>
	<?php } ?>

	<form action="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" method="post">

		<?php do_action( 'woocommerce_before_cart_table' ); ?>

		<table class="shop_table cart" cellspacing="0">
			<thead>
				<tr>
					<th class="product-remove">&nbsp;</th>
					<th class="product-thumbnail">&nbsp;</th>
					<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
					<th class="product-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
					<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
					<th class="product-subtotal"><?php _e( 'Total', 'woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php


				// CREATE ARRAY FOR PRODUCT IDs
				$_product_ids = array();

				// CART ARRAY
				$cart_items = WC()->cart->get_cart();

				// SORT THE MULTIDIMENSIONAL ARRAY IN ORDER OF VARIATIONS
				uasort( $cart_items, "variation_sort");
				function variation_sort( $a, $b ) {
					return $a["variation"]["attribute_pa_size"] < $b["variation"]["attribute_pa_size"];
				}

				uasort( $cart_items, "custom_sort");
				function custom_sort( $a, $b ) {
					return $a[ "product_id" ] > $b[ "product_id" ];
				}

				// LOOP THROUGH CART ITEMS
				foreach ( $cart_items as $cart_item_key => $cart_item ) {					
				
					$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					// echo $product_id . "<br>";

					// IF NOT ALREADY IN ARRAY
					if ( !in_array( $product_id, $_product_ids, true ) ) {
				        // PUSH PRODUCT ID TO ARRAY
				        $_product_ids[] = $product_id;
				        $variation = false;
				    } else {
				        $variation = true;
				    }
				    	
						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						?>
							<tr class="<?php if ( $variation ) { echo "variation_row "; } echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

								<td class="product-remove">
									<?php
										$img_src = get_bloginfo( 'template_url' ) . "/img/close.svg";
										echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
										'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s"><img src="%s"/></a>',
										esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
										__( 'Remove this item', 'woocommerce' ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() ),
										$img_src
										), $cart_item_key );
									?>
								</td>

								<td class="product-thumbnail">
									<?php if ( !$variation ) {
										if ( get_field( 'product_images', $product_id ) ) :

											$image = get_field( "product_images", $product_id )[0]["product_image"];
											if( !empty($image) ): 
												$thumb = $image['sizes'][ "thumbnail" ]; // 300
												$medium = $image['sizes'][ "medium" ]; // 600
												$large = $image['sizes'][ "large" ]; // 800
												$extralarge = $image['sizes'][ "extra-large" ]; // 1024
												endif;
											?>

											<img class="" 
											sizes="100vw" 
											srcset="<?php echo $extralarge; ?> 1024w,
												<?php echo $large; ?> 800w,
											    <?php echo $medium; ?> 600w,
											    <?php echo $thumb; ?> 300w"
											src="<?php echo $extralarge; ?>"
											alt="Can Pep Rey — <?php the_title(); ?>"
											/>		

									<?php endif; 
										}
									?>
								</td>

								<td class="product-name">
									<a href="<?php echo get_bloginfo( 'url' ) . "/?p=" . $product_id; ?>">
										<?php echo $_product->get_title(); ?>
									</a>
									<?php								
									// Meta data
									echo WC()->cart->get_item_data( $cart_item );
									// var_dump( $cart_item );

									// Backorder notification
									if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
										echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>';
									}
									?>
								</td>

								<td class="product-price">
									<?php
										echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
									?>
								</td>

								<td class="product-quantity">
									<!-- DEFAULT NUMBER IS VISIBLE -->
									<span class="product-quantity-default">
										<?php echo $cart_item['quantity']; ?>
									</span>
									
									<!-- ON CLICK REPLACE WITH INPUT TO MODIFY QUANTITY -->
									<span class="product-quantity-input">
										<?php
										if ( $_product->is_sold_individually() ) {
											$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
										} else {
											$product_quantity = woocommerce_quantity_input( array(
											'input_name'  => "cart[{$cart_item_key}][qty]",
											'input_value' => $cart_item['quantity'],
											'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
											'min_value'   => '0'
											), $_product, false );
										}

										echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
										?>
									</span>
								</td>

								<td class="product-subtotal">
									<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
								</td>
							</tr>
					<?php

					}

				} // END OF FOREACH

				do_action( 'woocommerce_cart_contents' );
				?>
				<tr class="update_cart_wrapper">
					<td colspan="6" class="actions">

					<?php if ( WC()->cart->coupons_enabled() ) { ?>
						<?php if ( is_user_logged_in() ) : ?>
							<div class="coupon">

							<label for="coupon_code"><?php _e( 'Coupon', 'woocommerce' ); ?>:</label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="wholesale" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <input type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply Coupon', 'woocommerce' ); ?>" />

							<?php do_action( 'woocommerce_cart_coupon' ); ?>

							</div>
						<?php endif; ?>
					<?php } ?>

					<input type="submit" class="update_cart button" name="update_cart" value="<?php esc_attr_e( 'Update Cart', 'woocommerce' ); ?>" />

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart' ); ?>
					</td>
				</tr>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</tbody>
		</table>

		<?php do_action( 'woocommerce_after_cart_table' ); ?>

	</form>

	<div class="cart-collaterals">

		<?php do_action( 'woocommerce_cart_collaterals' ); ?>

	</div>

	<?php do_action( 'woocommerce_after_cart' ); ?>



</div><!-- end of #cart -->
