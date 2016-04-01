<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WWOF_Product_Listing {

    private static $_instance;

    /**
     * Singleton Pattern.
     *
     * @since 1.0.0
     *
     * @return WooCommerce_WholeSale_Order_Form
     */
    public static function getInstance() {

        if( !self::$_instance instanceof self )
            self::$_instance = new self;

        return self::$_instance;

    }

    /**
     * Validate and tidy up the category filter set on the WWOF settings area.
     * Mainly check if a category in the filter still exist, if not, remove that category in the filter and
     * update the filter accordingly.
     *
     * @since 1.3.1
     *
     * @param $catFilter
     * @return mixed
     */
    private function _categoryFilterValidator($catFilter ) {

        if ( is_array( $catFilter ) ) {

            $arrIndexToRemove = array();

            foreach ( $catFilter as $idx => $slug ) {

                if ( !get_term_by( 'slug' , $slug , 'product_cat' ) )
                    $arrIndexToRemove[] = $idx;

            }

            foreach ( $arrIndexToRemove as $index )
                unset( $catFilter[ $index ] );

            if ( !empty( $arrIndexToRemove ) )
                update_option( 'wwof_filters_product_category_filter' , $catFilter );

        }

        return $catFilter;

    }

    /**
     * Display Product Listing.
     *
     * @since 1.0.0
     * @since 1.3.0 Add capability to sort by sku
     * @since 1.3.1 Add product category validation
     *
     * @param int  $paged
     * @param null $search
     * @param null $searchCatFilter
     * @param bool $ajaxCall
     * @param      $userHasAccess
     *
     * @return string
     */
    public function displayProductListing($paged = 1, $search = null, $searchCatFilter = null, $ajaxCall = true, $userHasAccess ) {

        ob_start();

        if ( $userHasAccess ) {
            // User has permission

            if ( $ajaxCall === true ) {

                $paged = trim( $_POST[ 'paged' ] );
                $search = trim( $_POST[ 'search' ] );
                $searchCatFilter = trim( $_POST[ 'catFilter' ] );

            }

            if ( empty( $paged ) || is_null( $paged ) || !is_numeric( $paged ) )
                $paged = 1;

            if ( empty( $search ) || $search == "" )
                $search = null;

            global $WWOF_SETTINGS_DEFAULT_PPP, $WWOF_SETTINGS_DEFAULT_SORT_BY, $WWOF_SETTINGS_DEFAULT_SORT_ORDER;

            $posts_per_page      = get_option( 'wwof_general_products_per_page' );
            $show_zero_prod      = get_option( 'wwof_general_display_zero_products' );
            $settings_cat_filter = get_option( 'wwof_filters_product_category_filter' ); // Category Filter on the settings area, not on the search area
            $prod_filter         = get_option( 'wwof_filters_exclude_product_filter' );
            $sort_by             = get_option( 'wwof_general_sort_by' );
            $sort_order          = get_option( 'wwof_general_sort_order' );
            $search_sku          = get_option( 'wwof_general_allow_product_sku_search' );

            if ( !isset( $posts_per_page ) || $posts_per_page === false || strcasecmp( trim( $posts_per_page ) , '' ) == 0 )
                $posts_per_page = $WWOF_SETTINGS_DEFAULT_PPP;

            // Show all products if disable pagination is enabled
            if ( get_option( 'wwof_general_disable_pagination' ) == 'yes' )
                $posts_per_page = -1;

            if( !isset( $sort_by ) || $sort_by === false || strcasecmp( trim( $sort_by ) , '' ) == 0 )
                $sort_by = $WWOF_SETTINGS_DEFAULT_SORT_BY;

            if( !isset( $sort_order ) || $sort_order === false || strcasecmp( trim( $sort_order ) , '' ) == 0 )
                $sort_order = $WWOF_SETTINGS_DEFAULT_SORT_ORDER;


            // =========================================================================================================
            // Begin Construct Main Query Args
            // =========================================================================================================

            // Core args -----------------------------------------------------------------------------------------------
            $args = array(
                            'post_type'           => 'product',
                            'post_status'         => 'publish',
                            'posts_per_page'      => $posts_per_page,
                            'ignore_sticky_posts' => 1,
                            'meta_query'          => array(
                                array(
                                    'key'     => '_visibility',
                                    'value'   => 'visible',
                                    'compare' => '=',
                                    'type'    => 'string'
                                ),
                                array(
                                    'key'     => '_price',
                                    'value'   => array(''),
                                    'compare' => 'NOT IN'
                                )
                            )
                        );

            // Sort related args ---------------------------------------------------------------------------------------
            switch( $sort_by ){
                case 'default':
                    break;
                case 'menu_order':
                    $args[ 'order' ] = $sort_order;
                    $args[ 'orderby' ]  = 'menu_order title';
                    break;
                case 'name':
                    $args[ 'order' ] = $sort_order;
                    $args[ 'orderby' ] = 'title';
                    break;
                case 'date':
                    $args[ 'order' ] = $sort_order;
                    $args[ 'orderby' ] = 'date';
                    break;
                case 'sku':
                    $args[ 'order' ]    = $sort_order;
                    $args[ 'orderby' ]  = 'meta_value';
                    $args[ 'meta_key' ] = '_sku';
                case 'price':
                    //TODO: enhance price logic
                    //$args['order'] = $sort_order;
                    //$args['orderby'] = "meta_value_num";
                    //$args['meta_key'] = '_price';
                    //$args['meta_query'][] = array(
                    //    'key'   =>  '_price'
                    //);
                    break;
                case 'popularity':
                    // TODO:
                    break;
                case 'rating':
                    // TODO:
                    break;
            }

            // Paged related args --------------------------------------------------------------------------------------
            if ( $paged > 0 )
                $args[ 'paged' ] = $paged;

            // Category filter related args ----------------------------------------------------------------------------

            // Validate product category filter
            $settings_cat_filter = $this->_categoryFilterValidator( $settings_cat_filter );

            if ( is_array( $settings_cat_filter ) && !empty( $settings_cat_filter ) && $searchCatFilter == '' ) {

                $args[ 'tax_query' ] = array(
                    array(
                        'taxonomy'  =>  'product_cat',
                        'field'     =>  'slug',
                        'terms'     =>  $settings_cat_filter
                    )
                );

            } elseif( $searchCatFilter != '' ) {

                $args[ 'tax_query' ] = array(
                    array(
                        'taxonomy'  =>  'product_cat',
                        'field'     =>  'slug',
                        'terms'     =>  $searchCatFilter
                    )
                );

            }

            // Product exclusion related args --------------------------------------------------------------------------
            if ( is_array( $prod_filter ) && !empty( $prod_filter ) )
                $args[ 'post__not_in' ] = $prod_filter;

            // =========================================================================================================
            // End Construct Main Query Args
            // =========================================================================================================


            // Instock Products
            $in_stock_products = array();
            if ( $show_zero_prod != 'yes' )
                $in_stock_products = WWOF_Product_Listing_Helper::get_all_instock_products();

            // Product Search
            $search_products = array();
            if ( !is_null( $search ) ) {

                $search_the_sku = ( $search_sku == 'yes' ) ? true : false;
                $search_products = WWOF_Product_Listing_Helper::get_search_products( $search , $search_the_sku );

            }

            // Post in
            $post_in = array();

            if ( !empty( $search_products ) && !empty( $in_stock_products ) )
                $post_in = array_unique( array_intersect( $search_products , $in_stock_products ) );
            elseif ( !empty( $search_products ) && empty( $in_stock_products ) )
                $post_in = $search_products;
            elseif ( empty( $search_products ) && !empty( $in_stock_products ) )
                $post_in = $in_stock_products;

            // We need to check if post_in is empty, and there are some explicit filters
            // 1. if do not show zero inventory products
            // 2. if there is a search
            // if we put empty array in post_in on wp query, it will return all posts
            // that's why we need to add an array with value of zero ( no post has id of zero ) so post_in fails, which is what we want
            // coz meaning either or both 1. and 2. is not meet.
            if ( empty( $post_in ) && ( $show_zero_prod != 'yes' || !is_null( $search ) ) )
                $post_in = array( 0 );

            // Execute Main Query ======================================================================================
            if ( !empty( $post_in ) ) {

                if ( is_array( $prod_filter ) && !empty( $prod_filter ) )
                    $post_in = array_diff( $post_in , $prod_filter );

                $args[ 'post__in' ] = $post_in;

            }

            $args = apply_filters( 'wwof_filter_product_listing_query_arg' , $args );

            $product_loop = new WP_Query( $args );
            $product_loop = apply_filters( 'wwof_filter_product_listing_query' , $product_loop );

            do_action( 'wwof_action_before_product_listing' );

            if ( get_option( 'wwof_general_use_alternate_view_of_wholesale_page' ) == 'yes' )
                $tpl = 'wwof-product-listing-alternate.php';
            else
                $tpl = 'wwof-product-listing.php';

            // Load product listing template
            $this->_loadTemplate(
                $tpl,
                array(
                    'product_loop'  =>  $product_loop,
                    'paged'         =>  $paged,
                    'search'        =>  $search,
                    'cat_filter'    =>  $searchCatFilter
                ),
                WWOF_PLUGIN_DIR.'templates/'
            );

            wp_reset_postdata();

        } else {

            // User don't have permission
            $title = trim( stripslashes( strip_tags( get_option( 'wwof_permissions_noaccess_title' ) ) ) );
            $message = trim( stripslashes( get_option( 'wwof_permissions_noaccess_message' ) ) );
            $loginUrl = trim( get_option( 'wwof_permissions_noaccess_login_url' ) );

            if ( empty( $title ) )
                $title = __( 'Access Denied' , 'woocommerce-wholesale-order-form' );

            if ( empty( $message ) )
                $message = __( "You do not have permission to view wholesale product listing" , 'woocommerce-wholesale-order-form' );

            if ( empty( $loginUrl ) )
                $loginUrl = wp_login_url();

            ?>
            <div id="wwof_access_denied">
                <h2 class="content-title"><?php echo $title; ?></h2>
                <?php echo html_entity_decode( $message ); ?>
                <p class="login-link-container"><a class="login-link" href="<?php echo $loginUrl; ?>"><?php _e( 'Login Here' , 'woocommerce-wholesale-order-form' ); ?></a></p>
            </div>
            <?php

        }

        if( $ajaxCall === true ) {

            // To return the buffered output
            echo ob_get_clean();
            die();

        } else
            return ob_get_clean();

    }

    /**
     * Get single product details.
     *
     * @param null $productID
     * @param bool $ajaxCall
     *
     * @return string
     * @since 1.0.0
     */
    public function getProductDetails( $productID = null, $ajaxCall = true ) {

        if( $ajaxCall === true )
            $productID = $_GET[ 'productID' ];

        $product = wc_get_product( $productID );

        if ( $product === false ) {

            $noProductDetailsMsg = apply_filters( 'wwof_filter_no_product_details_message' , '<em class="no-product-details">' . __( 'No Product Details Available' , 'woocommerce-wholesale-order-form' ) . '</em>' );

            if ( $ajaxCall === true ) {

                echo $noProductDetailsMsg;
                die();

            } else
                return $noProductDetailsMsg;

        }

        ob_start();

        $this->_loadTemplate(
                    'wwof-product-details.php',
                    array(
                        'product'   =>  $product
                    ),
                    WWOF_PLUGIN_DIR . 'templates/'
                );

        if ( $ajaxCall === true ) {

            echo ob_get_clean();
            die();

        } else
            return ob_get_clean();

    }

    /**
     * Add product to cart.
     *
     * @param null $productType
     * @param null $productID
     * @param null $variationID
     * @param null $quantity
     * @param bool $ajaxCall
     *
     * @return bool
     * @since 1.0.0
     */
    public function addProductToCart ( $productType = null , $productID = null , $variationID = null , $quantity = null , $ajaxCall = true ) {

        if( $ajaxCall === true ) {

            $productID      =   $_POST[ 'productID' ];
            $variationID    =   $_POST[ 'variationID' ];
            $quantity       =   $_POST[ 'quantity' ];
            $productType    =   $_POST[ 'productType' ];

        }

        if( ( empty( $variationID ) || !is_numeric( $variationID ) || $variationID <= 0 ) && strcasecmp( $productType , 'variable' ) == 0 ) {

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( array(
                    'status'        =>  'failed',
                    'error_message' =>  __( 'Trying to add a variable product with no variation provided' , 'woocommerce-wholesale-order-form' )
                ) );
                die();

            } else
                return false;

        }

        if ( !empty( $variationID ) && is_numeric( $variationID ) && $variationID > 0 ) {

            $variation = wc_get_product( $variationID );
            $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation' , true , $productID , $quantity , $variationID , $variation->get_variation_attributes() );

            if ( $passed_validation ) {

                $cart_key = WC()->cart->add_to_cart( $productID , $quantity , $variationID , $variation->get_variation_attributes() );

            } else {

                if ( $ajaxCall === true ) {

                    header( 'Content-Type: application/json' ); // specify we return json
                    echo json_encode( array(
                        'status'        =>  'failed',
                        'error_message' =>  __( 'Failed add to cart validation' , 'woocommerce-wholesale-order-form' )
                    ) );
                    die();

                } else
                    return false;

            }

        } else {

            $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation' , true , $productID , $quantity );

            if ( $passed_validation ) {

                $cart_key = WC()->cart->add_to_cart( $productID , $quantity );

            } else {

                if ( $ajaxCall === true ) {

                    header('Content-Type: application/json'); // specify we return json
                    echo json_encode( array(
                        'status'        =>  'failed',
                        'error_message' =>  __( 'Failed add to cart validation' , 'woocommerce-wholesale-order-form' )
                    ) );
                    die();

                } else
                    return false;

            }

        }

        do_action( 'woocommerce_ajax_added_to_cart' , $productID );

        // Set cart woocommerce and cart cookies. Bug Fix : WWOF-16
        $this->maybe_set_cart_cookies();

        if ( $ajaxCall === true ) {

            // Get mini cart
            ob_start();

            woocommerce_mini_cart();

            $mini_cart = ob_get_clean();

            header('Content-Type: application/json'); // specify we return json
            echo json_encode( array(
                'status'                =>  'success',
                'cart_subtotal_markup'  =>  $this->getCartSubtotal(),
                'cart_key'              =>  $cart_key,
                'fragments'             =>  apply_filters( 'woocommerce_add_to_cart_fragments' , array( 'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>' ) ),
                'cart_hash'             =>  apply_filters( 'woocommerce_add_to_cart_hash' , WC()->cart->get_cart() ? md5( json_encode( WC()->cart->get_cart() ) ) : '' , WC()->cart->get_cart() )
            ) );
            die();

        } else
            return true;

    }

    /**
     * Add products to cart.
     *
     * @param null $products
     * @param bool $ajaxCall
     * @return bool
     *
     * @since 1.1.0
     */
    public function addProductsToCart ( $products = null , $ajaxCall = true ) {

        if( $ajaxCall === true )
            $products = $_POST[ 'products' ];

        $cart_keys = array();
        $successfully_added = array();
        $failed_to_add = array();
        $total_added = 0;
        $total_failed = 0;

        foreach ( $products as $product ) {

            if ( ( empty( $product[ 'variationID' ] ) || !is_numeric( $product[ 'variationID' ] ) || $product[ 'variationID' ] <= 0 ) && strcasecmp( $product[ 'productType' ] , 'variable' ) == 0 ) {

                $failed_to_add[] = array(
                                        'product_id'    =>  $product[ 'productID' ],
                                        'error_message' =>  __( 'Contains invalid variation id. Either empty, not numeric or less than zero' , 'woocommerce-wholesale-order-form' ),
                                        'quantity'      =>  $product[ 'quantity' ]
                                    );
                $total_failed += $product[ 'quantity' ];
                continue;

            }

            if ( !empty( $product[ 'variationID' ] ) && is_numeric( $product[ 'variationID' ] ) && $product[ 'variationID' ] > 0 ) {

                $variation = wc_get_product( $product[ 'variationID' ] );
                $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation' , true , $product[ 'productID' ] , $product[ 'quantity' ] , $product[ 'variationID' ] , $variation->get_variation_attributes() );

                if ( $passed_validation ) {

                    $cart_keys[] = WC()->cart->add_to_cart( $product[ 'productID' ] , $product[ 'quantity' ] , $product[ 'variationID' ] , $variation->get_variation_attributes() );
                    $successfully_added[ $product[ 'variationID' ] ] = $product[ 'quantity' ];
                    $total_added += $product[ 'quantity' ];
                    do_action( 'woocommerce_ajax_added_to_cart' , $product[ 'productID' ] );

                } else {

                    $failed_to_add[] = array(
                        'product_id'    =>  $product[ 'productID' ],
                        'error_message' =>  __( 'Failed add to cart validation' , 'woocommerce-wholesale-order-form' ),
                        'quantity'      =>  $product[ 'quantity' ]
                    );
                    $total_failed += $product[ 'quantity' ];
                    continue;

                }

            } else {

                $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product[ 'productID' ], $product[ 'quantity' ] );

                if ( $passed_validation ) {

                    $cart_keys[] = WC()->cart->add_to_cart( $product[ 'productID' ] , $product[ 'quantity' ] );
                    $successfully_added[ $product[ 'productID' ] ] = $product[ 'quantity' ];
                    $total_added += $product[ 'quantity' ];
                    do_action( 'woocommerce_ajax_added_to_cart', $product[ 'productID' ] );

                } else {

                    $failed_to_add[] = array(
                        'product_id'    =>  $product[ 'productID' ],
                        'error_message' =>  __( 'Failed add to cart validation' , 'woocommerce-wholesale-order-form' ),
                        'quantity'      =>  $product[ 'quantity' ]
                    );
                    $total_failed += $product[ 'quantity' ];
                    continue;

                }

            }

        }

        // Set cart woocommerce and cart cookies. Bug Fix : WWOF-16
        $this->maybe_set_cart_cookies();

        if( $ajaxCall === true ){

            // Get mini cart
            ob_start();

            woocommerce_mini_cart();

            $mini_cart = ob_get_clean();

            header( 'Content-Type: application/json' ); // specify we return json
            echo json_encode( array(
                'status'                =>  'success',
                'cart_subtotal_markup'  =>  $this->getCartSubtotal(),
                'cart_keys'             =>  $cart_keys,
                'successfully_added'    =>  $successfully_added,
                'total_added'           =>  $total_added,
                'failed_to_add'         =>  $failed_to_add,
                'total_failed'          =>  $total_failed,
                'fragments'             =>  apply_filters( 'woocommerce_add_to_cart_fragments' , array( 'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>' ) ),
                'cart_hash'             =>  apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart() ? md5( json_encode( WC()->cart->get_cart() ) ) : '', WC()->cart->get_cart() )
            ) );
            die();

        } else
            return true;

    }

    /**
     * Set cart cookies.
     * Bug Fix : WWOF-16
     *
     * @since 1.2.2
     */
    public function maybe_set_cart_cookies () {

        if ( sizeof( WC()->cart->cart_contents ) > 0 )
            $this->_set_cart_cookies( true );
        elseif ( isset( $_COOKIE[ 'woocommerce_items_in_cart' ] ) )
            $this->_set_cart_cookies( false );

    }

    /**
     * Set cart cookies.
     * Bug Fix : WWOF-16
     *
     * @param bool $set
     *
     * @since 1.2.2
     */
    private function _set_cart_cookies( $set = true ) {

        if ( $set ) {

            wc_setcookie( 'woocommerce_items_in_cart', 1 );
            wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( WC()->cart->get_cart_for_session() ) ) );

        } elseif ( isset( $_COOKIE[ 'woocommerce_items_in_cart' ] ) ) {

            wc_setcookie( 'woocommerce_items_in_cart', 0, time() - ( 60 * 60 ) ); // (60 * 60 ) Hours in seconds
            wc_setcookie( 'woocommerce_cart_hash', '', time() - ( 60 * 60 ) );

        }

        do_action( 'woocommerce_set_cart_cookies', $set );

    }

    /**
     * Create wholesale page.
     *
     * @param null $dummyArg
     * @param bool $ajaxCall
     *
     * @return bool
     * @since 1.0.0
     */
    public function createWholesalePage ( $dummyArg = null, $ajaxCall = true ) {

        if ( get_post_status( get_option( WWOF_SETTINGS_WHOLESALE_PAGE_ID ) ) !== 'publish' && !get_page_by_title( 'Wholesale Page' ) ) {

            $wholesale_page = array(
                                    'post_content'  =>  '[wwof_product_listing]',// The full text of the post.
                                    'post_title'    =>  __( 'Wholesale Ordering' , 'woocommerce-wholesale-order-form' ),// The title of your post.
                                    'post_status'   =>  'publish',
                                    'post_type'     =>  'page'
                                );

            $result = wp_insert_post( $wholesale_page );

            if ( $result === 0 || is_wp_error( $result ) ) {

                if ( $ajaxCall === true ) {

                    header('Content-Type: application/json'); // specify we return json
                    echo json_encode(array(
                        'status'        =>  'failed',
                        'error_message' =>  __( 'Failed to create wholesale ordering page.' , 'woocommerce-wholesale-order-form' )
                    ));
                    die();

                } else
                    return false;

            } else {

                // Update wholesale page id setting
                update_option( WWOF_SETTINGS_WHOLESALE_PAGE_ID , $result );

                if ( $ajaxCall === true ) {

                    header( 'Content-Type: application/json' ); // specify we return json
                    echo json_encode( array( 'status' => 'success' ) );
                    die();

                } else
                    return true;

            }

        } else {

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( array( 'status' => 'success' ) );
                die();

            } else
                return true;

        }

    }

    /**
     * Do modifications on product query with search. Mostly for plugin integrations with plugins that enhances search results.
     *
     * @param $productQuery
     *
     * @since 1.0.0
     */
    public function productQueryWithSearch( $productQuery ) {

        // If relevanssi is installed and active
        if ( in_array( 'relevanssi/relevanssi.php' , apply_filters( 'active_plugins' , get_option( 'active_plugins' ) ) ) ) {

            relevanssi_do_query( $productQuery );

        }

    }


    /*
     |------------------------------------------------------------------------------------------------------------------
     | Product Listing Data Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Sort a taxonomy term in hierarchy. Recursive function.
     *
     * Credit:
     * http://wordpress.stackexchange.com/questions/14652/how-to-show-a-hierarchical-terms-list#answer-99516
     *
     * @since 1.3.0
     *
     * @param array $cats
     * @param array $into
     * @param int   $parentId
     */
    function sortTermsHierarchicaly( Array &$cats , Array &$into , $parentId = 0 )
    {

        foreach ( $cats as $i => $cat ) {
            if ( $cat->parent == $parentId ) {
                $into[ $cat->term_id ] = $cat;
                unset( $cats[ $i ] );
            }
        }

        foreach ( $into as $topCat ) {
            $topCat->children = array();
            $this->sortTermsHierarchicaly( $cats , $topCat->children , $topCat->term_id );
        }

    }

    /**
     * Build product category options markup. For use inside a select tag. Recursive function.
     *
     * @since 1.3.0
     *
     * @param     $cats
     * @param     $cats_markup
     * @param int $indent
     */
    public function buildProductCategoryOptionsMarkup($cats , &$cats_markup , $indent = 0 ) {

        $indent_str = '';
        $indent_ctr = $indent;

        while ( $indent_ctr > 0 ) {

            $indent_str .= "&nbsp;&nbsp;&nbsp;";
            $indent_ctr--;

        }

        foreach ( $cats as $cat ) {

            $cats_markup .= '<option value="' . $cat->slug . '">' . $indent_str . $cat->name . '</option>';

            if ( !empty( $cat->children ) )
                $this->buildProductCategoryOptionsMarkup( $cat->children , $cats_markup , ( $indent + 1 ) );

        }

    }

    /**
     * Get product listing filter section.
     *
     * @since 1.0.0
     * @since 1.3.0 Add hierarchy to the list of categories inside the categories filter select markup.
     * @since 1.3.2 Bug Fix. WWOF-70.
     *
     * @param $search_placeholder_text
     */
    public function getProductListingFilter( $search_placeholder_text ) {

        // Get product categories appropriately
        $include = array();
        $catFilter = get_option( 'wwof_filters_product_category_filter' );
        if ( is_array( $catFilter ) && !empty( $catFilter ) ) {

            foreach ( $catFilter as $catSlug ) {

                $currTerm = get_term_by( 'slug' , $catSlug , 'product_cat' );

                if ( $currTerm )
                    $include[] = (int) $currTerm->term_id;

            }

        }

        $termArgs = array( 'hide_empty' => false );

        if ( !empty( $include ) )
            $termArgs[ 'include' ] = $include;

        // Get all product cats (Object)
        $productTermsObject = get_terms( 'product_cat' , $termArgs );

        // Backwards Compatibility with versions prior to 1.3.0
        $productTerms = array();

        foreach( $productTermsObject as $term ) {
            $productTerms[ $term->slug ] = $term->name;
        }

        // Set product cats in hierarchy
        $productTermsHierarchy = array();
        $this->sortTermsHierarchicaly( $productTermsObject , $productTermsHierarchy );

        /*
         * It will not be empty if there are child categories that has no parent category
         * Usually occurs if user only selected few categories on the wwof settings.
         * If this happends, those child categories will not be included on $productTermsHierarchy
         * we need to merge it there.
         * */
        if ( !empty( $productTermsObject ) ) {

            $productTermsHierarchy = array_merge( $productTermsHierarchy , $productTermsObject );
            $productTermsObject = array();

        }

        // Sort the product terms hierarchy
        usort( $productTermsHierarchy , array( $this , 'productTermsHierarchyUSortCallback' ) );

        // Build product cats options markup
        $productTermsOptionMarkup = '';
        $this->buildProductCategoryOptionsMarkup( $productTermsHierarchy , $productTermsOptionMarkup );

        $this->_loadTemplate(
            'wwof-product-listing-filter.php',
            array(
                'search_placeholder_text'  => apply_filters( 'wwof_filter_search_placeholder_text' , $search_placeholder_text ),
                'product_category_options' => $productTermsOptionMarkup,
                'product_terms'            => $productTerms // Backwards compatibility with versions prior to 1.3.0
            ),
            WWOF_PLUGIN_DIR . 'templates/'
        );

    }

    /**
     * Custom sort call back for sorting product terms hierarchy. It sorts by slug.
     *
     * @since 1.3.2
     *
     * @param $a
     * @param $b
     * @return int
     */
    public function productTermsHierarchyUSortCallback( $a , $b ) {

        if ( $a->slug == $b->slug )
            return 0;

        return ( $a->slug < $b->slug ) ? -1 : 1;

    }

    /**
     * Reserved, got some ideas inside the code we might use in the future. Currently not used.
     *
     * @param $product
     *
     * @return array
     * @since 1.0.0
     */
    private function getProducts( $product ) {

        if ( $product->product_type == 'variable' ) {

            $variation_query = new WP_Query();
            $args_variation = array(
                                    'post_status' => 'publish',
                                    'post_type'   => 'product_variation',
                                    'post_parent' => $product->ID
                                );
            $variation_query->query( $args_variation );
            $products = array();

            foreach( $variation_query->posts as $variation ) {
                $products[] = wc_get_product( $variation->ID );
            }

            return $products;

        } else
            return array( $product );

    }

    /**
     * Get product meta.
     * @param $product
     *
     * @return mixed
     * @since 1.0.0
     */
    public function getProductMeta ( $product ) {

        $product_meta = '<span class="product_type" style="display: none !important;">' . $product->product_type . '</span>';
        $product_meta .= '<span class="main_product_id" style="display: none !important;">' . $product->id . '</span>';

        return apply_filters( 'wwof_filter_product_meta' , $product_meta );

    }

    /**
     * Get product title.
     *
     * @param $product
     * @param $permalink
     *
     * @return string
     * @since 1.0.0
     */
    public function getProductTitle( $product , $permalink ) {

        $main_product_title = '<a class="product_link" href="' . $this->getProductLink( $product->id , $permalink ) . '">' . $product->get_title() . '</a>';
        $main_product_title = apply_filters( 'wwof_filter_product_title' , $main_product_title );

        return $main_product_title;

    }

    /**
     * Get product variation field.
     *
     * Version 1.3.2 change set:
     * We determine if a variation is active or not is by also checking the inventory status of the parent variable
     * product.
     *
     * @param $product
     *
     * @since 1.0.0
     * @since 1.3.2
     *
     * @return string
     */
    public function getProductVariationField ( $product ) {

        if ( $product->product_type == 'variable' ) {

            $product_variations = $product->get_available_variations();
            $product_attributes = $product->get_attributes();
            $variation_arr = array();

            $variation_select_box = '<label class="product_variations_label">' . __( 'Variations:' , 'woocommerce-wholesale-order-form' ) . '</label>';
            $variation_select_box .= '<select class="product_variations">';

            foreach ( $product_variations as $variation ) {

                $variation_obj = wc_get_product( $variation[ 'variation_id' ] );
                $variation_attributes = $variation_obj->get_variation_attributes();
                $friendly_variation_text = null;

                foreach ( $variation_attributes as $variation_name => $variation_val ) {

                    foreach ( $product_attributes as $attribute_key => $attribute_arr ) {

                        if ( $variation_name != 'attribute_' . sanitize_title( $attribute_arr[ 'name' ] ) )
                            continue;

                        $attr_found = false;

                        if ( $attribute_arr[ 'is_taxonomy' ] ) {

                            // This is a taxonomy attribute
                            $variation_taxonomy_attribute = wp_get_post_terms( $product->id , $attribute_arr[ 'name' ] );

                            foreach ( $variation_taxonomy_attribute as $var_tax_attr ) {

                                if ( $variation_val == $var_tax_attr->slug ) {

                                    if ( is_null( $friendly_variation_text ) )
                                        $friendly_variation_text = str_replace( ":" , "" , wc_attribute_label( $attribute_arr[ 'name' ] ) ) . ": " . $var_tax_attr->name;
                                    else
                                        $friendly_variation_text .= ", " . str_replace( ":" , "" , wc_attribute_label( $attribute_arr[ 'name' ] ) ) . ": " . $var_tax_attr->name;

                                    $attr_found = true;
                                    break;

                                } elseif ( empty( $variation_val ) ) {

                                    if ( is_null( $friendly_variation_text ) )
                                        $friendly_variation_text = str_replace( ":" , "" , wc_attribute_label( $attribute_arr[ 'name' ] ) ) . ": Any";
                                    else
                                        $friendly_variation_text .= ", " . str_replace( ":" , "" , wc_attribute_label( $attribute_arr[ 'name' ] ) ) . ": Any";

                                    $attr_found = true;
                                    break;

                                }

                            }

                        } else {

                            // This is not a taxonomy attribute

                            $attr_val = explode( '|' , $attribute_arr[ 'value' ] );

                            foreach ( $attr_val as $attr ) {

                                $attr = trim( $attr );

                                // I believe the reason why I wrapped the $attr with sanitize_title is to remove special chars
                                // We need ot wrap variation_val too to properly compare them
                                if ( sanitize_title( $variation_val ) == sanitize_title( $attr ) ) {

                                    if ( is_null( $friendly_variation_text ) )
                                        $friendly_variation_text = str_replace( ":" , "" , $attribute_arr[ 'name' ] ) . ": " . $attr;
                                    else
                                        $friendly_variation_text .= ", " . str_replace( ":" , "" , $attribute_arr[ 'name' ] ) . ": " . $attr;

                                    $attr_found = true;
                                    break;

                                } elseif ( empty( $variation_val ) ) {

                                    if ( is_null( $friendly_variation_text ) )
                                        $friendly_variation_text = str_replace( ":" , "" , wc_attribute_label( $attribute_arr[ 'name' ] ) ) . ": Any";
                                    else
                                        $friendly_variation_text .= ", " . str_replace( ":" , "" , wc_attribute_label( $attribute_arr[ 'name' ] ) ) . ": Any";

                                    $attr_found = true;
                                    break;

                                }

                            }

                        }

                        if ( $attr_found )
                            break;

                    }

                }

                if ( ( $product->managing_stock() === true && $product->get_total_stock() > 0 && $variation_obj->managing_stock() === true && $variation_obj->get_total_stock() > 0 && $variation_obj->is_purchasable() ) ||
                     ( $product->managing_stock() !== true && $variation_obj->is_in_stock() && $variation_obj->is_purchasable() ) ||
                     ( $variation_obj->backorders_allowed() && $variation_obj->is_purchasable() ) ) {

                //if ( $variation[ 'is_in_stock' ] && $variation_obj->is_purchasable() ) {
                    $variation_arr[] = array(
                                            'value'     =>  $variation[ 'variation_id' ],
                                            //'text'      =>  wc_get_formatted_variation( $variation[ 'attributes' ] , false ),
                                            'text'      =>  $friendly_variation_text,
                                            'disabled'  =>  false,
                                            'visible'   =>  true
                                        );

                } else {

                    $visibility = false;
                    if ( $variation_obj->variation_is_visible() )
                        $visibility = true;

                    $variation_arr[] = array(
                                            'value'     =>  0,
                                            //'text'      =>  wc_get_formatted_variation( $variation[ 'attributes' ] , false ),
                                            'text'      =>  $friendly_variation_text,
                                            'disabled'  =>  true,
                                            'visible'   =>  $visibility
                                        );

                }

            }

            wp_reset_postdata();

            //usort( $variation_arr , array( $this , 'usortCallback' ) ); // Sort variations alphabetically
            usort( $variation_arr , array( $this , 'usortVariationMenuOrder' ) ); // Sort variations via menu order

            foreach ( $variation_arr as $variation ) {

                if ( !$variation[ 'visible' ] )
                    continue;

                $variation_select_box .= '<option value="' . $variation[ 'value' ] . '" ' . ( $variation[ 'disabled' ] ? 'disabled' : '' ) . '>' . $variation[ 'text' ] . '</option>';

            }

            $variation_select_box .= '</select>';

            $variation_select_box = apply_filters( 'wwof_filter_product_variation' , $variation_select_box );

            return $variation_select_box;

        }

    }

    /**
     * Sorting callback for usort function. Mainly for sorting variable variations.
     *
     * @param $arr1
     * @param $arr2
     * @return int
     *
     * @since 1.1.1
     */
    public function usortCallback ( $arr1 , $arr2 ) {

        return strcasecmp( $arr1[ 'text' ] , $arr2[ 'text' ] );

    }

    /**
     * usort callback that sorts variations based on menu order.
     *
     * @since 1.3.0
     *
     * @param $arr1
     * @param $arr2
     * @return int
     */
    public function usortVariationMenuOrder( $arr1 , $arr2 ) {

        $product1_id = $arr1[ 'value' ];
        $product2_id = $arr2[ 'value' ];

        $product1_menu_order = get_post_field( 'menu_order', $product1_id );
        $product2_menu_order = get_post_field( 'menu_order', $product2_id );

        if ( $product1_menu_order == $product2_menu_order )
            return 0;

        return ( $product1_menu_order < $product2_menu_order ) ? -1 : 1;

    }

    /**
     * Get product thumbnail.
     *
     * @param $product
     * @param $permalink
     * @param $imageSize
     *
     * @return string
     * @since 1.0.0
     */
    public function getProductImage ( $product , $permalink , $imageSize ) {

        $showThumbnail = get_option( 'wwof_general_show_product_thumbnail' );

        if ( $showThumbnail !== false && $showThumbnail == 'yes' ) {

            if ( has_post_thumbnail( $product->id ) ) {



                $img    =   get_the_post_thumbnail(
                    $product->id,
                    $imageSize,
                    array(
                        'class' =>  'wwof_product_listing_item_thumbnail',
                        'alt'   =>  $product->get_title()
                    )
                );

            } else
                $img = '<img class="wwof_product_listing_item_thumbnail wp-post-image" width="48" height="48" alt="' . $product->get_title() . '" src="' . WWOF_IMAGES_ROOT_URL . 'product_image_placeholder.png">';

            $img = '<a class="product_link" href="' . $this->getProductLink( $product->id , $permalink ) . '">' . $img . '</a>';
            $img = apply_filters( 'wwof_product_item_image' , $img );

        
            return $img;

        }

    }

    /**
     * Get product link.
     *
     * @param $productID
     * @param $productLink
     *
     * @return mixed
     * @since 1.0.0
     */
    public function getProductLink( $productID , $productLink ) {

        $showProductDetailsOnPopup = get_option( 'wwof_general_display_product_details_on_popup' );

        if ( $showProductDetailsOnPopup !== false && $showProductDetailsOnPopup == 'yes' ) {

            // Show details via pop up
            return apply_filters( 'wwof_filter_product_link' , admin_url( 'admin-ajax.php' ) . '?action=wwof_getProductDetails&productID=' . $productID );

        } else {

            // Direct to product page
            return apply_filters( 'wwof_filter_product_link' , $productLink );

        }

    }

    /**
     * Return product sku visibility classes.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getProductSkuVisibilityClass () {

        $showSku = get_option( 'wwof_general_show_product_sku' );

        if ( $showSku === 'yes' )
            return apply_filters( 'wwof_filter_sku_visibility_class' , 'visible' );
        else
            return apply_filters( 'wwof_filter_sku_visibility_class' , 'hidden' );

    }

    /**
     * Return product stock quantity visibility class.
     *
     * @return mixed
     *
     * @since 1.2.0
     */
    public function getProductStockQuantityVisibilityClass () {

        $showStockQuantity = get_option( 'wwof_general_show_product_stock_quantity' );

        if ( $showStockQuantity === 'yes' )
            return apply_filters( 'wwof_filter_stock_quantity_visibility_class' , 'visible' );
        else
            return apply_filters( 'wwof_filter_stock_quantity_visibility_class' , 'hidden' );

    }

    /**
     * Get product sku.
     *
     * @param $product
     *
     * @return string
     * @since 1.0.0
     */
    public function getProductSku( $product ) {

        $showSku = get_option( 'wwof_general_show_product_sku' );

        if ( $showSku !== false && $showSku == 'yes' ) {

            if ( $product->product_type == 'variable' ) {

                $variation_query = new WP_Query();
                $args_variation = array(
                                        'post_status'   =>  'publish',
                                        'post_type'     =>  'product_variation',
                                        'post_parent'   =>  $product->id
                                    );
                $variation_query->query( $args_variation );

                $sku = '<div class="variable_sku">';

                foreach ( $variation_query->posts as $variation ) {

                    $variation = wc_get_product( $variation->ID );

                    if ( $variation->is_in_stock() )
                        $sku .= '<span data-variation-id="' . $variation->variation_id . '" class="sku">' . $variation->get_sku() . '</span>';

                }

                $sku .= '</div>';

            } else {

                // Simple Product
                $sku = '<span class="sku_wrapper"><span class="sku">' . $product->get_sku() . '</span></span>';

            }

            $sku = apply_filters( 'wwof_filter_product_sku' , $sku );

            return $sku;

        }

    }

    /**
     * Get product wholesale price per order quantity list html.
     *
     * @since 1.3.0
     * @since 1.3.1 Add Aelia currency switcher plugin integration
     *
     * @param $product
     * @return string
     */
    public function getProductWholesalePricePerOrderQuantityListHTML( $product ) {

        // Display wholesale price per order quantity list
        global $wc_wholesale_prices , $wc_wholesale_prices_premium;

        $quantity_discount_html = '';

        // We only do this if WWPP is installed and active
        if ( get_class( $wc_wholesale_prices ) == 'WooCommerceWholeSalePrices' &&
             get_class( $wc_wholesale_prices_premium ) == 'WooCommerceWholeSalePricesPremium' ) {

            if ( $product->product_type == 'simple' )
                $product_id = $product->id;
            elseif ( $product->product_type == 'variation' )
                $product_id = $product->variation_id;

            $wholesale_role = $wc_wholesale_prices->getUserWholesaleRole();

            // Since quantity based wholesale pricing relies on the presence of the wholesale price at a product level
            // We need to get the original wholesale price ( per product level ), we don't need to filter the wholesale price.
            $wholesale_price = $wc_wholesale_prices_premium->getProductWholesalePrice( $product_id , $wholesale_role );

            $hide_quantity_discount_table = get_option( 'wwpp_settings_hide_quantity_discount_table' , false );

            if ( !empty( $wholesale_price ) && $hide_quantity_discount_table != 'yes' ) {

                $enabled = get_post_meta( $product_id , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );

                $mapping = get_post_meta( $product_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                if (!is_array($mapping))
                    $mapping = array();

                if ( $enabled == 'yes' && !empty( $mapping ) ) {

                    ob_start();

                    /*
                     * Get the base currency mapping. The base currency mapping well determine what wholesale
                     * role and range pairing a product has wholesale price with.
                     */
                    $baseCurrencyMapping = $this->_getBaseCurrencyMapping( $mapping , $wholesale_role );

                    if ( WWOF_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                        $active_currency = get_woocommerce_currency();
                        $base_currency = WWOF_ACS_Integration_Helper::get_product_base_currency( $product_id );

                        // No point on doing anything if have no base currency mapping
                        if ( !empty( $baseCurrencyMapping ) ) {

                            if ( $base_currency == $active_currency ) {

                                /*
                                 * If active currency is equal to base currency, then we just need to pass
                                 * the base currency mapping.
                                 */
                                $this->_printWholesalePricePerOrderQuantityList( $baseCurrencyMapping , array() , $mapping , $product , $wholesale_role , true , $base_currency , $active_currency );

                            } else {

                                $specific_currency_mapping = $this->_getSpecificCurrencyMapping( $mapping , $wholesale_role , $active_currency , $baseCurrencyMapping );

                                $this->_printWholesalePricePerOrderQuantityList( $baseCurrencyMapping , $specific_currency_mapping , $mapping , $product , $wholesale_role , false , $base_currency , $active_currency );

                            }

                        }

                    } else {

                        // Default without Aelia currency switcher plugin

                        if ( !empty( $baseCurrencyMapping ) )
                            $this->_printWholesalePricePerOrderQuantityList( $baseCurrencyMapping , array() , $mapping , $product , $wholesale_role , true , get_woocommerce_currency() , get_woocommerce_currency() );


                    }

                    $quantity_discount_html = ob_get_clean();

                }

            }

        }

        return $quantity_discount_html;

    }

    /**
     * Print wholesale pricing per order quantity list.
     *
     * @since 1.3.1
     *
     * @param $baseCurrencyMapping
     * @param $specificCurrencyMapping
     * @param $mapping
     * @param $product
     * @param $userWholesaleRole
     * @param $isBaseCurrency
     * @param $baseCurrency
     * @param $activeCurrency
     */
    private function _printWholesalePricePerOrderQuantityList( $baseCurrencyMapping , $specificCurrencyMapping , $mapping , $product , $userWholesaleRole , $isBaseCurrency , $baseCurrency , $activeCurrency ) {
        do_action( 'wwof_action_before_wholesale_price_per_order_quantity_list_html' ); ?>

        <ul class="wholesale-price-quantity-discount-lists">

            <?php
            if ( !$isBaseCurrency ) {

                // Specific currency

                foreach ( $baseCurrencyMapping as $baseMap ) {

                    /*
                     * Even if this is a not a base currency, we will still rely on the base currency "RANGE".
                     * Because some range that are present on the base currency, may not be present in this current currency.
                     * But this current currency still has a wholesale price for that range, its wholesale price will be derived
                     * from base currency wholesale price by converting it to this current currency.
                     *
                     * Also if a wholesale price is set for this current currency range ( ex. 10 - 20 ) but that range
                     * is not present on the base currency mapping. We don't recognize this specific product on this range
                     * ( 10 - 20 ) as having wholesale price. User must set wholesale price on the base currency for the
                     * 10 - 20 range for this to be recognized as having a wholesale price.
                     */

                    $qty = $baseMap[ 'start_qty' ];

                    if ( !empty( $baseMap[ 'end_qty' ] ) )
                        $qty .= ' - ' . $baseMap[ 'end_qty' ];
                    else
                        $qty .= '+';

                    $price = '';

                    /*
                     * First check if a price is set for this wholesale role : range pair in the specific currency mapping.
                     * If wholesale price is present, then use it.
                     */
                    foreach ( $specificCurrencyMapping as $specificMap ) {

                        if ( $specificMap[ $activeCurrency . '_start_qty' ] == $baseMap[ 'start_qty' ] && $specificMap[ $activeCurrency . '_end_qty' ] == $baseMap[ 'end_qty' ] )
                            $price = wc_price( $specificMap[ $activeCurrency . '_wholesale_price' ] , array( 'currency' => $activeCurrency ) );

                    }

                    /*
                     * Now if there is no mapping for this specific wholesale role : range pair inn the specific currency mapping,
                     * since this range is present on the base map mapping. We derive the price by converting the price set on the
                     * base currency mapping to this active currency.
                     */
                    if ( !$price ) {

                        $price = WWOF_ACS_Integration_Helper::convert( $baseMap[ 'wholesale_price' ] , $activeCurrency , $baseCurrency );
                        $price = wc_price( $price , array( 'currency' => $activeCurrency ) );

                    } ?>

                    <li>
                        <?php do_action( 'wwof_action_before_wholesale_price_per_order_quantity_list_item_html' , $baseMap , $product , $userWholesaleRole ); ?>
                        <span class="quantity-range"><?php echo $qty; ?></span><span class="sep">:</span><span class="discounted-price"><?php echo $price; ?></span>
                        <?php do_action( 'wwof_action_after_wholesale_price_per_order_quantity_list_item_html' , $baseMap , $product , $userWholesaleRole ); ?>
                    </li>

                <?php }

            } else {

                /*
                 * Base currency.
                 * Also the default if Aelia currency switcher plugin isn't active.
                 */
                foreach ( $baseCurrencyMapping as $map ) {

                    $qty = $map[ 'start_qty' ];

                    if ( !empty( $map[ 'end_qty' ] ) )
                        $qty .= ' - ' . $map[ 'end_qty' ];
                    else
                        $qty .= '+';

                    $price = wc_price( $map[ 'wholesale_price' ] , array( 'currency' => $baseCurrency ) ); ?>

                    <li>
                        <?php do_action( 'wwof_action_before_wholesale_price_per_order_quantity_list_item_html' , $map , $product , $userWholesaleRole ); ?>
                        <span class="quantity-range"><?php echo $qty; ?></span><span class="sep">:</span><span class="discounted-price"><?php echo $price; ?></span>
                        <?php do_action( 'wwof_action_after_wholesale_price_per_order_quantity_list_item_html' , $map , $product , $userWholesaleRole ); ?>
                    </li>

                <?php }

            } ?>

        </ul><!-- .wholesale-price-per-order-quantity-list -->

        <?php do_action( 'wwof_action_after_wholesale_price_per_order_quantity_list_html' );

    }

    /**
     * Get the base currency mapping from the wholesale price per order quantity mapping.
     *
     * @since 1.3.1
     *
     * @param $mapping
     * @param $userWholesaleRole
     * @return array
     */
    private function _getBaseCurrencyMapping( $mapping , $userWholesaleRole ) {

        $baseCurrencyMapping = array();

        foreach ( $mapping as $map ) {

            // Skip non base currency mapping
            if ( array_key_exists( 'currency' , $map ) )
                continue;

            // Skip mapping not meant for the current user wholesale role
            if ( $userWholesaleRole[ 0 ] != $map[ 'wholesale_role' ] )
                continue;

            $baseCurrencyMapping[] = $map;

        }

        return $baseCurrencyMapping;

    }

    /**
     * Get the specific currency mapping from the wholesale price per order quantity mapping.
     *
     * @since 1.3.1
     *
     * @param $mapping
     * @param $userWholesaleRole
     * @param $activeCurrency
     * @param $baseCurrencyMapping
     * @return array
     */
    private function _getSpecificCurrencyMapping( $mapping , $userWholesaleRole , $activeCurrency , $baseCurrencyMapping ) {

        // Get specific currency mapping
        $specificCurrencyMapping = array();

        foreach ( $mapping as $map ) {

            // Skip base currency
            if ( !array_key_exists( 'currency' , $map ) )
                continue;

            // Skip mappings that are not for the active currency
            if ( !array_key_exists( $activeCurrency . '_wholesale_role' , $map ) )
                continue;

            // Skip mapping not meant for the currency user wholesale role
            if ( $userWholesaleRole[ 0 ] != $map[ $activeCurrency . '_wholesale_role' ] )
                continue;

            // Only extract out mappings for this current currency that has equivalent mapping
            // on the base currency.
            foreach ( $baseCurrencyMapping as $base_map ) {

                if ( $base_map[ 'start_qty' ] == $map[ $activeCurrency . '_start_qty' ] && $base_map[ 'end_qty' ] == $map[ $activeCurrency . '_end_qty' ] ) {

                    $specificCurrencyMapping[] = $map;
                    break;

                }

            }

        }

        return $specificCurrencyMapping;

    }

    /**
     * Get product price.
     *
     * Version 1.3.2 change set:
     * We determine if a variation is active or not is by also checking the inventory status of the parent variable
     * product.
     *
     * @since 1.0.0
     * @since 1.3.0 Added feature to display wholesale price per order quantity as a list.
     * @since 1.3.2
     *
     * @param $product
     * @return string
     */
    public function getProductPrice ( $product ) {

        $discount_per_order_qty_html = "";
        $price_html = "";

        if ( $product->product_type == 'variable' ) {

            $product_variations = $product->get_available_variations();

            $price_html = '<div class="variable_price">';

            foreach ( $product_variations as $variation ) {

                $variation_obj = wc_get_product( $variation[ 'variation_id' ] );

                if ( ( $product->managing_stock() === true && $product->get_total_stock() > 0 && $variation_obj->managing_stock() === true && $variation_obj->get_total_stock() > 0 && $variation_obj->is_purchasable() ) ||
                    ( $product->managing_stock() !== true && $variation_obj->is_in_stock() && $variation_obj->is_purchasable() ) ||
                    ( $variation_obj->backorders_allowed() && $variation_obj->is_purchasable() ) ) {

                //if ( $variation_obj->is_in_stock() && $variation_obj->is_purchasable() ) {

                    if ( get_option( "wwof_general_hide_quantity_discounts" ) != 'yes' )
                        $discount_per_order_qty_html = $this->getProductWholesalePricePerOrderQuantityListHTML( $variation_obj );

                    $price_html .= '<span data-variation-id="' . $variation[ 'variation_id' ] . '" class="price">' . $variation_obj->get_price_html() . $discount_per_order_qty_html . '</span>';

                }

            }

            $price_html .= '</div>';

        } elseif ( $product->product_type == 'simple' ) {

            if ( get_option( "wwof_general_hide_quantity_discounts" ) != 'yes' )
                $discount_per_order_qty_html = $this->getProductWholesalePricePerOrderQuantityListHTML( $product );

            $price_html = '<span class="price">' . $product->get_price_html() . $discount_per_order_qty_html . '</span>';

        }

        $price_html = apply_filters( 'wwof_filter_product_item_price' , $price_html , $product );

        return $price_html;

    }

    /**
     * Get product stock quantity.
     *
     * @param $product
     * @return string
     *
     * @since 1.2.0
     */
    public function getProductStockQuantity ( $product ) {

        if ( $product->product_type == 'variable' ) {

            $product_variations = $product->get_available_variations();

            $stock_html = '<div class="variable_stock_quantity">';

            foreach ( $product_variations as $variation ) {

                $variation_obj = wc_get_product( $variation[ 'variation_id' ] );


                if ( ( $product->managing_stock() === true && $product->get_total_stock() > 0 && $variation_obj->managing_stock() === true && $variation_obj->get_total_stock() > 0 && $variation_obj->is_purchasable() ) ||
                    ( $product->managing_stock() !== true && $variation_obj->is_in_stock() && $variation_obj->is_purchasable() ) ||
                    ( $variation_obj->backorders_allowed() && $variation_obj->is_purchasable() ) ) {

                //if ( $variation_obj->is_in_stock() && $variation_obj->is_purchasable() ) {

                    $stock_quantity = '';

                    // First get parent variable product stock quantity
                    if ( $product->manage_stock == 'yes' )
                        $stock_quantity = $product->get_stock_quantity();

                    // Override stock quantity if variation is set to manage stock
                    if ( $variation_obj->manage_stock == 'yes' )
                        $stock_quantity = $variation_obj->get_stock_quantity();

                    // Only apply after item quantity text if there is indeed quantity
                    if ( $stock_quantity )
                        $stock_quantity = '<span class="quantity">' . $stock_quantity . '</span>';

                    $stock_html .= '<span data-variation-id="' . $variation[ 'variation_id' ] . '" class="stock_quantity">' . $stock_quantity . '</span>';

                }

            }

            $stock_html .= '</div>';

        } else {

            $stock_quantity = '';

            if ( $product->is_in_stock() )
                if ( $product->manage_stock == 'yes' && $product->get_stock_quantity() )
                    $stock_quantity = '<span class="quantity">' . $product->get_stock_quantity() . '</span>';

            $stock_html = '<span class="stock_quantity">' . $stock_quantity . '</span>';

        }

        $stock_html = apply_filters( 'wwof_filter_product_stock_quantity' , $stock_html , $product );

        return $stock_html;

    }

    /**
     * Get product quantity field.
     *
     * @param $product
     *
     * @return string
     * @since 1.0.0
     */
    public function getProductQuantityField( $product ) {

        // TODO: dynamically change max value depending on product stock ( specially when changing variations of a variable product )

        global $wc_wholesale_prices_premium, $wc_wholesale_prices;

        $initial_value = 1;
        $min_order_qty_html = '';

        // We only do this if WWPP is installed and active
        if ( get_class( $wc_wholesale_prices_premium ) == 'WooCommerceWholeSalePricesPremium' &&
             get_class( $wc_wholesale_prices ) == 'WooCommerceWholeSalePrices' ) {

            $wholesale_role = $wc_wholesale_prices->getUserWholesaleRole();

            // We only do this if wholesale user
            if ( !empty( $wholesale_role ) ) {

                if ( $product->product_type == 'variable' ) {

                    $product_variations = $product->get_available_variations();

                    // $min_order_qty_html = '<div class="variable-minimum-order-quantity" style="display: none;">';

                    // foreach ( $product_variations as $variation ) {

                    //     $wholesale_price = $wc_wholesale_prices_premium->getProductWholesalePrice( $variation[ 'variation_id' ] , $wholesale_role );
                    //     $wholesale_price = apply_filters( 'wwp_filter_wholesale_price_shop' , $wholesale_price , $variation[ 'variation_id' ] , $wholesale_role );

                    //     // We only do this if there is a wholesale price set, we don't want to enforce minimum order quantity if there is no wholesale price
                    //     if ( is_numeric( $wholesale_price ) ) {

                    //         $min_order_qty = get_post_meta( $variation[ 'variation_id' ] , $wholesale_role[ 0 ] . '_wholesale_minimum_order_quantity' , true );
                    //         if ( !$min_order_qty )
                    //             $min_order_qty = 1;

                    //         $min_order_qty_html .= '<span data-variation-id="' . $variation[ 'variation_id' ] . '" class="min-order-qty">' . $min_order_qty . '</span>';

                    //     }

                    // }

                    // $min_order_qty_html .= '</div>';

                } else {

                    $wholesale_price = $wc_wholesale_prices_premium->getProductWholesalePrice( $product->id , $wholesale_role );
                    $wholesale_price = apply_filters( 'wwp_filter_wholesale_price_shop' , $wholesale_price , $product->id , $wholesale_role );

                    if ( is_numeric( $wholesale_price ) ) {

                        $min_order_qty = get_post_meta( $product->id , $wholesale_role[ 0 ] . '_wholesale_minimum_order_quantity' , true );
                        if ( $min_order_qty )
                            $initial_value = $min_order_qty;

                    }

                }

            } // Wholesale Role Check

        } // WWPP check

        $initial_value = 1;

        if ( $product->is_in_stock() ) {

            if ( $product->manage_stock == 'yes' ) {

                $max_str = "";
                $stock_quantity = $product->get_stock_quantity();

                if ( $stock_quantity > 0 )
                    $max_str = 'max="'. $stock_quantity .'"';

                $quantity_field = '<div class="quantity buttons_added"><input type="button" value="-" class="minus"><input type="number" step="1" min="1" ' . $max_str . ' name="quantity" value="1" title="Qty" class="input-text qty text 1835" size="4"><input type="button" value="+" class="plus"></div>';

            } else
                $quantity_field = '<div class="quantity buttons_added"><input type="button" value="-" class="minus"><input type="number" step="1" min="1" name="quantity" value="1" title="Qty" class="input-text qty text" size="4"><input type="button" value="+" class="plus"></div>';
            
        } else
            $quantity_field = '<span class="out-of-stock">' . __( 'Out of Stock' , 'woocommerce-wholesale-order-form' ) . '</span>';

        $quantity_field = $min_order_qty_html . $quantity_field;

        $quantity_field = apply_filters( 'wwof_filter_product_item_quantity' , $quantity_field , $product );

        return $quantity_field;
        
    }

    /**
     * Get product row actions fields.
     *
     * @param $product
     * @param $alternate
     *
     * @return string
     * @since 1.0.0
     */
    public function getProductRowActionFields( $product , $alternate = false ){

        if ( $product->is_in_stock() ) {

            if ( $alternate )
                $action_field = '<input type="checkbox" class="wwof_add_to_cart_checkbox" /><label>' . __( 'Add To Cart' , 'woocommerce-wholesale-order-form' ) .'</label>';
            else
                $action_field = '<input type="button" class="wwof_add_to_cart_button btn btn-primary single_add_to_cart_button button alt" value="' . __( 'Add To Cart' , 'woocommerce-wholesale-order-form' ) . '"/><span class="spinner"></span>';

        } else
            $action_field = '<span class="out-of-stock">' . __( 'Out of Stock' , 'woocommerce-wholesale-order-form' ) . '</span>';

        $action_field = apply_filters( 'wwof_filter_product_item_action_controls' , $action_field , $product->is_in_stock() , $alternate );

        return $action_field;

    }

    /**
     * Get wholesale product listing pagination.
     *
     * @param $paged
     * @param $max_num_pages
     * @param $search
     * @param $cat_filter
     *
     * @return mixed
     * @since 1.0.0
     */
    public function getGalleryListingPagination( $paged , $max_num_pages , $search , $cat_filter ) {

        $big    =   999999999; // need an unlikely integer
        $args   =   array(
                        'base'      =>  str_replace( $big , '%#%' , esc_url( get_pagenum_link( $big ) ) ),
                        'format'    =>  '?paged=%#%',
                        'current'   =>  max( 1 , $paged ),
                        'total'     =>  $max_num_pages,
                        'type'      =>  'list',
                        'prev_text' =>  sprintf( __( '%1$s Previous' , 'woocommerce-wholesale-order-form' ) , '&laquo;' ),
                        'next_text' =>  sprintf( __( 'Next %1$s' , 'woocommerce-wholesale-order-form' ) , '&raquo;' ),
                        'add_args'  =>  array(
                                            'cat_filter' => $cat_filter
                                        )
                    );

        // Determine if we need to append the search keyword to the href url
        $search = trim( $search );
        if( !empty( $search ) && !is_null( $search ) && !$search == "" )
            $args[ 'add_args' ][ 'search' ] = urlencode( $search );

        $pagination_links = paginate_links( $args );

        $pagination_links = apply_filters( 'wwof_filter_product_listing_pagination' , $pagination_links , $paged , $max_num_pages );

        return $pagination_links;

    }

    /**
     * Get cart url.
     *
     * @return mixed
     *
     * @since 1.1.0
     */
    public function getCartUrl () {

        return WC()->cart->get_cart_url();

    }

    /**
     * Get cart sub total (including/excluding) tax.
     *
     * @return string
     *
     * @since 1.2.0
     */
    public function getCartSubtotal () {

        ob_start();

        if ( get_option( 'wwof_general_display_cart_subtotal' ) == 'yes' ) { ?>

            <div class="wwof_cart_sub_total">

            <?php
            if ( WC()->cart->get_cart_contents_count() ) {

                if ( get_option( 'wwof_general_cart_subtotal_prices_display' ) == 'excl' ) { ?>

                    <span class="sub_total excluding_tax">
                        <?php
                        _e( 'Subtotal: ' , 'woocommerce-wholesale-order-form' );
                        echo wc_price( WC()->cart->cart_contents_total );
                        ?>
                    </span>

                <?php } else { ?>

                    <span class="sub_total including_tax">
                        <?php
                        _e( 'Subtotal: ' , 'woocommerce-wholesale-order-form' );
                        echo wc_price( WC()->cart->cart_contents_total + WC()->cart->tax_total ) . ' <small>' . WC()->countries->inc_tax_or_vat() . '</small>';
                        ?>
                    </span>

                <?php }

            } else { ?>

                <span class="empty_cart"><?php _e( 'Cart Empty' , 'woocommerce-wholesale-order-form' ); ?></span>

            <?php } ?>

            </div>
            <?php

        }

        return ob_get_clean();

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Short Codes Callbacks
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Product listing shortcode.
     *
     * @return string
     * @since 1.0.0
     */
    public function sc_productListing() {

        // To buffer the output
        ob_start();

        require_once ( WWOF_VIEWS_ROOT_DIR . 'shortcodes/wwof-shortcode-product-listing.php' );

        // To return the buffered output
        return ob_get_clean();

    }

    /**
     * Apply certain classes to body tag wherever page/post the shortcode [wwof_product_listing] is applied.
     *
     * @param $classes
     *
     * @return mixed
     * @since 1.0.0
     */
    public function sc_bodyClasses( $classes ) {

        global $post;

        if ( isset( $post->post_content ) && has_shortcode( $post->post_content , 'wwof_product_listing' ) ) {

            $classes [] = 'wwof-woocommerce';
            $classes [] = 'woocommerce';
            $classes [] = 'woocommerce-page';

        }

        return apply_filters( 'wwof_filter_body_classes' , $classes );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Utility Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    /**
     * Load templates in an overridable manner.
     *
     * @param $template Template path
     * @param $options Options to pass to the template
     * @param $defaultTemplatePath Default template path
     *
     * @since 1.0.0
     */
    private function _loadTemplate( $template , $options , $defaultTemplatePath ) {

        woocommerce_get_template( $template , $options , '' , $defaultTemplatePath );

    }

}