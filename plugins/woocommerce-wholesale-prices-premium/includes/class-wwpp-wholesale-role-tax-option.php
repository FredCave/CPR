<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_Tax_Option' ) ) {

    /**
     * Wholesale role tax option controller.
     *
     * @class WWPP_Wholesale_Role_Tax_Option
     */
    class WWPP_Wholesale_Role_Tax_Option {

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
         * @return WWPP_Wholesale_Role_Tax_Option
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
         * Add an entry to wholesale role / tax option mapping.
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * tax_exempted
         *
         * @since 1.4.7
         * @param null $mapping
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwpp_add_wholesale_role_tax_option( $mapping = null , $ajax_call = true ) {

            if ( $ajax_call === true )
                $mapping = $_POST[ 'mapping' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( array_key_exists( $mapping[ 'wholesale_role' ] , $tax_option_mapping ) ) {

                $response = array(
                    'status'        =>  'fail',
                    'error_message' =>  __( 'Duplicate Wholesale Role Tax Option Entry, Already Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $tax_option_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

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
         * Edit an entry of wholesale role / tax option mapping.
         *
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * tax_exempted
         *
         * @since 1.4.7
         * @param null $mapping
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwpp_edit_wholesale_role_tax_option( $mapping = null , $ajax_call = true ) {

            if ( $ajax_call === true )
                $mapping = $_POST[ 'mapping' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( !array_key_exists( $mapping[ 'wholesale_role' ] , $tax_option_mapping ) ) {

                $response = array(
                    'status'        =>  'fail',
                    'error_message' =>  __( 'Wholesale Role Tax Option Entry You Wish To Edit Does Not Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $tax_option_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

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
         * Delete an entry of wholesale role / tax option mapping.
         *
         * @since 1.4.7
         * @param null $wholesale_role
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwpp_delete_wholesale_role_tax_option( $wholesale_role = null , $ajax_call = true ) {

            if ( $ajax_call === true )
                $wholesale_role = $_POST[ 'wholesale_role' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( !array_key_exists( $wholesale_role , $tax_option_mapping ) ) {

                $response = array(
                    'status'        =>  'fail',
                    'error_message' =>  'Wholesale Role Tax Option Entry You Wish To Delete Does Not Exist'
                );

            } else {

                unset( $tax_option_mapping[ $wholesale_role ] );

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

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