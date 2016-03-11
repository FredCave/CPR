<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * This is the main plugin class. It's purpose generally is for "ALL PLUGIN RELATED STUFF ONLY".
 * This file or class may also serve as a controller to some degree but most if not all business logic is distributed
 * across include files.
 *
 * Class WooCommerceWholeSalePricesPremium
 */

require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-wpdb-helper.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-aelia-currency-switcher-integration-helper.php' );
require_once ( WWP_INCLUDES_PATH  . 'class-wwp-wholesale-roles.php' ); // WWP
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-custom-fields.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-custom-messages.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-custom-meta.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-product-category-custom-fields.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-products-filter.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-settings.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-shipping-method-filter.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-wholesale-prices.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-wws-license-settings.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-wholesale-role-general-discount.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-product-custom-fields.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-payment-gateways.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-wholesale-role-order-requirement.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-wholesale-role-tax-option.php' );
require_once ( WWPP_INCLUDES_PATH . 'class-wwpp-wc-functions.php' );

class WooCommerceWholeSalePricesPremium {

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Class Members
     |------------------------------------------------------------------------------------------------------------------
     */

    private static $_instance;

    private $_wwpp_roles_page_handle;
    private $_wwpp_roles_page_slug;

    private $_wwpp_custom_fields;
    private $_wwpp_custom_messages;
    private $_wwpp_custom_meta;
    private $_wwpp_product_category_custom_fields;
    private $_wwpp_products_filter;
    private $_wwpp_settings;
    private $_wwpp_shipping_methods_filter;
    private $_wwpp_wholesale_prices;
    private $_wwpp_wws_license_settings;
    private $_wwpp_wholesale_roles;
    private $_wwpp_general_discount;
    private $_wwpp_product_custom_fields;
    private $_wwpp_payment_gateways;
    private $_wwpp_wholesale_role_order_requirement;
    private $_wwpp_wholesale_role_tax_option;

    const VERSION = '1.7.3';




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Mesc Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.0.0
     */
    public function __construct(){

        $this->_wwpp_roles_page_slug = 'wwpp-wholesale-roles-page';

        $this->_wwpp_custom_fields = WWPP_Custom_Fields::getInstance();
        $this->_wwpp_custom_messages = WWPP_Custom_Messages::getInstance();
        $this->_wwpp_custom_meta = WWPP_Custom_Meta::getInstance();
        $this->_wwpp_product_category_custom_fields = WWPP_Product_Category_Custom_Fields::getInstance();
        $this->_wwpp_products_filter = WWPP_Products_Filter::getInstance();
        $this->_wwpp_settings = WWPP_Settings::getInstance();
        $this->_wwpp_shipping_methods_filter = WWPP_Shipping_Method_Filter::getInstance();
        $this->_wwpp_wholesale_prices = WWPP_Wholesale_Prices::getInstance();
        $this->_wwpp_wws_license_settings = WWPP_WWS_License_Settings::getInstance();
        $this->_wwpp_wholesale_roles = WWP_Wholesale_Roles::getInstance();
        $this->_wwpp_general_discount = WWPP_Wholesale_Role_General_Discount::getInstance();
        $this->_wwpp_product_custom_fields = WWPP_Product_Custom_Fields::getInstance();
        $this->_wwpp_payment_gateways = WWPP_Payment_Gateways::getInstance();
        $this->_wwpp_wholesale_role_order_requirement = WWPP_Wholesale_Role_Order_Requirement::get_instance();
        $this->_wwpp_wholesale_role_tax_option = WWPP_Wholesale_Role_Tax_Option::get_instance();

    }

    /**
     * Singleton Pattern.
     *
     * @since 1.0.0
     *
     * @return WooCommerceWholeSalePricesPremium
     */
    public static function getInstance() {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self;

        return self::$_instance;

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Bootstrap/Shutdown Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Plugin activation hook callback.
     *
     * @since 1.0.0
     */
    public function activate() {

        if ( !get_option( 'wwpp_settings_wholesale_price_title_text' , false ) )
            update_option( 'wwpp_settings_wholesale_price_title_text' , 'Wholesale Price:' );

        // Initialize product visibility related meta
        $this->_initializeProductVisibilityFilterMeta();

        // Apply any major functionality change migration
        $this->_functionalityChangeMigration();

        update_option( WWPP_OPTION_ACTIVATION_CODE_TRIGGERED , 'yes' );

    }

    /**
     * Plugin initializaton.
     *
     * @since 1.0.1
     */
    public function initialize() {

        // Check if activation has been triggered, if not trigger it
        // Activation codes are not triggered if wwpp is installed first before wwp
        // Or wwp is installed first but the version didn't meet to the required version wwpp requires
        if ( get_option( WWPP_OPTION_ACTIVATION_CODE_TRIGGERED ) != 'yes' )
            $this->activate();

    }

    /**
     * Plugin deactivation hook callback.
     *
     * @since 1.0.0
     */
    public function deactivate() {

        delete_option( WWPP_OPTION_ACTIVATION_CODE_TRIGGERED );

    }

    /**
     * Function to migrate from old functionality to new functionality.
     *
     * @since 1.3.0
     */
    private function _functionalityChangeMigration () {

        /*
         * 1.2.X backwards compatibility
         *
         * Prior to version 1.3.X, it only allows 1 shipping method mapping per wholesale role, therefore the format
         * of the option data saved is not compatible with what 1.3.X have. On activation we need to reformat this old data
         * to the format 1.3.X understands.
         *
         * @from 1.2.X
         * @to 1.3.X
         */

        $newMapping = array();
        $savedMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING );
        if ( !is_array( $savedMapping ) )
            $savedMapping = array();

        $allRegisteredWholesaleRoles = $this->wwppGetAllRegisteredWholesaleRoles( null , false );
        if ( !is_array( $allRegisteredWholesaleRoles ) )
            $allRegisteredWholesaleRoles = array();

        foreach ( $allRegisteredWholesaleRoles as $roleKey => $role ) {

            if ( array_key_exists( $roleKey , $savedMapping ) ) {

                $tempVar = $savedMapping[ $roleKey ];
                $tempVar[ 'wholesale_role' ] = $roleKey;
                $newMapping[] = $tempVar;

            }

        }

        if ( !empty( $newMapping ) )
            update_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING , $newMapping );

    }

    /**
     * The main purpose for this function as follows.
     * Get all products
     * Check if product has no 'wwpp_product_wholesale_visibility_filter' meta key yet
     * If above is true, then set a meta for the current product with a key of 'wwpp_product_wholesale_visibility_filter' and value of 'all'
     *
     * This in turn specify that this product is available for viewing for all users of the site.
     * and yup, the sql statement below does all that.
     *
     * @since 1.4.2
     */
    private function _initializeProductVisibilityFilterMeta() {

        global $wpdb;

        // Initialize wwpp_product_wholesale_visibility_filter meta
        // This meta is in charge of product visibility. We need to set this to 'all' as mostly
        // all imported products will not have this meta. Meaning, all imported products
        // with no 'wwpp_product_wholesale_visibility_filter' meta set is visible to all users by default.
        $wpdb->query("
            INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
            SELECT $wpdb->posts.ID , 'wwpp_product_wholesale_visibility_filter' , 'all'
            FROM $wpdb->posts
            WHERE $wpdb->posts.post_type = 'product'
            AND $wpdb->posts.ID NOT IN (
                SELECT $wpdb->posts.ID
                FROM $wpdb->posts
                INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                WHERE meta_key = 'wwpp_product_wholesale_visibility_filter' )
        ");

        // Properly set {wholesale_role}_have_wholesale_price meta
        // There will be cases where users import products from external sources and they
        // "set up" wholesale prices via external tools prior to importing
        // We need to handle those cases.
        $allWholesaleRoles = $this->wwppGetAllRegisteredWholesaleRoles( null , false );

        foreach ( $allWholesaleRoles as $roleKey => $role ) {

            // We need to delete prior to inserting, else we will have a stacked meta, same multiple meta for a single post
            $wpdb->query("
                DELETE FROM $wpdb->postmeta
                WHERE meta_key = '{$roleKey}_have_wholesale_price'
            ");

            $wpdb->query("
                INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                SELECT $wpdb->posts.ID , '{$roleKey}_have_wholesale_price' , 'yes'
                FROM $wpdb->posts
                WHERE $wpdb->posts.post_type = 'product'
                AND $wpdb->posts.ID IN (

                        SELECT DISTINCT $wpdb->postmeta.post_id
                        FROM $wpdb->postmeta
                        WHERE (
                                ( meta_key = '{$roleKey}_wholesale_price' AND meta_value > 0  )
                                OR
                                ( meta_key = '{$roleKey}_variations_with_wholesale_price' AND meta_value != '' )
                              )

                    )
            ");

        }

    }




    /*
    |------------------------------------------------------------------------------------------------------------------
    | WooCommerce Wholesale Suit License Settings
    |------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Register general wws license settings page.
     *
     * @since 1.0.1
     */
    public function registerWWSLicenseSettingsMenu () {

        /*
         * Since we don't have a primary plugin to add this license settings, we have to check first if other plugins
         * belonging to the WWS plugin suite has already added a license settings page.
         */
        if ( !defined( 'WWS_LICENSE_SETTINGS_PAGE' ) ) {

            if ( !defined( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' ) )
                define( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' , 'wwpp' );

            // Register WWS Settings Menu
            add_submenu_page(
                'options-general.php', // Settings
                __( 'WooCommerce Wholesale Suit License Settings' , 'woocommerce-wholesale-prices-premium' ),
                __( 'WWS License' , 'woocommerce-wholesale-prices-premium' ),
                'manage_options',
                'wwc_license_settings',
                array( self::getInstance() , "wwcGeneralLicenseSettingsPage" )
            );

            /*
             * We define this constant with the text domain of the plugin who added the settings page.
             */
            define( 'WWS_LICENSE_SETTINGS_PAGE' , 'woocommerce-wholesale-prices-premium' );

        }

    }

    /**
     * General WWS general license settings view.
     *
     * @since 1.0.2
     */
    public function wwcGeneralLicenseSettingsPage (){

        require_once("views/wws-license-settings/wwpp-view-general-wws-settings-page.php");

    }

    /**
     * WWPP WWS license settings header.
     *
     * @since 1.0.2
     */
    public function wwcLicenseSettingsHeader () {

        ob_start();

        if ( isset( $_GET[ 'tab' ] ) )
            $tab = $_GET[ 'tab' ];
        else
            $tab = WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN;

        global $wp;
        $current_url = add_query_arg( $wp->query_string , '?' , home_url( $wp->request ) );
        $wwpp_license_settings_url = $current_url . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwpp";

        ?>
        <a href="<?php echo $wwpp_license_settings_url; ?>" class="nav-tab <?php echo ( $tab == "wwpp" ) ? "nav-tab-active" : ""; ?>"><?php _e( 'Wholesale Prices' , 'woocommerce-wholesale-prices-premium' ); ?></a>
        <?php

        echo ob_get_clean();

    }

    /**
     * WWPP WWS license settings page content.
     *
     * @since 1.0.2
     */
    public function wwcLicenseSettingsPage () {

        ob_start();

        require_once ( "views/wws-license-settings/wwpp-view-wss-settings-page.php" );

        echo ob_get_clean();

    }




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Admin Functions
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Load Admin or Backend Related Styles and Scripts.
     *
     * @since 1.0.0
     *
     * @param $handle
     */
    public function loadBackEndStylesAndScripts ( $handle ) {
        // Only load plugin styles and scripts on the right time and on the right place

        global $post, $pagenow;

        // Woocommerce screen stuff to determine the current page
        $screen = get_current_screen();

        // Wholesale Roles Page Scripts
        if ( strcasecmp( $handle,$this->_wwpp_roles_page_handle ) == 0 ) {

            // Styles
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_roles_page_css' , WWPP_CSS_URL . 'wwp-back-end-wholesale-roles.css' , array() , self::VERSION , 'all' );

            // Scripts
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_wholesaleRolesListingActions_js' , WWPP_JS_URL . 'app/modules/WholesaleRolesListingActions.js' , array( 'jquery' ), self::VERSION , true );
            wp_enqueue_script( 'wwpp_wholesaleRolesFormActions_js' , WWPP_JS_URL . 'app/modules/WholesaleRolesFormActions.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_wholesale_roles_main_js' , WWPP_JS_URL . 'app/wholesale-roles-main.js' , array( 'jquery' ) , self::VERSION , true );

        }

        // Woocommerce single product admin page
        if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' && isset( $post ) && $post->post_type == 'product' ) {

            // Styles
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_single_product_admin_css' , WWPP_CSS_URL . 'wwpp-single-product-admin.css' , array() , self::VERSION , 'all' );

            // Scripts
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , self::VERSION , true );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_single_product_admin_js' , WWPP_JS_URL . 'app/wwpp-single-product-admin.js' , array( 'jquery' ) , self::VERSION , true );

        }

        // WWPP WSS License Settings Page
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' &&
           ( ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwpp' ) || WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN == 'wwpp' ) ) {

            // CSS
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all');
            wp_enqueue_style( 'wwof_wws_license_settings_css' , WWPP_CSS_URL . 'wwpp-wws-license-settings.css' , array() , self::VERSION , 'all');

            // JS
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , 1 , true );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js', array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_wws_license_settings_js' , WWPP_JS_URL . 'app/wwpp-wws-license-settings.js' , array( 'jquery' ) , self::VERSION );

        }

        // WWPP General Settings
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
             isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
             ( !isset( $_GET[ 'section' ] ) || ( isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == '' ) ) ) {

            // CSS
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_order_requirement_per_wholesale_role_css' , WWPP_CSS_URL . 'wwpp-order-requirement-per-wholesale-role.css' , array() , self::VERSION , 'all' );

            // JS
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , self::VERSION , true );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_order_requirement_per_wholesale_role_js' , WWPP_JS_URL . 'app/wwpp-order-requirement-per-wholesale-role.js' , array( 'jquery' ) , self::VERSION , true );

            $wholesale_role_order_requirement_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING );
            if ( !is_array( $wholesale_role_order_requirement_mapping ) )
                $wholesale_role_order_requirement_mapping = array();

            wp_localize_script(
                                'wwpp_order_requirement_per_wholesale_role_js' ,
                                'wwpp_order_requirement_per_wholesale_role_var' ,
                                array(
                                        'wholesale_role_txt_with_col'       =>  __( 'Wholesale Role:' , 'woocommerce-wholesale-prices-premium' ),
                                        'min_order_qty_txt_with_col'        =>  __( 'Minimum Order Quantity:' , 'woocommerce-wholesale-prices-premium' ),
                                        'wholesale_role_txt'                =>  __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                                        'min_order_qty_txt'                 =>  __( 'Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ),
                                        'no_mapping_txt'                    =>  __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                                        'choose_wholesale_role_txt'         =>  __( 'Choose wholesale role...' , 'woocommerce-wholesale-prices-premium' ),
                                        'empty_fields_txt'                  =>  __( 'Please specify values for the following field/s:' , 'woocommerce-wholesale-prices-premium' ),
                                        'form_error_txt'                    =>  __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                                        'success_add_mapping_txt'           =>  __( 'Successfully Added Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'failed_add_mapping_txt'            =>  __( 'Failed To Add New Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'success_edit_mapping_txt'          =>  __( 'Successfully Updated Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'failed_edit_mapping_txt'           =>  __( 'Failed To Update Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'delete_mapping_prompt_txt'         =>  __( 'Clicking OK will remove the current wholesale role order requirement mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'success_delete_mapping_txt'        =>  __( 'Successfully Deleted Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'failed_delete_mapping_txt'         =>  __( 'Failed To Delete Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'minimum_subtotal_txt_with_col'     =>  sprintf( __( 'Minimum Sub-total Amount (%1$s):' , 'woocommerce-wholesale-prices-premium' ) , get_woocommerce_currency_symbol() ),
                                        'minimum_order_logic_txt_with_col'  =>  __( 'Minimum Order Logic:' , 'woocommerce-wholesale-prices-premium' ),
                                        'minimum_subtotal_txt'              =>  sprintf( __( 'Minimum Sub-total Amount (%1$s)' , 'woocommerce-wholesale-prices-premium' ) , get_woocommerce_currency_symbol() ),
                                        'minimum_order_logic_txt'           =>  __( 'Minimum Order Logic' , 'woocommerce-wholesale-prices-premium' ),
                                        'and_txt'                           =>  __( 'AND' , 'woocommerce-wholesale-prices-premium' ),
                                        'or_txt'                            =>  __( 'OR' , 'woocommerce-wholesale-prices-premium' ),
                                        'cancel_txt'                        =>  __( 'Cancel' , 'woocommerce-wholesale-prices-premium' ),
                                        'save_mapping_txt'                  =>  __( 'Save Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'add_mapping_txt'                   =>  __( 'Add Mapping' , 'woocommerce-wholesale-prices-premium' ),
                                        'wholesale_roles'                   =>  $this->wwppGetAllRegisteredWholesaleRoles( null , false ),
                                        'order_requirement'                 =>  ( $wholesale_role_order_requirement_mapping ) ? $wholesale_role_order_requirement_mapping : array()
                                    )
                            );

        }

        // WWPP Tax Settings
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
            isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
            isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_tax_section' ) {

            // CSS
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_settings_tax_css' , WWPP_CSS_URL . 'wwpp-settings-tax.css' , array() , self::VERSION , 'all' );

            // JS
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , self::VERSION );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js', array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_settings_tax_js' , WWPP_JS_URL . 'app/wwpp-settings-tax.js' , array( 'jquery' ) , self::VERSION , true );
            wp_localize_script(
                    'wwpp_settings_tax_js' ,
                    'wwpp_settings_tax_var' ,
                    array(
                        'wholesale_role_txt'                =>  __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                        'empty_fields_txt'                  =>  __( 'Please specify values for the following field/s:' , 'woocommerce-wholesale-prices-premium' ),
                        'form_error_txt'                    =>  __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                        'no_mappings_found_txt'             =>  __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                        'success_add_mapping_txt'           =>  __( 'Successfully Added Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                        'failed_add_mapping_txt'            =>  __( 'Failed To Add New Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                        'success_edit_mapping_txt'          =>  __( 'Successfully Updated Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                        'failed_edit_mapping_txt'           =>  __( 'Failed To Update Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                        'delete_mapping_prompt_confirm_txt' =>  __( 'Clicking OK will remove the current wholesale role tax option mapping' , 'woocommerce-wholesale-prices-premium' ),
                        'success_delete_mapping_txt'        =>  __( 'Successfully Deleted Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                        'failed_delete_mapping_txt'         =>  __( 'Failed To Delete Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' )
                    )
                );

        }

        // WWPP Shipping Settings
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
             isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
             isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_shipping_section' ) {

            // CSS
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_shipping_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-shipping-controls-custom-field.css' , array() , self::VERSION , 'all' );

            // JS
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , self::VERSION );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_shipping_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-shipping-controls-custom-field.js' , array( 'jquery' ) , self::VERSION , true );

        }

        // WWPP Discount Settings
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
             isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
             isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_discount_section' ) {

            // CSS
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_discount_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-discount-controls-custom-field.css' , array() , self::VERSION , 'all' );

            // JS
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , self::VERSION );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_discount_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-discount-controls-custom-field.js' , array( 'jquery' ) , self::VERSION , true );

        }

        // WWPP Surcharge Settings
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
             isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
             isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_payment_gateway_section' ) {

            // CSS
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL. 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_payment_gateway_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-payment-gateway-controls-custom-field.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_wholesale_role_payment_gateway_mapping_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-wholesale-role-payment-gateway-mapping-controls-custom-field.css' , array() , self::VERSION , 'all' );

            // JS
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , self::VERSION );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_payment_gateway_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-payment-gateway-controls-custom-field.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_wholesale_role_payment_gateway_mapping_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-wholesale-role-payment-gateway-mapping-controls-custom-field.js' , array( 'jquery' ) , self::VERSION , true );

        }

        // WWPP Help Settings
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
             isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
             isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_help_section' ) {

            // CSS
            wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), self::VERSION , 'all' );
            wp_enqueue_style( 'wwpp_settings_debug_css' , WWPP_CSS_URL . 'wwpp-settings-debug.css' , array() , self::VERSION , 'all' );

            // JS
            wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION , true );
            wp_enqueue_script( 'wwpp_settings_debug_js' , WWPP_JS_URL . 'app/wwpp-settings-debug.js' , array( 'jquery' ) , self::VERSION , true );
            wp_localize_script(
                'wwpp_settings_debug_js' ,
                'wwpp_settings_debug_var' ,
                array(
                    'success_initialize_visibility_meta_txt'   =>  __( 'Visibility Meta Successfully Initialized' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_initialize_visibility_meta_txt'    =>  __( 'Failed To Initialize Visibility Meta' , 'woocommerce-wholesale-prices-premium' ),
                )
            );

        }

    }

    /**
     * Load Frontend Related Styles and Scripts.
     *
     * @since 1.0.0
     */
    public function loadFrontEndStylesAndScripts() {
        // Only load plugin styles and scripts on the right time and on the right place

        global $post;

        if ( is_checkout() ) {

            wp_enqueue_script( 'wwpp_checkout_page_js' , WWPP_JS_URL . 'app/wwpp-checkout-page.js' , array( 'jquery' , 'wc-checkout' ) , self::VERSION , true );

        } elseif ( is_product() ) {

            wp_enqueue_style( 'wwpp_single_product_page_css' , WWPP_CSS_URL . 'wwpp-single-product-page.css' , array() , self::VERSION , 'all' );

            // The whole point of the whole block of code below is to sync with the current selected variation of a
            // variable product the minimum order quantity of that variation.
            // if variation A is selected, then we need to set the value and min attribute of the quantity field to the
            // minimum order quantity of variation A if one is set
            if ( $post->post_type == 'product' ) {

                if ( function_exists( 'wc_get_product' ) )
                    $product = wc_get_product( $post->ID );
                else
                    $product = WWPP_WC_Functions::wc_get_product( $post->ID );

                if ( $product->product_type == 'variable' ) {

                    $userWholesaleRole = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
                    $variationsArr = array();

                    if ( !empty( $userWholesaleRole ) ) {

                        foreach ( $product->get_available_variations() as $variation ) {

                            if ( function_exists( 'wc_get_product' ) )
                                $variationProduct = wc_get_product( $variation[ 'variation_id' ] );
                            else
                                $variationProduct = WWPP_WC_Functions::wc_get_product( $variation[ 'variation_id' ] );

                            if ( method_exists( $variationProduct , 'get_display_price' ) )
                                $currVarPrice = $variationProduct->get_display_price();
                            else
                                $currVarPrice = WWPP_WC_Functions::get_display_price( $variationProduct );

                            $minimumOrder = get_post_meta( $variation[ 'variation_id' ] , $userWholesaleRole[ 0 ] . "_wholesale_minimum_order_quantity" , true );

                            $wholesalePrice = trim( $this->_wwpp_wholesale_prices->getProductWholesalePrice( $variation[ 'variation_id' ] , $userWholesaleRole ) );
                            $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_shop' , $wholesalePrice, $variation[ 'variation_id' ] , $userWholesaleRole );

                            if ( !$minimumOrder )
                                $minimumOrder = 0;

                            // Only pass through to wc_price if a numeric value given otherwise it will spit out $0.00
                            if ( is_numeric( $wholesalePrice ) ) {

                                $wholesalePriceTitleText = __( 'Wholesale Price:' , 'woocommerce-wholesale-prices-premium' );
                                $wholesalePriceTitleText = apply_filters( 'wwp_filter_wholesale_price_title_text' , $wholesalePriceTitleText );

                                $wholesalePriceHTML =   '<del>' . wc_price($currVarPrice) . $product->get_price_suffix() . '</del>
                                                        <span style="display: block;" class="wholesale_price_container">
                                                            <span class="wholesale_price_title">' . $wholesalePriceTitleText . '</span>
                                                            <ins>' . wc_price( $wholesalePrice ) . $product->get_price_suffix() . '</ins>
                                                        </span>';

                                $wholesalePriceHTML = apply_filters( 'wwp_filter_wholesale_price_html' , $wholesalePriceHTML , $currVarPrice , $variationProduct , $userWholesaleRole , $wholesalePriceTitleText , $wholesalePrice );

                                $wholesalePriceHTML = '<span class="price">' . $wholesalePriceHTML . '</span>';

                                $priceHTML = $wholesalePriceHTML;
                                $hasWholesalePrice = true;

                            } else {

                                $priceHTML = '<p class="price">' . wc_price( $currVarPrice ) . $product->get_price_suffix() . '</p>';
                                $hasWholesalePrice = false;

                            }

                            $variationsArr[] =  array(
                                                    'variation_id'          =>  $variation[ 'variation_id' ],
                                                    'minimum_order'         =>  (int) $minimumOrder,
                                                    'price_html'            =>  $priceHTML,
                                                    'has_wholesale_price'   =>  $hasWholesalePrice
                                                );

                        }

                        wp_enqueue_script( 'wwpp_variable_product_page_js' , WWPP_JS_URL . 'app/wwpp-variable-product-page.js' , array( 'jquery' ) , self::VERSION , true );
                        wp_localize_script( 'wwpp_variable_product_page_js' , 'WWPPVariableProductPageVars' , array( 'variations' => $variationsArr ) );

                    }

                }

            }

        }

    }

    public function adminNotices() {

    }

    /**
     * Register plugin custom menus.
     *
     * @since 1.0.0
     */
    public function registerMenu() {

        // Register Settings Menu (Append to woocommerce admin area)
        $this->_wwpp_roles_page_handle =  add_submenu_page(
                                                'woocommerce',
                                                'WooCommerce Wholesale Prices | Wholesale Roles',
                                                'Wholesale Roles',
                                                'manage_options',
                                                $this->_wwpp_roles_page_slug,
                                                array(self::getInstance(),"wholesaleRolesView")
                                            );

    }

    /**
     * View for wholesale roles page.
     *
     * @since 1.0.0
     */
    public function wholesaleRolesView(){

        $allRegisteredWholesaleRoles = $this->wwppGetAllRegisteredWholesaleRoles(null,false);

        if(count($allRegisteredWholesaleRoles) <= 1)
            $wholeSaleRolesTotalText = '<span class="wholesale-roles-count">'.count($allRegisteredWholesaleRoles).'</span> item';
        else
            $wholeSaleRolesTotalText = '<span class="wholesale-roles-count">'.count($allRegisteredWholesaleRoles).'</span> items';

        // Move the main wholesale role always on top of the array
        foreach ($allRegisteredWholesaleRoles as $key => $arr) {

            if ( array_key_exists( 'main', $arr ) && $arr['main'] ){
                $mainWholesaleRole = $allRegisteredWholesaleRoles[$key];
                unset($allRegisteredWholesaleRoles[$key]);
                $allRegisteredWholesaleRoles = array( $key => $mainWholesaleRole ) + $allRegisteredWholesaleRoles;
                break;
            }

        }

        $allShippingClasses = WC()->shipping->get_shipping_classes();

        require_once ('views/view-wwpp-wholesale-roles.php');

    }

    /**
     * Add plugin listing custom action link ( settings ).
     *
     * @param $links
     * @param $file
     * @return mixed
     *
     * @since 1.0.2
     */
    public function addPluginListingCustomActionLinks ( $links , $file ) {

        if ( $file == plugin_basename( WWPP_PLUGIN_PATH . 'woocommerce-wholesale-prices-premium.bootstrap.php' ) ) {

            $settings_link = '<a href="admin.php?page=wc-settings&tab=wwp_settings">' . __( 'Plugin Settings' , 'woocommerce-wholesale-prices-premium' ) . '</a>';
            $license_link = '<a href="options-general.php?page=wwc_license_settings&tab=wwpp">' . __( 'License Settings' , 'woocommerce-wholesale-prices-premium' ) . '</a>';
            array_unshift( $links , $license_link );
            array_unshift( $links , $settings_link );

        }

        return $links;

    }




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | WooCommerce Integration (Single Product Page)
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Embed custom fields relating to wholesale role filter into the single product admin page.
     *
     * @since 1.0.0
     */
    public function productVisibilityFilter(){

        $this->_wwpp_custom_fields->productVisibilityFilter( $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Save custom embeded fields relating to wholesale role filter.
     *
     * @since 1.0.0
     */
    public function saveIntegratedCustomWholesaleFieldsOnProductPage(){

        $this->_wwpp_custom_fields->saveIntegratedCustomWholesaleFieldsOnProductPage();

    }

    /**
     * Apply wholesale roles filter to shop and archive pages.
     *
     * @param $productQuery
     * @since 1.0.0
     */
    public function preGetPosts($productQuery){

        $this->_wwpp_custom_fields->preGetPosts( $productQuery , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Same as preGetPosts function but only intended for WooCommerce Wholesale Order Form integration,
     * you see the WWOF uses custom query, so unlike the usual way of filter query object, we can't do that with WWOF,
     * but we can filter the query args thus achieving the same effect.
     *
     * @param $args
     *
     * @return mixed
     * @since 1.0.0
     */
    public function preGetPostsArg ( $args ) {

        return $this->_wwpp_custom_fields->preGetPostsArg( $args , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Filter inter sells products ( cross-sells, up-sells ).
     *
     * @since 1.7.3
     *
     * @param $product_ids
     * @param $product
     * @return array
     */
    public function filterProductInterSells( $product_ids , $product ) {

        return $this->_wwpp_products_filter->filterProductInterSells( $product_ids , $product , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Apply wholesale roles filter to single product page (redirect to shop page).
     *
     * @since 1.0.0
     */
    public function wholesaleVisibilityFilterForSingleProduct () {

        global $wc_wholesale_prices_premium;
        $this->_wwpp_custom_fields->wholesaleVisibilityFilterForSingleProduct( $wc_wholesale_prices_premium->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Wholesale products are products who have wholesale price meta greater than zero.
     * This filter does not apply to admins coz we don't restrict admins in any way.
     * ( Shop and Archive Pages ).
     *
     * @param $productQuery
     *
     * @since 1.0.3
     */
    public function onlyShowWholesaleProductsToWholesaleUsers ( $productQuery ) {

        $this->_wwpp_products_filter->onlyShowWholesaleProductsToWholesaleUsers( $productQuery , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Wholesale products are products who have wholesale price meta greater than zero.
     * This filter does not apply to admins coz we don't restrict admins in any way.
     * ( WooCommerce Wholesale Order Form Integration ).
     *
     * @param $args
     * @return mixed
     *
     * @since 1.0.3
     */
    public function onlyShowWholesaleProductsToWholesaleUsersArg ( $args ) {

        return $this->_wwpp_products_filter->onlyShowWholesaleProductsToWholesaleUsersArg( $args , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Wholesale products are products who have wholesale price meta greater than zero.
     * This filter does not apply to admins coz we don't restrict admins in any way.
     * ( Single Product Page ).
     *
     * @since 1.0.3
     */
    public function onlyShowWholesaleProductsToWholesaleUsersSingleProductPage () {

        $this->_wwpp_products_filter->onlyShowWholesaleProductsToWholesaleUsersSingleProductPage( $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Apply filter to only show variations of a variable product on proper time and place.
     * ( Only show variations with wholesale price on wholesale users if setting is enabled )
     * ( Only show variations to appropriate wholesale users if it is set to be exclusively visible to certain wholesale roles )
     *
     * Make variation invisible.
     *
     * @param $visible
     * @param $variation_id
     * @param $variable_id
     * @param $variation_obj
     * @return bool
     *
     * @since 1.3.0
     */
    public function filterVariationVisibility ( $visible , $variation_id , $variable_id , $variation_obj = null ) {

        return $this->_wwpp_products_filter->filterVariationVisibility( $visible , $variation_id , $variable_id , $variation_obj , $this->_wwpp_wholesale_roles->getUserWholesaleRole() , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) , $this->_wwpp_wholesale_prices );

    }

    /**
     * Apply filter to only show variations of a variable product on proper time and place.
     * ( Only show variations with wholesale price on wholesale users if setting is enabled )
     * ( Only show variations to appropriate wholesale users if it is set to be exclusively visible to certain wholesale roles )
     *
     * Make variation un-purchasable
     *
     * @param $purchasable
     * @param $variation_obj
     * @return mixed
     *
     * @since 1.2.0
     */
    public function filterVariationPurchasability ( $purchasable , $variation_obj ) {

        return $this->_wwpp_products_filter->filterVariationPurchasability( $purchasable , $variation_obj , $this->_wwpp_wholesale_roles->getUserWholesaleRole() , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) , $this->_wwpp_wholesale_prices );

    }

    /**
     * Always allow wholesale users to perform backorders no matter what.
     *
     * @since 1.6.0
     * @param $backorders_allowed
     * @param $product_id
     * @return mixed
     */
    public function alwaysAllowBackordersToWholesaleUsers( $backorders_allowed , $product_id ) {

        return $this->_wwpp_products_filter->alwaysAllowBackordersToWholesaleUsers( $backorders_allowed , $product_id , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }




  /*
   |--------------------------------------------------------------------------------------------------------------------
   | Woocommerce Wholesale Prices Integration (Settings)
   |--------------------------------------------------------------------------------------------------------------------
   */

    /**
     * Change appropriately the title of the general section of the plugin's settings.
     *
     * @param $generalSectionTitle
     * @return mixed
     *
     * @since 1.0.3
     */
    public function pluginSettingsGeneralSectionTitle( $generalSectionTitle ) {

        return $this->_wwpp_settings->pluginSettingsGeneralSectionTitle( $generalSectionTitle );

    }

    /**
     * Attach plugin settings sections to main plugin ( the free version )
     *
     * @param $sections
     *
     * @return mixed
     * @since 1.0.0
     */
    public function pluginSettingsSections( $sections ) {

        return $this->_wwpp_settings->pluginSettingsSections( $sections );

    }

    /**
     * Attach plugin settings section contents to main plugin ( the free version )
     *
     * @param $settings
     * @param $current_section
     *
     * @return mixed
     * @since 1.0.0
     */
    public function pluginSettingsSectionContent( $settings , $current_section ) {

        return $this->_wwpp_settings->pluginSettingsSectionContent( $settings, $current_section );

    }

    /**
     * Add custom control field that will be used as the shipping controls for the shipping section of the plugin's settings.
     *
     * @since 1.0.3
     */
    public function renderPluginSettingsCustomFieldShippingControls() {

        require_once ( 'views/plugin-settings-custom-fields/view-wwpp-shipping-controls-custom-field.php' );

    }

    /**
     * Add custom control field that will be used as the discount controls for the discount section of the plugin's settings.
     *
     * @since 1.2.0
     */
    public function renderPluginSettingsCustomFieldDiscountControls() {

        require_once ( 'views/plugin-settings-custom-fields/view-wwpp-discount-controls-custom-field.php' );

    }

    /**
     * Add custom control that will be used as the field to set surcharges per payment gateway per wholesale role
     *
     * @since 1.3.0
     */
    public function renderPluginSettingsCustomFieldPaymentGatewaySurchargeControls() {

        require_once ( 'views/plugin-settings-custom-fields/view-wwpp-payment-gateway-surcharge-controls-custom-field.php' );

    }

    /**
     * Add custom control that will be used as the field to set which payment gateways are enabled per wholesale role
     *
     * @since 1.3.0
     */
    public function renderPluginSettingsCustomFieldWholesaleRolePaymentGatewayControls() {

        require_once ( 'views/plugin-settings-custom-fields/view-wwpp-wholesale-role-payment-gateway-controls-custom-field.php' );

    }

    /**
     * Add custom control field that will be used to display help resources.
     *
     * @since 1.4.1
     */
    public function renderPluginSettingsCustomFieldHelpResourcesControls() {

        require_once ( 'views/plugin-settings-custom-fields/view-wwpp-help-resources-controls-custom-field.php' );

    }

    /**
     * Add custom control field that will be used to display wholesale role tax exemption mapping.
     *
     * @since 1.5.0
     */
    public function renderPluginSettingsCustomFieldWholesaleRoleTaxOptionsMappingControls() {

        require_once( 'views/plugin-settings-custom-fields/view-wwpp-wholesale-role-tax-options-mapping-controls-custom-field.php' );

    }

    /**
     * Add a custom button field to initialize product visibility meta.
     *
     * @since 1.5.2
     */
    public function renderPluginSettingsCustomFieldInitializeProductVisibilityMetaButton() {

        require_once( 'views/plugin-settings-custom-fields/view-wwpp-initialize-product-visibility-meta-button-custom-field.php' );

    }




  /*
   |--------------------------------------------------------------------------------------------------------------------
   | Product Custom Fields
   |--------------------------------------------------------------------------------------------------------------------
   */

    /**
     * Add minimum order quantity custom field to simple products on product edit screen.
     *
     * @since 1.2.0
     */
    public function addSimpleProductMinimumOrderQuantityCustomField () {

        $this->_wwpp_product_custom_fields->addSimpleProductMinimumOrderQuantityCustomField( $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Add minimum order quantity custom field to variable products on product edit screen.
     * Custom fields are added per variation, not to the parent variable product.
     *
     * @param $loop
     * @param $variation_data
     * @param $variation
     *
     * @since 1.2.0
     */
    public function addVariableProductMinimumOrderQuantityCustomField ( $loop , $variation_data , $variation ) {

        $this->_wwpp_product_custom_fields->addVariableProductMinimumOrderQuantityCustomField( $loop , $variation_data , $variation , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Add order quantity based wholesale pricing custom fields to simple products.
     *
     * @since 1.6.0
     */
    public function addSimpleProductQuantityBasedWholesalePriceCustomField() {

        $this->_wwpp_product_custom_fields->addSimpleProductQuantityBasedWholesalePriceCustomField( $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Add order quantity based wholesale pricing custom fields to variable products.
     *
     * @since 1.6.0
     * @param $loop
     * @param $variation_data
     * @param $variation
     */
    public function addVariableProductQuantityBasedWholesalePriceCustomField( $loop , $variation_data , $variation ) {

        $this->_wwpp_product_custom_fields->addVariableProductQuantityBasedWholesalePriceCustomField( $loop , $variation_data , $variation , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Add wholesale users exclusive variation custom field to variable products on product edit screen.
     * Custom fields are added per variation, not to the parent variable product.
     *
     * @param $loop
     * @param $variation_data
     * @param $variation
     *
     * @since 1.3.0
     */
    public function addVariableProductWholesaleOnlyVariationCustomField ( $loop , $variation_data , $variation ) {

        $this->_wwpp_product_custom_fields->addVariableProductWholesaleOnlyVariationCustomField( $loop , $variation_data ,$variation , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Save minimum order quantity custom field value for simple products on product edit page.
     *
     * @param $post_id
     *
     * @since 1.2.0
     */
    public function saveSimpleProductMinimumOrderQuantityCustomField ( $post_id ) {

        $this->_wwpp_product_custom_fields->saveSimpleProductMinimumOrderQuantityCustomField( $post_id , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Save minimum order quantity custom field value for variable products on product edit page.
     *
     * @param $post_id
     * @since 1.2.0
     */
    public function saveVariableProductMinimumOrderQuantityCustomField ( $post_id ) {

        $this->_wwpp_product_custom_fields->saveVariableProductMinimumOrderQuantityCustomField( $post_id , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Save wholesale exclusive variation custom field for variable products on product edit page.
     *
     * @param $post_id
     *
     * @since 1.3.0
     */
    public function saveVariableProductWholesaleOnlyVariationCustomField ( $post_id ) {

        $this->_wwpp_product_custom_fields->saveVariableProductWholesaleOnlyVariationCustomField( $post_id , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }




  /*
   |--------------------------------------------------------------------------------------------------------------------
   | Product Category Custom Fields
   |--------------------------------------------------------------------------------------------------------------------
   */

    /**
     * Add wholesale price fields to product category taxonomy add page.
     *
     * @param $taxonomy
     *
     * @since 1.0.5
     */
    public function productCategoryAddCustomFields ( $taxonomy ) {

        $this->_wwpp_product_category_custom_fields->productCategoryAddCustomFields( $taxonomy , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Add wholesale price fields to product category taxonomy edit page.
     *
     * @param $term
     *
     * @since 1.0.5
     */
    public function productCategoryEditCustomFields ( $term ) {

        $this->_wwpp_product_category_custom_fields->productCategoryEditCustomFields( $term , $this->wwppGetAllRegisteredWholesaleRoles( null , false ) );

    }

    /**
     * Save wholesale price fields data on product category taxonomy add and edit page.
     *
     * @param $term_id
     * @param $taxonomy_term_id
     *
     * @since 1.0.5
     */
    public function productCategorySaveCustomFields ( $term_id , $taxonomy_term_id ) {

        $this->_wwpp_product_category_custom_fields->productCategorySaveCustomFields( $term_id );

    }




  /*
   |--------------------------------------------------------------------------------------------------------------------
   | Wholesale Pricing, Shipping and Taxes
   |--------------------------------------------------------------------------------------------------------------------
   */

    /**
     * Display quantity based discount markup on single product pages.
     *
     * @since 1.6.0
     * @param $wholesalePriceHTML
     * @param $price
     * @param $product
     * @param $userWholesaleRole
     * @return string
     */
    public function displayOrderQuantityBasedWholesalePricing( $wholesalePriceHTML , $price , $product , $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->displayOrderQuantityBasedWholesalePricing( $wholesalePriceHTML , $price , $product , $userWholesaleRole );

    }

    /**
     * Apply quantity based discount on products on cart.
     *
     * @since 1.6.0
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @param int $quantity
     * @return mixed
     */
    public function applyOrderQuantityBasedWholesalePricing( $wholesalePrice , $productID , $userWholesaleRole , $quantity = 1 ) {

        return $this->_wwpp_wholesale_prices->applyOrderQuantityBasedWholesalePricing( $wholesalePrice , $productID , $userWholesaleRole , $quantity );

    }

    /**
     * Apply product category level wholesale discount. Only applies when a product has no wholesale price set.
     *
     * @since 1.0.5
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @return mixed
     */
    public function applyProductCategoryWholesaleDiscount( $wholesalePrice , $productID , $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->applyProductCategoryWholesaleDiscount( $wholesalePrice , $productID , $userWholesaleRole );

    }

    /**
     * Apply wholesale role general discount to the product being purchased by this user.
     *
     * @since 1.2.0
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @return string
     */
    public function applyWholesaleRoleGeneralDiscount( $wholesalePrice , $productID , $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->applyWholesaleRoleGeneralDiscount( $wholesalePrice , $productID , $userWholesaleRole );

    }

    /**
     * Apply appropriate shipping method to products in a cart.
     *
     * @param $shippingMethod
     * @param $package
     * @return array
     *
     * @since 1.0.3
     */
    public function applyAppropriateShippingMethod ( $shippingMethod , $package ) {

        return $this->_wwpp_shipping_methods_filter->applyAppropriateShippingMethod( $shippingMethod , $package , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Apply tax exemptions to wholesale users based on settings.
     *
     * @return array
     * @since 1.0.0
     */
    public function applyTaxExemptionsToWholesaleUsers(){

        return $this->_wwpp_wholesale_prices->applyTaxExemptionsToWholesaleUsers($this->_wwpp_wholesale_roles->getUserWholesaleRole());

    }

    /**
     * Integrate tax (either include or exclude tax) on the price of the products on the shop pages.
     *
     * @param $wholesalePrice
     * @param $productId
     * @param $userWholesaleRole
     *
     * @return mixed
     * @since 1.0.0
     */
    public function integrateTaxToWholesalePriceOnShop ( $wholesalePrice, $productId, $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->integrateTaxToWholesalePriceOnShop( $wholesalePrice, $productId, $userWholesaleRole );

    }

    /**
     * Set coupons availability to wholesale users.
     *
     * @param $enabled
     * @return bool
     *
     * @since 1.5.0
     */
    public function toggleAvailabilityOfCouponsToWholesaleUsers ( $enabled ) {

        return $this->_wwpp_wholesale_prices->toggleAvailabilityOfCouponsToWholesaleUsers( $enabled , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Override "woocommerce_tax_display_cart" option for wholesale users.
     *
     * @since 1.4.6
     * @param $optionValue
     * @return string
     */
    public function wholesaleTaxDisplayCart( $optionValue ) {

        return $this->_wwpp_wholesale_prices->wholesaleTaxDisplayCart( $optionValue , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Filter wholesale product price on cart page and cart widget to apply taxing accordingly.
     *
     * @since 1.4.6
     * @param $price
     * @param $cartItem
     * @param $cartItemKey
     * @return mixed
     */
    public function wholesaleCartItemPrice( $price , $cartItem , $cartItemKey ) {

        if ( $cartItem[ 'data' ]->product_type == 'simple' )
            $product_id = $cartItem[ 'data' ]->id;
        elseif ( $cartItem[ 'data' ]->product_type == 'variation' )
            $product_id = $cartItem[ 'data' ]->variation_id;

        $filteredPrice = $price;

        $userWholesaleRole = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

        if ( !empty( $userWholesaleRole ) ) {

            $wholesalePrice = $this->getProductWholesalePrice( $product_id , $userWholesaleRole );
            $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_cart' , $wholesalePrice , $product_id , $userWholesaleRole , $cartItem );

            $filteredPrice = $this->_wwpp_wholesale_prices->wholesaleCartItemPrice( $price , $cartItem , $cartItemKey , $wholesalePrice , $userWholesaleRole );

        }

        $filteredPrice = apply_filters( 'wwpp_filter_cart_item_price' , $filteredPrice , $price , $userWholesaleRole , $cartItem , $cartItemKey );

        return $filteredPrice;

    }

    /**
     * Filter callback that determines whether or not to apply wholesale pricing for the current order of a wholesale
     * user. This checks for order requirement for all wholesale roles or per wholesale role.
     *
     * @since 1.0.0
     * @param $apply_wholesale_price The flag that determines if wholesale pricing should be applied to this current order.
     * @param $cart_object The WooCommerce cart object (WC_Cart).
     * @param $userWholesaleRole The user's wholesale role (Array).
     * @return bool
     */
    public function applyWholesalePriceFlagFilter ( $apply_wholesale_price , $cart_object , $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->applyWholesalePriceFlagFilter( $apply_wholesale_price , $cart_object , $userWholesaleRole );

    }

    /**
     * Filter callback to determined whether or not to apply wholesale pricing per product basis.
     *
     * @param $apply_wholesale_price
     * @param $value
     * @param $cart_object
     * @param $userWholesaleRole
     * @return array|bool
     *
     * @since 1.2.0
     */
    public function applyWholesalePricePerProductBasisFilter ( $apply_wholesale_price , $value , $cart_object , $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->applyWholesalePricePerProductBasisFilter( $apply_wholesale_price , $value , $cart_object , $userWholesaleRole );

    }

    /**
     * Filter the text for the wholesale price title.
     *
     * @param $titleText
     *
     * @return mixed
     * @since 1.0.0
     */
    public function filterWholesalePriceTitleText ( $titleText ) {

        return $this->_wwpp_wholesale_prices->filterWholesalePriceTitleText($titleText);

    }

    /**
     * Used to show/hide original product price.
     *
     * @param $wholesalePriceHTML
     *
     * @return mixed
     * @since 1.3.2
     */
    public function filterProductOriginalPriceVisibility( $wholesalePriceHTML ) {

        return $this->_wwpp_wholesale_prices->filterProductOriginalPriceVisibility( $wholesalePriceHTML );

    }

    /**
     * Override the price suffix for wholesale users only.
     *
     * @param $priceDisplaySuffix
     *
     * @return mixed
     * @since 1.4.0
     */
    public function overrideWholesalePriceSuffix( $priceDisplaySuffix ) {

        return $this->_wwpp_wholesale_prices->overrideWholesalePriceSuffix( $priceDisplaySuffix , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Filter the price to show the minimum order quantity for wholesale users.
     *
     * @param $wholesalePriceHTML
     * @param $price
     * @param $product
     * @param $userWholesaleRole
     * @return string
     *
     * @since 1.4.0
     */
    public function displayMinimumWholesaleOrderQuantity( $wholesalePriceHTML , $price , $product , $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->displayMinimumWholesaleOrderQuantity( $wholesalePriceHTML , $price , $product , $userWholesaleRole );

    }

    /**
     * Set minimum order quantity as minimum value ( default value ) for a given product if one is set.
     *
     * @param $args
     * @param $product
     * @return mixed
     *
     * @since 1.4.2
     */
    public function setMinimumOrderQuantityAsInitialValue ( $args , $product = null ) {

        if ( is_null( $product ) )
            $product = $GLOBALS[ 'product' ];

        return $this->_wwpp_wholesale_prices->setMinimumOrderQuantityAsInitialValue( $args , $product , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Filter product category product items count.
     *
     * @since 1.7.3
     *
     * @param $count_markup
     * @param $category
     * @return string
     */
    public function filterProductCategoryPostCount( $count_markup , $category ) {

        return $this->_wwpp_products_filter->filterProductCategoryPostCount( $count_markup , $category , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Check what kind of table rate shipping plugin is installed.
     * Either woo themes one? or code canyon one?
     * Returns false if none otherwise.
     *
     * @return bool|string
     *
     * @since 1.3.0
     */
    public function checkTableRateShippingType () {

        return $this->_wwpp_shipping_methods_filter->checkTableRateShippingType();

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Payment Gateways
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Apply custom payment gateway surcharge.
     *
     * @param $wc_cart
     *
     * @since 1.3.0
     */
    public function applyPaymentGatewaySurcharge ( $wc_cart ) {

        $this->_wwpp_payment_gateways->applyPaymentGatewaySurcharge( $wc_cart , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }

    /**
     * Apply taxable notice to surcharge.
     *
     * @param $cart_totals_fee_html
     * @param $fee
     * @return string
     *
     * @since 1.3.0
     */
    public function applyTaxableNoticeOnSurcharge ( $cart_totals_fee_html , $fee ) {

        return $this->_wwpp_payment_gateways->applyTaxableNoticeOnSurcharge( $cart_totals_fee_html , $fee );

    }

    /**
     * Filter payment gateway to be available to certain wholesale role.
     *
     * @param $availableGateways
     * @return array
     *
     * @since 1.3.0
     */
    public function filterAvailablePaymentGateways ( $availableGateways ) {

        return $this->_wwpp_payment_gateways->filterAvailablePaymentGateways( $availableGateways , $this->_wwpp_wholesale_roles->getUserWholesaleRole() );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Woocommerce Integration (Custom Fields)
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * dd custom column to order listing page.
     *
     * @param $columns
     *
     * @return mixed
     * @since 1.0.0
     */
    public function addOrdersListingCustomColumn( $columns ){

        return $this->_wwpp_custom_fields->addOrdersListingCustomColumn($columns);

    }

    /**
     * Add content to the custom column on order listing page.
     *
     * @param $column
     * @param $postid
     *
     * @since 1.0.0
     */
    public function addOrdersListingCustomColumnContent( $column, $postid ){

        $this->_wwpp_custom_fields->addOrdersListingCustomColumnContent($column, $postid, $this->wwppGetAllRegisteredWholesaleRoles(null,false));

    }

    /**
     * Add custom filter on order listing page ( order type filter ).
     *
     * @since 1.0.0
     */
    public function addWholesaleRoleOrderListingFilter () {

        $this->_wwpp_custom_fields->addWholesaleRoleOrderListingFilter( $this->wwppGetAllRegisteredWholesaleRoles(null,false) );

    }

    /**
     * Add functionality to the custom filter added on order listing page ( order type filter ).
     *
     * @param $query
     *
     * @since 1.0.0
     */
    public function wholesaleRoleOrderListingFilter( $query ){

        $this->_wwpp_custom_fields->wholesaleRoleOrderListingFilter( $query );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Woocommerce Integration (Custom Message)
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Add custom thank you message on thank you page after successful order.
     *
     * @param $origMsg
     *
     * @return string
     * @since 1.0.0
     */
    public function customThankYouMessage ( $origMsg ){

        return $this->_wwpp_custom_messages->customThankYouMessage( $origMsg );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Woocommerce Integration (Custom Meta)
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Add custom meta to orders ( order type metadata ) to be used later for filtering orders by order type
     * on the order listing page.
     *
     * @param $orderId
     *
     * @since 1.0.0
     */
    public function addOrderTypeMetaToOrders( $orderId ){

        $this->_wwpp_custom_meta->addOrderTypeMetaToOrders( $orderId, $this->wwppGetAllRegisteredWholesaleRoles( null, false ) );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | AJAX Handlers
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Register AJAX interface callbacks.
     *
     * @since 1.0.0
     */
    public function registerAJAXCAllHandlers() {

        // Note: You have to register your ajax interface to both wp_ajax_ and wp_ajax_nopriv_ if you want it to be
        // accessible to both logged in and unauthenticated users.

        // Authenticated user "ONLY" AJAX interfaces

        // Get all registered wholesale roles
        add_action( "wp_ajax_wwppGetAllRegisteredWholesaleRoles" , array( self::getInstance() , 'wwppGetAllRegisteredWholesaleRoles' ) );

        // Wholesale role
        add_action( "wp_ajax_wwppAddNewWholesaleRole" , array( self::getInstance() , 'wwppAddNewWholesaleRole' ) );
        add_action( "wp_ajax_wwppEditWholesaleRole" , array( self::getInstance() , 'wwppEditWholesaleRole' ) );
        add_action( "wp_ajax_wwpDeleteWholesaleRole" , array( self::getInstance() , 'wwpDeleteWholesaleRole' ) );

        // Plugin license settings
        add_action( "wp_ajax_wwppSaveLicenseDetails" , array( $this->_wwpp_wws_license_settings , 'wwppSaveLicenseDetails' ) );

        // Wholesale role shipping method mapping
        add_action( "wp_ajax_wwppAddWholesaleRoleShippingMethodMapping" , array( $this->_wwpp_shipping_methods_filter , 'wwppAddWholesaleRoleShippingMethodMapping' ) );
        add_action( "wp_ajax_wwppEditWholesaleRoleShippingMethodMapping" , array( $this->_wwpp_shipping_methods_filter , 'wwppEditWholesaleRoleShippingMethodMapping' ) );
        add_action( "wp_ajax_wwppDeleteWholesaleRoleShippingMethodMapping" , array( $this->_wwpp_shipping_methods_filter , 'wwppDeleteWholesaleRoleShippingMethodMapping' ) );
        add_action( "wp_ajax_wwppGetAllShippingZones" , array( $this->_wwpp_shipping_methods_filter , 'wwppGetAllShippingZones' ) );
        add_action( "wp_ajax_wwppGetAllShippingZoneMethods" , array( $this->_wwpp_shipping_methods_filter , 'wwppGetAllShippingZoneMethods' ) );
        add_action( "wp_ajax_wwppGetAllShippingZoneTableRates" , array( $this->_wwpp_shipping_methods_filter , 'wwppGetAllShippingZoneTableRates' ) );

        // Wholesale role general discount mapping
        add_action( "wp_ajax_wwppAddWholesaleRoleGeneralDiscountMapping" , array( $this->_wwpp_general_discount , 'wwppAddWholesaleRoleGeneralDiscountMapping' ) );
        add_action( "wp_ajax_wwppEditWholesaleRoleGeneralDiscountMapping" , array( $this->_wwpp_general_discount , 'wwppEditWholesaleRoleGeneralDiscountMapping' ) );
        add_action( "wp_ajax_wwppDeleteWholesaleRoleGeneralDiscountMapping" , array( $this->_wwpp_general_discount , 'wwppDeleteWholesaleRoleGeneralDiscountMapping' ) );

        // Wholesale role payment gateway surcharge mapping
        add_action( "wp_ajax_wwppAddPaymentGatewaySurcharge" , array( $this->_wwpp_payment_gateways , 'wwppAddPaymentGatewaySurcharge' ) );
        add_action( "wp_ajax_wwppUpdatePaymentGatewaySurcharge" , array( $this->_wwpp_payment_gateways , 'wwppUpdatePaymentGatewaySurcharge' ) );
        add_action( "wp_ajax_wwppDeletePaymentGatewaySurcharge" , array( $this->_wwpp_payment_gateways , 'wwppDeletePaymentGatewaySurcharge' ) );

        // Wholesale role payment gateway mapping
        add_action( "wp_ajax_wwppAddWholesaleRolePaymentGatewayMapping" , array( $this->_wwpp_payment_gateways , 'wwppAddWholesaleRolePaymentGatewayMapping' ) );
        add_action( "wp_ajax_wwppUpdateWholesaleRolePaymentGatewayMapping" , array( $this->_wwpp_payment_gateways , 'wwppUpdateWholesaleRolePaymentGatewayMapping' ) );
        add_action( "wp_ajax_wwppDeleteWholesaleRolePaymentGatewayMapping" , array( $this->_wwpp_payment_gateways , 'wwppDeleteWholesaleRolePaymentGatewayMapping' ) );

        // Wholesale role order requirement mapping
        add_action( "wp_ajax_wwpp_add_wholesale_role_order_requirement" , array( $this->_wwpp_wholesale_role_order_requirement , 'wwpp_add_wholesale_role_order_requirement' ) );
        add_action( "wp_ajax_wwpp_edit_wholesale_role_order_requirement" , array( $this->_wwpp_wholesale_role_order_requirement , 'wwpp_edit_wholesale_role_order_requirement' ) );
        add_action( "wp_ajax_wwpp_delete_wholesale_role_order_requirement" , array( $this->_wwpp_wholesale_role_order_requirement , 'wwpp_delete_wholesale_role_order_requirement' ) );

        // Wholesale role tax option mapping
        add_action( "wp_ajax_wwpp_add_wholesale_role_tax_option" , array( $this->_wwpp_wholesale_role_tax_option , 'wwpp_add_wholesale_role_tax_option' ) );
        add_action( "wp_ajax_wwpp_edit_wholesale_role_tax_option" , array( $this->_wwpp_wholesale_role_tax_option , 'wwpp_edit_wholesale_role_tax_option' ) );
        add_action( "wp_ajax_wwpp_delete_wholesale_role_tax_option" , array( $this->_wwpp_wholesale_role_tax_option , 'wwpp_delete_wholesale_role_tax_option' ) );

        // Product Quantity Based Wholesale Pricing
        add_action( "wp_ajax_wwppToggleProductQuantityBasedWholesalePricing" , array( $this->_wwpp_product_custom_fields , 'wwppToggleProductQuantityBasedWholesalePricing' ) );
        add_action( "wp_ajax_wwppToggleProductQuantityBasedWholesalePricingMappingView" , array( $this->_wwpp_product_custom_fields , 'wwppToggleProductQuantityBasedWholesalePricingMappingView' ) );
        add_action( "wp_ajax_wwppAddQuantityDiscountRule" , array( $this->_wwpp_product_custom_fields , 'wwppAddQuantityDiscountRule' ) );
        add_action( "wp_ajax_wwppSaveQuantityDiscountRule" , array( $this->_wwpp_product_custom_fields , 'wwppSaveQuantityDiscountRule' ) );
        add_action( "wp_ajax_wwppDeleteQuantityDiscountRule" , array( $this->_wwpp_product_custom_fields , 'wwppDeleteQuantityDiscountRule' ) );

        // Help options
        add_action( "wp_ajax_wwpp_initialize_product_visibility_meta" , array( $this , 'wwpp_initialize_product_visibility_meta' ) );

        // Unauthenticated user "ONLY" AJAX interfaces
        //add_action("wp_ajax_nopriv_",array(self::getInstance(),''));

    }

    /**
     * Get all registered wholesale roles.
     *
     * @param null $dummyArg
     * @param bool $ajaxCall
     *
     * @return mixed
     * @since 1.0.0
     */
    public function wwppGetAllRegisteredWholesaleRoles($dummyArg = null, $ajaxCall = true){

        $allRegisteredWholesaleRoles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

        if($ajaxCall === true){

            header('Content-Type: application/json'); // specify we return json
            echo json_encode($allRegisteredWholesaleRoles);
            die();

        }else{

            return $allRegisteredWholesaleRoles;

        }

    }

    /**
     * Add new wholesale role.
     *
     * @param null $newRole
     * @param bool $ajaxCall
     *
     * @return array
     * @since 1.0.0
     */
    public function wwppAddNewWholesaleRole($newRole = null, $ajaxCall = true){

        if($ajaxCall === true){
            $newRole = $_POST['newRole'];
        }

        $response = array();

        global $wp_roles;

        if(!isset($wp_roles))
            $wp_roles = new WP_Roles();

        $allUserRoles = $wp_roles->get_names();

        // Add plugin custom roles and capabilities
        if(!array_key_exists($newRole['roleKey'],$allUserRoles)){

            $this->_wwpp_wholesale_roles->addCustomRole($newRole['roleKey'],$newRole['roleName']);
            $this->_wwpp_wholesale_roles->registerCustomRole(
                                                            $newRole['roleKey'],
                                                            $newRole['roleName'],
                                                            array(
                                                                'desc'                  =>  $newRole['roleDesc'],
                                                                'shippingClassName'     =>  $newRole['roleShippingClassName'],
                                                                'shippingClassTermId'   =>  $newRole['roleShippingClassTermId'],
                                                            ));
            $this->_wwpp_wholesale_roles->addCustomCapability($newRole['roleKey'],'have_wholesale_price');

            $response['status'] = 'success';

        }else{

            $response['status'] = 'error';
            $response['error_message'] = 'Wholesale Role ('. $newRole['roleKey'].') Already Exist, make sure role key and preferably role name are unique';

        }

        if($ajaxCall === true){

            header('Content-Type: application/json'); // specify we return json
            echo json_encode($response);
            die();

        }else{

            return $response;

        }

    }

    /**
     * Edit wholesale role.
     *
     * @param null $role
     * @param bool $ajaxCall
     *
     * @return array
     * @since 1.0.0
     */
    public function wwppEditWholesaleRole($role = null, $ajaxCall = true){

        if($ajaxCall === true){
            $role = $_POST['role'];
        }

        $wpRoles = get_option( 'wp_user_roles' );

        if ( !is_array( $wpRoles ) ) {

            global $wp_roles;
            if( !isset( $wp_roles ) )
                $wp_roles = new WP_Roles();

            $wpRoles = $wp_roles->roles;

        }

        if(array_key_exists($role['roleKey'],$wpRoles)){

            // Update role in WordPress record
            $wpRoles[$role['roleKey']]['name'] = $role['roleName'];
            update_option( 'wp_user_roles', $wpRoles );

            // Update role in registered wholesale roles record
            $registeredWholesaleRoles = unserialize(get_option(WWP_OPTIONS_REGISTERED_CUSTOM_ROLES));

            $registeredWholesaleRoles[$role['roleKey']]['roleName'] = $role['roleName'];
            $registeredWholesaleRoles[$role['roleKey']]['desc'] = $role['roleDesc'];
            $registeredWholesaleRoles[$role['roleKey']]['shippingClassName'] = $role['roleShippingClassName'];
            $registeredWholesaleRoles[$role['roleKey']]['shippingClassTermId'] = $role['roleShippingClassTermId'];

            update_option(WWP_OPTIONS_REGISTERED_CUSTOM_ROLES,serialize($registeredWholesaleRoles));

            $response = array('status' => 'success');

        }else{

            // Specified role to edit doesn't exist
            $response = array(
                                'status'        =>  'error',
                                'error_message' =>  'Specified Wholesale Role ('.$role['roleKey'].') Does not Exist'
                            );

        }

        if($ajaxCall === true){

            header('Content-Type: application/json'); // specify we return json
            echo json_encode($response);
            die();

        }else{

            return array($response);

        }

    }

    /**
     * Delete wholesale role.
     *
     * @param null $roleKey
     * @param bool $ajaxCall
     *
     * @return array
     * @since 1.0.0
     */
    public function wwpDeleteWholesaleRole($roleKey = null, $ajaxCall = true){

        if($ajaxCall === true){
            $roleKey = $_POST['roleKey'];
        }

        // Remove plugin custom roles and capabilities
        $this->_wwpp_wholesale_roles->removeCustomCapability($roleKey,'have_wholesale_price');
        $this->_wwpp_wholesale_roles->removeCustomRole($roleKey);
        $this->_wwpp_wholesale_roles->unregisterCustomRole($roleKey);

        $response = array('status' => 'success');

        if($ajaxCall === true){

            header('Content-Type: application/json'); // specify we return json
            echo json_encode($response);
            die();

        }else{

            return $response;

        }

    }

    /**
     * Initialize product visibility meta for all simple and variable products that was not added to the shop in a normal way.
     * Ex. Product added to the the database manually, imported using external tools or plugins, etc.
     * This only affect products mention above, not products added normally.
     *
     * @since 1.5.2
     * @param null $dummy_arg
     * @param bool|true $ajax_call
     * @return bool
     */
    public function wwpp_initialize_product_visibility_meta( $dummy_arg = null , $ajax_call = true ) {

        $this->_initializeProductVisibilityFilterMeta();

        if ( $ajax_call === true ) {

            header( "Content-Type: application/json" );
            echo json_encode( array( 'status' => 'success' ) );
            die();

        } else
            return true;

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Utilities
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Write test log.
     *
     * @param      $msg
     * @param bool $append
     *
     * @since 1.0.0
     */
    public function writeTestLog($msg,$append = true){

        if($append === true)
            file_put_contents(WWPP_LOGS_PATH.'test_logs.txt',$msg,FILE_APPEND);
        else
            file_put_contents(WWPP_LOGS_PATH.'test_logs.txt',$msg);

    }

    /**
     * Write error log.
     *
     * @param      $msg
     * @param bool $append
     *
     * @since 1.0.0
     */
    public function writeErrorLog($msg,$append = true){

        if($append === true)
            file_put_contents(WWPP_LOGS_PATH.'error_logs.txt',$msg,FILE_APPEND);
        else
            file_put_contents(WWPP_LOGS_PATH.'error_logs.txt',$msg);

    }

    /**
     * Get Woocommerce Version Number
     *
     * @return null
     * @since 1.0.0
     */
    public function getWooCommerceVersion() {

        // If get_plugins() isn't available, require it
        if ( ! function_exists( 'get_plugins' ) )
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {

            return $plugin_folder[$plugin_file]['Version'];

        } else {

            // Otherwise return null
            return NULL;

        }

    }

    /**
     * Check if in wwpp license settings page.
     *
     * @return bool
     *
     * @since 1.2.2
     */
    public function checkIfInWWPPSettingsPage () {

        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwpp' )
            return true;
        else
            return false;

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Public Interfaces
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Get wholesale price of a product ( Per Product, Per Category and Per General Discount of a Wholesale Role ).
     *
     * @param $productID
     * @param $userWholesaleRole
     * @return mixed
     *
     * @since 1.4.0
     */
    public function getProductWholesalePrice ( $productID , $userWholesaleRole ) {

        return $this->_wwpp_wholesale_prices->getProductWholesalePrice( $productID , $userWholesaleRole );

    }

}
