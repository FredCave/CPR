<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WWPP_Custom_Meta {

    private static $_instance;

    public static function getInstance() {
        if (!self::$_instance instanceof self)
            self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * Attach custom meta to orders ( the order type metadata ) to be used later for filtering orders by order type
     * on the order listing page.
     *
     * @param $orderId
     * @param $allRegisteredWholesaleRoles
     *
     * @since 1.0.0
     */
    public function addOrderTypeMetaToOrders ( $orderId, $allRegisteredWholesaleRoles ) {

        $currentOrder = new WC_Order( $orderId );
        $currentOrderWPUser = get_userdata( $currentOrder->get_user_id() );
        $currentOrderUserRoles = array();

        if ( $currentOrderWPUser )
            $currentOrderUserRoles = $currentOrderWPUser->roles;

        if ( !is_array( $currentOrderUserRoles ) )
            $currentOrderUserRoles = array();

        $allRegisteredWholesaleRolesKeys = array();
        foreach ( $allRegisteredWholesaleRoles as $roleKey => $role )
            $allRegisteredWholesaleRolesKeys[] = $roleKey;

        $orderUserWholesaleRole = array_intersect( $currentOrderUserRoles, $allRegisteredWholesaleRolesKeys );

        if( !empty( $orderUserWholesaleRole ) ){

            update_post_meta( $orderId, '_wwpp_order_type', 'wholesale' );
            update_post_meta( $orderId, '_wwpp_wholesale_order_type', $orderUserWholesaleRole[0] );

        }else{

            update_post_meta( $orderId, '_wwpp_order_type', 'retail' );
            update_post_meta( $orderId, '_wwpp_wholesale_order_type', '' );

        }

    }

}