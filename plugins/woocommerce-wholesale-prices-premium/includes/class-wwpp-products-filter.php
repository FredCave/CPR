<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Products_Filter' ) ) {

    class WWPP_Products_Filter {

        private static $_instance;

        public static function getInstance () {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self;

            return self::$_instance;

        }

        /**
         * Wholesale products are products with wholesale price greater than zero.
         * This filter does not apply to admins coz we don't restrict admins in any way.
         * ( Shop and Archive Pages ).
         *
         * @param $productQuery
         * @param $allRegisteredWholesaleRoles
         *
         * @since 1.0.3
         */
        public function onlyShowWholesaleProductsToWholesaleUsers ( $productQuery, $allRegisteredWholesaleRoles ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            // And also check if settings for "Only Showing Wholesale Products To Wholesale Users" option is checked.
            if ( !current_user_can( 'manage_options' ) &&
                get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' ) == 'yes' ) {

                if ( ! $productQuery->is_main_query() ) return;

                //if ( ! $productQuery->is_post_type_archive() ) return;

                //if ( ! is_admin() && ( is_shop() || is_product_category() || is_product_taxonomy() || is_search() ) ) {
                if ( ! is_admin() && ( is_shop() || is_product_category() || is_product_taxonomy() ) ) {

                    global $current_user;
                    $currentUserRoles = $current_user->roles;
                    $currentUserWholesaleRole = null;

                    $roleDiscount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
                    if ( !is_array( $roleDiscount ) )
                        $roleDiscount = array();

                    foreach($allRegisteredWholesaleRoles as $roleKey => $role){

                        if(in_array($roleKey,$currentUserRoles)){
                            $currentUserWholesaleRole = $roleKey;
                            break;
                        }

                    }

                    // If there is a default general wholesale discount set for this wholesale role
                    // then all products are considered wholesale for this dude
                    if ( !is_null( $currentUserWholesaleRole ) && !array_key_exists( $currentUserWholesaleRole , $roleDiscount ) ) {

                        $productQuery->set( 'meta_query' , array(
                            array(
                                'key'       =>  $currentUserWholesaleRole . '_have_wholesale_price',
                                'value'     =>  'yes',
                                'compare'   =>  '='
                            ),
                        ) );

                    }

                }

            }

            global $wc_wholesale_prices_premium;
            remove_action( 'pre_get_posts' , array( $wc_wholesale_prices_premium , 'onlyShowWholesaleProductsToWholesaleUsers' ) );

        }

        /**
         * Wholesale products are products with wholesale price greater than zero.
         * This filter does not apply to admins coz we don't restrict admins in any way.
         * ( WooCommerce Wholesale Order Form Integration ).
         *
         * @param $args
         * @param $allRegisteredWholesaleRoles
         * @return mixed
         *
         * @since 1.0.3
         */
        public function onlyShowWholesaleProductsToWholesaleUsersArg ( $args , $allRegisteredWholesaleRoles ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            // And also check if settings for "Only Showing Wholesale Products To Wholesale Users" option is checked.
            if ( !current_user_can( 'manage_options' ) && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' ) == 'yes' ) {

                global $current_user;
                $currentUserRoles = $current_user->roles;
                $currentUserWholesaleRole = null;

                $roleDiscount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
                if ( !is_array( $roleDiscount ) )
                    $roleDiscount = array();

                foreach( $allRegisteredWholesaleRoles as $roleKey => $role ){

                    if(in_array($roleKey,$currentUserRoles)){
                        $currentUserWholesaleRole = $roleKey;
                        break;
                    }

                }

                // If there is a default general wholesale discount set for this wholesale role
                // then all products are considered wholesale for this dude
                if ( !is_null( $currentUserWholesaleRole ) && !array_key_exists( $currentUserWholesaleRole , $roleDiscount ) ) {

                    $args[ 'meta_query' ][] =   array(
                                                    'key'       =>  $currentUserWholesaleRole . '_have_wholesale_price',
                                                    'value'     =>  'yes',
                                                    'compare'   =>  '='
                                                 );

                }

            }

            return $args;

        }

        /**
         * Filter inter sells products ( cross-sells, up-sells ).
         *
         * @since 1.7.3
         *
         * @param $product_ids
         * @param $product
         * @param $userWholesaleRole
         * @return array
         */
        public function filterProductInterSells( $product_ids , $product , $userWholesaleRole ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) ) {

                $userWholesaleRole = $userWholesaleRole ? $userWholesaleRole : null;
                $filtered_product_ids = array();

                // This only affects wholesale users
                if ( !is_null( $userWholesaleRole ) && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' ) == 'yes' ) {

                    $roleDiscount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
                    if ( !is_array( $roleDiscount ) )
                        $roleDiscount = array();

                    // If there is a default general wholesale discount set for this wholesale role
                    // then all products are considered wholesale for this dude
                    if ( !array_key_exists( $userWholesaleRole[0] , $roleDiscount ) ) {

                        foreach ( $product_ids as $product_id ) {

                            if ( get_post_meta( $product_id , $userWholesaleRole[0] . '_have_wholesale_price' , true ) == 'yes' )
                                $filtered_product_ids[] = $product_id;

                        }

                    } else
                        $filtered_product_ids = $product_ids;

                } else {

                    foreach ( $product_ids as $product_id ) {

                        $role = $userWholesaleRole ? $userWholesaleRole[0] : null;

                        $visibility_filter = get_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
                        if ( !is_array( $visibility_filter ) )
                            $visibility_filter = array();

                        if ( in_array( $role , $visibility_filter ) || in_array( 'all' , $visibility_filter ) )
                            $filtered_product_ids[] = $product_id;

                    }

                }

                return $filtered_product_ids;

            } else
                return $product_ids;

        }

        /**
         * Wholesale products are products with wholesale price greater than zero.
         * This filter does not apply to admins coz we don't restrict admins in any way.
         * ( Single Product Page ).
         *
         * @param $allRegisteredWholesaleRoles
         *
         * @since 1.0.3
         */
        public function onlyShowWholesaleProductsToWholesaleUsersSingleProductPage ( $allRegisteredWholesaleRoles ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            // And also check if settings for "Only Showing Wholesale Products To Wholesale Users" option is checked.
            if ( !current_user_can( 'manage_options' ) && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' ) == 'yes' ) {

                if ( is_product() ) {

                    global $current_user,$post;
                    $currentUserRoles = $current_user->roles;
                    $currentUserWholesaleRole = null;

                    $roleDiscount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
                    if ( !is_array( $roleDiscount ) )
                        $roleDiscount = array();

                    foreach($allRegisteredWholesaleRoles as $roleKey => $role){

                        if(in_array($roleKey,$currentUserRoles)){
                            $currentUserWholesaleRole = $roleKey;
                            break;
                        }

                    }

                    // If there is a default general wholesale discount set for this wholesale role
                    // then all products are considered wholesale for this dude
                    if ( !is_null( $currentUserWholesaleRole ) && !array_key_exists( $currentUserWholesaleRole , $roleDiscount ) ) {

                        $haveWholesalePrice = get_post_meta( $post->ID , $currentUserWholesaleRole . '_have_wholesale_price' , true );

                        if ( $haveWholesalePrice != 'yes' ) {

                            wp_redirect( get_permalink( woocommerce_get_page_id( 'shop' ) ) );
                            exit();

                        }

                    }

                }

            }

        }

        /**
         * Apply filter to only show variations of a variable product on proper time and place.
         * ( Only show variations with wholesale price on wholesale users if setting is enabled )
         * ( Only show variations to appropriate wholesale users if it is set to be exclusively visible to certain wholesale roles )
         *
         * Make variation invisible.
         *
         * @since 1.6.0
         * @param $visible
         * @param $variation_id
         * @param $variable_id
         * @param $variation_obj
         * @param $userWholesaleRole
         * @param $registeredCustomRoles
         * @param WWPP_Wholesale_Prices $wwpp_wholesale_prices
         * @return bool
         */
        public function filterVariationVisibility ( $visible , $variation_id , $variable_id , $variation_obj , $userWholesaleRole , $registeredCustomRoles , WWPP_Wholesale_Prices $wwpp_wholesale_prices ) {

            // Only display to wholesale users variations of a variable product with wholesale price.
            // Wholesale price can be from the variation itself,
            // the product category of the parent variable product,
            // or from the general discount of the user's wholesale role
            if ( !current_user_can( 'manage_options' ) && !empty( $userWholesaleRole ) && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' ) == 'yes' ) {

                $wholesalePrice = $wwpp_wholesale_prices->getProductWholesalePrice( $variation_id , $userWholesaleRole );
                $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_shop' , $wholesalePrice , $variation_id , $userWholesaleRole );

                if ( empty( $wholesalePrice ) )
                    $visible = false;

            }

            // Check if this variation is set to be visible exclusively only to certain wholesale roles
            if ( !current_user_can( 'manage_options' ) ) {

                $exclusive_user_roles = array();
                foreach ( $registeredCustomRoles as $roleKey => $role ) {

                    if ( get_post_meta( $variation_id , $roleKey . '_exclusive_variation' , true ) == 'yes' )
                        $exclusive_user_roles[] = $roleKey;

                }

                if ( !empty( $exclusive_user_roles ) ) {

                    if ( empty( $userWholesaleRole ) || !in_array( $userWholesaleRole[ 0 ] , $exclusive_user_roles ) )
                        $visible = false;

                }

            }

            return $visible;

        }

        /**
         * Apply filter to only show variations of a variable product on proper time and place.
         * ( Only show variations with wholesale price on wholesale users if setting is enabled )
         * ( Only show variations to appropriate wholesale users if it is set to be exclusively visible to certain wholesale roles )
         *
         * Make variation un-purchasable
         *
         * @since 1.6.0
         * @param $purchasable
         * @param $variation_obj
         * @param $userWholesaleRole
         * @param $registeredCustomRoles
         * @param WWPP_Wholesale_Prices $wwpp_wholesale_prices
         * @return bool
         */
        public function filterVariationPurchasability ( $purchasable , $variation_obj , $userWholesaleRole , $registeredCustomRoles , WWPP_Wholesale_Prices $wwpp_wholesale_prices ) {

            // Only display to wholesale users variations of a variable product with wholesale price.
            // Wholesale price can be from the variation itself,
            // the product category of the parent variable product,
            // or from the general discount of the user's wholesale role
            if ( !current_user_can( 'manage_options' ) && !empty( $userWholesaleRole ) && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' ) == 'yes' ) {

                $wholesalePrice = $wwpp_wholesale_prices->getProductWholesalePrice( $variation_obj->variation_id , $userWholesaleRole );
                $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_shop' , $wholesalePrice , $variation_obj->variation_id , $userWholesaleRole );

                if ( empty( $wholesalePrice ) )
                    $purchasable = false;

            }

            // Check if this variation is set to be visible exclusively only to certain wholesale roles
            if ( !current_user_can( 'manage_options' ) ) {

                $exclusive_user_roles = array();
                foreach ( $registeredCustomRoles as $roleKey => $role ) {

                    if ( get_post_meta( $variation_obj->variation_id , $roleKey . '_exclusive_variation' , true ) == 'yes' )
                        $exclusive_user_roles[] = $roleKey;

                }

                if ( !empty( $exclusive_user_roles ) ) {

                    if ( empty( $userWholesaleRole ) || !in_array( $userWholesaleRole[ 0 ] , $exclusive_user_roles ) )
                        $purchasable = false;

                }

            }

            return $purchasable;

        }

        /**
         * Always allow wholesale users to perform backorders no matter what.
         *
         * @since 1.6.0
         * @param $backorders_allowed
         * @param $product_id
         * @param $userWholesaleRole
         * @return mixed
         */
        public function alwaysAllowBackordersToWholesaleUsers( $backorders_allowed , $product_id , $userWholesaleRole ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) ) {

                $filter_flag = get_option( 'wwpp_settings_always_allow_backorders_to_wholesale_users' , false );

                if ( $filter_flag == 'yes' && !empty( $userWholesaleRole ) )
                    $backorders_allowed = true;

            }

            return apply_filters( 'wwpp_filter_product_backorders_allowed' , $backorders_allowed , $product_id , $userWholesaleRole );

        }

        /**
         * Filter product category product items count.
         *
         * @since 1.7.3
         *
         * @param $count_markup
         * @param $category
         * @param $userWholesaleRole
         * @return string
         */
        public function filterProductCategoryPostCount( $count_markup , $category , $userWholesaleRole ) {

            $product_ids = array();
            $products = WWPP_WPDB_Helper::getProductsByCategory( $category->term_id );

            foreach ( $products as $product )
                $product_ids[] = $product->ID;

            // Reuse the logic for the filter product inter sells, filter only products visible to this wholesale role
            $product_ids = $this->filterProductInterSells( $product_ids , null , $userWholesaleRole );

            return ' <mark class="count">(' . count( $product_ids ) . ')</mark>';

        }

    }

}
