<?php
/*
Plugin Name:    WooCommerce Wholesale Prices Premium
Plugin URI:     https://wholesalesuiteplugin.com/
Description:    WooCommerce Premium Extension for the Woocommerce Wholesale Prices Plugin
Author:         Rymera Web Co
Version:        1.7.3
Author URI:     http://rymera.com.au/
Text Domain:    woocommerce-wholesale-prices-premium
*/

// This file is the main plugin boot loader

/**
 * Check if Woocommerce Wholesale Prices is installed and active
 **/
if ( in_array( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // Include Necessary Files
    require_once ('woocommerce-wholesale-prices-premium.options.php');
    require_once ('woocommerce-wholesale-prices-premium.plugin.php');

    // Check WWP version
    // WWPP ( 1.6.0 and up ) we need WWP 1.1.7
    // WWPP ( 1.7.0 and up ) we need WWP 1.2.0
    if ( version_compare( WooCommerceWholeSalePrices::VERSION , '1.2.0' , "<" ) ) {

        // Indicate that activation callback isn't triggered
        delete_option( 'wwpp_option_activation_code_triggered' );

        // Required minimum version of wwp is not met

        /**
         * Provide admin notice when WWP version does not meet the required version for this plugin.
         *
         * @since 1.1.0
         */
        function wwppAdminNotices () {

            global $current_user ;
            $user_id = $current_user->ID;

            /* Check that the user hasn't already clicked to ignore the message */
            if ( ! get_user_meta( $user_id , 'wwpp_ignore_incompatible_free_version_notice' ) ) {

                $sptInstallText = '<a href="' . wp_nonce_url('update.php?action=upgrade-plugin&plugin=woocommerce-wholesale-prices', 'upgrade-plugin_woocommerce-wholesale-prices') . '">Click here to update WooCommerce Wholesale Prices Plugin &rarr;</a>';

                ?>
                <div class="error">
                    <p>Please ensure you have the latest version of <a href="http://wordpress.org/plugins/woocommerce-wholesale-prices/" target="_blank">WooCommerce Wholesale Price</a> plugin installed and activated along with the Premium extension.</p>
                    <p></p><?php echo $sptInstallText; ?></p>
                </div>
                <?php

            }

        }

        add_action( 'admin_notices', 'wwppAdminNotices' );

    } else {

        // Minimum version requirement of WooCommerce Wholesale Prices Plugin Meet

        // Get Instance of Main Plugin Class
        $wc_wholesale_prices_premium = WooCommerceWholeSalePricesPremium::getInstance();
        $GLOBALS['wc_wholesale_prices_premium'] = $wc_wholesale_prices_premium;

        // Register Activation Hook
        register_activation_hook(__FILE__, array($wc_wholesale_prices_premium,'activate'));

        // Register Deactivation Hook
        register_deactivation_hook(__FILE__, array($wc_wholesale_prices_premium,'deactivate'));

        // Initialize Plugin
        add_action('init',array($wc_wholesale_prices_premium,'initialize'));

        //  Register AJAX Call Handlers
        add_action('init', array( $wc_wholesale_prices_premium, 'registerAJAXCAllHandlers' ));

        // Load Backend CSS and JS
        add_action('admin_enqueue_scripts', array( $wc_wholesale_prices_premium, 'loadBackEndStylesAndScripts' ));

        // Load Frontend CSS and JS
        add_action("wp_enqueue_scripts", array( $wc_wholesale_prices_premium, 'loadFrontEndStylesAndScripts' ));

        // Register Plugin Menu
        add_action("admin_menu", array( $wc_wholesale_prices_premium, 'registerMenu' ));

        // Register Admin Notices
        add_action("admin_notices", array( $wc_wholesale_prices_premium, 'adminNotices' ));




        // WooCommerce Wholesale Suit License Settings =================================================================

        // Add WooCommerce Wholesale Suit License Settings
        add_action( "admin_menu" , array( $wc_wholesale_prices_premium , 'registerWWSLicenseSettingsMenu' ) );

        // Add WWS License Settings Header Tab Item
        add_action( "wws_action_license_settings_tab" , array( $wc_wholesale_prices_premium , 'wwcLicenseSettingsHeader' ) );

        // Add WWS License Settings Page (WWPP)
        add_action( "wws_action_license_settings_wwpp" , array( $wc_wholesale_prices_premium , 'wwcLicenseSettingsPage' ) );




        // Plugin Settings =============================================================================================

        WooCommerceWholeSalePrices::getInstance()->activatePluginSettings();

        // Change appropriately the title of the general section of the plugin's settings
        add_filter( 'wwp_filter_settings_general_section_title' , array( $wc_wholesale_prices_premium , 'pluginSettingsGeneralSectionTitle' ) , 10 , 1 );

        // Add plugin settings sections
        add_filter( 'wwp_filter_settings_sections' , array( $wc_wholesale_prices_premium, 'pluginSettingsSections' ) , 10  , 1 );

        // Add contents to the recently added settings sections
        add_filter( 'wwof_settings_section_content' , array( $wc_wholesale_prices_premium, 'pluginSettingsSectionContent' ) , 10 , 2 );

        // Add custom control field that will be used as the shipping controls for the shipping section of the plugin settings
        add_action( 'woocommerce_admin_field_shipping_controls' , array( $wc_wholesale_prices_premium , 'renderPluginSettingsCustomFieldShippingControls' ) );

        // Add custom control field that will be used as the discount controls for the discount section of the plugin settings
        add_action( 'woocommerce_admin_field_discount_controls' , array( $wc_wholesale_prices_premium , 'renderPluginSettingsCustomFieldDiscountControls' ) );

        // Add custom control field that will be used as the payment gateway surcharge field
        add_action( 'woocommerce_admin_field_payment_gateway_surcharge_controls' , array( $wc_wholesale_prices_premium , 'renderPluginSettingsCustomFieldPaymentGatewaySurchargeControls' ) );

        // Add custom control field that will be used to set which payment gateways are available per wholesale role
        add_action( 'woocommerce_admin_field_wholesale_role_payment_gateway_controls' , array( $wc_wholesale_prices_premium , 'renderPluginSettingsCustomFieldWholesaleRolePaymentGatewayControls' ) );

        // Add custom control field that will be used to display help resources
        add_action( 'woocommerce_admin_field_help_resources_controls' , array( $wc_wholesale_prices_premium , 'renderPluginSettingsCustomFieldHelpResourcesControls' ) );

        // Add custom control field that will be used to display wholesale role tax exemption mapping
        add_action( 'woocommerce_admin_field_wholesale_role_tax_options_mapping_controls' , array( $wc_wholesale_prices_premium , 'renderPluginSettingsCustomFieldWholesaleRoleTaxOptionsMappingControls' ) );

        // Add a custom button field to initialize product visibility meta.
        add_action( 'woocommerce_admin_field_initialize_product_visibility_meta_button' , array( $wc_wholesale_prices_premium , 'renderPluginSettingsCustomFieldInitializeProductVisibilityMetaButton' ) );




        // Product Custom Fields =======================================================================================

        // Add product custom fields

        // Add minimum order quantity custom field for simple products
        add_action( 'woocommerce_product_options_pricing' , array( $wc_wholesale_prices_premium , 'addSimpleProductMinimumOrderQuantityCustomField' ) , 20 , 1 );

        // Add minimum order quantity custom field for variable products
        add_action( 'woocommerce_product_after_variable_attributes' , array( $wc_wholesale_prices_premium ,'addVariableProductMinimumOrderQuantityCustomField' ) , 20 , 3 );

        // Add wholesale users exclusive variation custom field for variable products
        add_action( 'woocommerce_product_after_variable_attributes' , array( $wc_wholesale_prices_premium , 'addVariableProductWholesaleOnlyVariationCustomField' ) , 20 , 3 );

        // Add order quantity based wholesale pricing custom fields to simple products.
        add_action( 'woocommerce_product_options_pricing' , array( $wc_wholesale_prices_premium , 'addSimpleProductQuantityBasedWholesalePriceCustomField' ) , 30 , 1 );

        // Add order quantity based wholesale pricing custom fields to variable products.
        add_action( 'woocommerce_product_after_variable_attributes', array( $wc_wholesale_prices_premium , 'addVariableProductQuantityBasedWholesalePriceCustomField' ) , 30, 3 );


        // Save product custom fields

        // Save minimum order quantity custom field value for simple products
        add_action( 'woocommerce_process_product_meta_simple' , array( $wc_wholesale_prices_premium , 'saveSimpleProductMinimumOrderQuantityCustomField' ) , 20 , 1 );

        // Save minimum order quantity custom field value for variable products
        add_action( 'woocommerce_process_product_meta_variable', array( $wc_wholesale_prices_premium , 'saveVariableProductMinimumOrderQuantityCustomField' ) , 20, 1 );

        // Save minimum order quantity custom field value for variable products via the new "Save Changes" button added on WooCommerce 2.4.* series
        add_action( 'woocommerce_ajax_save_product_variations' , array( $wc_wholesale_prices_premium , 'saveVariableProductMinimumOrderQuantityCustomField' ) , 20 , 1 );

        // Save wholesale users exclusive variation custom field for variable products
        add_action( 'woocommerce_process_product_meta_variable', array( $wc_wholesale_prices_premium , 'saveVariableProductWholesaleOnlyVariationCustomField' ) , 20 , 1 );

        // Save wholesale users exclusive variation custom field for variable products via the new "Save Changes" button added on WooCommerce 2.4.* series
        add_action( 'woocommerce_ajax_save_product_variations' , array( $wc_wholesale_prices_premium , 'saveVariableProductWholesaleOnlyVariationCustomField' ) , 20 , 1 );




        // Filter product price to show the minimum order quantity for wholesale users.
        add_filter( 'wwp_filter_wholesale_price_html' , array( $wc_wholesale_prices_premium , 'displayMinimumWholesaleOrderQuantity' ) , 100 , 4 );

        // Set minimum order quantity as minimum value ( default value ) for a given product if one is set.
        add_filter( 'woocommerce_quantity_input_args' , array( $wc_wholesale_prices_premium , 'setMinimumOrderQuantityAsInitialValue' ) , 10 , 2 );

        // Filter product category product items count.
        add_filter( 'woocommerce_subcategory_count_html' , array( $wc_wholesale_prices_premium , 'filterProductCategoryPostCount' ) , 10 , 2 );




        // Product Visibility ==========================================================================================

        // Add product visibility filter
        add_action( "post_submitbox_misc_actions" , array( $wc_wholesale_prices_premium, 'productVisibilityFilter' ) , 100 );

        // Save custom fields integrated to single product admin page (Wholesale role visibility filter)
        add_action( 'save_post' , array( $wc_wholesale_prices_premium , 'saveIntegratedCustomWholesaleFieldsOnProductPage' ) );

        // Apply wholesale role visibility filter to only show products accordingly (Shop Page and Archive Page)
        add_action( 'pre_get_posts' , array( $wc_wholesale_prices_premium , 'preGetPosts'  ));

        // Apply wholesale role visibility filter to only show products accordingly ( WooCommerce Wholesale Order Form Integration )
        add_filter( 'wwof_filter_product_listing_query_arg' , array( $wc_wholesale_prices_premium , 'preGetPostsArg' ) , 10, 1 );

        // Apply wholesale role visibility filter to only show products accordingly (Single Product Page)
        add_action( 'template_redirect' , array( $wc_wholesale_prices_premium, 'wholesaleVisibilityFilterForSingleProduct' ) );

        // Apply per product filtering to related products loop
        add_filter( 'woocommerce_related_products_args' , array( $wc_wholesale_prices_premium , 'preGetPostsArg' ) , 10 , 1 );

        // Apply per product filtering to woocommerce shortcodes
        add_filter( 'woocommerce_shortcode_products_query' , array( $wc_wholesale_prices_premium , 'preGetPostsArg' ) , 10 , 1 );

        // Apply product filtering to cross sell products
        add_filter( 'woocommerce_product_crosssell_ids' , array( $wc_wholesale_prices_premium , 'filterProductInterSells' ) , 10 , 2 );

        // Apply product filtering to up sells products
        add_filter( 'woocommerce_product_upsell_ids' , array( $wc_wholesale_prices_premium , 'filterProductInterSells' ) , 10 , 2 );


        //--------------------------------------------------------------------------------------------------------------

        // Apply filter to only show wholesale products to wholesale roles ( Shop Page and Archive Page )
        add_action( 'pre_get_posts' , array( $wc_wholesale_prices_premium , 'onlyShowWholesaleProductsToWholesaleUsers' ) );

        // Apply filter to only show wholesale products to wholesale roles ( WooCommerce Wholesale Order Form Integration )
        add_filter( 'wwof_filter_product_listing_query_arg' , array( $wc_wholesale_prices_premium , 'onlyShowWholesaleProductsToWholesaleUsersArg' ) , 100, 1 );

        // Apply filter to only show wholesale products to wholesale roles ( Single Product Page )
        add_filter( 'template_redirect' , array( $wc_wholesale_prices_premium , 'onlyShowWholesaleProductsToWholesaleUsersSingleProductPage' ) , 100 );

        // Apply filter to only show variations of a variable product that has a wholesale price if option of only showing wholesale products to wholesale role is enabled

        // Apply filter to only show variations of a variable product on proper time and place.
        // ( Only show variations with wholesale price on wholesale users if setting is enabled )
        // ( Only show variations to appropriate wholesale users if it is set to be exclusively visible to certain wholesale roles )

        // Make product invisible
        add_filter( 'woocommerce_variation_is_visible' , array( $wc_wholesale_prices_premium , 'filterVariationVisibility' ) , 10 , 4 );

        // Make product not un-purchasable
        add_filter( 'woocommerce_variation_is_purchasable' , array( $wc_wholesale_prices_premium , 'filterVariationPurchasability' ) , 10 , 2 );

        // Always allow wholesale users to perform backorders no matter what.
        add_filter( 'woocommerce_product_backorders_allowed' , array( $wc_wholesale_prices_premium , 'alwaysAllowBackordersToWholesaleUsers' ) , 10 , 2 );

        // Apply general wholesale user filtering to related products loop
        add_filter( 'woocommerce_related_products_args' , array( $wc_wholesale_prices_premium , 'onlyShowWholesaleProductsToWholesaleUsersArg' ) , 100 , 1 );




        // Custom Fields On Product Category Taxonomy ==================================================================

        // Add wholesale price fields to product category taxonomy add page
        add_action( 'product_cat_add_form_fields' , array( $wc_wholesale_prices_premium , 'productCategoryAddCustomFields' ) );

        // Add wholesale price fields to product category taxonomy edit page
        add_action( 'product_cat_edit_form_fields' , array( $wc_wholesale_prices_premium , 'productCategoryEditCustomFields' ) );

        // Save wholesale price fields data on product category taxonomy add and edit page
        add_action( 'edited_product_cat' , array( $wc_wholesale_prices_premium , 'productCategorySaveCustomFields' ) , 10 , 2 );
        add_action( 'create_product_cat' , array( $wc_wholesale_prices_premium , 'productCategorySaveCustomFields' ) , 10 , 2 );




        // Prices , Shipping , Taxing and Coupons ======================================================================

        // Display order quantity based wholesale pricing ( On shop pages )
        add_filter( 'wwp_filter_wholesale_price_html' , array( $wc_wholesale_prices_premium , 'displayOrderQuantityBasedWholesalePricing' ) , 110 , 4 );

        // Apply order quantity based wholesale pricing ( Only apply on cart )
        add_filter( 'wwp_filter_wholesale_price_cart' , array( $wc_wholesale_prices_premium , 'applyOrderQuantityBasedWholesalePricing' ) , 10 , 4 );

        // Apply product category level wholesale discount
        add_filter( 'wwp_filter_wholesale_price_shop' , array( $wc_wholesale_prices_premium , 'applyProductCategoryWholesaleDiscount' ) , 10 , 3 );
        add_filter( 'wwp_filter_wholesale_price_cart' , array( $wc_wholesale_prices_premium , 'applyProductCategoryWholesaleDiscount' ) , 100 , 3 );

        // Apply general discount for the current user if
        // * General discount is set
        // * No category level discount is set
        // * No wholesale price set for the product being purchased
        // It is very important that this filter call back be executed after "applyProductCategoryWholesaleDiscount"
        // Coz if wholesale price is still empty after "applyProductCategoryWholesaleDiscount" meaning no wholesale discount is set at category level
        // Note: Make sure this callback gets called after per category level discount callback.
        add_filter( 'wwp_filter_wholesale_price_shop' , array( $wc_wholesale_prices_premium , 'applyWholesaleRoleGeneralDiscount' ) , 20 , 3 );
        add_filter( 'wwp_filter_wholesale_price_cart' , array( $wc_wholesale_prices_premium , 'applyWholesaleRoleGeneralDiscount' ) , 200 , 3 );

        // Apply appropriate shipping method to products in the cart
        add_filter( 'woocommerce_package_rates' , array( $wc_wholesale_prices_premium , 'applyAppropriateShippingMethod' ) , 10 , 2 );

        // Apply filters to determine whether or not to apply the wholesale pricing ( For all wholesale roles or per wholesale role )
        add_filter( 'wwp_filter_apply_wholesale_price_flag' , array( $wc_wholesale_prices_premium , 'applyWholesalePriceFlagFilter' ) , 10 , 3 );

        // Apply filters to determine whether or not to apply wholesale pricing per product basis
        add_filter( 'wwp_filter_apply_wholesale_price_per_product_basis' , array( $wc_wholesale_prices_premium , 'applyWholesalePricePerProductBasisFilter' ) , 10 , 4 );

        // Filter the text for the wholesale price title
        add_filter( 'wwp_filter_wholesale_price_title_text' , array( $wc_wholesale_prices_premium , 'filterWholesalePriceTitleText' ) , 10 , 1 );

        // Filter the product price to hide the original price for wholesale users
        add_filter( 'wwp_filter_wholesale_price_html' , array( $wc_wholesale_prices_premium , 'filterProductOriginalPriceVisibility' ) , 10 , 1 );

        // Set coupons availability to wholesale users.
        add_filter( 'woocommerce_coupons_enabled' , array( $wc_wholesale_prices_premium , 'toggleAvailabilityOfCouponsToWholesaleUsers' ) );

        // Apply filters to override the default wholesale price suffix if the Override Price Suffix in the settings is set
        add_filter( 'woocommerce_get_price_suffix' , array( $wc_wholesale_prices_premium , 'overrideWholesalePriceSuffix' ) , 10 , 1 );

        // Apply tax exemptions to wholesale users based on settings
        add_action( 'woocommerce_before_calculate_totals' , array( $wc_wholesale_prices_premium , 'applyTaxExemptionsToWholesaleUsers' ) );
        add_action( 'woocommerce_before_cart_contents' , array( $wc_wholesale_prices_premium , 'applyTaxExemptionsToWholesaleUsers' ) );
        add_action( 'woocommerce_before_shipping_calculator' , array( $wc_wholesale_prices_premium , 'applyTaxExemptionsToWholesaleUsers' ) );
        add_action( 'woocommerce_before_checkout_billing_form' , array( $wc_wholesale_prices_premium , 'applyTaxExemptionsToWholesaleUsers' ) );

        // Integrate tax to wholesale price on shop pages ( Display Purposes )
        // Note: Tax exemptions only apply to the cart. If wholesale user is tax exempted and on the settings it is set to
        //       display prices on the shop pages with tax, then on the shop pages it will display price with tax.
        //       Again, wholesale user tax exemptions only trigger on cart.
        // Note: No need to integrate tax to wholesale price on cart and checkout page, because WC already takes care of this
        //       because of the way we apply wholesale pricing. Since we used the "woocommerce_before_calculate_totals" action
        //       hook, we changed the product price (apply wholesale pricing) before any WC calculations (including taxing) is done.
        //       So by the time we finished applying our wholesale pricing,
        //       WC will apply its own calculations stuff above our wholesale price (including taxing).
        // Note: Make sure this callback gets executed after the per product category level discount and per wholesale role general discount callbacks.
        add_filter( 'wwp_filter_wholesale_price_shop' , array( $wc_wholesale_prices_premium , 'integrateTaxToWholesalePriceOnShop' ) , 30 , 3 );

        // Override "woocommerce_tax_display_cart" option for wholesale users.
        add_filter( 'option_woocommerce_tax_display_cart' , array( $wc_wholesale_prices_premium , 'wholesaleTaxDisplayCart' ) );

        // Filter wholesale product price on cart page and cart widget to apply taxing accordingly.
        add_filter( 'woocommerce_cart_item_price' , array( $wc_wholesale_prices_premium , 'wholesaleCartItemPrice' ) , 100 , 3 );




        // Payment Gateways ============================================================================================

        // Apply custom payment gateway surcharge
        add_action( 'woocommerce_cart_calculate_fees' , array( $wc_wholesale_prices_premium , 'applyPaymentGatewaySurcharge' ) );

        // Apply taxable notice to surcharge
        add_filter( 'woocommerce_cart_totals_fee_html' , array( $wc_wholesale_prices_premium , 'applyTaxableNoticeOnSurcharge' ) , 10 , 2 );

        // Filter payment gateways that should only be available to certain wholesale roles
        add_filter( 'woocommerce_available_payment_gateways' , array( $wc_wholesale_prices_premium , 'filterAvailablePaymentGateways' ) , 100 , 1 );




        // Woocommerce integration ( custom messages ) =================================================================

        // Custom thank you message
        add_filter( 'woocommerce_thankyou_order_received_text', array( $wc_wholesale_prices_premium, 'customThankYouMessage' ), 10, 1 );




        // Custom column on order listing page =========================================================================

        // Add custom column to order listing page
        add_filter('manage_edit-shop_order_columns', array( $wc_wholesale_prices_premium, 'addOrdersListingCustomColumn' ), 15, 1 );
        add_action('manage_shop_order_posts_custom_column', array( $wc_wholesale_prices_premium, 'addOrdersListingCustomColumnContent' ), 10, 2 );




        // Custom filter on order listing page =========================================================================

        // Add order type to orders
        add_action( 'woocommerce_checkout_order_processed', array( $wc_wholesale_prices_premium, 'addOrderTypeMetaToOrders' ), 10, 2 );

        // Add custom wholesale role filter on order listing page
        add_action( 'restrict_manage_posts', array( $wc_wholesale_prices_premium, 'addWholesaleRoleOrderListingFilter' ) );

        // Custom wholesale role filter on order listing page functionality
        add_filter( 'parse_query', array( $wc_wholesale_prices_premium, 'wholesaleRoleOrderListingFilter' ), 10, 1 );




        // Add Custom Plugin Listing Action Links ======================================================================

        // Settings
        add_filter( 'plugin_action_links' , array( $wc_wholesale_prices_premium , 'addPluginListingCustomActionLinks' ) , 10 , 2 );




        // Update Checker ==============================================================================================

        // Get license email and key
        $wwpp_option_license_key = get_option( WWPP_OPTION_LICENSE_KEY );
        $wwpp_option_license_email = get_option( WWPP_OPTION_LICENSE_EMAIL );

        if ( $wwpp_option_license_key && $wwpp_option_license_email ) {

            require 'plugin-updates/class-wws-plugin-update-checker.php';

            $wws_wwpp_update_checker = new WWS_Plugin_Update_Checker(
                'https://wholesalesuiteplugin.com/wp-admin/admin-ajax.php?action=wumGetUpdateInfo&plugin=prices-premium&licence=' . $wwpp_option_license_key . '&email=' . $wwpp_option_license_email,
                __FILE__,
                'woocommerce-wholesale-prices-premium',
                12,
                ''
            );

        } else {

            /**
             * Check if show notice if license details is not entered.
             *
             * @since 1.2.2
             */
            function wwppAdminNotices () {

                global $current_user ;
                $user_id = $current_user->ID;
                global $wc_wholesale_prices_premium;

                /* Check that the user hasn't already clicked to ignore the message */
                if ( !get_user_meta( $user_id , 'wwpp_ignore_empty_license_notice' ) && !$wc_wholesale_prices_premium->checkIfInWWPPSettingsPage() ) {

                    $current_url = $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ];

                    if ( strpos( $current_url , '?' ) !== false )
                        $mod_current_url = '//' . $current_url . '&wwpp_ignore_empty_license_notice=0';
                    else
                        $mod_current_url = '//' . $current_url . '?wwpp_ignore_empty_license_notice=0'; ?>

                    <div class="error">
                        <p>
                            <?php _e( 'Please <a href="options-general.php?page=wwc_license_settings&tab=wwpp">enter your license details</a> for the <b>WooCommerce Wholesale Prices Premium</b> plugin to enable plugin updates.' , 'woocommerce-wholesale-prices-premium' ); ?>
                            <a href="<?php echo $mod_current_url; ?>" style="float: right;" id="wwpp_ignore_empty_license_notice"><?php _e( 'Hide Notice' , 'woocommerce-wholesale-prices-premium' ); ?></a>
                        </p>
                    </div>

                <?php }

            }

            add_action( 'admin_notices', 'wwppAdminNotices' );

            /**
             * Ignore empty license notice.
             *
             * @since 1.2.2
             */
            function wwppHideAdminNotices() {

                global $current_user;
                $user_id = $current_user->ID;

                /* If user clicks to ignore the notice, add that to their user meta */
                if ( isset( $_GET[ 'wwpp_ignore_empty_license_notice' ] ) && '0' == $_GET[ 'wwpp_ignore_empty_license_notice' ] )
                    add_user_meta( $user_id , 'wwpp_ignore_empty_license_notice' , 'true' , true );

            }

            add_action( 'admin_init', 'wwppHideAdminNotices' );

        }

    }

} else {

    // Indicate that activation callback isn't triggered
    delete_option( 'wwpp_option_activation_code_triggered' );

    // WooCommerce Wholesale Prices plugin not installed or inactive

    /**
     * Provide admin admin notice when premium plugin is active but the WWP is either not installed or inactive.
     *
     * @since 1.0.0
     */
    function wwppAdminNotices () {

        global $current_user ;
        $user_id = $current_user->ID;

        /* Check that the user hasn't already clicked to ignore the message */
        if ( ! get_user_meta( $user_id , 'wwpp_ignore_inactive_free_version_notice' ) ) {

            $plugin_file = 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php';
            $sptFile = trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( $plugin_file );

            $sptInstallText = '<a href="' . wp_nonce_url( 'update.php?action=install-plugin&plugin=woocommerce-wholesale-prices', 'install-plugin_woocommerce-wholesale-prices' ) . '">Click here to install from WordPress.org repo &rarr;</a>';
            if ( file_exists( $sptFile ) )
                $sptInstallText = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;s', 'activate-plugin_' . $plugin_file) . '" title="' . esc_attr__( 'Activate this plugin' ) . '" class="edit">Click here to activate &rarr;</a>';

            ?>
            <div class="error">
                <p>
                    Please ensure you have the <a href="http://wordpress.org/plugins/woocommerce-wholesale-prices/" target="_blank">WooCommerce Wholesale Price</a> plugin installed and activated along with the Premium extension. <br/>
                    <?php echo $sptInstallText; ?>
                </p>
            </div>
            <?php

        }

    }

    add_action( 'admin_notices' , 'wwppAdminNotices' );

}
