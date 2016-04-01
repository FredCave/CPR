<?php
/**
 * The template for displaying product listing
 *
 * Override this template by copying it to yourtheme/woocommerce/wwof-product-listing.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleOrderForm/Templates
 * @version     1.3.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.

global $wc_wholesale_order_form;
?>

<div id="wwof_product_listing_table_container" class="wholesale_page" style="position: relative;">
    <table id="wwof_product_listing_table">
        <thead>
            <tr>
                <th><?php _e( 'Product' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th class="<?php echo $wc_wholesale_order_form->getProductSkuVisibilityClass(); ?>"><?php _e( 'SKU' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th><?php _e( 'Price' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th class="<?php echo $wc_wholesale_order_form->getProductStockQuantityVisibilityClass(); ?>"><?php _e( 'In Stock' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th><?php _e( 'Quantity' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th><?php _e( 'Product' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th class="<?php echo $wc_wholesale_order_form->getProductSkuVisibilityClass(); ?>"><?php _e( 'SKU' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th><?php _e( 'Price' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th class="<?php echo $wc_wholesale_order_form->getProductStockQuantityVisibilityClass(); ?>"><?php _e( 'In Stock' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th><?php _e( 'Quantity' , 'woocommerce-wholesale-order-form' ); ?></th>
                <th></th>
            </tr>
        </tfoot>
        <tbody>
        <?php
        $ignored_products = 0;

        if ( $product_loop->have_posts() ) {

            while ( $product_loop->have_posts() ) {

                $product_loop->the_post();
                $product = wc_get_product( get_the_ID() );
                $product_id = $product->id;

                // TODO: add composite and bundled product support for next version, for now lets just skip them
                if ( $product->product_type == 'bto' || $product->product_type == 'composite' || $product->product_type == 'bundle' ) {
                    $ignored_products++;
                    continue;
                } ?>
                    <?php 
                    // GET ASSOCIATED TAG
                    $tags = get_the_terms( get_the_ID(), 'product_tag' );
                    $cat = get_the_terms( get_the_ID(), 'product_cat' );
                    // var_dump( $cat );
                    ?>

                <tr id="<?php echo get_the_ID(); ?>" class="<?php echo $tags[0]->slug . " " . $cat[0]->slug; ?>">
                    
                    <td class="product_meta_col" style="display: none !important;">
                        <?php echo $wc_wholesale_order_form->getProductMeta( $product ); ?>
                    </td>
                    
                    <td class="product_title_col">
                        <!-- DIVIDE IN TWO -->
                        <div class="wholesale_product_image">
                            <!-- GET GALLERY -->
                            <ul class="gallery">
                                <?php 
                                    $images = get_field( "product_images", $product->id ); 
                                    $i = 1;
                                    foreach ( $images as $image ) {
                                        // var_dump($image);
                                        if( !empty($image) ): 
                                            $thumb = $image["product_image"]["sizes"][ "thumbnail" ]; // 300
                                            $medium = $image["product_image"]["sizes"][ "medium" ]; // 600
                                            $large = $image["product_image"]["sizes"][ "large" ]; // 800
                                            $extralarge = $image["product_image"]["sizes"][ "extra-large" ]; // 1024
                                            $full = $image["product_image"]["url"];
                                        endif; ?>
                                    <li class="<?php if ( $i === 1 ) { echo "visible"; } ?>">
                                        <img class="wholesale_image" 
                                            sizes="(max-width: 800px) 50vw, 25vw" 
                                            srcset="<?php echo $full; ?> 2000w,
                                                    <?php echo $extralarge; ?> 1024w,
                                                    <?php echo $large; ?> 800w,
                                                    <?php echo $medium; ?> 600w,
                                                    <?php echo $thumb; ?> 300w"
                                            src="<?php echo $medium; ?>"
                                            alt="Can Pep Rey"
                                        />
                                    </li>                              
                                <?php 
                                    $i++;
                                } ?>
                            </ul>
                            <div class="gallery_arrow">
                                <img src="<?php bloginfo('template_url'); ?>/img/gallery_arrow_large.svg" />
                            </div> 
                        </div><!-- end of .wholesale_product_image -->

                        <?php /* echo $wc_wholesale_order_form->getProductImage( $product , get_the_permalink() , array( 48 , 48 ) ); // array here means image dimension */ ?>
                        <div class="wholesale_product_title">
                            <p><?php echo $wc_wholesale_order_form->getProductTitle( $product , get_the_permalink() ); ?></p>
                            <p><?php echo $this_sku = $product->get_sku(); ?></p>
                       
                            <p><?php echo $wc_wholesale_order_form->getProductVariationField( $product ); ?></p>

                            <span class="product_price_col wholesale_prices">
                                <?php echo $wc_wholesale_order_form->getProductPrice( $product ); ?>
                            </span>
                        
                            <!-- VIGNETTES OF RELATED ITEMS -->
                            <?php
                            other_colours( $product_id, true );
                            ?>

                            <!-- PLACEHOLDER FOR TITLES -->
                            <p class="wholesale_other_colours_title"></p>
                         
                            <!-- DESCRIPTION -->
                            <div class="wholesale_desc wholesale_desc_left">
                            <?php if ( get_field( "product_description", $product_id ) ) {
                                echo get_field( "product_description", $product_id );
                            }?>
                            </div>

                        </div>



                    </td>
                    <td class="product_sku_col <?php echo $wc_wholesale_order_form->getProductSkuVisibilityClass(); ?>">
                        <!--<?php echo $wc_wholesale_order_form->getProductSku( $product ); ?>-->
                    </td>
                    <td class="product_price_col">
                        
                    </td>
                    <td class="product_stock_quantity_col <?php echo $wc_wholesale_order_form->getProductStockQuantityVisibilityClass(); ?>">
                        <?php /* echo $wc_wholesale_order_form->getProductStockQuantity( $product ); */ ?>
                        <!-- DESCRIPTION -->
                        <div class="wholesale_desc wholesale_desc_right">
                        <?php if ( get_field( "product_description", $product_id ) ) {
                            echo get_field( "product_description", $product_id );
                        }?>
                        </div>


                    </td>
                    <td class="product_quantity_col">
                    
                        <?php 
                        $product_variations = $product->get_available_variations();
                        foreach ( array_reverse($product_variations) as $variation ) { 
                            $variation_id = $variation['variation_id'];
                            ?>
                            <div class="variation_wrapper" data-variation="<?php echo $variation_id; ?>">
                                <p>
                                    <?php echo $variation["attributes"]["attribute_pa_size"]; ?>
                                    <!-- ALREADY IN CART -->   
                                    <?php 
                                        // echo $variation_id;
                                        foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
                                            $cart_id = $values['data']->variation_id;
                                            //echo $values ["product_id"] . ", " . get_the_ID();
                                            // IF CART PRODUCT ID === CURRENT ID
                                            if ( $variation_id === $cart_id ) {
                                                echo "<span class'already_in_cart'>" . " (x " . $values['quantity'] . ")</span>";
                                                // echo $values["variation"]["attribute_pa_size"] . "<br>";
                                                //echo "<span class'already_in_cart'>" . " x " . $values['quantity'] . $values["variation"]["attribute_pa_size"] . "</span>";
                                                // //echo $values["data"]["post_title"];
                                                // echo $values["data"]->post->post_title . "<br>";
                                            }
                                        }
                                    ?>
                                </p>
                                <?php echo $wc_wholesale_order_form->getProductQuantityField( $product ); ?> 
                                <input type="button" class="wwof_add_to_cart_button btn btn-primary single_add_to_cart_button button alt" value="<?php echo __( 'Add To Cart' , 'woocommerce-wholesale-order-form' ); ?>"/>
                                <span class="spinner"></span> 

                            </div>
                        <?php
                        }
                        ?>
                        

                       
                    </td>
                    <td class="product_row_action">
                        
                    </td>
                </tr>
                <?php

            }// End while loop
            $product_loop->reset_postdata();

        }else{

            ?>
            <tr class="no-products">
                <td colspan="4">
                    <span><?php _e( 'No Products Found' , 'woocommerce-wholesale-order-form' ); ?></span>
                </td>
            </tr>
            <?php

        }
        ?>
        </tbody>
    </table><!--#wwof_product_listing_table-->
</div><!--#wwof_product_listing_table_container-->

<div id="wwof_product_listing_pagination">

    <div class="total_products_container">
        <span class="total_products">
            <?php
            $total_products = $product_loop->found_posts - $ignored_products;
            echo sprintf( __( '%1$s Product/s Found' , 'woocommerce-wholesale-order-form' ) , $total_products );
            ?>
        </span>
    </div>

<?php echo $wc_wholesale_order_form->getCartSubtotal(); ?>

    <?php echo $wc_wholesale_order_form->getGalleryListingPagination( $paged , $product_loop->max_num_pages , $search , $cat_filter ); ?>

<!-- VIEW CART BUTTON -->


<div class="wholesale_go_to_cart">
    <a href="<?php bloginfo( 'url' ); ?>/cart/" class="button">View Cart</a>
</div>


</div><!--#wwof_product_listing_pagination-->

<script>
    wholesaleInit();
</script>