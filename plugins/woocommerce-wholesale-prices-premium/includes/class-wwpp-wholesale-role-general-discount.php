<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_General_Discount' ) ) {

    class WWPP_Wholesale_Role_General_Discount {

        private static $_instance;

        public static function getInstance() {

            if(!self::$_instance instanceof self)
                self::$_instance = new self;
            return self::$_instance;

        }

        /**
         * Add wholesale role / general discount mapping.
         * $discountMapping variable is expected to be an array with the following keys.
         * wholesale_role
         * general_discount
         *
         * @param null $discountMapping
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.2.0
         */
        public function wwppAddWholesaleRoleGeneralDiscountMapping ( $discountMapping = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $discountMapping = $_POST[ 'discountMapping' ];

            $savedDiscountMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $savedDiscountMapping ) )
                $savedDiscountMapping = array();

            if ( !array_key_exists( $discountMapping[ 'wholesale_role' ] , $savedDiscountMapping ) )
                $savedDiscountMapping[ $discountMapping[ 'wholesale_role' ] ] = $discountMapping[ 'general_discount' ];
            else {

                if ( $ajaxCall === true ) {

                    header('Content-Type: application/json');
                    echo json_encode(array(
                        'status'        =>  'fail',
                        'error_message' =>  'Duplicate Entry, Entry Already Exists'
                    ));
                    die();

                } else
                    return false;

            }

            update_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , $savedDiscountMapping );

            if ( $ajaxCall === true ) {

                header('Content-Type: application/json');
                echo json_encode(array(
                    'status'    =>  'success',
                ));
                die();

            } else
                return true;

        }

        /**
         * Edit saved wholesale role / general discount mapping.
         *
         * @param null $discountMapping
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.2.0
         */
        public function wwppEditWholesaleRoleGeneralDiscountMapping ( $discountMapping = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $discountMapping = $_POST[ 'discountMapping' ];

            $savedDiscountMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $savedDiscountMapping ) )
                $savedDiscountMapping = array();

            if ( array_key_exists( $discountMapping[ 'wholesale_role' ] , $savedDiscountMapping ) )
                $savedDiscountMapping[ $discountMapping[ 'wholesale_role' ] ] = $discountMapping[ 'general_discount' ];
            else {

                if ( $ajaxCall === true ) {

                    header('Content-Type: application/json');
                    echo json_encode(array(
                        'status'        =>  'fail',
                        'error_message' =>  'Entry to be edited does not exist'
                    ));
                    die();

                } else
                    return false;

            }

            update_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , $savedDiscountMapping );

            if ( $ajaxCall === true ) {

                header('Content-Type: application/json');
                echo json_encode(array(
                    'status'    =>  'success',
                ));
                die();

            } else
                return true;

        }

        /**
         * Delete a wholesale role / general discount mapping entry.
         *
         * @param null $wholesaleRole
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.2.0
         */
        public function wwppDeleteWholesaleRoleGeneralDiscountMapping ( $wholesaleRole = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $wholesaleRole = $_POST[ 'wholesaleRole' ];

            $savedDiscountMapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $savedDiscountMapping ) )
                $savedDiscountMapping = array();

            if ( array_key_exists( $wholesaleRole , $savedDiscountMapping ) )
                unset( $savedDiscountMapping[ $wholesaleRole ] );
            else {

                if ( $ajaxCall === true ) {

                    header('Content-Type: application/json');
                    echo json_encode(array(
                        'status'        =>  'fail',
                        'error_message' =>  'Entry to be deleted does not exist'
                    ));
                    die();

                } else
                    return false;

            }

            update_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , $savedDiscountMapping );

            if ( $ajaxCall === true ) {

                header('Content-Type: application/json');
                echo json_encode(array(
                    'status'    =>  'success',
                ));
                die();

            } else
                return true;

        }

    }

}