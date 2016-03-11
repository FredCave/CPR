<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WWPP_Custom_Fields {

    private static $_instance;

    public static function getInstance(){
        if(!self::$_instance instanceof self)
            self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * Embed custom fields relating to wholesale role filter into the single product admin page.
     *
     * @since 1.0.0
     */
    public function productVisibilityFilter ( $allRegisteredWholesaleRoles ) {

        global $post;

        if($post->post_type == 'product'){

            $currProductWholesaleFilter = get_post_meta($post->ID,WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER);

            if(!is_array($currProductWholesaleFilter))
                $currProductWholesaleFilter = array();

            require_once (WWPP_VIEWS_PATH.'view-wwpp-product-visibility-filter.php');

        }

    }

    /**
     * Save custom embeded fields relating to wholesale role filter.
     *
     * @since 1.0.0
     */
    public function saveIntegratedCustomWholesaleFieldsOnProductPage(){

        // Check if this is an inline edit. If true then return.
        if ( isset( $_POST[ '_inline_edit' ] ) && wp_verify_nonce ( $_POST[ '_inline_edit' ] , 'inlineeditnonce' ) )
            return;

        if(isset($_POST) && !empty($_POST)){

            if(array_key_exists('post_type',$_POST) && $_POST['post_type'] == 'product'){

                // Because we are adding post meta via add_post_meta
                // We make sure to delete old post meta so the meta won't get stacked (contains duplicate values)
                delete_post_meta( $_POST[ 'post_ID' ] , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );

                if ( isset( $_POST[ 'wholesale-visibility-select' ] ) && $_POST[ 'wholesale-visibility-select' ] ) {

                    foreach( $_POST[ 'wholesale-visibility-select' ] as $wholesaleRole ) {
                        add_post_meta( $_POST[ 'post_ID' ] , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , $wholesaleRole );
                    }

                } else {

                    add_post_meta( $_POST[ 'post_ID' ] , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , 'all' );

                }

            }

        }

    }

    /**
     * Apply wholesale roles filter to shop and archive pages.
     *
     * @param $productQuery
     * @param $allRegisteredWholesaleRoles
     * @since 1.0.0
     */
    public function preGetPosts ( $productQuery, $allRegisteredWholesaleRoles ) {

        // Check if user is not an admin, else we don't want to restrict admins in any way.
        if ( !current_user_can( 'manage_options' ) ) {

            if ( ! $productQuery->is_main_query() ) return;

            //if ( ! $productQuery->is_post_type_archive() ) return;

            global $wp;
            $current_url = home_url( add_query_arg( array() , $wp->request ) );

            $shop_front_page = false;
            if ( $current_url == home_url() ) {

                $front_page_id = ( int ) get_option( 'page_on_front' );
                $shop_page_id = woocommerce_get_page_id( 'shop' );

                if ( $front_page_id == $shop_page_id )
                    $shop_front_page = true;

            }

            //if ( ! is_admin() && ( $shop_front_page || is_shop() || is_product_category() || is_product_taxonomy() || is_search() ) ) {
            if ( ! is_admin() && ( $shop_front_page || is_shop() || is_product_category() || is_product_taxonomy() ) ) {

                global $current_user;
                $currentUserRoles = $current_user->roles;
                $currentUserWholesaleRole = null;

                foreach($allRegisteredWholesaleRoles as $roleKey => $role){

                    if(in_array($roleKey,$currentUserRoles)){
                        $currentUserWholesaleRole = $roleKey;
                        break;
                    }

                }

                $productQuery->set( 'meta_query' , array(
                    array(
                        'key'       =>  WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER,
                        'value'     =>  array( $currentUserWholesaleRole ,'all' ),
                        'compare'   =>  'IN'
                    )
                ) );

            }

        }

        global $wc_wholesale_prices_premium;
        remove_action( 'pre_get_posts' , array( $wc_wholesale_prices_premium , 'preGetPosts' ) );

    }

    /**
     * Same as preGetPosts function but only intended for WooCommerce Wholesale Order Form integration,
     * you see the WWOF uses custom query, so unlike the usual way of filter query object, we can't do that with WWOF,
     * but we can filter the query args thus achieving the same effect.
     *
     * @param $args
     * @param $allRegisteredWholesaleRoles
     *
     * @return mixed
     * @since 1.0.0
     */
    public function preGetPostsArg ( $args , $allRegisteredWholesaleRoles ) {

        // Check if user is not an admin, else we don't want to restrict admins in any way.
        if ( !current_user_can( 'manage_options' ) ) {

            global $current_user;
            $currentUserRoles = $current_user->roles;
            $currentUserWholesaleRole = null;

            foreach($allRegisteredWholesaleRoles as $roleKey => $role){

                if(in_array($roleKey,$currentUserRoles)){
                    $currentUserWholesaleRole = $roleKey;
                    break;
                }

            }

            $args['meta_query'][] = array(
                'key'       =>  WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER,
                'value'     =>  array($currentUserWholesaleRole,'all'),
                'compare'   =>  'IN'
            );

        }

        return $args;

    }

    /**
     * Apply wholesale roles filter to single product page (redirect to shop page).
     *
     * @param $allRegisteredWholesaleRoles
     * @since 1.0.0
     */
    public function wholesaleVisibilityFilterForSingleProduct( $allRegisteredWholesaleRoles ){

        // Check if user is not an admin, else we don't want to restrict admins in any way.
        if ( !current_user_can( 'manage_options' ) ) {

            if ( is_product() ) {

                global $current_user,$post;
                $currentUserRoles = $current_user->roles;
                $currentUserWholesaleRole = null;

                foreach($allRegisteredWholesaleRoles as $roleKey => $role){

                    if(in_array($roleKey,$currentUserRoles)){
                        $currentUserWholesaleRole = $roleKey;
                        break;
                    }

                }

                $postWholesaleFilter = get_post_meta($post->ID,WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER);

                if( !in_array( $currentUserWholesaleRole , $postWholesaleFilter ) && !in_array( 'all' , $postWholesaleFilter ) ){

                    wp_redirect( get_permalink( woocommerce_get_page_id( 'shop' ) ) );
                    exit();

                }

            }

        }

    }

    /**
     * Add custom column to order listing page.
     *
     * @param $columns
     *
     * @return mixed
     * @since 1.0.0
     */
    public function addOrdersListingCustomColumn( $columns ){

        $arrayKeys = array_keys($columns);
        $lastIndex = $arrayKeys[count($arrayKeys)-1];
        $lastValue = $columns[$lastIndex];
        array_pop($columns);

        $columns['wwpp_order_type'] = __( 'Order Type');

        $columns[$lastIndex] = $lastValue;

        return $columns;

    }

    /**
     * Add content to the custom column on order listing page.
     *
     * @param $column
     * @param $postId
     * @param $allRegisteredWholesaleRoles
     *
     * @since 1.0.0
     */
    public function addOrdersListingCustomColumnContent( $column, $postId, $allRegisteredWholesaleRoles ){

        if ( $column == 'wwpp_order_type' ) {

            $orderType = get_post_meta( $postId, '_wwpp_order_type', true );

            if ( $orderType == '' || $orderType == null || $orderType == false || $orderType == 'retail' ) {

                echo "Retail";

            } elseif ( $orderType == 'wholesale' ) {

                $wholesaleOrderType = get_post_meta( $postId, '_wwpp_wholesale_order_type', true );

                echo "Wholesale ( ".$allRegisteredWholesaleRoles[$wholesaleOrderType]['roleName']." )";

            }

        }

    }

    /**
     * Add custom filter on order listing page ( order type filter ).
     *
     * @param $allRegisteredWholesaleRoles
     *
     * @since 1.0.0
     */
    public function addWholesaleRoleOrderListingFilter ( $allRegisteredWholesaleRoles ) {

        global $typenow;

        if ( $typenow == 'shop_order' ) {

            ob_start();

            $wwpp_fbwr = null;
            if ( isset( $_GET['wwpp_fbwr'] ) )
                $wwpp_fbwr = $_GET['wwpp_fbwr'];

            $allRegisteredWholesaleRoles = array( 'all_wholesale_orders' => array( 'roleName' => 'All Wholesale Orders' ) ) + $allRegisteredWholesaleRoles;
            $allRegisteredWholesaleRoles = array( 'all_retail_orders' => array( 'roleName' => 'All Retail Orders' ) ) + $allRegisteredWholesaleRoles;
            $allRegisteredWholesaleRoles = array( 'all_order_types' => array( 'roleName' => 'Show all order types' ) ) + $allRegisteredWholesaleRoles;

            //wwpp_fbwr = Filter By Wholesale Role
            ?>
            <select name="wwpp_fbwr" id="filter-by-wholesale-role" class="chosen_select">
                <?php
                foreach ( $allRegisteredWholesaleRoles as $roleKey => $role ) {
                    ?>
                    <option value="<?php echo $roleKey; ?>" <?php echo ( $roleKey == $wwpp_fbwr ) ? 'selected' : '' ; ?>><?php echo $role["roleName"]; ?></option>
                    <?php
                }
                ?>
            </select>
            <?php

            echo ob_get_clean();

        }

    }

    /**
     * Add functionality to the custom filter added on order listing page ( order type filter ).
     *
     * @param $query
     *
     * @since 1.0.0
     */
    public function wholesaleRoleOrderListingFilter ( $query ) {

        global $pagenow;
        $q = &$query;
        $qv = &$query->query_vars;
        $wholesaleFilter = null;

        if(isset($_GET['wwpp_fbwr']))
            $wholesaleFilter = trim($_GET['wwpp_fbwr']);

        if ( $pagenow == 'edit.php' && isset($qv['post_type']) && $qv['post_type'] == 'shop_order' && !is_null($wholesaleFilter) ) {

            switch ( $wholesaleFilter ) {

                case 'all_order_types':
                    // Do nothing
                    break;

                case 'all_retail_orders':

                    $q->set('meta_query', array(
                        'relation'      =>  'OR',
                        array(
                            'key'       =>  '_wwpp_order_type',
                            'value'     =>  array('retail'),
                            'compare'   =>  'IN'
                        ),
                        array(
                            'key'       =>  '_wwpp_order_type',
                            'value'     =>  'gebbirish', // Pre WP 3.9 bug, must set string for NOT EXISTS to work
                            'compare'   =>  'NOT EXISTS'
                        )
                    ));

                    break;

                case 'all_wholesale_orders':

                    $qv['meta_key'] = '_wwpp_order_type';
                    $qv['meta_value'] = 'wholesale';

                    break;

                default:

                    $qv['meta_key'] = '_wwpp_wholesale_order_type';
                    $qv['meta_value'] = $wholesaleFilter;

                    break;

            }

        }

    }

}
