<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Product_Custom_Fields' ) ) {

    class WWPP_Product_Custom_Fields {

        private static $_instance;

        public static function getInstance () {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self;

            return self::$_instance;

        }




        /*
         |--------------------------------------------------------------------------------------------------------------
         | Add Custom Fields
         |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Add minimum order quantity custom field to simple products on product edit screen.
         *
         * @param $registeredCustomRoles
         *
         * @since 1.2.0
         */
        public function addSimpleProductMinimumOrderQuantityCustomField ( $registeredCustomRoles ) {

            global $woocommerce, $post;

            echo '<div class="options_group">';
            echo '<h3 style="padding-bottom:0;">' . __( 'Wholesale Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ) . '</h3>';
            echo '<p style="margin:0; padding:0 12px;">' . __( "Minimum number of items to be purchased in order to avail this product's wholesale price.<br/>Only applies to wholesale users." , 'woocommerce-wholesale-prices-premium' ) . '</p>';

            foreach ( $registeredCustomRoles as $roleKey => $role ) {

                woocommerce_wp_text_input(
                    array(
                        'id'            =>  $roleKey . '_wholesale_minimum_order_quantity',
                        'label'         =>  __( $role[ 'roleName' ] , 'woocommerce-wholesale-prices-premium' ),
                        'placeholder'   =>  '',
                        'desc_tip'      =>  'true',
                        'description'   =>  __( 'Only applies to users with the role of "' . $role[ 'roleName' ] . '"' , 'woocommerce-wholesale-prices-premium' ),
                    )
                );

            }

            echo '</div>';

        }

        /**
         * Add minimum order quantity custom field to variable products on product edit screen.
         * Custom fields are added per variation, not to the parent variable product.
         *
         * @param $loop
         * @param $variation_data
         * @param $variation
         * @param $registeredCustomRoles
         *
         * @since 1.2.0
         */
        public function addVariableProductMinimumOrderQuantityCustomField ( $loop , $variation_data , $variation , $registeredCustomRoles ) {

            global $woocommerce, $post;

            // Get the variable product data manually
            // Don't rely on the variation data woocommerce supplied
            // There is a logic change introduced on 2.3 series where they only send variation data (or variation meta)
            // That is built in to woocommerce, so all custom variation meta added to a variable product don't get passed along
            $variable_product_meta = get_post_meta( $variation->ID ); ?>

            <tr>
                <td colspan="2">
                    <?php
                    echo '<hr>';
                    echo '<h4 style="margin:0; padding:0; font-size:14px;">' . __( 'Wholesale Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ) . '</h4>';
                    echo '<p style="margin:0; padding:0;">' . __( "Minimum number of items to be purchased in order to avail this product's wholesale price.<br/>Only applies to wholesale users." , 'woocommerce-wholesale-prices-premium' ) . '</p>';
                    ?>
                </td>
            </tr>

            <?php foreach ( $registeredCustomRoles as $roleKey => $role ) { ?>

                <tr>
                    <td colspan="2">
                        <?php woocommerce_wp_text_input(
                            array(
                                'id'                =>  $roleKey . '_wholesale_minimum_order_quantity[' . $loop . ']',
                                'label'             =>  __( $role['roleName'] , 'woocommerce-wholesale-prices-premium' ),
                                'placeholder'       =>  '',
                                'desc_tip'      =>  'true',
                                'description'   =>  __( 'Only applies to users with the role of "' . $role['roleName'] . '"' , 'woocommerce-wholesale-prices-premium' ),
                                'value'         =>  isset( $variable_product_meta[ $roleKey . '_wholesale_minimum_order_quantity' ][ 0 ] ) ? $variable_product_meta[ $roleKey . '_wholesale_minimum_order_quantity' ][ 0 ] : ''
                            )
                        ); ?>
                    </td>
                </tr>

            <?php }

        }

        /**
         * Add order quantity based wholesale pricing custom fields to simple products.
         *
         * @since 1.6.0
         * @param $registeredCustomRoles
         */
        public function addSimpleProductQuantityBasedWholesalePriceCustomField( $registeredCustomRoles ) {

            global $post;

            $this->_printOrderQuantityBasedWholesalePricingControls( $post->ID , $registeredCustomRoles , 'simple' );

        }

        /**
         * Add order quantity based wholesale pricing custom fields to variable products.
         *
         * @since 1.6.0
         * @param $loop
         * @param $variation_data
         * @param $variation
         * @param $registeredCustomRoles
         */
        public function addVariableProductQuantityBasedWholesalePriceCustomField( $loop , $variation_data , $variation , $registeredCustomRoles ) {

            echo "<hr/>";

            $this->_printOrderQuantityBasedWholesalePricingControls( $variation->ID , $registeredCustomRoles , 'variable' );

        }

        /**
         * Print order quantity based wholesale pricing custom fields.
         *
         * @since 1.6.0
         * @since 1.7.0 Add Aelia Currency Switcher Plugin Integration
         *
         * @param $product_id
         * @param $registeredCustomRoles
         * @param $classes
         */
        private function _printOrderQuantityBasedWholesalePricingControls( $product_id , $registeredCustomRoles , $classes ) {

            $aelia_currency_switcher_active = WWPP_ACS_Integration_Helper::aelia_currency_switcher_active();

            if ( $aelia_currency_switcher_active ) {

                $currencySymbol = "";

                $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $product_id );

                $woocommerce_currencies = get_woocommerce_currencies();
                $enabled_currencies = WWPP_ACS_Integration_Helper::enabled_currencies();

            } else
                $currencySymbol = " (" . get_woocommerce_currency_symbol() . ")";

            $wholesale_roles_arr = array();
            foreach ( $registeredCustomRoles as $roleKey => $role )
                $wholesale_roles_arr[ $roleKey ] = $role[ 'roleName' ];

            $pqbwp_enable = get_post_meta( $product_id , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );

            $pqbwp_controls_styles = '';
            if ( $pqbwp_enable != 'yes' )
                $pqbwp_controls_styles = 'display: none;';

            $mapping = get_post_meta( $product_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
            if ( !is_array( $mapping ) )
                $mapping = array(); ?>

            <div class="product-quantity-based-wholesale-pricing options_group <?php echo $classes; ?>" >

                <div>

                    <h3 class="pqbwp-heading"><?php _e( 'Product Quantity Based Wholesale Pricing' ,  'woocommerce-wholesale-prices-premium' ); ?></h3>
                    <p class="pqbwp-desc">
                        <?php _e( 'Specify wholesale price for this current product depending on the quantity being purchased.<br/>Only applies to the wholesale roles that you specify.' , 'woocommerce-wholesale-prices-premium' ); ?>
                    </p>

                    <?php if ($aelia_currency_switcher_active ) { ?>

                        <p class="pbwp-desc">
                            <?php _e( 'Note: If you have not specify mapping for other currencies for a given wholesale role, it will derive its wholesale price automatically by converting the base currency wholesale price to that currency' , 'woocommerce-wholesale-prices-premium' ); ?>
                        </p>

                    <?php } ?>

                </div>

                <p class="form-field pqbwp-enable-field-container">

                    <span class="hidden post-id"><?php echo $product_id; ?></span>
                    <input type="checkbox" class="pqbwp-enable checkbox" value="yes" <?php echo ( $pqbwp_enable == 'yes' ) ? 'checked' : ''; ?>>
                    <span class="description"><?php _e( "Enable further wholesale pricing discounts based on quantity purchased?" , "woocommerce-wholesale-prices-premium" ); ?></span>

                </p>

                <div class="processing-indicator"><span class="spinner"></span></div>

                <div class="pqbwp-controls" style="<?php echo $pqbwp_controls_styles; ?>">

                    <input type="hidden" class="mapping-index" value="">

                    <?php
                    // The fields below aren't really saved via woocommerce, we just used it here to house our rule controls.
                    // We use these to add our rule controls to abide with woocommerce styling.

                    woocommerce_wp_select(
                        array(
                            'id'            =>  'pqbwp_registered_wholesale_roles',
                            'class'         =>  'pqbwp_registered_wholesale_roles',
                            'label'         =>  __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'   =>  '',
                            'desc_tip'      =>  'true',
                            'description'   =>  __( 'Select wholesale role to which this rule applies.' , 'woocommerce-wholesale-prices-premium' ),
                            'options'       =>  $wholesale_roles_arr
                        )
                    );

                    woocommerce_wp_text_input(
                        array(
                            'id'            =>  'pqbwp_minimum_order_quantity',
                            'class'         =>  'pqbwp_minimum_order_quantity',
                            'label'         =>  __( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'   =>  '',
                            'desc_tip'      =>  'true',
                            'description'   =>  __( 'Minimum order quantity required for this rule. Must be a number.' , 'woocommerce-wholesale-prices-premium' ),
                        )
                    );

                    woocommerce_wp_text_input(
                        array(
                            'id'            =>  'pqbwp_maximum_order_quantity',
                            'class'         =>  'pqbwp_maximum_order_quantity',
                            'label'         =>  __( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'   =>  '',
                            'desc_tip'      =>  'true',
                            'description'   =>  __( 'Maximum order quantity required for this rule. Must be a number. Leave this blank for no maximum quantity.' , 'woocommerce-wholesale-prices-premium' ),
                        )
                    );

                    woocommerce_wp_text_input(
                        array(
                            'id'            =>  'pqbwp_wholesale_price',
                            'class'         =>  'pqbwp_wholesale_price',
                            'label'         =>  __( 'Wholesale Price' . $currencySymbol , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'   =>  '',
                            'desc_tip'      =>  'true',
                            'description'   =>  __( 'Wholesale price for this specific rule.' , 'woocommerce-wholesale-prices-premium' ),
                            'data_type'     =>  'price'
                        )
                    );

                    if ( $aelia_currency_switcher_active ) {

                        $currency_select_options = array();

                        foreach ( $enabled_currencies as $currency ) {

                            if ( $currency == $base_currency )
                                $text = $woocommerce_currencies[ $currency ] . " (Base Currency)";
                            else
                                $text = $woocommerce_currencies[ $currency ];

                            $currency_select_options[ $currency ] = $text;

                        }

                        woocommerce_wp_select(
                            array(
                                'id'            =>  'pqbwp_enabled_currencies',
                                'class'         =>  'pqbwp_enabled_currencies',
                                'label'         =>  __( 'Currency' , 'woocommerce-wholesale-prices-premium' ),
                                'placeholder'   =>  '',
                                'desc_tip'      =>  'true',
                                'description'   =>  __( 'Select Currency' , 'woocommerce-wholesale-prices-premium' ),
                                'options'       =>  $currency_select_options,
                                'value'         =>  $base_currency
                            )
                        );

                    } ?>

                    <p class="form-field button-controls add-mode">

                        <input type="button" class="pqbwp-cancel button button-secondary" value="<?php _e( 'Cancel' , 'woocommerce-wholesale-prices-premium' ); ?>">
                        <input type="button" class="pqbwp-save-rule button button-primary" value="<?php _e( 'Save Quantity Discount Rule' , 'woocommerce-wholesale-prices-premium' ); ?>">
                        <input type="button" class="pqbwp-add-rule button button-primary" value="<?php _e( 'Add Quantity Discount Rule' , 'woocommerce-wholesale-prices-premium' ); ?>">
                        <span class="spinner"></span>

                        <div style="float: none; clear: both; display: block;"></div>

                    </p>

                    <div class="form-field table-mapping">
                        <table class="pqbwp-mapping wp-list-table widefat">

                            <thead>
                                <tr>
                                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Wholesale Price' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <?php echo $aelia_currency_switcher_active ? "<th>" . __( 'Currency' , 'woocommerce-wholesale-prices-premium' ) . "</th>" : ""; ?>
                                    <th></th>
                                </tr>
                            </thead>

                            <tfoot>
                                <tr>
                                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Wholesale Price' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <?php echo $aelia_currency_switcher_active ? "<th>" . __( 'Currency' , 'woocommerce-wholesale-prices-premium' ) . "</th>" : ""; ?>
                                    <th></th>
                                </tr>
                            </tfoot>

                            <tbody>

                            <?php
                            if ( !empty( $mapping ) ) {

                                if ( $aelia_currency_switcher_active ) {

                                    $itemNumber =   0;
                                    foreach ( $mapping as $index => $map ) {

                                        foreach ( $enabled_currencies as $currency ) {

                                            if ( $currency == $base_currency ) {

                                                $wholesale_role_meta_key  = 'wholesale_role';
                                                $wholesale_price_meta_key = 'wholesale_price';
                                                $start_qty_meta_key = 'start_qty';
                                                $end_qty_meta_key   = 'end_qty';

                                            } else {

                                                $wholesale_role_meta_key  = $currency . '_wholesale_role';
                                                $wholesale_price_meta_key = $currency . '_wholesale_price';
                                                $start_qty_meta_key = $currency . '_start_qty';
                                                $end_qty_meta_key   = $currency . '_end_qty';

                                            }

                                            $args = array( 'currency' => $currency );

                                            if ( array_key_exists( $wholesale_role_meta_key , $map ) ) {

                                                $itemNumber++;

                                                // One key check is enough
                                                $this->_printMappingItem( $itemNumber , $index , $map , $registeredCustomRoles , $wholesale_role_meta_key , $wholesale_price_meta_key , $start_qty_meta_key , $end_qty_meta_key , $aelia_currency_switcher_active , $args , $currency );

                                            }

                                        }

                                    }

                                } else {

                                    $itemNumber =   0;
                                    foreach ( $mapping as $index => $map ) {

                                        // Skip none base currency mapping
                                        if ( array_key_exists( 'currency' , $map ) )
                                            continue;

                                        $itemNumber++;

                                        $wholesale_role_meta_key  = 'wholesale_role';
                                        $wholesale_price_meta_key = 'wholesale_price';
                                        $start_qty_meta_key = 'start_qty';
                                        $end_qty_meta_key   = 'end_qty';
                                        $args = array( 'currency' => get_woocommerce_currency() );

                                        $this->_printMappingItem( $itemNumber , $index , $map , $registeredCustomRoles , $wholesale_role_meta_key , $wholesale_price_meta_key , $start_qty_meta_key , $end_qty_meta_key , false , $args );

                                    }

                                }

                            } else { ?>

                                <tr class="no-items">
                                    <td class="colspanchange" colspan="10"><?php _e( 'No Quantity Discount Rules Found' , 'woocommerce-wholesale-prices-premium' ); ?></td>
                                </tr>

                            <?php } ?>

                            </tbody>

                        </table><!--#pqbwp-mapping-->
                    </div>

                </div>

            </div><!--.product-quantity-based-wholesale-pricing-->

            <?php

        }

        /**
         * Print wholesale pricing mapping item.
         *
         * @since 1.7.0
         *
         * @param $itemNumber
         * @param $index
         * @param $map
         * @param $registeredCustomRoles
         * @param $wholesale_role_meta_key
         * @param $wholesale_role_price_key
         * @param $start_qty_meta_key
         * @param $end_qty_meta_key
         * @param $aelia_currency_switcher_active
         * @param $args
         * @param null $currency
         */
        private function _printMappingItem( $itemNumber , $index , $map , $registeredCustomRoles , $wholesale_role_meta_key , $wholesale_role_price_key , $start_qty_meta_key , $end_qty_meta_key , $aelia_currency_switcher_active , $args , $currency = null ) {

            if ( $itemNumber % 2 == 0 )
                $row_class = "even";
            else
                $row_class = "odd alternate"; ?>

            <tr class="<?php echo $row_class; ?>">

                <td class="meta hidden">
                    <span class="index"><?php echo $index; ?></span>
                    <span class="wholesale-role"><?php echo $map[ $wholesale_role_meta_key ]; ?></span>
                    <span class="wholesale-price"><?php echo $map[ $wholesale_role_price_key ]; ?></span>
                </td>
                <td class="wholesale-role-text"><?php echo $registeredCustomRoles[ $map[ $wholesale_role_meta_key ] ][ 'roleName' ]; ?></td>
                <td class="start-qty"><?php echo $map[ $start_qty_meta_key ]; ?></td>
                <td class="end-qty"><?php echo $map[ $end_qty_meta_key ]; ?></td>
                <td class="wholesale-price-text"><?php echo wc_price( $map[ $wholesale_role_price_key ] , $args ); ?></td>
                <?php if ( $aelia_currency_switcher_active ) { ?>
                    <td class="currency"><?php echo $currency; ?></td>
                <?php } ?>
                <td class="controls">
                    <a class="edit dashicons dashicons-edit"></a>
                    <a class="delete dashicons dashicons-no"></a>
                </td>

            </tr>

            <?php

        }

        /**
         * Add wholesale users exclusive variation custom field to variable products on product edit screen.
         * Custom fields are added per variation, not to the parent variable product.
         *
         * @param $loop
         * @param $variation_data
         * @param $variation
         * @param $registeredCustomRoles
         *
         * @since 1.3.0
         */
        public function addVariableProductWholesaleOnlyVariationCustomField ( $loop , $variation_data , $variation , $registeredCustomRoles ) {

            global $woocommerce, $post;

            // Get the variable product data manually
            // Don't rely on the variation data woocommerce supplied
            // There is a logic change introduced on 2.3 series where they only send variation data (or variation meta)
            // That is built in to woocommerce, so all custom variation meta added to a variable product don't get passed along
            $variable_product_meta = get_post_meta( $variation->ID ); ?>

            <tr>
                <td colspan="2">
                    <?php
                    echo '<hr>';
                    echo '<h4 style="margin:0; padding:0; font-size:14px;">' . __( 'Wholesale Exclusive Variation' , 'woocommerce-wholesale-prices-premium' ) . '</h4>';
                    echo '<p style="margin:0; padding:0;">' . __( "Here you can specify if this variation is exclusive only to wholesale users and which wholesale role/s specifically. Just left un-check to disable this functionality (Therefore making this variation accessible to anyone)<br/>If using this variation as a wholesale only variation, please ensure you set the same price for the regular pricing or WooCommerce will still hide it." , 'woocommerce-wholesale-prices-premium' ) . '</p>';
                    ?>
                </td>
            </tr>

            <?php foreach ( $registeredCustomRoles as $roleKey => $role ) { ?>

                <tr>
                    <td colspan="2">
                        <?php woocommerce_wp_checkbox(
                            array(
                                'id'            =>  $roleKey . '_exclusive_variation[' . $loop . ']',
                                'label'         =>  '',
                                'description'   =>  __( "<b>" . $role['roleName'] . "</b> will have access to this variation" , 'woocommerce-wholesale-prices-premium' ),
                                'value'         =>  isset( $variable_product_meta[ $roleKey . '_exclusive_variation' ][ 0 ] ) ? $variable_product_meta[ $roleKey . '_exclusive_variation' ][ 0 ] : ''
                            )
                        ); ?>
                    </td>
                </tr>

            <?php }

        }




        /*
         |--------------------------------------------------------------------------------------------------------------
         | Save Custom Fields
         |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Save minimum order quantity custom field value for simple products on product edit page.
         *
         * @param $post_id
         * @param $registeredCustomRoles
         *
         * @since 1.2.0
         */
        public function saveSimpleProductMinimumOrderQuantityCustomField ( $post_id , $registeredCustomRoles ) {

            foreach ( $registeredCustomRoles as $roleKey => $role ) {

                $wholesaleMOQ = trim( esc_attr( $_POST[ $roleKey . '_wholesale_minimum_order_quantity' ] ) );

                if ( !empty( $wholesaleMOQ ) ) {

                    if( !is_numeric( $wholesaleMOQ ) )
                        $wholesaleMOQ = '';
                    elseif ( $wholesaleMOQ < 0 )
                        $wholesaleMOQ = 0;
                    else
                        $wholesaleMOQ = wc_format_decimal( $wholesaleMOQ );

                    $wholesaleMOQ = round( $wholesaleMOQ );

                }

                $wholesaleMOQ = wc_clean( apply_filters( 'wwpp_filter_before_save_wholesale_minimum_order_quantity' , $wholesaleMOQ , $roleKey , $post_id , 'simple' ) );
                update_post_meta( $post_id , $roleKey . '_wholesale_minimum_order_quantity' , $wholesaleMOQ );

            }

        }

        /**
         * Save minimum order quantity custom field value for variable products on product edit page.
         *
         * @param $post_id
         * @param $registeredCustomRoles
         *
         * @since 1.2.0
         */
        public function saveVariableProductMinimumOrderQuantityCustomField ( $post_id , $registeredCustomRoles ) {

            global $_POST;

            if ( isset( $_POST[ 'variable_sku' ] ) ) {

                $variable_post_id = $_POST[ 'variable_post_id' ];
                $max_loop = max( array_keys( $variable_post_id ) );

                foreach ( $registeredCustomRoles as $roleKey => $role ) {

                    $wholesaleMOQ = $_POST[ $roleKey . '_wholesale_minimum_order_quantity' ];

                    for ( $i = 0; $i <= $max_loop; $i++ ){

                        if ( !isset( $variable_post_id[ $i ] ) )
                            continue;

                        $variation_id = (int) $variable_post_id[ $i ];

                        if ( isset( $wholesaleMOQ[ $i ] ) ) {

                            $wholesaleMOQ[ $i ] = trim( esc_attr( $wholesaleMOQ[ $i ] ) );

                            if ( !empty( $wholesaleMOQ[ $i ] ) ) {

                                if( !is_numeric( $wholesaleMOQ[ $i ] ) )
                                    $wholesaleMOQ[ $i ] = '';
                                elseif( $wholesaleMOQ[ $i ] < 0 )
                                    $wholesaleMOQ[ $i ] = 0;
                                else
                                    $wholesaleMOQ[ $i ] = wc_format_decimal( $wholesaleMOQ[ $i ] );

                                $wholesaleMOQ[ $i ] = round( $wholesaleMOQ[ $i ] );

                            }

                            $wholesaleMOQ[ $i ] = wc_clean( apply_filters( 'wwpp_filter_before_save_wholesale_minimum_order_quantity' , $wholesaleMOQ[ $i ] , $roleKey , $variation_id , 'variation' ) );
                            update_post_meta( $variation_id , $roleKey . '_wholesale_minimum_order_quantity' , $wholesaleMOQ[ $i ] );

                        }

                    }

                }

            }

        }

        /**
         * Save wholesale exclusive variation custom field for variable products on product edit page.
         *
         * @param $post_id
         * @param $registeredCustomRoles
         *
         * @since 1.3.0
         */
        public function saveVariableProductWholesaleOnlyVariationCustomField ( $post_id , $registeredCustomRoles ) {

            global $_POST;

            if ( isset( $_POST[ 'variable_sku' ] ) ) {

                $variable_post_id = $_POST[ 'variable_post_id' ];
                $max_loop = max( array_keys( $variable_post_id ) );

                foreach ( $registeredCustomRoles as $roleKey => $role ) {

                    $wholesaleExclusive = array();
                    if ( isset( $_POST[ $roleKey . '_exclusive_variation' ] ) )
                        $wholesaleExclusive = $_POST[ $roleKey . '_exclusive_variation' ];

                    for ( $i = 0; $i <= $max_loop; $i++ ) {

                        if ( !isset( $variable_post_id[ $i ] ) )
                            continue;

                        $variation_id = (int) $variable_post_id[ $i ];

                        if ( isset( $wholesaleExclusive[ $i ] ) )
                            update_post_meta( $variation_id , $roleKey . '_exclusive_variation' , $wholesaleExclusive[ $i ] );
                        else
                            delete_post_meta( $variation_id , $roleKey . '_exclusive_variation' );

                    }

                }

            }

        }




        /*
         |--------------------------------------------------------------------------------------------------------------
         | AJAX Interfaces
         |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * AJAX interface to toggle product quantity based wholesale pricing feature of a product.
         *
         * @since 1.6.0
         * @param null $post_id
         * @param null $enable
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwppToggleProductQuantityBasedWholesalePricing( $post_id = null , $enable = null , $ajax_call = true ) {

            if ( $ajax_call === true ) {
                $post_id = $_POST[ 'post_id' ];
                $enable = $_POST[ 'enable' ];
            }

            update_post_meta( $post_id , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , $enable );

            $response = array( 'status' => 'success' );

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        public function wwppToggleProductQuantityBasedWholesalePricingMappingView( $post_id = null , $view = null , $ajax_call = true ) {

            if ( $ajax_call === true ) {

                $post_id = $_POST[ 'post_id' ];
                $view = $_POST[ 'view' ];

            }

            if ( empty( $post_id ) || empty( $view ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  __( 'Invalid mapping view data passed' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                update_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING_VIEW , $view );

                $response = array( 'status' => 'success' );

            }

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Add quantity discount rule. $rule parameter expected to have the following items below.
         *
         * wholesale_role
         * start_qty
         * end_qty
         * wholesale_price
         *
         * @since 1.6.0
         * @since 1.7.0 Add Aelia Currency Switcher Plugin Integration
         *
         * @param null $post_id
         * @param null $rule
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwppAddQuantityDiscountRule( $post_id = null , $rule = null , $ajax_call = true ) {

            if ( $ajax_call === true ) {

                $rule = $_POST[ 'rule' ];
                $post_id = $_POST[ 'post_id' ];

            }

            $thousand_sep = get_option( 'woocommerce_price_thousand_sep' );
            $decimal_sep = get_option( 'woocommerce_price_decimal_sep' );

            if ( $thousand_sep )
                $rule[ 'wholesale_price' ] = str_replace( $thousand_sep , '' , $rule[ 'wholesale_price' ] );

            if ( $decimal_sep )
                $rule[ 'wholesale_price' ] = str_replace( $decimal_sep , '.' , $rule[ 'wholesale_price' ] );

            // Check data format
            if ( !is_array( $rule ) || !isset( $post_id , $rule[ 'wholesale_role' ] , $rule[ 'start_qty' ] , $rule[ 'end_qty' ] , $rule[ 'wholesale_price' ] ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  __( 'Quantity discount rule data passed is in invalid format.' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                // Check data validity
                $post_id = sanitize_text_field( $post_id );
                $rule[ 'wholesale_role' ] = sanitize_text_field( $rule[ 'wholesale_role' ] );
                $rule[ 'start_qty' ] = sanitize_text_field( $rule[ 'start_qty' ] );
                $rule[ 'end_qty' ] = sanitize_text_field( $rule[ 'end_qty' ] );
                $rule[ 'wholesale_price' ] = sanitize_text_field( $rule[ 'wholesale_price' ] );

                if ( empty( $post_id ) || empty( $rule[ 'wholesale_role' ] ) || empty( $rule[ 'start_qty' ] ) || empty( $rule[ 'wholesale_price' ] ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields are required ( Wholesale Role / Starting Qty / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !is_numeric( $rule[ 'start_qty' ] ) || !is_numeric( $rule[ 'wholesale_price' ] ) || ( !empty( $rule[ 'end_qty' ] ) && !is_numeric( $rule[ 'end_qty' ] ) ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields must be a number ( Starting Qty / Ending Qty / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !empty( $rule[ 'end_qty' ] ) && $rule[ 'end_qty' ] < $rule[ 'start_qty' ] ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Ending Qty must not be less than Starting Qty' , 'woocommerce-wholesale-prices-premium' )
                    );

                } else {

                    $rule[ 'wholesale_price' ] = wc_format_decimal( $rule[ 'wholesale_price' ] );

                    if ( $rule[ 'wholesale_price' ] < 0 )
                        $rule[ 'wholesale_price' ] = 0;

                    $quantity_discount_rule_mapping = get_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                    if ( !is_array( $quantity_discount_rule_mapping ) )
                        $quantity_discount_rule_mapping = array();

                    $dup             = false;
                    $startQtyOverlap = false;
                    $endQtyOverlap   = false;
                    $errIndexes      = array();

                    if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                        $baseCurrency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                        if ( $rule[ 'currency' ] == $baseCurrency ) {

                            $wholesale_role_meta_key = 'wholesale_role';
                            $start_qty_meta_key = 'start_qty';
                            $end_qty_meta_key = 'end_qty';

                        } else {

                            $wholesale_role_meta_key = $rule[ 'currency' ] . '_wholesale_role';
                            $start_qty_meta_key = $rule[ 'currency' ] . '_start_qty';
                            $end_qty_meta_key = $rule[ 'currency' ] . '_end_qty';

                        }

                    } else {

                        $wholesale_role_meta_key = 'wholesale_role';
                        $start_qty_meta_key = 'start_qty';
                        $end_qty_meta_key = 'end_qty';

                    }

                    foreach ( $quantity_discount_rule_mapping as $idx => $mapping ) {

                        if ( !array_key_exists( $wholesale_role_meta_key , $mapping ) )
                            continue; // One key to check is enough
                        else {

                            if ( $mapping[ $wholesale_role_meta_key ] == $rule[ 'wholesale_role' ] ) {

                                // If it has the same wholesale role and starting quantity then they are considered as the duplicate
                                if ( $mapping[ $start_qty_meta_key ] == $rule[ 'start_qty' ] && !$dup ) {

                                    $dup = true;
                                    if ( !in_array( $idx , $errIndexes ) )
                                        $errIndexes[] = $idx;

                                }

                                // Check for overlapping mappings. Only do this if no dup yet

                                if ( !$dup ) {

                                    if ( $rule[ 'start_qty' ] > $mapping[ $start_qty_meta_key ] && $rule[ 'start_qty' ] <= $mapping[ $end_qty_meta_key ] && $startQtyOverlap == false ) {

                                        $startQtyOverlap = true;
                                        if ( !in_array( $idx , $errIndexes ) )
                                            $errIndexes[] = $idx;

                                    }

                                    if ( $rule[ 'end_qty' ] <= $mapping[ $end_qty_meta_key ] && $rule[ 'end_qty' ] >= $mapping[ $start_qty_meta_key ] && $endQtyOverlap == false ) {

                                        $endQtyOverlap = true;
                                        if ( !in_array( $idx , $errIndexes ) )
                                            $errIndexes[] = $idx;

                                    }

                                }

                            }

                        }

                        // break loop if there is dup or overlap
                        if ( $dup || ( $startQtyOverlap && $endQtyOverlap ) )
                            break;

                    } // foreach ( $quantity_discount_rule_mapping as $idx => $mapping )

                    if ( $dup ) {

                        $response = array(
                            'status'          => 'fail',
                            'error_message'   => __( 'Duplicate quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                            'additional_data' => array(
                                                    'dup_index' => $errIndexes
                                                )
                        );

                    } elseif ( $startQtyOverlap && $endQtyOverlap ) {

                        $response = array(
                            'status'          => 'fail',
                            'error_message'   => __( 'Overlap quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                            'additional_data' => array(
                                                    'dup_index' => $errIndexes
                                                )
                        );

                    } else {

                        $args = array();
                        $wholesale_price = $rule[ 'wholesale_price' ]; // We could be changing the key for this so we cached this here

                        if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                            $baseCurrency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                            $args[ 'currency' ] = $rule[ 'currency' ];

                            if ( $rule[ 'currency' ] == $baseCurrency ) {

                                /*
                                 * Remove currency for base currency mapping. This is of compatibility reasons.
                                 * We want to make wwpp work with or without aelia currency switcher plugin.
                                 * We use the default keys here for base currency.
                                 */
                                unset( $rule[ 'currency' ] );

                            } else {

                                /*
                                 * For other currencies (not base currency) we modify the keys and append the currency code.
                                 * We do this for compatibility reasons. We don't want this to have the same keys as the
                                 * base currency. Coz what if Aelia was removed later? WWPP will not know what mapping to use
                                 * coz they have all the same keys.
                                 *
                                 * Note: exception here is the $rule[ 'currency' ]. We are not using 'currency' key before so
                                 * we can get away of not renaming that. Also we need not to rename this due to functionality
                                 * reasons.
                                 */
                                $rule = array(
                                    $rule[ 'currency' ] . '_wholesale_role'  => $rule[ 'wholesale_role' ],
                                    $rule[ 'currency' ] . '_start_qty'       => $rule[ 'start_qty' ],
                                    $rule[ 'currency' ] . '_end_qty'         => $rule[ 'end_qty' ],
                                    $rule[ 'currency' ] . '_wholesale_price' => $rule[ 'wholesale_price' ],
                                    'currency'                               => $rule[ 'currency' ]
                                );

                            }

                        }

                        $quantity_discount_rule_mapping[] = $rule;

                        update_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , $quantity_discount_rule_mapping );

                        end( $quantity_discount_rule_mapping );
                        $last_inserted_item_index = key( $quantity_discount_rule_mapping );

                        $response = array(
                            'status'                    => 'success',
                            'last_inserted_item_index'  =>  $last_inserted_item_index,
                            'wholesale_price_text'      =>  wc_price( $wholesale_price , $args )
                        );

                    }

                }

            }

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Edit quantity discount rule. $rule parameter expected to have the following items below.
         *
         * wholesale_role
         * start_qty
         * end_qty
         * wholesale_price
         *
         * @since 1.6.0
         * @param null $post_id
         * @param null $index
         * @param null $rule
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwppSaveQuantityDiscountRule( $post_id = null , $index = null , $rule = null , $ajax_call = true  ) {

            if ( $ajax_call === true ) {

                $rule = $_POST[ 'rule' ];
                $index = $_POST[ 'index' ];
                $post_id = $_POST[ 'post_id' ];

            }

            $thousand_sep = get_option( 'woocommerce_price_thousand_sep' );
            $decimal_sep = get_option( 'woocommerce_price_decimal_sep' );

            if ( $thousand_sep )
                $rule[ 'wholesale_price' ] = str_replace( $thousand_sep , '' , $rule[ 'wholesale_price' ] );

            if ( $decimal_sep )
                $rule[ 'wholesale_price' ] = str_replace( $decimal_sep , '.' , $rule[ 'wholesale_price' ] );

            // Check data format
            if ( !is_array( $rule ) || !isset( $post_id , $index , $rule[ 'wholesale_role' ] , $rule[ 'start_qty' ] , $rule[ 'end_qty' ] , $rule[ 'wholesale_price' ] ) ) {

                $response = array(
                    'status'        =>  'fail',
                    'error_message' =>  __( 'Quantity discount rule data passed is in invalid format.' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                // Check data validity
                $post_id = sanitize_text_field( $post_id );
                $index = sanitize_text_field( $index );
                $rule[ 'wholesale_role' ] = sanitize_text_field( $rule[ 'wholesale_role' ] );
                $rule[ 'start_qty' ] = sanitize_text_field( $rule[ 'start_qty' ] );
                $rule[ 'end_qty' ] = sanitize_text_field( $rule[ 'end_qty' ] );
                $rule[ 'wholesale_price' ] = sanitize_text_field( $rule[ 'wholesale_price' ] );

                if ( empty( $post_id ) || $index == '' || empty( $rule[ 'wholesale_role' ] ) || empty( $rule[ 'start_qty' ] ) || empty( $rule[ 'wholesale_price' ] ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields are required ( Wholesale Role / Starting Qty / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !is_numeric( $rule[ 'start_qty' ] ) || !is_numeric( $rule[ 'wholesale_price' ] ) || ( !empty( $rule[ 'end_qty' ] ) && !is_numeric( $rule[ 'end_qty' ] ) ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields must be a number ( Starting Qty / Ending Qty / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !empty( $rule[ 'end_qty' ] ) && $rule[ 'end_qty' ] < $rule[ 'start_qty' ] ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Ending Qty must not be less than Starting Qty' , 'woocommerce-wholesale-prices-premium' )
                    );

                }  else {

                    $quantity_discount_rule_mapping = get_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                    if ( !is_array( $quantity_discount_rule_mapping ) )
                        $quantity_discount_rule_mapping = array();

                    if ( !array_key_exists( $index , $quantity_discount_rule_mapping ) ) {

                        $response = array(
                                        'status'        =>  'fail',
                                        'error_message' =>  'Quantity discount rule entry you want to edit does not exist'
                                    );

                    } else {

                        $rule[ 'wholesale_price' ] = wc_format_decimal( $rule[ 'wholesale_price' ] );

                        if ( $rule[ 'wholesale_price' ] < 0 )
                            $rule[ 'wholesale_price' ] = 0;

                        $dup             = false;
                        $startQtyOverlap = false;
                        $endQtyOverlap   = false;
                        $errIndexes      = array();

                        if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                            $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                            if ( $rule[ 'currency' ] == $base_currency ) {

                                $wholesale_role_meta_key = 'wholesale_role';
                                $start_qty_meta_key = 'start_qty';
                                $end_qty_meta_key = 'end_qty';

                            } else {

                                $wholesale_role_meta_key = $rule[ 'currency' ] . '_wholesale_role';
                                $start_qty_meta_key = $rule[ 'currency' ] . '_start_qty';
                                $end_qty_meta_key = $rule[ 'currency' ] . '_end_qty';

                            }

                        } else {

                            $wholesale_role_meta_key = 'wholesale_role';
                            $start_qty_meta_key = 'start_qty';
                            $end_qty_meta_key = 'end_qty';

                        }

                        foreach ( $quantity_discount_rule_mapping as $idx => $mapping ) {

                            if ( !array_key_exists( $wholesale_role_meta_key , $mapping ) )
                                continue; // One meta key check is enough
                            else {

                                if ( $mapping[ $wholesale_role_meta_key ] == $rule[ 'wholesale_role' ] ) {

                                    // If it has the same wholesale role and starting quantity then they are considered as the duplicate
                                    // Since this is an edit, we need to check too if this is not the same entry as we are editing
                                    if ( $mapping[ $start_qty_meta_key ] == $rule[ 'start_qty' ] && $index != $idx && !$dup ) {

                                        $dup = true;
                                        if ( !in_array( $idx , $errIndexes ) )
                                            $errIndexes[] = $idx;

                                    }

                                    // Check for overlapping mappings. Only do this if no dup yet

                                    if ( !$dup && $index != $idx ) {

                                        if ( $rule[ 'start_qty' ] >= $mapping[ $start_qty_meta_key ] && $rule[ 'start_qty' ] <= $mapping[ $end_qty_meta_key ] && $startQtyOverlap == false ) {

                                            $startQtyOverlap = true;
                                            if ( !in_array( $idx , $errIndexes ) )
                                                $errIndexes[] = $idx;

                                        }

                                        if ( $rule[ 'end_qty' ] <= $mapping[ $end_qty_meta_key ] && $rule[ 'end_qty' ] >= $mapping[ $start_qty_meta_key ] && $endQtyOverlap == false ) {

                                            $endQtyOverlap = true;
                                            if ( !in_array( $idx , $errIndexes ) )
                                                $errIndexes[] = $idx;

                                        }

                                    }

                                }

                            }

                            // break loop if there is dup or overlap
                            if ( $dup || ( $startQtyOverlap && $endQtyOverlap ) )
                                break;

                        } // foreach ( $quantity_discount_rule_mapping as $idx => $mapping )

                        if ( $dup ) {

                            $response = array(
                                'status'          => 'fail',
                                'error_message'   => __( 'Duplicate quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                                'additional_data' => array(
                                                        'dup_index' => $errIndexes
                                                    )
                            );

                        } elseif ( $startQtyOverlap && $endQtyOverlap ) {

                            $response = array(
                                'status'          => 'fail',
                                'error_message'   => __( 'Overlap quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                                'additional_data' => array(
                                                        'dup_index' => $errIndexes
                                                    )
                            );

                        } else {

                            $args = array();
                            $wholesale_price = $rule[ 'wholesale_price' ]; // We could be changing the key for this so we cached this here

                            if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                                $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                                $args[ 'currency' ] = $rule[ 'currency' ];

                                if ( $rule[ 'currency' ] == $base_currency ) {

                                    /*
                                     * Remove currency for base currency mapping. This is of compatibility reasons.
                                     * We want to make wwpp work with or without aelia currency switcher plugin.
                                     * We use the default keys here for base currency.
                                     */
                                    unset( $rule[ 'currency' ] );

                                } else {

                                    /*
                                     * For other currencies (not base currency) we modify the keys and append the currency code.
                                     * We do this for compatibility reasons. We don't want this to have the same keys as the
                                     * base currency. Coz what if Aelia was removed later? WWPP will not know what mapping to use
                                     * coz they have all the same keys.
                                     *
                                     * Note: exception here is the $rule[ 'currency' ]. We are not using 'currency' key before so
                                     * we can get away of not renaming that. Also we need not to rename this due to functionality
                                     * reasons.
                                     */
                                    $rule = array(
                                        $rule[ 'currency' ] . '_wholesale_role'  => $rule[ 'wholesale_role' ],
                                        $rule[ 'currency' ] . '_start_qty'       => $rule[ 'start_qty' ],
                                        $rule[ 'currency' ] . '_end_qty'         => $rule[ 'end_qty' ],
                                        $rule[ 'currency' ] . '_wholesale_price' => $rule[ 'wholesale_price' ],
                                        'currency'                               => $rule[ 'currency' ]
                                    );

                                }

                            }

                            $quantity_discount_rule_mapping[ $index ] = $rule;

                            update_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , $quantity_discount_rule_mapping );

                            $response = array(
                                            'status'                => 'success',
                                            'wholesale_price_text'  =>  wc_price( $wholesale_price , $args )
                                        );

                        }

                    }

                }

            }

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Delete quantity discount rule.
         *
         * @since 1.6.0
         * @param null $post_id
         * @param null $index
         * @param bool|true $ajax_call
         * @return array
         */
        public function wwppDeleteQuantityDiscountRule( $post_id = null , $index = null , $ajax_call = true ) {

            if ( $ajax_call === true ) {

                $post_id = $_POST[ 'post_id' ];
                $index = $_POST[ 'index' ];

            }

            $quantity_discount_rule_mapping = get_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
            if ( !is_array( $quantity_discount_rule_mapping ) )
                $quantity_discount_rule_mapping = array();

            if ( !array_key_exists( $index , $quantity_discount_rule_mapping ) ) {

                $response = array(
                                'status'        =>  'fail',
                                'error_message' =>  'Quantity discount rule entry you want to delete does not exist'
                            );

            } else {

                unset( $quantity_discount_rule_mapping[ $index ] );

                update_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , $quantity_discount_rule_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

    }

}