<?php
/**
 * The template for displaying product listing
 *
 * Override this template by copying it to yourtheme/woocommerce/wwof-product-details.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleOrderForm/Templates
 * @version     1.3.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.

$product_post_data = $product->get_post_data(); ?>

<div class="wwof-popup-product-details-container">

    <div class="wwof-popup-product-images">
        <?php
        // Main Product Image
        echo $product->get_image('medium');
        ?>
        <div class="gallery">
            <?php
            // Product Gallery
            $product_gallery_ids = $product->get_gallery_attachment_ids();
            foreach( $product_gallery_ids as $gallery_id ) {
                echo wp_get_attachment_image( $gallery_id );
            }
            ?>
            <div style="clear: both; float: none; display: block;"></div>
        </div>
    </div><!--.wwof-popup-product-images-->

    <div class="wwof-popup-product-summary">
        <h2 class="product-title"><?php echo $product->get_title(); ?></h2>
        <div class="product-rating">
            <?php echo $product->get_rating_html(); ?>
            <div style="clear: both; float: none; display: block;"></div>
        </div>
        <div class="product-price">
            <?php echo $product->get_price_html(); ?>
        </div>
        <p class="product-desc"><?php echo $product_post_data->post_content; ?></p>
        <p class="product-categories"><?php echo $product->get_categories(); ?></p>
    </div><!--.wwof-popup-product-summary-->

    <div style="clear: both; float: none; display: block;"></div>
</div>