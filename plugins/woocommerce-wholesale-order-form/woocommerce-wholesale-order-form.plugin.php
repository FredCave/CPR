<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once ( WWOF_INCLUDES_ROOT_DIR . 'class-wwof-aelia-currency-switcher-integration-helper.php' );
require_once ( WWOF_INCLUDES_ROOT_DIR . 'class-wwof-product-listing-helper.php' );
require_once ( WWOF_INCLUDES_ROOT_DIR . 'class-wwof-product-listing.php' );
require_once ( WWOF_INCLUDES_ROOT_DIR . 'class-wwof-permissions.php' );
require_once ( WWOF_INCLUDES_ROOT_DIR . 'class-wwof-wws-license-settings.php' );

class WooCommerce_WholeSale_Order_Form {

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Class Members
     |------------------------------------------------------------------------------------------------------------------
     */
    private static $_instance;

    private $_wwof_product_listings;
    private $_wwof_permissions;
    private $_wwof_wws_license_settings;

    const VERSION = '1.3.4';




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
    public function __construct() {

        $this->_wwof_product_listings = WWOF_Product_Listing::getInstance();
        $this->_wwof_permissions = WWOF_Permissions::getInstance();
        $this->_wwof_wws_license_settings = WWOF_WWS_License_Settings::getInstance();

    }

    /**
     * Singleton Pattern.
     *
     * @since 1.0.0
     *
     * @return WooCommerce_WholeSale_Order_Form
     */
    public static function getInstance() {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self;

        return self::$_instance;

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Internationalization and Localization
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Load plugin text domain.
     *
     * @since 1.2.0
     */
    public function loadPluginTextDomain() {

        load_plugin_textdomain( 'woocommerce-wholesale-order-form' , false , WWOF_PLUGIN_BASE_PATH . 'languages/' );

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

        // Set initial settings
        global $WWOF_SETTINGS_DEFAULT_PPP, $WWOF_SETTINGS_DEFAULT_SORT_BY, $WWOF_SETTINGS_DEFAULT_SORT_ORDER;

        // General section settings
        if ( get_option( 'wwof_general_products_per_page' ) === false )
            update_option( 'wwof_general_products_per_page' , $WWOF_SETTINGS_DEFAULT_PPP );

        if ( get_option( 'wwof_general_sort_by') === false )
            update_option( 'wwof_general_sort_by' , $WWOF_SETTINGS_DEFAULT_SORT_BY );

        if ( get_option( 'wwof_general_sort_order' ) === false )
            update_option( 'wwof_general_sort_order' , $WWOF_SETTINGS_DEFAULT_SORT_ORDER );

        // Create wholesale pages
        $this->wwof_createWholesalePage( null , false );

    }

    /**
     * Plugin initialization.
     *
     * @since 1.0.1
     */
    public function initialize() {

    }

    /**
     * Plugin deactivation hook callback.
     *
     * @since 1.0.0
     */
    public function deactivate() {

    }
    



    /*
    |------------------------------------------------------------------------------------------------------------------
    | WooCommerce WholeSale Suit License Settings
    |------------------------------------------------------------------------------------------------------------------
    */

    /**
    * Register general wws license settings page.
    *
    * @since 1.0.1
    */
    public function registerWWSLicenseSettingsMenu() {

        /*
         * Since we don't have a primary plugin to add this license settings, we have to check first if other plugins
         * belonging to the WWS plugin suite has already added a license settings page.
         */
        if ( !defined( 'WWS_LICENSE_SETTINGS_PAGE' ) ) {

            if ( !defined( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' ) )
                define( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' , 'wwof' );

            // Register WWS Settings Menu
            add_submenu_page(
                'options-general.php', // Settings
                __( 'WooCommerce WholeSale Suit License Settings' , 'woocommerce-wholesale-order-form' ),
                __( 'WWS License' , 'woocommerce-wholesale-order-form' ),
                'manage_options',
                'wwc_license_settings',
                array( self::getInstance() , "wwcGeneralLicenseSettingsPage" )
            );

            /*
             * We define this constant with the text domain of the plugin who added the settings page.
             */
            define( 'WWS_LICENSE_SETTINGS_PAGE' , 'woocommerce-wholesale-order-form' );

        }

    }

    /**
     * General WWS license settings page template.
     *
     * @since 1.0.1
     */
    public function wwcGeneralLicenseSettingsPage() {

        require_once( "views/wws-license-settings/view-wwof-general-wws-settings-page.php" );

    }

    /**
     * WWOF WWC license settings header tab item.
     *
     * @since 1.0.1
     */
    public function wwcLicenseSettingsHeader() {

        ob_start();

        if ( isset( $_GET[ 'tab' ] ) )
            $tab = $_GET[ 'tab' ];
        else
            $tab = WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN;

        global $wp;
        $current_url = add_query_arg( $wp->query_string , '?' , home_url( $wp->request ) );
        $wwof_license_settings_url = $current_url . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwof";

        ?>
        <a href="<?php echo $wwof_license_settings_url; ?>" class="nav-tab <?php echo ( $tab == "wwof" ) ? "nav-tab-active" : ""; ?>"><?php _e( 'Wholesale Ordering' , 'woocommerce-wholesale-order-form' ); ?></a>
        <?php

        echo ob_get_clean();

    }

    /**
     * WWOF WWS license settings page.
     *
     * @since 1.0.1
     */
    public function wwcLicenseSettingsPage() {

        ob_start();

        require_once( "views/wws-license-settings/view-wwof-wss-settings-page.php" );

        echo ob_get_clean();

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
    public function addPluginListingCustomActionLinks( $links , $file ) {

        if ( $file == plugin_basename( WWOF_PLUGIN_DIR . 'woocommerce-wholesale-order-form.bootstrap.php' ) ) {

            $settings_link = '<a href="admin.php?page=wc-settings&tab=wwof_settings">' . __( 'Plugin Settings' , 'woocommerce-wholesale-order-form' ) . '</a>';
            $license_link = '<a href="options-general.php?page=wwc_license_settings&tab=wwof">' . __( 'License Settings' , 'woocommerce-wholesale-order-form' ) . '</a>';
            array_unshift( $links , $license_link );
            array_unshift( $links , $settings_link );

        }

        return $links;

    }




    /*
    |------------------------------------------------------------------------------------------------------------------
    | Admin Functions
    |------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Load Admin or Backend Related Styles and Scripts.
     *
     * @since 1.0.0
     */
    public function loadBackEndStylesAndScripts() {

        $screen = get_current_screen();

        // Settings
        if ( in_array( $screen->id , array( 'woocommerce_page_wc-settings' ) ) ) {

            // General styles to be used on all settings sections
            wp_enqueue_style( 'wwof_toastr_css' , WWOF_JS_ROOT_URL . 'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );

            // General scripts to be used on all settings sections
            wp_enqueue_script( 'wwof_BackEndAjaxServices_js' , WWOF_JS_ROOT_URL.'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
            wp_enqueue_script( 'wwof_toastr_js' , WWOF_JS_ROOT_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION );

            if( !isset( $_GET[ 'section' ] ) || $_GET[ 'section' ] == '' ) {

                // General

            } elseif ( isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwof_setting_filters_section' ) {

                // Filters

            } elseif( isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwof_settings_permissions_section' ) {

                // Permissions

            } elseif( isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwof_settings_help_section' ) {

                // Help
                wp_enqueue_style( 'wwof_HelpSettings_css' , WWOF_CSS_ROOT_URL . 'HelpSettings.css' , array() , self::VERSION , 'all' );

                wp_enqueue_script( 'wwof_HelpSettings_js' , WWOF_JS_ROOT_URL . 'app/HelpSettings.js' , array( 'jquery' ) , self::VERSION );
                wp_localize_script( 'wwof_HelpSettings_js',
                                    'WPMessages',
                                    array(
                                        'success_message'   =>  __( 'Wholesale Ordering Page Created Successfully' , 'woocommerce-wholesale-order-form' ),
                                        'failure_message'   =>  __( 'Failed To Create Wholesale Ordering Page' , 'woocommerce-wholesale-order-form' )
                                    )
                                );

            }

        } elseif ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' &&
                   ( ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwof' ) || WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN == 'wwof' ) ) {

            // CSS
            wp_enqueue_style( 'wwof_toastr_css' , WWOF_JS_ROOT_URL . 'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwof_WWSLicenseSettings_css' , WWOF_CSS_ROOT_URL . 'WWSLicenseSettings.css' , array() , self::VERSION , 'all' );

            // JS
            wp_enqueue_script( 'wwof_toastr_js' , WWOF_JS_ROOT_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION );
            wp_enqueue_script( 'wwof_BackEndAjaxServices_js' , WWOF_JS_ROOT_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
            wp_enqueue_script( 'wwof_WWSLicenseSettings_js' , WWOF_JS_ROOT_URL . 'app/WWSLicenseSettings.js' , array( 'jquery' ) , self::VERSION );
            wp_localize_script( 'wwof_WWSLicenseSettings_js',
                            'WPMessages',
                            array(
                                'success_message'   =>  __( 'Wholesale Ordering License Details Successfully Saved' , 'woocommerce-wholesale-order-form' ),
                                'failure_message'   =>  __( 'Failed To Save Wholesale Ordering License Details' , 'woocommerce-wholesale-order-form' )
                            )
                        );

        }

    }

    /**
     * Load Frontend Related Styles and Scripts.
     *
     * @since 1.0.0
     */
    public function loadFrontEndStylesAndScripts(){

        global $post;

        if ( isset( $post->post_content ) && has_shortcode( $post->post_content , 'wwof_product_listing' ) ) {
            // Only load our styles and script files as needed

            // Styles
            wp_enqueue_style( 'wwof_fancybox_css' , WWOF_JS_ROOT_URL . 'lib/fancybox/jquery.fancybox.css' , array() , self::VERSION , 'all' );
            wp_enqueue_style( 'wwof_WholesalePage_css' , WWOF_CSS_ROOT_URL . 'WholesalePage.css' , array() , self::VERSION , 'all' );

            // Scripts
            wp_enqueue_script( 'wwof_fancybox_js' , WWOF_JS_ROOT_URL . 'lib/fancybox/jquery.fancybox.pack.js' , array( 'jquery' ) , self::VERSION );
            wp_enqueue_script( 'wwof_FrontEndAjaxServices_js' , WWOF_JS_ROOT_URL . 'app/modules/FrontEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
            wp_localize_script( 'wwof_FrontEndAjaxServices_js' , 'Ajax' , array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
            wp_enqueue_script( 'wwof_WholesalePage_js' , WWOF_JS_ROOT_URL . 'app/WholesalePage.js' , array( 'jquery' ) , self::VERSION );
            wp_localize_script( 'wwof_WholesalePage_js',
                                'Options',
                                array(
                                    'display_details_on_popup'  =>  get_option( 'wwof_general_display_product_details_on_popup' ),
                                    'no_variation_message'      =>  __( 'No variation selected' , 'woocommerce-wholesale-order-form' )
                                )
                            );

        }

    }

    /**
     * Initialize plugin settings.
     *
     * @param $settings
     *
     * @return array
     * @since 1.0.0
     */
    public function initialPluginWoocommerceSettings( $settings ) {

        $settings[] = include( WWOF_INCLUDES_ROOT_DIR . "class-wwof-settings.php" );

        return $settings;

    }

    /**
     * Check if site user has access to view the wholesale product listing page
     *
     * since 1.0.0
     *
     * @return bool
     */
    public function userHasAccess() {

        return $this->_wwof_permissions->userHasAccess();

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Plugin Integration
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Do modifications on product query with search. Mostly for plugin integrations with plugins that enhances search results.
     *
     * @param $productQuery
     *
     * @since 1.0.0
     */
    public function productQueryWithSearch( $productQuery ) {

        $this->_wwof_product_listings->productQueryWithSearch( $productQuery );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | AJAX Handlers
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Register all AJAX interface handlers.
     *
     * since 1.0.0
     */
    public function registerAJAXCAllHandlers() {
        // wp_ajax_         = for admin
        // wp_ajax_nopriv   = for unauthenticated users
        // You have to load your ajax call back on both if you want it to be available on both

        // Admin only AJAX Interfaces
        add_action( "wp_ajax_wwof_createWholesalePage" , array( self::getInstance() , 'wwof_createWholesalePage' ) );
        add_action( "wp_ajax_wwof_saveLicenseDetails" , array( $this->_wwof_wws_license_settings , 'wwof_saveLicenseDetails' ) );

        // General AJAX Interfaces
        add_action( "wp_ajax_wwof_displayProductListing" , array( self::getInstance() , 'wwof_displayProductListing' ) );
        add_action( "wp_ajax_wwof_getProductDetails" , array( self::getInstance() , 'wwof_getProductDetails' ) );
        add_action( "wp_ajax_wwof_addProductToCart" , array( self::getInstance() , 'wwof_addProductToCart' ) );
        add_action( "wp_ajax_wwof_addProductsToCart" , array( self::getInstance() , 'wwof_addProductsToCart' ) );
        add_action( "wp_ajax_nopriv_wwof_displayProductListing" , array( self::getInstance() , 'wwof_displayProductListing' ) );
        add_action( "wp_ajax_nopriv_wwof_getProductDetails" , array( self::getInstance() , 'wwof_getProductDetails' ) );
        add_action( "wp_ajax_nopriv_wwof_addProductToCart" , array( self::getInstance() , 'wwof_addProductToCart' ) );
        add_action( "wp_ajax_nopriv_wwof_addProductsToCart" , array( self::getInstance() , 'wwof_addProductsToCart' ) );
    }

    /**
     * Display product listing ajax handler.
     *
     * @param int  $paged
     * @param null $search
     * @param null $catFilter
     * @param bool $ajaxCall
     *
     * @return mixed
     * @since 1.0.0
     */
    public function wwof_displayProductListing( $paged = 1 , $search = null , $catFilter = null , $ajaxCall = true ) {

        return $this->_wwof_product_listings->displayProductListing( $paged , $search , $catFilter , $ajaxCall , $this->userHasAccess() );

    }

    /**
     * This is an ajax interface to load product details of a product. Used by fancy box, Requested via GET.
     *
     * @since 1.0.0
     *
     * @param null $productID Product ID
     * @param bool $ajaxCall Is it an ajax call?
     */
    public function wwof_getProductDetails( $productID = null , $ajaxCall = true ) {

        return $this->_wwof_product_listings->getProductDetails( $productID , $ajaxCall );

    }

    /**
     * Add to cart ajax handler.
     *
     * @since 1.0.0
     *
     * @param null $productType Product type (simple, variable)
     * @param null $productID Product ID
     * @param null $variationID Variation ID (if variable product)
     * @param null $quantity Quantity
     * @param bool $ajaxCall Is it an ajax call?
     *
     * @return bool
     */
    public function wwof_addProductToCart( $productType = null , $productID = null , $variationID = null , $quantity = null , $ajaxCall = true ) {

        return $this->_wwof_product_listings->addProducttoCart( $productType , $productID , $variationID , $quantity , $ajaxCall );

    }

    /**
     * Add products to cart ajax handler.
     *
     * @param null $products
     * @param bool $ajaxCall
     * @return mixed
     *
     * @since 1.1.0
     */
    public function wwof_addProductsToCart( $products = null , $ajaxCall = true ) {

        return $this->_wwof_product_listings->addProductsToCart ( $products , $ajaxCall );

    }

    /**
     * Create wholesale page
     *
     * @since 1.0.0
     *
     * @param null $dummyArg Just dummy argument coz wp ajax always pass something as a first argument, dummy arg is used to catch it
     * @param bool $ajaxCall
     *
     * @return bool
     */
    public function wwof_createWholesalePage( $dummyArg = null , $ajaxCall = true ) {

        return $this->_wwof_product_listings->createWholesalePage( $dummyArg , $ajaxCall );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Short Codes Callbacks
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Shortcode to display product listing.
     *
     * @since 1.0.0
     *
     * @return string Product Listing HTML
     */
    public function sc_productListing() {

        return $this->_wwof_product_listings->sc_productListing();

    }

    /**
     * Add classes to body tag for the current page or post where the shortcode above is added.
     *
     * @since 1.0.0
     *
     * @param $classes Array of classes containing existing class to add to body tag
     *
     * @return array Array of classes to add to body tag
     */
    public function sc_bodyClasses( $classes ) {

        return $this->_wwof_product_listings->sc_bodyClasses( $classes );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Product Listing Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Product listing filter area.
     *
     * @param $search_placeholder_text
     * @since 1.0.0
     */
    public function getProductListingFilter( $search_placeholder_text ) {

        $this->_wwof_product_listings->getProductListingFilter( $search_placeholder_text );

    }

    /**
     * Return an array of variation products of a variable product or just return a simple product inside an array. Currently not used but might be useful later on.
     *
     * @since 1.0.0
     *
     * @param $product Woocommerce product object
     *
     * @return array Returns an array containing Woocommerce product object
     */
    private function getProducts( $product ) {

        return $this->_wwof_product_listings->getProducts( $product );

    }

    /**
     * Get product meta of a product listing item.
     *
     * @since 1.0.0
     *
     * @param $product Woocommerce product object
     *
     * @return string Product meta markup
     */
    public function getProductMeta( $product ) {

        return $this->_wwof_product_listings->getProductMeta( $product );

    }

    /**
     * Get product title of a product listing item.
     *
     * @param $product
     * @param $permalink
     *
     * @return mixed
     * @since 1.0.0
     */
    public function getProductTitle( $product , $permalink ) {

        return $this->_wwof_product_listings->getProductTitle( $product , $permalink );

    }

    /**
     * Get product variation field for variable products.
     *
     * @since 1.0.0
     *
     * @param $product Woocommerce product object
     *
     * @return string Variation field markup
     */
    public function getProductVariationField( $product ) {

        return $this->_wwof_product_listings->getProductVariationField( $product );

    }

    /**
     * Get product thumbnail (with link).
     *
     * @param $product Woocommerce product object
     * @param $permalink Product permalink
     * @param $imageSize Image size (W X H)
     *
     * @return string Product image mark up
     */
    public function getProductImage( $product , $permalink , $imageSize ) {

        return $this->_wwof_product_listings->getProductImage( $product , $permalink , $imageSize );

    }

    /**
     * Get product link (either link to pop up or product page).
     *
     * @since 1.0.0
     *
     * @param $productID Product ID
     * @param $productLink Product Permalink
     *
     * @return string Product permalink (either pop up link or product link)
     */
    public function getProductLink( $productID , $productLink ) {

        return $this->_wwof_product_listings->getProductLink( $productID , $productLink );

    }

    /**
     * Return product sku visibility classes.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getProductSkuVisibilityClass() {

        return $this->_wwof_product_listings->getProductSkuVisibilityClass();

    }

    /**
     * Return product stock quantity visibility class.
     *
     * @return mixed
     *
     * @since 1.2.0
     */
    public function getProductStockQuantityVisibilityClass() {

        return $this->_wwof_product_listings->getProductStockQuantityVisibilityClass();

    }

    /**
     * Get product sku
     *
     * @since 1.0.0
     *
     * @param $product Woocommerce product object
     *
     * @return string Product sku markup
     */
    public function getProductSku( $product ) {

        return $this->_wwof_product_listings->getProductSku( $product );

    }

    /**
     * Get product price.
     *
     * @since 1.0.0
     *
     * @param $product Woocommerce product object
     *
     * @return string Product price markup
     */
    public function getProductPrice( $product ) {

        return $this->_wwof_product_listings->getProductPrice( $product );

    }

    /**
     * Get product stock quantity.
     *
     * @param $product
     * @return mixed
     *
     * @since 1.2.0
     */
    public function getProductStockQuantity( $product ) {

        return $this->_wwof_product_listings->getProductStockQuantity( $product );

    }

    /**
     * Get product quantity field.
     *
     * @since 1.0.0
     *
     * @param $product Woocommerce product object
     *
     * @return string Product quantity field markup
     */
    public function getProductQuantityField( $product ) {

        return $this->_wwof_product_listings->getProductQuantityField( $product );

    }

    /**
     * Get product listing action fields.
     *
     * @since 1.0.0
     *
     * @param $product Woocommerce product object
     * @param $alternate
     *
     * @return string Product row action fields markup
     */
    public function getProductRowActionFields( $product , $alternate = false ) {

        return $this->_wwof_product_listings->getProductRowActionFields( $product , $alternate );

    }

    /**
     * Get product listing pagination.
     *
     * @since 1.0.0
     *
     * @param $paged Current product listing page
     * @param $max_num_pages Maximum page
     * @param $search Search key
     * @param $cat_filter
     *
     * @return mixed Pagination Links
     */
    public function getGalleryListingPagination( $paged , $max_num_pages , $search , $cat_filter ) {

        return $this->_wwof_product_listings->getGalleryListingPagination( $paged , $max_num_pages , $search , $cat_filter );

    }

    /**
     * Get cart url.
     *
     * @return mixed
     *
     * @since 1.1.0
     */
    public function getCartUrl() {

        return $this->_wwof_product_listings->getCartUrl();

    }

    /**
     * Get cart sub total (including/excluding) tax.
     *
     * @return mixed
     *
     * @since 1.2.0
     */
    public function getCartSubtotal() {

        return $this->_wwof_product_listings->getCartSubtotal();

    }

    /**
     * Check if in wwof license settings page.
     *
     * @return bool
     *
     * @since 1.1.2
     */
    public function checkIfInWWOFSettingsPage() {

        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwof' )
            return true;
        else
            return false;

    }

}