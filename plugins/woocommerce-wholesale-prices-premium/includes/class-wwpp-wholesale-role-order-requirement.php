<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_Order_Requirement' ) ) {

    /**
     * Wholesale role order requirement controller.
     *
     * @since 1.5.0
     */
    class WWPP_Wholesale_Role_Order_Requirement {

        /*
         |--------------------------------------------------------------------------------------------------------------
         | Class Members
         |--------------------------------------------------------------------------------------------------------------
         */
        private static $_instance;




        /*
         |--------------------------------------------------------------------------------------------------------------
         | Mesc Functions
         |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Class constructor.
         *
         * @since 1.5.0
         */
        public function __construct() {}

        /**
         * Singleton Pattern.
         *
         * @since 1.5.0
         * @return WWPP_Wholesale_Role_Order_Requirement
         */
        public static function get_instance() {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self;

            return self::$_instance;

        }




        /*
         |--------------------------------------------------------------------------------------------------------------
         | AJAX Interfaces
         |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Add an entry to wholesale role / order requirement mapping.
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * minimum_order_quantity
         * minimum_order_subtotal
         * minimum_order_logic
         *
         * @since 1.5.0
         * @param null $mapping Array. Entry of order requirement for wholesale role.
         * @param bool|true $ajax_call Parameter to check if function is called via ajax or not.
         * @return array Transaction response which determines the outcome of the operation of this function.
         */
        public function wwpp_add_wholesale_role_order_requirement( $mapping = null , $ajax_call = true ) {

            if ( $ajax_call === true )
                $mapping = $_POST[ 'mapping' ];

            $order_requirement_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , array() );
            if ( !is_array( $order_requirement_mapping ) )
                $order_requirement_mapping = array();

            if ( array_key_exists( $mapping[ 'wholesale_role' ] , $order_requirement_mapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  __( 'Duplicate Wholesale Role Order Requirement Entry, Already Exist' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $order_requirement_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , $order_requirement_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( $ajax_call === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Edit an entry of wholesale role / order requirement mapping.
         *
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * minimum_order_quantity
         * minimum_order_subtotal
         * minimum_order_logic
         *
         * @since 1.5.0
         * @param null $mapping Array. Entry of order requirement for wholesale role.
         * @param bool|true $ajax_call Parameter to check if function is called via ajax or not.
         * @return array Transaction response which determines the outcome of the operation of this function.
         */
        public function wwpp_edit_wholesale_role_order_requirement( $mapping = null , $ajax_call = true ) {

            if ( $ajax_call === true )
                $mapping = $_POST[ 'mapping' ];

            $order_requirement_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , array() );
            if ( !is_array( $order_requirement_mapping ) )
                $order_requirement_mapping = array();

            if ( !array_key_exists( $mapping[ 'wholesale_role' ] , $order_requirement_mapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  __( 'Wholesale Role Order Requirement Entry You Wish To Edit Does Not Exist' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $order_requirement_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , $order_requirement_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( $ajax_call === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Delete an entry of wholesale role / order requirement mapping.
         *
         * @since 1.5.0
         * @param null $wholesale_role
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwpp_delete_wholesale_role_order_requirement( $wholesale_role = null , $ajax_call = true ) {

            if ( $ajax_call === true )
                $wholesale_role = $_POST[ 'wholesale_role' ];

            $order_requirement_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , array() );
            if ( !is_array( $order_requirement_mapping ) )
                $order_requirement_mapping = array();

            if ( !array_key_exists( $wholesale_role , $order_requirement_mapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Wholesale Role Order Requirement Entry You Wish To Delete Does Not Exist'
                            );

            } else {

                unset( $order_requirement_mapping[ $wholesale_role ] );

                update_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , $order_requirement_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( $ajax_call === true ) {

                header( "Content-Type: application/json" );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

    }

}