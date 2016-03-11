<?php
/*
Plugin Name:    WooCommerce Wholesale Order Form
Plugin URI:     https://wholesalesuiteplugin.com/
Description:    WooCommerce Extension to Provide Wholesale Product Listing Functionality
Author:         Rymera Web Co
Version:        1.3.4
Author URI:     http://rymera.com.au/
Text Domain:    woocommerce-wholesale-order-form
*/

// This file is the main plugin boot loader

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // Include Necessary Files
    require_once ( 'woocommerce-wholesale-order-form.options.php' );
    require_once ( 'woocommerce-wholesale-order-form.plugin.php' );

    // Get Instance of Main Plugin Class
    $wc_wholesale_order_form = WooCommerce_WholeSale_Order_Form::getInstance();
    $GLOBALS[ 'wc_wholesale_order_form' ] = $wc_wholesale_order_form;

    // Load Plugin Text Domain
    add_action( 'plugins_loaded' , array( $wc_wholesale_order_form , 'loadPluginTextDomain' ) );

    // Register Activation Hook
    register_activation_hook( __FILE__ , array( $wc_wholesale_order_form , 'activate' ) );

    // Register Deactivation Hook
    register_deactivation_hook( __FILE__ , array( $wc_wholesale_order_form , 'deactivate' ) );

    // Initialize Plugin
    add_action( 'init' , array( $wc_wholesale_order_form , 'initialize' ) );

    //  Register AJAX Call Handlers
    add_action( 'init' , array( $wc_wholesale_order_form , 'registerAJAXCAllHandlers' ) );

    // Load Backend CSS and JS
    add_action( 'admin_enqueue_scripts' , array( $wc_wholesale_order_form , 'loadBackEndStylesAndScripts' ) );

    // Load Frontend CSS and JS
    add_action( "wp_enqueue_scripts" , array( $wc_wholesale_order_form , 'loadFrontEndStylesAndScripts' ) );




    /*
    |------------------------------------------------------------------------------------------------------------------
    | WooCommerce WholeSale Suit License Settings
    |------------------------------------------------------------------------------------------------------------------
    */

    // Add WooCommerce Wholesale Suit License Settings
    add_action( "admin_menu" , array( $wc_wholesale_order_form , 'registerWWSLicenseSettingsMenu' ) );

    // Add WWS License Settings Header Tab Item
    add_action( "wws_action_license_settings_tab" , array( $wc_wholesale_order_form , 'wwcLicenseSettingsHeader' ) );

    // Add WWS License Settings Page (WWOF)
    add_action( "wws_action_license_settings_wwof" , array( $wc_wholesale_order_form , 'wwcLicenseSettingsPage' ) );




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Settings
    |-------------------------------------------------------------------------------------------------------------------
    */

    // Register Settings Page
    add_filter( "woocommerce_get_settings_pages" , array( $wc_wholesale_order_form , 'initialPluginWoocommerceSettings' ) );




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Short Codes
    |-------------------------------------------------------------------------------------------------------------------
    */

    // Register Short Codes
    add_shortcode( 'wwof_product_listing' , array( $wc_wholesale_order_form , 'sc_productListing' ) );
    add_filter( 'body_class' , array( $wc_wholesale_order_form , 'sc_bodyClasses' ) );




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Plugin Integration
    |-------------------------------------------------------------------------------------------------------------------
    */

    // Plugin Integration
    add_action( 'wwof_action_product_query_with_search' , array( $wc_wholesale_order_form , 'productQueryWithSearch' ) );




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Add Custom Plugin Listing Action Links
    |-------------------------------------------------------------------------------------------------------------------
    */

    // Settings
    add_filter( 'plugin_action_links' , array( $wc_wholesale_order_form , 'addPluginListingCustomActionLinks' ) , 10 , 2 );
    



    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Update Checker
    |-------------------------------------------------------------------------------------------------------------------
    */

    // Get license email and key
    $wwof_option_license_key = get_option( WWOF_OPTION_LICENSE_KEY );
    $wwof_option_license_email = get_option( WWOF_OPTION_LICENSE_EMAIL );

    if ( $wwof_option_license_key && $wwof_option_license_email ) {

        require 'plugin-updates/class-wws-plugin-update-checker.php';

        $wws_wwof_update_checker = new WWS_Plugin_Update_Checker(
            'https://wholesalesuiteplugin.com/wp-admin/admin-ajax.php?action=wumGetUpdateInfo&plugin=order-form&licence=' . $wwof_option_license_key . '&email=' . $wwof_option_license_email,
            __FILE__,
            'woocommerce-wholesale-order-form',
            12,
            ''
        );

    } else {

        /**
         * Check if show notice if license details is not entered.
         *
         * @since 1.1.2
         */
        function wwofAdminNotices () {

            global $current_user ;
            $user_id = $current_user->ID;
            global $wc_wholesale_order_form;

            /* Check that the user hasn't already clicked to ignore the message */
            if ( !get_user_meta( $user_id , 'wwof_ignore_empty_license_notice' ) && !$wc_wholesale_order_form->checkIfInWWOFSettingsPage() ) {

                $current_url = $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ];

                if ( strpos( $current_url , '?' ) !== false )
                    $mod_current_url = '//' . $current_url . '&wwof_ignore_empty_license_notice=0';
                else
                    $mod_current_url = '//' . $current_url . '?wwof_ignore_empty_license_notice=0'; ?>

                <div class="error">
                    <p>
                        <?php echo sprintf( __('Please <a href="%1$s">enter your license details</a> for the <b>WooCommerce Wholesale Order Form</b> plugin to enable plugin updates.' , 'woocommerce-wholesale-order-form' ) , 'options-general.php?page=wwc_license_settings&tab=wwof' ); ?>
                        <a href="<?php echo $mod_current_url; ?>" style="float: right;" id="wwof_ignore_empty_license_notice"><?php _e( 'Hide Notice' , 'woocommerce-wholesale-order-form' ); ?></a>
                    </p>
                </div>

            <?php }

        }

        add_action( 'admin_notices', 'wwofAdminNotices' );

        /**
         * Ignore empty license notice.
         *
         * @since 1.1.2
         */
        function wwofHideAdminNotices() {

            global $current_user;
            $user_id = $current_user->ID;

            /* If user clicks to ignore the notice, add that to their user meta */
            if ( isset( $_GET[ 'wwof_ignore_empty_license_notice' ] ) && '0' == $_GET[ 'wwof_ignore_empty_license_notice' ] )
                add_user_meta( $user_id , 'wwof_ignore_empty_license_notice' , 'true' , true );

        }

        add_action( 'admin_init', 'wwofHideAdminNotices' );

    }

}