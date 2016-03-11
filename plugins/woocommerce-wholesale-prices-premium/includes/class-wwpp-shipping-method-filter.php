<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Shipping_Method_Filter' ) ) {

    class WWPP_Shipping_Method_Filter {

        private static $_instance;

        public static function getInstance () {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self;

            return self::$_instance;

        }

        /**
         * Apply appropriate shipping method to products in a cart.
         *
         * Important Note:
         * For all shipping methods built in or not
         * Even if a wholesale user is set to use a certain shipping method, but if that wholesale user did not meet
         * the requirement of that shipping method, that shipping method will not be applied to that wholesale user,
         * Rather it will use whatever shipping method woocommerce deemed appropriate.
         *
         * Also shipping methods does not need to be enabled. If a shipping method is disabled, and is used on the mapping,
         * it will still work. However the validation and qualification of each shipping method will still be implemented.
         *
         *
         * Now for this shipping add-on
         * WooCommerce Table Rate Shipping
         * By Mike Jolley
         * ----------
         * There are 2 types of behaviour:
         *
         * Behaviour A:
         * Blank shipping zone and shipping zone method. On this case, we will enforce using the table rate shipping, but
         * lets this plugin decide what shipping zone and shipping zone method to use.
         *
         * Behaviour B:
         * Allows user to set shipping zone and shipping zone method.
         *
         *
         * Luckily for this add-on
         * WooCommerce Table Rate Shipping ( Code Canyon Version )
         * By Erica Dion
         * ----------
         * Only behaviour A.
         *
         *
         *
         * Table Rate Shipping Plus ( Mango Hour )
         * This plugin behaviour is different among the code canyon and woo themes versions.
         * Instead it has shipping zones and shipping services that is independent to each other.
         * Then table rates or rates are created in combination of the 2 ( zone and service )
         * Also it has no is_available method to check if the current package qualifies for this shipping method
         * It determines qualification directly on the calculate_shipping method
         * Due to this, users are required to specify shipping zone and shipping service during mapping.
         *
         * Note: same as above, even if user set a specific mapping, but the package does not qualify for the mapping set,
         * the mapping will not be followed.
         *
         * This plugin does not need to be enabled to be mapped.
         *
         *
         * @param $packageRates
         * @param $package
         * @param $userWholesaleRole
         * @return array
         *
         * @since 1.0.3
         */
        public function applyAppropriateShippingMethod ( $packageRates , $package , $userWholesaleRole ) {

            // Get all site's shipping methods
            $wcShippingMethods = WC_Shipping::instance()->load_shipping_methods();

            $WS_SM_Mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING );
            if ( !is_array( $WS_SM_Mapping ) )
                $WS_SM_Mapping = array();

            if ( !empty( $userWholesaleRole ) ) {

                $hasMapping = false;

                if ( !empty( $WS_SM_Mapping ) ) {

                    $WS_SM_Mapping = $this->_filterTableRatesOnShippingMapping( $this->checkTableRateShippingType() , $WS_SM_Mapping );

                    foreach ( $WS_SM_Mapping as $savedMapping ) {

                        if ( $userWholesaleRole[ 0 ] == $savedMapping[ 'wholesale_role' ] &&  array_key_exists( $savedMapping[ 'shipping_method' ] , $wcShippingMethods ) ) {

                            $hasMapping = true;

                            if ( strpos( $savedMapping[ 'shipping_method' ] , 'table_rate' ) !== false && $savedMapping[ 'shipping_method' ] != 'mh_wc_table_rate_plus' ) {

                                // For backwards compatibility ( Previous versions has no notion of shipping zone and shipping zone method )
                                if ( !array_key_exists( 'shipping_zone' , $savedMapping ) ||
                                     $savedMapping[ 'shipping_zone' ] == '' ) {

                                    // We use whatever table rate shipping woocommerce have calculated
                                    // its on the $packageRates parameter
                                    foreach ( $packageRates as $key => $val ) {

                                        if ( strpos( $key , 'table_rate' ) !== false )
                                            $filteredShippingMethods[ $key ] = $val;

                                    }

                                } elseif ( $this->checkTableRateShippingType() == 'code_canyon' ) {

                                    // Get the appropriate shipping zone table rates
                                    $all_shipping_zone_table_rates = $this->wwppGetAllShippingZoneTableRates( $savedMapping[ 'shipping_zone' ] , false );
                                    $shipping_zone_table_rates = $all_shipping_zone_table_rates[ "shipping_zone_table_rates" ];

                                    if ( $savedMapping[ "shipping_zone_table_rate" ] != "" ) {

                                        $temp_arr = array();
                                        foreach ( $shipping_zone_table_rates as $zone_rate ) {

                                            if ( $zone_rate[ 'identifier' ] == $savedMapping[ 'shipping_zone_table_rate' ] )
                                                $temp_arr[] = $zone_rate;

                                        }

                                        $shipping_zone_table_rates = $temp_arr;

                                    }

                                    $sm = new $wcShippingMethods[ $savedMapping[ 'shipping_method' ] ];
                                    $sm->enabled = 'yes';

                                    $sm->calculate_shipping( $package );

                                    if ( !empty( $sm->rates ) && is_array( $sm->rates ) ) {

                                        foreach ( $sm->rates as $rate ) {

                                            foreach ( $shipping_zone_table_rates as $map_rate ) {

                                                if ( $rate->id == 'table_rate_shipping_' . $map_rate[ 'identifier' ] )
                                                    $filteredShippingMethods[ $rate->id ] = $rate;

                                            }

                                        }

                                    }

                                } elseif ( $this->checkTableRateShippingType() == 'woo_themes' ) {

                                    // We need to get the shipping zone that this order qualifies for
                                    // "wc_get_shipping_zone" returns a WC_Shipping_Zone object.
                                    $defaultComputedShippingZone = wc_get_shipping_zone( $package );

                                    if ( $defaultComputedShippingZone->zone_id == $savedMapping[ 'shipping_zone' ] ) {

                                        $class_callback = 'woocommerce_get_shipping_method_table_rate';
                                        $shippingZoneMethod = call_user_func( $class_callback , $savedMapping[ 'shipping_zone_method' ] );
                                        $shippingZoneMethod->enabled = 'yes';

                                        // Check if it meets the requirements
                                        if ( $shippingZoneMethod->is_available( $package ) ) {

                                            $shippingZoneMethod->get_rates( $package );
                                            $shippingZoneMethod->calculate_shipping( $package );

                                            if ( !empty( $shippingZoneMethod->rates ) && is_array( $shippingZoneMethod->rates ) ) {

                                                foreach( $shippingZoneMethod->rates as $rate )
                                                    $filteredShippingMethods[ $rate->id ] = $rate;

                                            }

                                        }

                                    }

                                }

                            } elseif ( $savedMapping[ 'shipping_method' ] == 'mh_wc_table_rate_plus' ) {

                                // Table Rate Shipping Plus ( Mango Hour Version ) is different.
                                // It has no is_available method that is usually used to check if a package qualifies for this current shipping method
                                // Instead it determines the qualification of the package itself directly during the calculate_shipping method
                                // So that's why we trigger the calculate_shipping method directly without executing is_available function
                                $sm = new $wcShippingMethods[ $savedMapping[ 'shipping_method' ] ];
                                $sm->enabled = 'yes';

                                $sm->calculate_shipping( $package );

                                if ( !empty( $sm->rates ) && is_array( $sm->rates ) ) {

                                    // Get all table rates
                                    $all_table_rates = get_option( 'mh_wc_table_rate_plus_table_rates' , array() );

                                    // Extract the id of all table rates that passed the mapping requirements
                                    $passed_table_rate_ids = array();
                                    foreach ( $all_table_rates as $rate ) {

                                        if ( $rate[ 'zone' ] == $savedMapping[ 'shipping_zone' ] && $rate[ 'service' ] == $savedMapping[ 'shipping_service' ] )
                                            $passed_table_rate_ids[] = $rate[ 'id' ];

                                    }

                                    foreach ( $sm->rates as $rate ) {

                                        foreach ( $passed_table_rate_ids as $rate_id ) {

                                            if ( $rate->id == ( 'mh_wc_table_rate_plus_' . $rate_id ) )
                                                $filteredShippingMethods[ $rate->id ] = $rate;

                                        }

                                    }

                                }

                            } else {

                                // We create an instance and explicitly set enabled to yes
                                // The idea is we need to use is_available function to validate if wholesale user
                                // qualifies for this shipping method. Unfortunately is_available returns false if
                                // shipping method is disabled. So we manually tick this as yes.
                                $sm = new $wcShippingMethods[ $savedMapping[ 'shipping_method' ] ];
                                $sm->enabled = 'yes';

                                if ( $sm->is_available( $package ) ) {

                                    $sm->calculate_shipping( $package );

                                    if ( !empty( $sm->rates ) && is_array( $sm->rates ) ) {

                                        foreach ( $sm->rates as $rate )
                                            $filteredShippingMethods[ $rate->id ] = $rate;

                                    }

                                }

                            }

                        }

                    }

                    if ( !empty( $filteredShippingMethods ) )
                        $packageRates = $filteredShippingMethods;

                }

                if ( ( empty( $WS_SM_Mapping ) || !$hasMapping ) && get_option( 'wwpp_settings_wholesale_users_use_free_shipping' ) == 'yes' ) {

                    // If setting for making all wholesale users have free shipping
                    // Note: we will still half to check if the customer qualifies for the free shipping requirements
                    // Note: The times this is executed are:
                    // 1.) No mapping set ( Empty mapping ) or
                    // 2.) No mapping set for this specific wholesale role ( There is a mapping set but none for this wholesale role )
                    // If there is a mapping set for this specific role, but the package didn't meet the requirement
                    // It will not automatically fallback here, instead it will use whatever woocommerce generated as appropriate shipping method ( specifically rates )

                    $sm = new $wcShippingMethods[ 'free_shipping' ];
                    $sm->enabled = 'yes';

                    if ( $sm->is_available( $package ) ) {

                        $sm->calculate_shipping();

                        if ( !empty( $sm->rates ) && is_array( $sm->rates ) ) {

                            $packageRates = array();

                            foreach ( $sm->rates as $rate )
                                $packageRates[ $rate->id ] = $rate;

                        }

                    }

                }

            }

            return $packageRates;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | AJAX callbacks
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Check if a wholesale role / shipping method mapping is unique.
         *
         * @param $mapping
         * @param $index
         * @return bool
         *
         * @since 1.3.0
         */
        private function _checkIfMappingIsUnique ( $mapping , $index = null ) {

            $savedMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING );
            if ( !is_array( $savedMapping ) )
                $savedMapping = array();

            foreach ( $savedMapping as $idx => $smap ) {

                if ( $mapping[ "wholesale_role" ] == $smap[ "wholesale_role" ] &&
                     $mapping[ "shipping_method" ] == $smap[ "shipping_method" ] &&
                     $mapping[ "shipping_zone" ] == $smap[ "shipping_zone" ] &&
                     $mapping[ "shipping_zone_method" ] == $smap[ "shipping_zone_method" ] &&
                     $mapping[ "shipping_zone_table_rate" ] ==  $smap[ "shipping_zone_table_rate" ] &&
                     $mapping[ "shipping_service" ] == $smap[ "shipping_service" ] ) {

                    if ( is_null( $index ) )
                        return false;
                    elseif ( $idx != $index )
                        return false;

                }

            }

            return true;

        }

        /**
         * Add wholesale role / shipping method mapping.
         *
         * @param null $mapping
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.0.3
         */
        public function wwppAddWholesaleRoleShippingMethodMapping( $mapping = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $mapping = $_POST[ 'mapping' ];

            $savedMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING );
            if ( !is_array( $savedMapping ) )
                $savedMapping = array();

            if ( !$this->_checkIfMappingIsUnique( $mapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Duplicate mapping. Mapping already exist'
                            );

            } else {

                $savedMapping[] = $mapping;
                update_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING , $savedMapping );

                $arr_keys = array_keys( $savedMapping );
                $latestIndex = end( $arr_keys );

                $response = array(
                                'status'        =>  'success',
                                'latest_index'  =>  $latestIndex
                            );

            }

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Edit wholesale role / shipping method mapping.
         *
         * @param null $mapping
         * @param null $index
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.0.3
         */
        public function wwppEditWholesaleRoleShippingMethodMapping ( $index = null, $mapping = null , $ajaxCall = true ) {

            if ( $ajaxCall === true ) {
                $index = trim( $_POST[ 'index' ] );
                $mapping = $_POST[ 'mapping' ];
            }

            $savedMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING );
            if ( !is_array( $savedMapping ) )
                $savedMapping = array();

            if ( !array_key_exists( $index , $savedMapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Mapping to be edited does not exist'
                            );

            } elseif( !$this->_checkIfMappingIsUnique( $mapping , $index ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Edited mapping data causes duplicate entry'
                            );

            } else {

                $savedMapping[ $index ] = $mapping;
                update_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING , $savedMapping );
                $response = array( 'status' =>  'success' );

            }

            if ( $ajaxCall === true ) {

                header('Content-Type: application/json'); // specify we return json
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Delete a wholesale role / shipping method mapping.
         *
         * @param null $index
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.0.3
         */
        public function wwppDeleteWholesaleRoleShippingMethodMapping ( $index = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $index = trim( $_POST[ 'index' ] );

            $savedMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING );
            if ( !is_array( $savedMapping ) )
                $savedMapping = array();

            // Wholesale role to be deleted does not exist.
            if ( !array_key_exists( $index , $savedMapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Mapping to be deleted does not exist'
                            );

            } else {

                unset( $savedMapping[ $index ] );
                update_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING , $savedMapping );
                $response = array( 'status' => 'success' );

            }

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Get all shipping zones.
         *
         * @param null $dummyArg
         * @param bool|true $ajaxCall
         * @return mixed
         *
         * @since 1.3.0
         */
        public function wwppGetAllShippingZones ( $dummyArg = null , $ajaxCall = true ) {

            global $wpdb;

            $results = $wpdb->get_results( "
                                            SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones
                                            ORDER BY zone_order ASC
                                            " );

            // Defaults
            $default               = new stdClass();
            $default->zone_id      = 0;
            $default->zone_name    = __( 'Default Zone (everywhere else)', 'woocommerce-wholesale-prices-premium' );
            $default->zone_type    = __( 'All countries', 'woocommerce-wholesale-prices-premium' );
            $default->zone_enabled = 1;
            $results[]         = $default;

            if ( $ajaxCall === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $results );
                die();

            } else
                return $results;

        }

        /**
         * Get all shipping method per shipping zone.
         *
         * @param null $shippingZoneID
         * @param bool|true $ajaxCall
         * @return array
         *
         * @since 1.3.0
         */
        public function wwppGetAllShippingZoneMethods ( $shippingZoneID = null , $ajaxCall = true ) {

            woocommerce_init_shipping_table_rate();

            global $wpdb;

            if ( $ajaxCall === true )
                $shippingZoneID = $_POST[ 'shippingZoneID' ];

            $raw_shipping_methods = $wpdb->get_results( $wpdb->prepare( "
                                                                        SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_shipping_methods
                                                                        WHERE zone_id = %s
                                                                        ORDER BY `shipping_method_order` ASC
                                                                    ", $shippingZoneID ) );

            $processed_shipping_methods = array();
            foreach ( $raw_shipping_methods as $method ) {

                $class_callback = 'woocommerce_get_shipping_method_' . $method->shipping_method_type;

                if ( function_exists( $class_callback ) ) {

                    $item = call_user_func( $class_callback, $method->shipping_method_id );
                    $item->shipping_method_id = $method->shipping_method_id;
                    $processed_shipping_methods[] = $item;

                }

            }

            if ( $ajaxCall === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $processed_shipping_methods );
                die();

            } else
                return $processed_shipping_methods;

        }

        /**
         * Get all table rates of a specified shipping zone ( Code Canyon ).
         *
         * @since 1.6.0
         * @param null $shippingZoneID
         * @param bool|true $ajaxCall
         * @return array
         */
        public function wwppGetAllShippingZoneTableRates ( $shippingZoneID = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $shippingZoneID = $_POST[ 'shippingZoneID' ];

            $shippingZoneID = sanitize_text_field( $shippingZoneID );

            $cc_shipping_zone_table_rates = get_option( 'woocommerce_table_rates' , array() );

            $filtered_zone_table_rates = array();
            foreach ( $cc_shipping_zone_table_rates as $zone_rate ) {

                if ( $zone_rate[ 'zone' ] == $shippingZoneID ) {

                    $dup = false;
                    foreach ( $filtered_zone_table_rates as $filtered_rate ) {

                        if ( $filtered_rate[ 'identifier' ] == $zone_rate[ 'identifier' ] ) {

                            $dup = true;
                            break;

                        }

                    }

                    if ( !$dup )
                        $filtered_zone_table_rates[] = $zone_rate;

                }

            }

            $response = array(
                            'status'                    =>  'success',
                            'shipping_zone_table_rates' =>  $filtered_zone_table_rates
                        );

            if ( $ajaxCall === true ) {

                header( "Content-Type:application/json" );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Helpers
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Check what type of table rate shipping plugin is installed.
         *
         * @return bool|string
         *
         * @since 1.3.0
         */
        public function checkTableRateShippingType () {

            if ( in_array( 'woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

                if ( class_exists( 'BE_Table_Rate_Shipping' ) )
                    return 'code_canyon';
                elseif( class_exists( 'WC_Shipping_Table_Rate' ) )
                    return 'woo_themes';

            } elseif ( in_array( 'mh-woocommerce-table-rate-shipping-plus/mh-wc-table-rate-plus.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

                return 'mango_hour';

            }

            return false;

        }

        /**
         * Filter table rates on shipping mapping to trim off mappings that are already under the scope of other mappings.
         *
         * @since 1.6.0
         * @param string $table_rate_type
         * @param array $shipping_mapping
         * @return array
         */
        private function _filterTableRatesOnShippingMapping( $table_rate_type = '' , $shipping_mapping = array() ) {

            if ( $table_rate_type == 'code_canyon' ) {

                // We allow mapping on 3 levels of specificity for table rate shipping code canyon version
                // 1. Wholesale Role | Shipping Method
                // 2. Wholesale Role | Shipping Method | Shipping Zone
                // 3. Wholesale Role | Shipping Method | Shipping Zone | Shipping Zone Table Rate
                // We need a way to filter off mappings that are already under the scope of an existing mapping
                // Ex. if mapping type 1. is present, then 2. and 3. should be remove coz its under the scoper of mapping 1.

                // Check if a level 1 mapping is present
                $has_level_1_mapping = false;
                foreach ( $shipping_mapping as $mapping ) {

                    if ( $mapping[ 'shipping_method' ] == 'table_rate_shipping' && $mapping['shipping_zone' ] == "" ) {

                        $has_level_1_mapping == true;
                        break;

                    }

                }

                if ( $has_level_1_mapping ) {

                    // Trim off level 2 and 3 mapping coz level 1 mapping is present
                    $filtered_shipping_mapping = array();
                    foreach ( $shipping_mapping as $mapping ) {

                        if ( $mapping[ 'shipping_method' ] == 'table_rate_shipping' && $mapping['shipping_zone' ] != "" )
                            break;

                        $filtered_shipping_mapping[] = $mapping;

                    }

                    return $filtered_shipping_mapping;

                } else {

                    // Check if a level 2 mapping is present
                    $has_level_2_mapping = false;
                    foreach ( $shipping_mapping as $mapping ) {

                        if ( $mapping[ 'shipping_method' ] == 'table_rate_shipping' && $mapping[ 'shipping_zone' ] != "" && $mapping[ 'shipping_zone_table_rate' ] == "" ) {

                            $has_level_2_mapping == true;
                            break;

                        }

                    }

                    if ( $has_level_2_mapping ) {

                        // Trim off level 3 mappings
                        $filtered_shipping_mapping = array();
                        foreach ( $shipping_mapping as $mapping ) {

                            if ( $mapping[ 'shipping_method' ] == 'table_rate_shipping' && $mapping[ 'shipping_zone' ] != "" && $mapping[ 'shipping_zone_table_rate' ] != "" )
                                continue;

                            $filtered_shipping_mapping[] = $mapping;

                        }

                        return $filtered_shipping_mapping;

                    }

                }

            }

            return $shipping_mapping;

        }

    }

}