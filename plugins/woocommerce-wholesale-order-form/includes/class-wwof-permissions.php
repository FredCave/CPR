<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WWOF_Permissions {

    private static $_instance;

    /**
     * Singleton Pattern.
     *
     * @since 1.0.0
     *
     * @return WooCommerce_WholeSale_Order_Form
     */
    public static function getInstance() {

        if(!self::$_instance instanceof self)
            self::$_instance = new self;

        return self::$_instance;

    }

    /**
     * Check if site user has access to view the wholesale product listing page.
     *
     * @return bool
     * @since 1.0.0
     */
    public function userHasAccess() {

        global $current_user;
        $userRoleFilters = get_option( 'wwof_permissions_user_role_filter' );
        $has_permission = false;

        if ( isset( $userRoleFilters ) && is_array( $userRoleFilters ) && !empty( $userRoleFilters ) ){

            $combined_arrays = array_intersect( $current_user->roles,$userRoleFilters );
            if( !empty( $combined_arrays ) )
                $has_permission = true;

        } else
            $has_permission = true;

        return apply_filters( 'wwof_filter_user_has_permission' , $has_permission , $userRoleFilters );

    }

}