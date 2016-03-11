<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WWPP_Custom_Messages {

    private static $_instance;

    public static function getInstance() {
        if (!self::$_instance instanceof self)
            self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * Add custom thank you message to thank you page after successful order.
     *
     * @param $origMsg
     *
     * @return string
     * @since 1.0.0
     */
    public function customThankYouMessage ( $origMsg ){

        $newMsg = trim( stripslashes( strip_tags( get_option('wwpp_settings_thankyou_message') ) ) );

        if ( strcasecmp( $newMsg, "" ) != 0 ) {

            $pos = get_option('wwpp_settings_thankyou_message_position');

            switch ( $pos ) {

                case 'append':
                    return $origMsg . '<br>' . $newMsg;
                    break;

                case 'prepend':
                    return $newMsg . '<br>' . $origMsg;
                    break;

                default:
                    return $newMsg;
                    break;

            }

        } else {

            return $origMsg;

        }

    }

}