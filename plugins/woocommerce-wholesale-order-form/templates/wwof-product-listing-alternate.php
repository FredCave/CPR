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

<div id="wwof_product_listing_table_container" style="position: relative;">
    <table id="wwof_product_listing_table">
        <thead>
        <tr>
            <th><?php _e( 'Product' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th class="<?php echo $wc_wholesale_order_form->getProductSkuVisibilityClass(); ?>"><?php _e( 'SKU' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th><?php _e( 'Price' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th class="<?php echo $wc_wholesale_order_form->getProductStockQuantityVisibilityClass(); ?>"><?php _e( 'Stock Quantity' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th><?php _e( 'Quantity' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th><?php _e( 'Product' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th class="<?php echo $wc_wholesale_order_form->getProductSkuVisibilityClass(); ?>"><?php _e( 'SKU' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th><?php _e( 'Price' , 'woocommerce-wholesale-order-form' ); ?></th>
            <th class="<?php echo $wc_wholesale_order_form->getProductStockQuantityVisibilityClass(); ?>"><?php _e( 'Stock Quantity' , 'woocommerce-wholesale-order-form' ); ?></th>
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

                // TODO: add composite and bundled product support for next version, for now lets just skip them
                if ( $product->product_type == 'bto' || $product->product_type == 'composite' || $product->product_type == 'bundle' ) {
                    $ignored_products++;
                    continue;
                } ?>

                <tr>
                    <td class="product_meta_col" style="display: none !important;">
                        <?php echo $wc_wholesale_order_form->getProductMeta( $product ); ?>
                    </td>
                    <td class="product_title_col">
                        <?php echo $wc_wholesale_order_form->getProductImage( $product , get_the_permalink() , array( 48 , 48 ) ); // array here means image dimension ?>
                        <?php echo $wc_wholesale_order_form->getProductTitle( $product , get_the_permalink() ); ?>
                        <?php echo $wc_wholesale_order_form->getProductVariationField( $product ); ?>
                    </td>
                    <td class="product_sku_col <?php echo $wc_wholesale_order_form->getProductSkuVisibilityClass(); ?>">
                        <?php echo $wc_wholesale_order_form->getProductSku( $product ); ?>
                    </td>
                    <td class="product_price_col">
                        <?php echo $wc_wholesale_order_form->getProductPrice( $product ); ?>
                    </td>
                    <td class="product_stock_quantity_col <?php echo $wc_wholesale_order_form->getProductStockQuantityVisibilityClass(); ?>">
                        <?php echo $wc_wholesale_order_form->getProductStockQuantity( $product ); ?>
                    </td>
                    <td class="product_quantity_col">
                        <?php echo $wc_wholesale_order_form->getProductQuantityField( $product ); ?>
                    </td>
                    <td class="product_row_action">
                        <?php echo $wc_wholesale_order_form->getProductRowActionFields( $product , true ); ?>
                    </td>
                </tr>
            <?php

            }// End while loop

        } else { ?>

            <tr class="no-products">
                <td colspan="4">
                    <span><?php _e( 'No Products Found' , 'woocommerce-wholesale-order-form' ); ?></span>
                </td>
            </tr>

        <?php } ?>

        </tbody>
    </table><!--#wwof_product_listing_table-->
</div><!--#wwof_product_listing_table_container-->

<div id="wwof_product_listing_pagination">

    <div class="bottom_list_actions">

        <input type="button" id="wwof_bulk_add_to_cart_button" class="btn btn-primary button alt" value="<?php _e( 'Add Selected Products To Cart' , 'woocommerce-wholesale-order-form' ); ?>"/>
        <span class="spinner"></span>

        <div class="products_added">
            <p><b></b><?php _e( ' Product/s Added' , 'woocommerce-wholesale-order-form' ); ?></p>
        </div>

        <div class="view_cart">
            <a href="<?php echo $wc_wholesale_order_form->getCartUrl(); ?>"><?php _e( 'View Cart &rarr;' , 'woocommerce-wholesale-order-form' ); ?></a>
        </div>

    </div>

    <?php echo $wc_wholesale_order_form->getCartSubtotal(); ?>

    <div class="total_products_container">
        <span class="total_products">
            <?php
            $total_products = $product_loop->found_posts - $ignored_products;
            echo sprintf( __( '%1$s Product/s Found' , 'woocommerce-wholesale-order-form' ) , $total_products );
            ?>
        </span>
    </div>

    <?php echo $wc_wholesale_order_form->getGalleryListingPagination( $paged , $product_loop->max_num_pages , $search , $cat_filter ); ?>

</div><!--#wwof_product_listing_pagination-->