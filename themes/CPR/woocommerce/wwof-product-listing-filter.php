<?php
/**
 * The template for displaying product listing
 *
 * Override this template by copying it to yourtheme/woocommerce/wwof-product-listing-filter.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleOrderForm/Templates
 * @version     1.3.2
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.
global $wc_wholesale_order_form;
if ( $wc_wholesale_order_form->userHasAccess() ) { ?>

<div id="search_wrapper" class="hidden">

    <div id="wwof_product_listing_filter">
        
        <!-- SHOW ALL -->
        <input type="button" id="wwof_product_displayall_btn" class="button button-secondary" value="<?php echo apply_filters( 'wwof_filter_listing_show_all_products_text' , __( 'Show All Products' , 'woocommerce-wholesale-order-form' ) ); ?>"/>

        <!-- SEARCH FIELD -->
        <input type="text" id="wwof_product_search_form" class="filter_field" placeholder="<?php echo $search_placeholder_text; ?>"/>
        <input type="button" id="wwof_product_search_btn" class="button button-primary" value="<?php echo apply_filters( 'wwof_filter_listing_search_text' , __( 'Search' , 'woocommerce-wholesale-order-form' ) ); ?>"/>
    
        <!-- FILTER TOGGLE -->
        <a href="" id="ws_filter_toggle" class="button">Filter by...</a>
    </div><!--#wwof_product_listing_filter-->

    <div id="wsale_filter_terms">
        <ul id="filter_cats">
            <?php
            // COLLECTIONS
            $filter_cats = get_terms ( "product_cat" ); 
            foreach ( $filter_cats as $filter_cat ) { ?>
                <li> 
                    <a href="" class="wsale_term" data-target="<?php echo $filter_cat->slug; ?>">
                        <?php echo $filter_cat->name; ?> 
                    </a>
                </li>
            <?php } 
            ?>
        </ul>
        <ul id="filter_tags">    
            <?php 
            // TAGS
            $filter_tags = get_terms ( "product_tag", "orderby=name" ); 
            foreach ( $filter_tags as $filter_tag ) { ?>
                <li>
                    <a href="" class="wsale_term" data-target="<?php echo $filter_tag->slug; ?>">
                        <?php echo $filter_tag->name; ?>
                    </a>
                </li>
            <?php } 
            ?>
            
        </ul>
    </div>

</div>

    <?php
}