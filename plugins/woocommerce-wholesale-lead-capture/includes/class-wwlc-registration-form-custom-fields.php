<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_Registration_Form_Custom_Fields' ) ) {

    class WWLC_Registration_Form_Custom_Fields {

        private static $_instance;

        /**
         * Class constructor.
         *
         * @since 1.0.0
         */
        public function __construct() {

            // Initialization stuff ...

        }

        /**
         * Singleton Pattern.
         *
         * @return WWLC_Registration_Form_Custom_Fields
         * @since 1.0.0
         */
        public static function getInstance() {

            if ( ! self::$_instance instanceof self ) {
                self::$_instance = new self;
            }

            return self::$_instance;

        }

        /**
         * Save registration form custom field. $customField expected to have the following keys
         * field_name
         * field_id
         * field_type
         * field_order
         * required
         * attributes
         * options
         *
         * @param null $customField
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.1.0
         */
        public function wwlc_addRegistrationFormCustomField( $customField = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $customField = $_POST[ 'customField' ];

            $field_id = $customField[ 'field_id' ];
            unset( $customField[ 'field_id' ] );

            $field_id = str_replace( 'wwlc_cf_' , '' , $field_id );
            $field_id = 'wwlc_cf_' . $field_id;

            if ( !ctype_alnum( str_replace( '_' , '' , $field_id ) ) ) {

                if ( $ajaxCall === true ) {

                    header( 'Content-Type: application/json' ); // specify we return json
                    echo json_encode( array(
                        'status'        =>  'fail',
                        'error_message' =>  sprintf( __( 'Field id %1$s contains none alpha numeric character/s' , 'woocommerce-wholesale-lead-capture' ) , $field_id )
                    ) );
                    die();

                } else
                    return false;

            }

            $customField[ 'field_order' ] = str_replace( array( '.' , ',' ) , '' , $customField[ 'field_order' ] );
            if ( !is_numeric( $customField[ 'field_order' ] ) )
                $customField[ 'field_order' ] = 0;

            $registrationFormCustomFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registrationFormCustomFields ) )
                $registrationFormCustomFields = array();

            if ( !array_key_exists( $field_id , $registrationFormCustomFields ) )
                $registrationFormCustomFields[ $field_id ] = $customField;
            else {

                if ( $ajaxCall === true ) {

                    header( 'Content-Type: application/json' ); // specify we return json
                    echo json_encode( array(
                        'status'        =>  'fail',
                        'error_message' =>  sprintf( __( 'Duplicate field, %1$s already exists.' , 'woocommerce-wholesale-lead-capture' ) , $field_id )
                    ) );
                    die();

                } else
                    return false;

            }

            update_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS , base64_encode( serialize( $registrationFormCustomFields ) ) );

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( array(
                    'status'    =>  'success'
                ) );
                die();

            } else
                return true;

        }

        /**
         * Edit registration form custom field. Same as above.
         *
         * @param null $customField
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.1.0
         */
        public function wwlc_editRegistrationFormCustomField ( $customField = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $customField = $_POST[ 'customField' ];

            $field_id = $customField[ 'field_id' ];
            unset( $customField[ 'field_id' ] );

            $field_id = str_replace( 'wwlc_cf_' , '' , $field_id );
            $field_id = 'wwlc_cf_' . $field_id;

            $customField[ 'field_order' ] = str_replace( array( '.' , ',' ) , '' , $customField[ 'field_order' ] );
            if ( !is_numeric( $customField[ 'field_order' ] ) )
                $customField[ 'field_order' ] = 0;

            $registrationFormCustomFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registrationFormCustomFields ) )
                $registrationFormCustomFields = array();

            if ( array_key_exists( $field_id , $registrationFormCustomFields ) )
                $registrationFormCustomFields[ $field_id ] = $customField;
            else {

                if ( $ajaxCall === true ) {

                    header('Content-Type: application/json'); // specify we return json
                    echo json_encode(array(
                        'status'        =>  'fail',
                        'error_message' =>  sprintf( __( '%1$s custom field that you wish to edit does not exist' , 'woocommerce-wholesale-lead-capture' ) , $field_id )
                    ));
                    die();

                } else
                    return false;

            }

            update_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS , base64_encode( serialize( $registrationFormCustomFields ) ) );

            if ( $ajaxCall === true ) {

                header('Content-Type: application/json'); // specify we return json
                echo json_encode(array(
                    'status'    =>  'success'
                ));
                die();

            } else
                return true;

        }

        /**
         * Delete registration form custom field.
         *
         * @param null $field_id
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.1.0
         */
        public function wwlc_deleteRegistrationFormCustomField ( $field_id = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $field_id = $_POST[ 'field_id' ];

            $registrationFormCustomFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registrationFormCustomFields ) )
                $registrationFormCustomFields = array();

            if ( array_key_exists( $field_id , $registrationFormCustomFields ) )
                unset( $registrationFormCustomFields[ $field_id ] );
            else {

                if ( $ajaxCall === true ) {

                    header( 'Content-Type: application/json' ); // specify we return json
                    echo json_encode( array(
                        'status'        =>  'fail',
                        'error_message' =>  sprintf( __( '%1$s custom field that you wish to delete does not exist' , 'woocommerce-wholesale-lead-capture' ) , $field_id )
                    ) );
                    die();

                } else
                    return false;

            }

            update_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS , base64_encode( serialize( $registrationFormCustomFields ) ) );

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( array(
                    'status'    =>  'success'
                ) );
                die();

            } else
                return true;

        }

        /**
         * Get custom field by id.
         *
         * @param null $field_id
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.1.0
         */
        public function wwlc_getCustomFieldByID( $field_id = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $field_id = $_POST[ 'field_id' ];

            $registrationFormCustomFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registrationFormCustomFields ) )
                $registrationFormCustomFields = array();

            if ( array_key_exists( $field_id , $registrationFormCustomFields ) ) {

                $customField = $registrationFormCustomFields[ $field_id ];
                $customField[ 'field_id' ] = $field_id;

            } else {

                if ( $ajaxCall === true ) {

                    header( 'Content-Type: application/json' ); // specify we return json
                    echo json_encode( array(
                        'status'        =>  'fail',
                        'error_message' =>  sprintf( __( 'Cannot retrieve custom field, %1$s does not exist' , 'woocommerce-wholesale-lead-capture' ) , $field_id )
                    ) );
                    die();

                } else
                    return false;

            }

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( array(
                    'status'        =>  'success',
                    'custom_field'  =>  $customField
                ) );
                die();

            } else
                return true;

        }

    }

}