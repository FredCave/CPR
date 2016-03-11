<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWOF_WWS_License_Settings' ) ) {

    class WWOF_WWS_License_Settings {

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
         * @since 1.0.1
         */
        public function __construct() {}

        /**
         * Singleton Pattern.
         *
         * @since 1.0.1
         *
         * @return WWOF_WWS_License_Settings
         */
        public static function getInstance() {

            if( !self::$_instance instanceof self )
                self::$_instance = new self;

            return self::$_instance;

        }

        /**
         * Save wwof license details.
         *
         * @param null $licenseDetails
         * @param bool $ajaxCall
         * @return bool
         *
         * @since 1.0.1
         */
        public function wwof_saveLicenseDetails ( $licenseDetails = null , $ajaxCall = true ) {

            if ( $ajaxCall === true )
                $licenseDetails = $_POST[ 'licenseDetails' ];

            update_option( WWOF_OPTION_LICENSE_EMAIL , trim( $licenseDetails[ 'license_email' ] ) );
            update_option( WWOF_OPTION_LICENSE_KEY , trim( $licenseDetails[ 'license_key' ] ) );

            if ( $ajaxCall === true ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( array( 'status' => 'success' ) );
                die();

            } else
                return true;

        }

    }

}