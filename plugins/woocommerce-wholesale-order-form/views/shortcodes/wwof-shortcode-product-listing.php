<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div id="wwof_product_listing_container">
    <?php
    global $wc_wholesale_order_form;
    $allowSKUSearch = get_option( 'wwof_general_allow_product_sku_search' );

    if( $allowSKUSearch !== false && $allowSKUSearch == 'yes' )
        $search_placeholder_text = __( 'Search by name or SKU ...' , 'woocommerce-wholesale-order-form' );
    else
        $search_placeholder_text = __( 'Search by name' , 'woocommerce-wholesale-order-form' );

    $wc_wholesale_order_form->getProductListingFilter( apply_filters( 'wwof_filter_search_placeholder_text' , $search_placeholder_text,$allowSKUSearch ) );
    ?>

    <div id="wwof_product_listing_ajax_content" style="position: relative;">
        <!--AJAX Content Goes Here -->
    </div><!--#wwof_product_listing_ajax_content-->
</div><!--#wwof_product_listing_container--->