<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( 'WWPP_Payment_Gateways' ) ) {

    class WWPP_Payment_Gateways {

        private static $_instance;

        public static function getInstance () {

            if (!self::$_instance instanceof self)
                self::$_instance = new self;

            return self::$_instance;

        }

        /**
         * Apply custom payment gateway surcharge.
         *
         * @param $wc_cart
         * @param $userWholesaleRole
         *
         * @since 1.3.0
         */
        public function applyPaymentGatewaySurcharge ( $wc_cart , $userWholesaleRole ) {

            if ( is_admin() && ! defined( 'DOING_AJAX' ) )
                return;

            if ( empty( $userWholesaleRole ) )
                return;

            $paymentGatewaySurcharge = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
            if ( !is_array( $paymentGatewaySurcharge ) )
                $paymentGatewaySurcharge = array();

            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            if ( !is_array( $available_gateways ) )
                $available_gateways = array();

            if ( ! empty( $available_gateways ) ) {

                // Chosen Method
                if ( isset( WC()->session->chosen_payment_method ) && isset( $available_gateways[ WC()->session->chosen_payment_method ] ) )
                    $current_gateway = $available_gateways[ WC()->session->chosen_payment_method ];
                elseif ( isset( $available_gateways[ get_option( 'woocommerce_default_gateway' ) ] ) )
                    $current_gateway = $available_gateways[ get_option( 'woocommerce_default_gateway' ) ];
                else
                    $current_gateway =  current( $available_gateways );

                foreach ( $paymentGatewaySurcharge as $mapping ) {


                    if ( $mapping[ 'wholesale_role' ] == $userWholesaleRole[ 0 ] && $mapping[ 'payment_gateway' ] == $current_gateway->id ) {

                        if ( $mapping[ 'surcharge_type' ] == 'percentage' )
                            $surcharge = round( ( ( WC()->cart->cart_contents_total + WC()->cart->shipping_total ) * $mapping[ 'surcharge_amount' ] ) / 100 , 2 );
                        else
                            $surcharge = $mapping[ 'surcharge_amount' ];

                        $taxable = ( $mapping[ 'taxable' ] == 'yes' ) ? true : false;

                        WC()->cart->add_fee( $mapping[ 'surcharge_title' ] , $surcharge , $taxable , '' );

                    }

                }

            }

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

            if ( $fee->taxable )
                $cart_totals_fee_html .= ' <small>' . WC()->countries->inc_tax_or_vat() . '</small>';

            return $cart_totals_fee_html;

        }

        /**
         * Filter payment gateway to be available to certain wholesale role.
         * Note: payment gateway not need to be enabled.
         *
         * @param $availableGateways
         * @param $userWholesaleRole
         * @return array
         *
         * @since 1.3.0
         */
        public function filterAvailablePaymentGateways ( $availableGateways , $userWholesaleRole ) {

            $wholesaleRolePaymentGatewayMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wholesaleRolePaymentGatewayMapping ) )
                $wholesaleRolePaymentGatewayMapping = array();

            if ( current_user_can( 'manage_options' ) || empty( $userWholesaleRole ) || empty( $wholesaleRolePaymentGatewayMapping ) )
                return $availableGateways;
            
            $allPaymentGateways = WC()->payment_gateways->payment_gateways();

            $filtered_gateways = array();

            foreach ( $wholesaleRolePaymentGatewayMapping as $wholesaleRoleKey => $paymentGateways ) {

                if ( $wholesaleRoleKey != $userWholesaleRole[ 0 ] ) continue;

                foreach ( $allPaymentGateways as $gateway ) {

                    foreach ( $paymentGateways as $pg ) {

                        if ( $pg[ 'id' ] == $gateway->id )
                            $filtered_gateways[ $pg[ 'id' ] ] = $gateway;

                    }

                }

            }

            WC()->payment_gateways()->set_current_gateway( $filtered_gateways );

            return $filtered_gateways;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | AJAX Call Handlers
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add wholesale role / payment gateway mapping.
         *
         * @param null $mapping
         * @param bool|true $ajaxCall
         * @return array
         *
         * @since 1.3.0
         */
        public function wwppAddWholesaleRolePaymentGatewayMapping ( $mapping = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $mapping = $_POST[ 'mapping' ];

            $wrpgMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wrpgMapping ) )
                $wrpgMapping = array();

            if ( array_key_exists( $mapping[ 'wholesale_role' ] , $wrpgMapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Wholesale role you wish to add payment gateway mapping already exist'
                            );

            } else {

                $wrpgMapping[ $mapping[ 'wholesale_role' ] ] = $mapping[ 'payment_gateways' ];
                update_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING , $wrpgMapping );
                $response = array( 'status' => 'success' );

            }

            if ( $ajaxCall === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Update wholesale role / payment gateway mapping.
         *
         * @param null $mapping
         * @param bool|true $ajaxCall
         * @return array
         *
         * @since 1.3.0
         */
        public function wwppUpdateWholesaleRolePaymentGatewayMapping ( $mapping = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $mapping = $_POST[ 'mapping' ];

            $wrpgMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wrpgMapping ) )
                $wrpgMapping = array();

            if ( !array_key_exists( $mapping[ 'wholesale_role' ] , $wrpgMapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Wholesale Role / Payment Gateway mapping you wish to edit does not exist on record'
                            );

            } else {

                $wrpgMapping[ $mapping[ 'wholesale_role' ] ] = $mapping[ 'payment_gateways' ];
                update_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING , $wrpgMapping );
                $response = array( 'status' => 'success' );

            }

            if ( $ajaxCall === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Delete wholesale role / payment gateway method.
         *
         * @param null $wholesaleRoleKey
         * @param bool|true $ajaxCall
         * @return array
         *
         * @since 1.3.0
         */
        public function wwppDeleteWholesaleRolePaymentGatewayMapping ( $wholesaleRoleKey = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $wholesaleRoleKey = $_POST[ 'wholesaleRoleKey' ];

            $wrpgMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wrpgMapping ) )
                $wrpgMapping = array();

            if ( !array_key_exists( $wholesaleRoleKey , $wrpgMapping ) ) {

                $response = array(
                    'status'        =>  'fail',
                    'error_message' =>  'Wholesale Role / Payment Gateway mapping you wish to delete does not exist on record'
                );

            } else {

                unset( $wrpgMapping[ $wholesaleRoleKey ] );
                update_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING , $wrpgMapping );
                $response = array( 'status' =>  'success' );

            }

            if ( $ajaxCall === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Add payment gateway surcharge to a wholesale role.
         * $surchargeData parameter is expected to be an array with the keys below.
         *
         * wholesale_role
         * payment_gateway
         * surcharge_title
         * surcharge_type
         * surcharge_amount
         * taxable
         *
         * @param null $surchargeData
         * @param bool $ajaxCall
         * @return array
         *
         * @since 1.3.0
         */
        public function wwppAddPaymentGatewaySurcharge ( $surchargeData = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $surchargeData = $_POST[ 'surchargeData' ];

            $surchargeMapping = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
            if ( !is_array( $surchargeMapping ) )
                $surchargeMapping = array();

            $surchargeMapping[] = $surchargeData;

            update_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING , $surchargeMapping );

            $arr_keys = array_keys( $surchargeMapping );
            $latestIndex = end( $arr_keys );

            $response = array(
                            'status'        =>  'success',
                            'latest_index'  =>  $latestIndex
                        );

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Update payment gateway surcharge for a wholesale role.
         *
         * @param null $idx
         * @param null $surchargeData
         * @param bool $ajaxCall
         * @return array
         *
         * @since 1.3.0
         */
        public function wwppUpdatePaymentGatewaySurcharge ( $idx = null , $surchargeData = null , $ajaxCall = true ) {

            if ( $ajaxCall === true ) {

                $idx = $_POST[ 'idx' ];
                $surchargeData = $_POST[ 'surchargeData' ];

            }

            $surchargeMapping = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
            if ( !is_array( $surchargeMapping ) )
                $surchargeMapping = array();

            if ( !array_key_exists( $idx , $surchargeMapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Payment gateway surcharge mapping you wish to update does not exist on record'
                            );

            } else {

                $surchargeMapping[ $idx ] = $surchargeData;
                update_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING , $surchargeMapping );

                $response = array(
                                'status'    =>  'success'
                            );

            }

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Delete payment gateway surcharge of a wholesale user.
         *
         * @param null $idx
         * @param bool $ajaxCall
         * @return array
         *
         * @since 1.3.0
         */
        public function wwppDeletePaymentGatewaySurcharge ( $idx = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $idx = $_POST[ 'idx' ];

            $surchargeMapping = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
            if ( !is_array( $surchargeMapping ) )
                $surchargeMapping = array();

            if ( !array_key_exists( $idx , $surchargeMapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Payment gateway surcharge you want to delete does not exist on record'
                            );

            } else {

                unset( $surchargeMapping[ $idx ] );
                update_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING , $surchargeMapping );

                $response = array(
                                'status'    =>  'success'
                            );

            }

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

    }

}