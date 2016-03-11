<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = 'wwpp_product_wholesale_visibility_filter'" );

delete_option( 'wwpp_settings_wholesale_price_title_text' );
