<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WWPP_Wholesale_Prices {

    private static $_instance;

    public static function getInstance(){
        if(!self::$_instance instanceof self)
            self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * Filter callback that determines whether or not to apply wholesale pricing for the current order of a wholesale
     * user. This checks for order requirement for all wholesale roles or per wholesale role.
     *
     * Important Note: This does not use the raw cart total, this calculate the cart total by using the wholesale price
     * of each product on the cart. The idea is that so even after the cart is applied with wholesale price, it will
     * still meet the minimum order price.
     *
     * Important Note: We are retrieving the raw wholesale price, not wholesale price with applied tax. Just the raw
     * wholesale price of the product.
     *
     * Important Note: Minimum order price is purely based on product price. It does not include tax and shipping costs.
     * Just the total product price on the cart using wholesale price.
     *
     * @since 1.0.0
     * @param $apply_wholesale_price The flag that determines if wholesale pricing should be applied to this current order.
     * @param $cart_object The WooCommerce cart object (WC_Cart).
     * @param $userWholesaleRole The user's wholesale role (Array).
     * @return bool
     */
    public function applyWholesalePriceFlagFilter( $apply_wholesale_price , $cart_object , $userWholesaleRole ) {

        if ( !empty( $userWholesaleRole ) ) {

            $cartItems = 0;
            $cartTotal = 0;
            $minimumCartItems = trim( get_option( 'wwpp_settings_minimum_order_quantity' ) );
            $minimumCartPrice = trim( get_option( 'wwpp_settings_minimum_order_price' ) );
            $minimumRequirementsConditionalLogic = get_option( 'wwpp_settings_minimum_requirements_logic' );
            $notices = array();
            $hasCartItems = false;

            // Check if there is an option that overrides wholesale price order requirement per role
            $override_per_wholesale_role = get_option( 'wwpp_settings_override_order_requirement_per_role' );

            if ( $override_per_wholesale_role == 'yes' ) {

                $per_wholesale_role_order_requirement = get_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , array() );
                if ( !is_array( $per_wholesale_role_order_requirement ) )
                    $per_wholesale_role_order_requirement = array();

                if ( array_key_exists( $userWholesaleRole[ 0 ] , $per_wholesale_role_order_requirement ) ) {

                    // Use minimum order quantity set for this current wholesale role
                    $minimumCartItems = $per_wholesale_role_order_requirement[ $userWholesaleRole[ 0 ] ][ 'minimum_order_quantity' ];
                    $minimumCartPrice = $per_wholesale_role_order_requirement[ $userWholesaleRole[ 0 ] ][ 'minimum_order_subtotal' ];
                    $minimumRequirementsConditionalLogic = $per_wholesale_role_order_requirement[ $userWholesaleRole[ 0 ] ][ 'minimum_order_logic' ];

                }

            }

            foreach ( $cart_object->get_cart() as $cart_item_key => $values ) {

                if ( !$hasCartItems )
                    $hasCartItems = true;

                $price = $values[ 'data' ]->get_price();
                if ( $values[ 'data' ]->is_on_sale() )
                    $price = $values[ 'data' ]->get_sale_price();

                $wholesalePrice = false;

                if ( $values[ 'data' ]->product_type == 'simple' ) {

                    $productID = $values[ 'data' ]->id;
                    $wholesalePrice = $this->getProductWholesalePrice( $productID , $userWholesaleRole );
                    $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_cart' , $wholesalePrice , $productID , $userWholesaleRole , $values );

                } elseif ( $values[ 'data' ]->product_type == 'variation' ) {

                    $productID = $values[ 'data' ]->variation_id;
                    $wholesalePrice = $this->getProductWholesalePrice( $productID , $userWholesaleRole );
                    $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_cart' , $wholesalePrice , $productID , $userWholesaleRole , $values );

                }

                if ( is_numeric( $wholesalePrice ) )
                    $price = $wholesalePrice;

                $cartTotal += $price * $values[ 'quantity' ];
                $cartItems += $values[ 'quantity' ];

            }

            if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() && $cartTotal ) {

                /*
                 * If current active currency on shop page on front end is different from shop base currency,
                 * then convert the total price from the active currency on the front end to the shop base currency.
                 * The reason being is that the minimum order price is in the base currency. So we need to convert the
                 * cart total to the base currency to compare the 2 values.
                 */

                $activeCurrency = get_woocommerce_currency();
                $shopBaseCurrency = WWPP_ACS_Integration_Helper::shop_base_currency();

                if ( $activeCurrency != $shopBaseCurrency )
                    $cartTotal = WWPP_ACS_Integration_Helper::convert( $cartTotal , $shopBaseCurrency , $activeCurrency );

            }

            // Filter if the current user is legible to avail the wholesale price.
            // It doesn't mean the user is a wholesale user, he/she automatically avails the wholesale price.
            // There maybe some additional requirements besides from being a wholesale user enforced by site the manager
            // in order for users to avail the wholesale price.
            // If current user fails to avail the wholesale price requirements, return an array containing error data.
            if ( $hasCartItems ) {

                if ( is_numeric( $minimumCartItems ) && ( !is_numeric( $minimumCartPrice ) || strcasecmp( $minimumCartPrice , '' ) == 0 || ( ( float ) $minimumCartPrice <= 0) ) ) {

                    $minimumCartItems = (int) $minimumCartItems;

                    if ( $cartItems < $minimumCartItems ) {

                        $notices[] = array(
                            'type'      =>  'notice',
                            'message'   =>  __( 'You have not met the minimum order quantity of <b>(' . $minimumCartItems . ')</b> to activate adjusted pricing. Retail  prices will be shown below until the minimum order threshold is met.' , 'woocommerce-wholesale-prices-premium' )
                        );

                    }

                } elseif ( is_numeric( $minimumCartPrice ) && ( !is_numeric( $minimumCartItems ) || strcasecmp( $minimumCartItems , '' ) == 0 || ( (int) $minimumCartItems <= 0) ) ){

                    $minimumCartPrice = (float) $minimumCartPrice;

                    if ( $cartTotal < $minimumCartPrice ) {

                        $notices[] = array(
                            'type'      =>  'notice',
                            'message'   =>  __( 'You have not met the minimum order subtotal of <b>(' . wc_price( $minimumCartPrice ) . ')</b> ' .
                                                'to activate adjusted pricing. Retail  prices will be shown below until the minimum order threshold is met. ' .
                                                'The cart subtotal calculated with wholesale prices is <b>' . wc_price( $cartTotal ) . '</b>' , 'woocommerce-wholesale-prices-premium' )
                        );

                    }

                } elseif ( is_numeric($minimumCartPrice) && is_numeric($minimumCartItems) ) {

                    if ( strcasecmp( $minimumRequirementsConditionalLogic , 'and' ) == 0) {

                        if ( $cartItems < $minimumCartItems || $cartTotal < $minimumCartPrice ) {

                            $notices[] = array(
                                'type'      =>  'notice',
                                'message'   =>  __( 'You have not met the minimum order quantity of <b>(' . $minimumCartItems . ')</b> and minimum order subtotal of <b>(' . wc_price( $minimumCartPrice ) . ')</b> ' .
                                                    'to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. ' .
                                                    'The cart subtotal calculated with wholesale prices is <b>' . wc_price( $cartTotal ) . '</b>' , 'woocommerce-wholesale-prices-premium' )
                            );

                        }

                    } else {

                        if ( $cartItems < $minimumCartItems && $cartTotal < $minimumCartPrice ) {

                            $notices[] = array(
                                'type'      =>  'notice',
                                'message'   =>  __( 'You have not met the minimum order quantity of <b>(' . $minimumCartItems . ')</b> or minimum order subtotal of <b>(' . wc_price( $minimumCartPrice ) . ')</b> ' .
                                                    'to activate adjusted pricing. Retail prices will be shown below until the minimum order threshold is met. ' .
                                                    'The cart subtotal calculated with wholesale prices is <b>' . wc_price( $cartTotal ) . '</b>' , 'woocommerce-wholesale-prices-premium' )
                            );

                        }

                    }

                }

            }

            $notices = apply_filters( 'wwpp_filter_wholesale_price_requirement_failure_notice' , $notices , $minimumCartItems , $minimumCartPrice , $cartItems , $cartTotal , $cart_object , $userWholesaleRole );

            if ( !empty( $notices ) )
                return $notices;
            else
                return $apply_wholesale_price;

        } else
            return false; // Not a wholesale user

    }

    /**
     * Get the price of a product on shop pages with taxing applied (Meaning either including or excluding tax
     * depending on the settings of the shop).
     *
     * @since 1.7.1
     *
     * @param $product
     * @param $price
     * @param $wc_price_arg
     * @return mixed
     */
    public function getProductShopPriceWithTaxingApplied( $product , $price , $wc_price_arg = array() ) {

        $taxes_enabled                = get_option( 'woocommerce_calc_taxes' );
        $wholesale_tax_display_shop   = get_option( 'wwpp_settings_incl_excl_tax_on_wholesale_price' );
        $woocommerce_tax_display_shop = get_option( 'woocommerce_tax_display_shop' );

        if ( $taxes_enabled == 'yes' && $wholesale_tax_display_shop == 'incl'  )
            $filtered_price = wc_price( $product->get_price_including_tax( 1 , $price ) , $wc_price_arg );
        elseif ( $wholesale_tax_display_shop == 'excl' )
            $filtered_price = wc_price( $product->get_price_excluding_tax( 1 , $price ) , $wc_price_arg );
        elseif ( empty( $wholesale_tax_display_shop ) ) {

            if ( $taxes_enabled == 'yes' && $woocommerce_tax_display_shop == 'incl' )
                $filtered_price = wc_price( $product->get_price_including_tax( 1 , $price ) , $wc_price_arg );
            else
                $filtered_price = wc_price( $product->get_price_excluding_tax( 1 , $price ) , $wc_price_arg );

        }

        return apply_filters( 'wwpp_filter_product_shop_price_with_taxing_applied' , $filtered_price , $price , $product );

    }

    /**
     * Filter callback to determined whether or not to apply wholesale pricing per product basis.
     *
     * @param $apply_wholesale_price
     * @param $value
     * @param $cart_object
     * @param $userWholesaleRole
     * @return array|bool
     *
     * @since 1.2.0
     */
    public function applyWholesalePricePerProductBasisFilter ( $apply_wholesale_price , $value , $cart_object , $userWholesaleRole ) {

        if ( !empty( $userWholesaleRole ) ) {

            $notices = array();
            $wholesalePrice = false;

            // Get wholesale price
            if ( $value[ 'data' ]->product_type == 'simple' ) {

                $productID = $value[ 'data' ]->id;
                $wholesalePrice = $this->getProductWholesalePrice( $productID , $userWholesaleRole );
                $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_cart' , $wholesalePrice , $productID , $userWholesaleRole , $value );

            } elseif ( $value[ 'data' ]->product_type == 'variation' ) {

                $productID = $value[ 'data' ]->variation_id;
                $wholesalePrice = $this->getProductWholesalePrice( $productID , $userWholesaleRole );
                $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_cart' , $wholesalePrice , $productID , $userWholesaleRole , $value );

            }

            if ( is_numeric( $wholesalePrice ) ) {

                if ( is_cart() || is_checkout() ) {

                    $applyTax = get_option( 'woocommerce_tax_display_cart' );
                    $tax_exempted = get_option( 'wwpp_settings_tax_exempt_wholesale_users' );

                    $wholesale_role_tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
                    if ( !is_array( $wholesale_role_tax_option_mapping ) )
                        $wholesale_role_tax_option_mapping = array();

                    if ( array_key_exists( $userWholesaleRole[ 0 ] , $wholesale_role_tax_option_mapping ) )
                        $tax_exempted = $wholesale_role_tax_option_mapping[ $userWholesaleRole[ 0 ] ][ 'tax_exempted' ];

                    if ( $applyTax == 'incl' && $tax_exempted != 'yes' )
                        $wholesalePrice = wc_price( $value[ 'data' ]->get_price_including_tax( 1 , $wholesalePrice ) ) ;
                    elseif ( $applyTax == 'excl' )
                        $wholesalePrice = wc_price( $value[ 'data' ]->get_price_excluding_tax( 1 , $wholesalePrice ) );

                } else {

                    $taxes_enabled = get_option( 'woocommerce_calc_taxes' );
                    $wholesale_tax_display_shop = get_option( 'wwpp_settings_incl_excl_tax_on_wholesale_price' );
                    $woocommerce_tax_display_shop = get_option( 'woocommerce_tax_display_shop' );

                    if ( $taxes_enabled == 'yes' && $wholesale_tax_display_shop == 'incl'  )
                        $wholesalePrice = wc_price( $value[ 'data' ]->get_price_including_tax( 1 , $wholesalePrice ) );
                    elseif ( $wholesale_tax_display_shop == 'excl' )
                        $wholesalePrice = wc_price( $value[ 'data' ]->get_price_excluding_tax( 1 , $wholesalePrice ) );
                    elseif ( empty( $wholesale_tax_display_shop ) ) {

                        if ( $taxes_enabled == 'yes' && $woocommerce_tax_display_shop == 'incl' )
                            $wholesalePrice = wc_price( $value[ 'data' ]->get_price_including_tax( 1 , $wholesalePrice ) );
                        else
                            $wholesalePrice = wc_price( $value[ 'data' ]->get_price_excluding_tax( 1 , $wholesalePrice ) );

                    }

                }

            }

            if ( $value[ 'data' ]->product_type == 'simple' ) {

                $moq = get_post_meta( $value[ 'data' ]->id , $userWholesaleRole[ 0 ] . '_wholesale_minimum_order_quantity' , true );
                $moq = ( is_numeric( $moq ) ) ? ( int ) $moq : 0;

                if ( $wholesalePrice && $value[ 'quantity' ] < $moq ) {

                    $notices[] = array(
                        'type'      =>  'notice',
                        'message'   =>  __( 'You did not meet the minimum order quantity <b>(' . $moq . ' items)</b> of the product <b>' . $value[ 'data' ]->get_title() . '</b> to activate wholesale pricing <b>(' . $wholesalePrice . ')</b>. Please increase quantities to the cart to activate adjusted pricing.' , 'woocommerce-wholesale-prices-premium' )
                    );

                }

            } elseif ( $value[ 'data' ]->product_type == 'variation' ) {

                $moq = get_post_meta( $value[ 'data' ]->variation_id , $userWholesaleRole[ 0 ] . '_wholesale_minimum_order_quantity' , true );
                $moq = ( is_numeric( $moq ) ) ? ( int ) $moq : 0;
                
                if ( $wholesalePrice && $value[ 'quantity' ] < $moq ) {

                    $variableAttributes = "";

                    if ( is_array( $value[ 'variation' ] ) && !empty( $value[ 'variation' ] ) ) {

                        foreach ( $value[ 'variation' ] as $attribute => $attributeVal ) {

                            $attribute = ucwords( str_replace( 'attribute_' , '' , $attribute ) );

                            if ( !empty( $variableAttributes ) )
                                $variableAttributes .= ", ";

                            $variableAttributes .= $attribute . " : " . $attributeVal;

                        }

                    }

                    if ( !empty( $variableAttributes ) )
                        $variableAttributes = "(" . $variableAttributes . ")";

                    $notices[] = array(
                        'type'      =>  'notice',
                        'message'   =>  __( 'You did not meet the minimum order quantity <b>(' . $moq . ' items)</b> of the product <b>' . $value[ 'data' ]->get_title() . " " . $variableAttributes . '</b> to activate wholesale pricing <b>(' . $wholesalePrice . ')</b>. Please increase quantities to the cart to activate adjusted pricing.' , 'woocommerce-wholesale-prices-premium' )
                    );

                }

            }

            // Also check if wholesale price is defined (no need to notify user if there's no wholesale price)
            if ( !empty( $notices ) && !empty( $wholesalePrice ) )
                return $notices;
            else
                return $apply_wholesale_price;

        } else
            return false; // Not a wholesale user

    }

    /**
     * Apply tax exemptions to wholesale users based on settings.
     *
     * @param $userWholesaleRole
     *
     * @return array
     * @since 1.0.0
     */
    public function applyTaxExemptionsToWholesaleUsers( $userWholesaleRole ) {

        if ( !empty( $userWholesaleRole ) ) {

            global $woocommerce;

            $tax_exempted = get_option( 'wwpp_settings_tax_exempt_wholesale_users' );

            $wholesale_role_tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $wholesale_role_tax_option_mapping ) )
                $wholesale_role_tax_option_mapping = array();

            if ( array_key_exists( $userWholesaleRole[ 0 ] , $wholesale_role_tax_option_mapping ) )
                $tax_exempted = $wholesale_role_tax_option_mapping[ $userWholesaleRole[ 0 ] ][ 'tax_exempted' ];

            // We just tax exempt wholesale users if they are eligible based on the settings
            // else, we don't explicitly set them to not be tax exempted as other plugins may modify tax exemptions too
            if ( $tax_exempted == 'yes' ) {

                $woocommerce->customer->set_is_vat_exempt( true );

            }

        }

    }

    /**
     * Filter the text for the wholesale price title.
     *
     * @param $titleText
     *
     * @return mixed
     * @since 1.0.0
     */
    public function filterWholesalePriceTitleText ( $titleText ) {

        $settingTitleText = esc_attr( trim( get_option( 'wwpp_settings_wholesale_price_title_text' ) ) );
        return $settingTitleText;

    }

    /**
     * Used to show/hide original product price.
     *
     * @param $wholesalePriceHTML
     *
     * @return mixed
     * @since 1.3.2
     */
    public function filterProductOriginalPriceVisibility( $wholesalePriceHTML ) {

        if ( get_option( 'wwpp_settings_hide_original_price' ) == "yes" ) {

            $dom = new DomDocument();
            $dom->loadHTML( $wholesalePriceHTML );
            $finder = new DomXPath( $dom );
            $className = "wholesale_price_container";
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

            $filteredWholesalePriceHTML = '<span style="display: block;" class="wholesale_price_container">';

            foreach ( $nodes as $node ) {

                $filteredWholesalePriceHTML .= $this->getNodeInnerHTML( $node );
                break;

            }

            $filteredWholesalePriceHTML .= '</span>';

            $wholesalePriceHTML = $filteredWholesalePriceHTML;

        }

        return $wholesalePriceHTML;

    }

    /**
     * Override the price suffix for wholesale users only.
     *
     * @param $priceDisplaySuffix
     * @param $userWholesaleRole
     *
     * @return mixed
     * @since 1.4.0
     */
    public function overrideWholesalePriceSuffix( $priceDisplaySuffix , $userWholesaleRole ) {

        $newPriceSuffix = get_option( 'wwpp_settings_override_price_suffix' );

        if ( !empty( $userWholesaleRole ) )
            return !empty( $newPriceSuffix ) ? ' <small class="woocommerce-price-suffix wholesale-price-suffix">' . $newPriceSuffix . '</small>' : $priceDisplaySuffix;
     
        return $priceDisplaySuffix;

    }

    /**
     * Filter the price to show the minimum order quantity for wholesale users.
     *
     * @param $wholesalePriceHTML
     * @param $price
     * @param $product
     * @param $userWholesaleRole
     * @return string
     *
     * @since 1.4.0
     */
    public function displayMinimumWholesaleOrderQuantity( $wholesalePriceHTML , $price , $product , $userWholesaleRole ) {

        if ( !empty( $userWholesaleRole ) ) {

            $product_id = null;

            // No need for variable product, we don't need it be applied on a price range
            // We need it to be applied per variation for variable products
            if ( $product->product_type == 'simple' )
                $product_id = $product->id;
            elseif ( $product->product_type == 'variation' )
                $product_id = $product->variation_id;

            if ( $product_id ) {

                $wholesalePrice = $this->getProductWholesalePrice( $product_id , $userWholesaleRole );
                $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_shop' , $wholesalePrice , $product_id , $userWholesaleRole );

                // Don't bother if there is no wholesale price
                if ( $wholesalePrice ) {

                    $minimumOrder = get_post_meta( $product_id , $userWholesaleRole[ 0 ] . "_wholesale_minimum_order_quantity" , true );

                    if( $minimumOrder && $minimumOrder > 0 )
                        $wholesalePriceHTML .= __( ' <span class="wholesale_price_minimum_order_quantity">Min: ' . $minimumOrder . '</span>' , 'woocommerce-wholesale-prices-premium' );

                }

            }

        }

        return $wholesalePriceHTML;

    }

    /**
     * Set minimum order quantity as minimum value ( default value ) for a given product if one is set.
     *
     * @param $args
     * @param $product
     * @param $userWholesaleRole
     * @return mixed
     *
     * @since 1.4.2
     */
    public function setMinimumOrderQuantityAsInitialValue ( $args , $product , $userWholesaleRole ) {

        $filteredArgs = $args;

        if ( !empty( $userWholesaleRole ) && ( is_product() || is_shop() || is_product_category() || is_product_tag() ) ) {

            $product_id = null;

            // No need for variable product, we don't need it be applied on a price range
            // We need it to be applied per variation for variable products
            if ( $product->product_type == 'simple' )
                $product_id = $product->id;
            elseif ( $product->product_type == 'variation' )
                $product_id = $product->variation_id;

            if ( $product_id ) {

                $wholesalePrice = $this->getProductWholesalePrice( $product_id , $userWholesaleRole );
                $wholesalePrice = apply_filters( 'wwp_filter_wholesale_price_shop' , $wholesalePrice , $product_id , $userWholesaleRole );

                $minimumOrder = get_post_meta( $product_id , $userWholesaleRole[ 0 ] . "_wholesale_minimum_order_quantity" , true );

                if ( $minimumOrder && $wholesalePrice )
                    $filteredArgs[ 'input_value' ] = $minimumOrder;

            }

        }

        return apply_filters( 'wwpp_filter_set_product_quantity_value_to_minimum_order_quantity' , $filteredArgs , $args , $product , $userWholesaleRole );

    }

    /**
     * Integrate tax to product price on shop pages ( Either include or exclude ).
     * Wholesale user role tax exemptions only apply on cart and checkout pages.
     * It don't apply on shop pages.
     * So even if wholesale user is tax exempted, if setting on the backend to display product price on the shop to
     * include taxes. Then prices will include tax on the shop page even for a tax exempted wholesale user.
     * After he/she adds that product to the cart and go to the cart, that's where tax exemption applies.
     *
     * @param $wholesalePrice
     * @param $productId
     * @param $userWholesaleRole
     *
     * @return mixed
     * @since 1.0.0
     */
    public function integrateTaxToWholesalePriceOnShop( $wholesalePrice , $productId , $userWholesaleRole ) {

        if ( !empty( $wholesalePrice ) && !empty( $userWholesaleRole ) ) {

            if ( function_exists( 'wc_get_product' ) )
                $product = wc_get_product( $productId );
            else
                $product = WWPP_WC_Functions::wc_get_product( $productId );

            $taxes_enabled = get_option( 'woocommerce_calc_taxes' );
            $wholesale_tax_display_shop = get_option( 'wwpp_settings_incl_excl_tax_on_wholesale_price' );
            $woocommerce_tax_display_shop = get_option( 'woocommerce_tax_display_shop' );

            if ( $taxes_enabled == 'yes' && $wholesale_tax_display_shop == 'incl'  )
                $wholesalePrice = $product->get_price_including_tax( 1 , $wholesalePrice );
            elseif ( $wholesale_tax_display_shop == 'excl' )
                $wholesalePrice = $product->get_price_excluding_tax( 1 , $wholesalePrice );
            elseif ( empty( $wholesale_tax_display_shop ) ) {

                if ( $taxes_enabled == 'yes' && $woocommerce_tax_display_shop == 'incl' )
                    $wholesalePrice = $product->get_price_including_tax( 1 , $wholesalePrice );
                else
                    $wholesalePrice = $product->get_price_excluding_tax( 1 , $wholesalePrice );

            }

        }

        return $wholesalePrice;

    }

    /**
     * Set coupons availability to wholesale users.
     *
     * @param $enabled
     * @param $userWholesaleRole
     * @return bool
     *
     * @since 1.5.0
     */
    public function toggleAvailabilityOfCouponsToWholesaleUsers ( $enabled , $userWholesaleRole ) {

        $disableCoupons = get_option( 'wwpp_settings_disable_coupons_for_wholesale_users' );

        if ( $disableCoupons == 'yes' && !empty( $userWholesaleRole ) )
            $enabled = false;

        return $enabled;

    }

    /**
     * Override "woocommerce_tax_display_cart" option for wholesale users.
     *
     * @since 1.5.0
     * @param $optionValue
     * @param $userWholesaleRole
     * @return string
     */
    public function wholesaleTaxDisplayCart( $optionValue , $userWholesaleRole ) {

        // Only apply to front end and on wholesale users
        if ( !is_admin() && !empty( $userWholesaleRole ) ) {

            $taxes_enabled = get_option( 'woocommerce_calc_taxes' );
            $wholesale_tax_display_cart = get_option( 'wwpp_settings_wholesale_tax_display_cart' );

            $tax_exempted = get_option( 'wwpp_settings_tax_exempt_wholesale_users' );

            $wholesale_role_tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $wholesale_role_tax_option_mapping ) )
                $wholesale_role_tax_option_mapping = array();

            if ( array_key_exists( $userWholesaleRole[ 0 ] , $wholesale_role_tax_option_mapping ) )
                $tax_exempted = $wholesale_role_tax_option_mapping[ $userWholesaleRole[ 0 ] ][ 'tax_exempted' ];

            if ( $taxes_enabled == 'yes' && $tax_exempted != 'yes' && $wholesale_tax_display_cart == 'incl' )
                $optionValue = 'incl';
            elseif ( $taxes_enabled != 'yes' || $tax_exempted == 'yes' || $wholesale_tax_display_cart == 'excl' )
                $optionValue = 'excl';

        }

        return $optionValue;

    }

    /**
     * Filter wholesale product price on cart page and cart widget to apply taxing accordingly.
     *
     * @since 1.4.6
     * @param $price
     * @param $cartItem
     * @param $cartItemKey
     * @param $wholesalePrice
     * @param $userWholesaleRole
     * @return mixed
     */
    public function wholesaleCartItemPrice( $price , $cartItem , $cartItemKey , $wholesalePrice , $userWholesaleRole ) {

        // Check first if we even need to filter the cart item price and display wholesale price.
        // Check if cart meets requirements to avail wholesale pricing.
        // If it did not meet the requirements, then no need to show wholesale pricing on the cart item.
        $applyWholesalePrice = $this->applyWholesalePriceFlagFilter( true , WC()->cart , $userWholesaleRole );
        if ( $applyWholesalePrice === true )
            $applyWholesalePrice = $this->applyWholesalePricePerProductBasisFilter( $applyWholesalePrice , $cartItem , WC()->cart , $userWholesaleRole );

        if ( $applyWholesalePrice === true && !empty( $userWholesaleRole ) ) {

            $applyTax = get_option( 'woocommerce_tax_display_cart' );

            $tax_exempted = get_option( 'wwpp_settings_tax_exempt_wholesale_users' );

            $wholesale_role_tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $wholesale_role_tax_option_mapping ) )
                $wholesale_role_tax_option_mapping = array();

            if ( array_key_exists( $userWholesaleRole[ 0 ] , $wholesale_role_tax_option_mapping ) )
                $tax_exempted = $wholesale_role_tax_option_mapping[ $userWholesaleRole[ 0 ] ][ 'tax_exempted' ];

            if ( $applyTax == 'incl' && $tax_exempted != 'yes' )
                return wc_price( $cartItem[ 'data' ]->get_price_including_tax( 1 , $wholesalePrice ) ) ;
            elseif ( $applyTax == 'excl' )
                return wc_price( $cartItem[ 'data' ]->get_price_excluding_tax( 1 , $wholesalePrice ) );

        }

        return $price;

    }

    /**
     * Display quantity based discount markup on single product pages.
     *
     * @since 1.6.0
     * @since 1.7.0 Add Aelia currency switcher plugin integration
     *
     * @param $wholesalePriceHTML
     * @param $price
     * @param $product
     * @param $userWholesaleRole
     * @return string
     */
    public function displayOrderQuantityBasedWholesalePricing( $wholesalePriceHTML , $price , $product , $userWholesaleRole ) {

        // Only apply this to single product pages
        if ( !empty( $userWholesaleRole ) && is_product() && ( $product->product_type == 'simple' || $product->product_type == 'variation' ) ) {

            if ( $product->product_type == 'simple' )
                $productId = $product->id;
            elseif ( $product->product_type == 'variation' )
                $productId = $product->variation_id;

            // Since quantity based wholesale pricing relies on the presence of the wholesale price at a product level
            // We need to get the original wholesale price ( per product level ), we don't need to filter the wholesale price.
            $wholesalePrice = self::getProductWholesalePrice( $productId , $userWholesaleRole );

            $hideQuantityDiscountTable = get_option( 'wwpp_settings_hide_quantity_discount_table' , false );

            if ( !empty( $wholesalePrice ) && $hideQuantityDiscountTable != 'yes' ) {

                $enabled = get_post_meta( $productId , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );

                $mapping = get_post_meta( $productId , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                if ( !is_array( $mapping ) )
                    $mapping = array();

                // Table view
                $mappingTableHtml = '';

                if ( $enabled == 'yes' && !empty( $mapping ) ) {
                    ob_start();

                    /*
                     * Get the base currency mapping. The base currency mapping well determine what wholesale
                     * role and range pairing a product has wholesale price with.
                     */
                    $baseCurrencyMapping = $this->_getBaseCurrencyMapping( $mapping , $userWholesaleRole );

                    if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                        $baseCurrency   = WWPP_ACS_Integration_Helper::get_product_base_currency( $productId );
                        $activeCurrency = get_woocommerce_currency();

                        // No point on doing anything if have no base currency mapping
                        if ( !empty( $baseCurrencyMapping ) ) {

                            if ( $baseCurrency == $activeCurrency ) {

                                /*
                                 * If active currency is equal to base currency, then we just need to pass
                                 * the base currency mapping.
                                 */
                                $this->_printWholesalePricePerOrderQuantityTable( $baseCurrencyMapping , array() , $mapping , $product , $userWholesaleRole , true , $baseCurrency , $activeCurrency );

                            } else {

                                $specific_currency_mapping = $this->_getSpecificCurrencyMapping( $mapping , $userWholesaleRole , $activeCurrency , $baseCurrencyMapping );

                                $this->_printWholesalePricePerOrderQuantityTable( $baseCurrencyMapping , $specific_currency_mapping , $mapping , $product , $userWholesaleRole , false , $baseCurrency , $activeCurrency );

                            }

                        }

                    } else {

                        // Default without Aelia currency switcher plugin

                        if ( !empty( $baseCurrencyMapping ) )
                            $this->_printWholesalePricePerOrderQuantityTable( $baseCurrencyMapping , array() , $mapping , $product , $userWholesaleRole , true , get_woocommerce_currency() , get_woocommerce_currency() );

                    }

                    $mappingTableHtml = ob_get_clean();

                }

                $wholesalePriceHTML .= $mappingTableHtml;

            }

        }

        return $wholesalePriceHTML;

    }

    /**
     * Print wholesale pricing per order quantity table.
     *
     * @since 1.7.0
     * @since 1.7.1 Apply taxing on the wholesale price on the per order quantity wholesale pricing table.
     *
     * @param $baseCurrencyMapping
     * @param $specificCurrencyMapping
     * @param $mapping
     * @param $product
     * @param $userWholesaleRole
     * @param $isBaseCurrency
     * @param $baseCurrency
     * @param $activeCurrency
     */
    private function _printWholesalePricePerOrderQuantityTable( $baseCurrencyMapping , $specificCurrencyMapping , $mapping , $product , $userWholesaleRole , $isBaseCurrency , $baseCurrency , $activeCurrency ) { ?>

        <table class="order-quantity-based-wholesale-pricing-view table-view">

            <thead>
                <tr>
                    <?php do_action( 'wwpp_action_before_wholesale_price_table_per_order_quantity_heading_view' , $mapping , $product , $userWholesaleRole ); ?>
                    <th><?php echo apply_filters( 'wwpp_filter_wholesale_price_table_per_order_quantity_qty_heading_txt' , __( 'Qty' , 'woocommerce-wholesale-prices-premium' ) );  ?></th>
                    <th><?php echo apply_filters( 'wwpp_filter_wholesale_price_table_per_order_quantity_price_heading_txt' , __( 'Price' , 'woocommerce-wholesale-prices-premium' ) );  ?></th>
                    <?php do_action( 'wwpp_action_after_wholesale_price_table_per_order_quantity_heading_view' , $mapping , $product , $userWholesaleRole ); ?>
                </tr>
            </thead>

            <tbody>

                <?php
                if ( !$isBaseCurrency ) {

                    // Specific currency

                    foreach ( $baseCurrencyMapping as $baseMap ) {

                        /*
                         * Even if this is a not a base currency, we will still rely on the base currency "RANGE".
                         * Because some range that are present on the base currency, may not be present in this current currency.
                         * But this current currency still has a wholesale price for that range, its wholesale price will be derived
                         * from base currency wholesale price by converting it to this current currency.
                         *
                         * Also if a wholesale price is set for this current currency range ( ex. 10 - 20 ) but that range
                         * is not present on the base currency mapping. We don't recognize this specific product on this range
                         * ( 10 - 20 ) as having wholesale price. User must set wholesale price on the base currency for the
                         * 10 - 20 range for this to be recognized as having a wholesale price.
                         */

                        $qty = $baseMap[ 'start_qty' ];

                        if ( !empty( $baseMap[ 'end_qty' ] ) )
                            $qty .= ' - ' . $baseMap[ 'end_qty' ];
                        else
                            $qty .= '+';

                        $price = '';

                        /*
                         * First check if a price is set for this wholesale role : range pair in the specific currency mapping.
                         * If wholesale price is present, then use it.
                         */
                        foreach ( $specificCurrencyMapping as $specificMap ) {

                            if ( $specificMap[ $activeCurrency . '_start_qty' ] == $baseMap[ 'start_qty' ] && $specificMap[ $activeCurrency . '_end_qty' ] == $baseMap[ 'end_qty' ] )
                                $price = wc_price( $specificMap[ $activeCurrency . '_wholesale_price' ] , array( 'currency' => $activeCurrency ) );

                        }

                        /*
                         * Now if there is no mapping for this specific wholesale role : range pair inn the specific currency mapping,
                         * since this range is present on the base map mapping. We derive the price by converting the price set on the
                         * base currency mapping to this active currency.
                         */
                        if ( !$price ) {

                            $price = WWPP_ACS_Integration_Helper::convert( $baseMap[ 'wholesale_price' ] , $activeCurrency , $baseCurrency );
                            $price = $this->getProductShopPriceWithTaxingApplied( $product , $price , array( 'currency' => $activeCurrency ) );

                        } ?>

                        <tr>
                            <?php do_action( 'wwpp_action_before_wholesale_price_table_per_order_quantity_entry_view' , $baseMap , $product , $userWholesaleRole ); ?>
                            <td><?php echo $qty; ?></td>
                            <td><?php echo $price; ?></td>
                            <?php do_action( 'wwpp_action_after_wholesale_price_table_per_order_quantity_entry_view' , $baseMap , $product , $userWholesaleRole ); ?>
                        </tr>

                    <?php }

                } else {

                    /*
                     * Base currency.
                     * Also the default if Aelia currency switcher plugin isn't active.
                     */
                    foreach ( $baseCurrencyMapping as $map ) {

                        $qty = $map[ 'start_qty' ];

                        if ( !empty( $map[ 'end_qty' ] ) )
                            $qty .= ' - ' . $map[ 'end_qty' ];
                        else
                            $qty .= '+';

                        $price = $this->getProductShopPriceWithTaxingApplied( $product , $map[ 'wholesale_price' ] , array( 'currency' => $baseCurrency ) ); ?>

                        <tr>
                            <?php do_action( 'wwpp_action_before_wholesale_price_table_per_order_quantity_entry_view' , $map , $product , $userWholesaleRole ); ?>
                            <td><?php echo $qty; ?></td>
                            <td><?php echo $price; ?></td>
                            <?php do_action( 'wwpp_action_after_wholesale_price_table_per_order_quantity_entry_view' , $map , $product , $userWholesaleRole ); ?>
                        </tr>

                    <?php }

                } ?>

            </tbody>

        </table><!--.order-quantity-based-wholesale-pricing-view table-view-->

    <?php

    }

    /**
     * Apply quantity based discount on products on cart.
     *
     * @since 1.6.0
     * @since 1.7.0 Add Aelia currency switcher plugin integration
     *
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @param $cartItem
     * @return mixed
     */
    public function applyOrderQuantityBasedWholesalePricing( $wholesalePrice , $productID , $userWholesaleRole , $cartItem ) {

        // Quantity based discount depends on a wholesale price being set on the per product level
        // If none is set, then, quantity based discount will not be applied even if it is defined
        if ( !empty( $wholesalePrice ) && !empty( $userWholesaleRole ) ) {

            $enabled = get_post_meta( $productID , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );

            $mapping = get_post_meta( $productID , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
            if ( !is_array( $mapping ) )
                $mapping = array();

            if ( $enabled == 'yes' && !empty( $mapping ) ) {

                /*
                 * Get the base currency mapping. The base currency mapping well determine what wholesale
                 * role and range pairing a product has wholesale price with.
                 */
                $baseCurrencyMapping = $this->_getBaseCurrencyMapping( $mapping , $userWholesaleRole );

                if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                    $baseCurrency   = WWPP_ACS_Integration_Helper::get_product_base_currency( $productID );
                    $activeCurrency = get_woocommerce_currency();

                    if ( $baseCurrency == $activeCurrency ) {

                        $wholesalePrice = $this->_getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , array() , $cartItem , $baseCurrency , $activeCurrency , true );

                    } else {

                        // Get specific currency mapping
                        $specific_currency_mapping = $this->_getSpecificCurrencyMapping( $mapping , $userWholesaleRole , $activeCurrency , $baseCurrencyMapping );

                        $wholesalePrice = $this->_getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , $specific_currency_mapping , $cartItem , $baseCurrency , $activeCurrency , false );

                    }

                } else {

                    $wholesalePrice = $this->_getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , array() , $cartItem , get_woocommerce_currency() , get_woocommerce_currency() , true );

                }

            } // if ( $enabled == 'yes' && !empty( $mapping ) )

        }

        return $wholesalePrice;

    }

    /**
     * Get the wholesale price of a wholesale role for the appropriate range from the wholesale price per order
     * quantity mapping that is appropriate for the current items on the current wholesale user's cart.
     *
     * @since 1.7.0
     *
     * @param $wholesalePrice
     * @param $baseCurrencyMapping
     * @param $specificCurrencyMapping
     * @param $cartItem
     * @param $baseCurrency
     * @param $activeCurrency
     * @param $isBaseCurrency
     * @return float|string
     */
    private function _getWholesalePriceFromMapping( $wholesalePrice , $baseCurrencyMapping , $specificCurrencyMapping , $cartItem , $baseCurrency , $activeCurrency , $isBaseCurrency ) {

        if ( !$isBaseCurrency ) {

            foreach ( $baseCurrencyMapping as $baseMap ) {

                $price = "";

                /*
                 * First check if a price is set for this wholesale role : range pair in the specific currency mapping.
                 * If wholesale price is present, then use it.
                 */
                foreach ( $specificCurrencyMapping as $specificMap ) {

                    if ( $cartItem[ 'quantity' ] >= $specificMap[ $activeCurrency . '_start_qty' ] &&
                        ( empty( $specificMap[ $activeCurrency . '_end_qty' ] ) || $cartItem[ 'quantity' ] <= $specificMap[ $activeCurrency . '_end_qty' ] ) &&
                        $specificMap[ $activeCurrency . '_wholesale_price' ] != '' )
                        $price = $specificMap[ $activeCurrency . '_wholesale_price' ];

                }

                /*
                 * Now if there is no mapping for this specific wholesale role : range pair inn the specific currency mapping,
                 * since this range is present on the base map mapping. We derive the price by converting the price set on the
                 * base currency mapping to this active currency.
                 */
                if ( !$price ) {

                    if ( $cartItem[ 'quantity' ] >= $baseMap[ 'start_qty' ] &&
                        ( empty( $baseMap[ 'end_qty' ] ) || $cartItem[ 'quantity' ] <= $baseMap[ 'end_qty' ] ) &&
                        $baseMap[ 'wholesale_price' ] != '' )
                        $price = WWPP_ACS_Integration_Helper::convert( $baseMap[ 'wholesale_price' ] , $activeCurrency , $baseCurrency );

                }

                if ( $price ) {

                    $wholesalePrice = $price;
                    break;

                }

            }

        } else {

            foreach ( $baseCurrencyMapping as $map ) {

                if ( $cartItem[ 'quantity' ] >= $map[ 'start_qty' ] &&
                    ( empty( $map[ 'end_qty' ] ) || $cartItem[ 'quantity' ] <= $map[ 'end_qty' ] ) &&
                    $map[ 'wholesale_price' ] != '' ) {

                    $wholesalePrice = $map[ 'wholesale_price' ];
                    break;

                }

            }

        }

        return $wholesalePrice;

    }

    /**
     * Get the base currency mapping from the wholesale price per order quantity mapping.
     *
     * @since 1.7.0
     *
     * @param $mapping
     * @param $userWholesaleRole
     * @return array
     */
    private function _getBaseCurrencyMapping( $mapping , $userWholesaleRole ) {

        $baseCurrencyMapping = array();

        foreach ( $mapping as $map ) {

            // Skip non base currency mapping
            if ( array_key_exists( 'currency' , $map ) )
                continue;

            // Skip mapping not meant for the current user wholesale role
            if ( $userWholesaleRole[ 0 ] != $map[ 'wholesale_role' ] )
                continue;

            $baseCurrencyMapping[] = $map;

        }

        return $baseCurrencyMapping;

    }

    /**
     * Get the specific currency mapping from the wholesale price per order quantity mapping.
     *
     * @since 1.7.0
     *
     * @param $mapping
     * @param $userWholesaleRole
     * @param $activeCurrency
     * @param $baseCurrencyMapping
     * @return array
     */
    private function _getSpecificCurrencyMapping( $mapping , $userWholesaleRole , $activeCurrency , $baseCurrencyMapping ) {

        // Get specific currency mapping
        $specificCurrencyMapping = array();

        foreach ( $mapping as $map ) {

            // Skip base currency
            if ( !array_key_exists( 'currency' , $map ) )
                continue;

            // Skip mappings that are not for the active currency
            if ( !array_key_exists( $activeCurrency . '_wholesale_role' , $map ) )
                continue;

            // Skip mapping not meant for the currency user wholesale role
            if ( $userWholesaleRole[ 0 ] != $map[ $activeCurrency . '_wholesale_role' ] )
                continue;

            // Only extract out mappings for this current currency that has equivalent mapping
            // on the base currency.
            foreach ( $baseCurrencyMapping as $base_map ) {

                if ( $base_map[ 'start_qty' ] == $map[ $activeCurrency . '_start_qty' ] && $base_map[ 'end_qty' ] == $map[ $activeCurrency . '_end_qty' ] ) {

                    $specificCurrencyMapping[] = $map;
                    break;

                }

            }

        }

        return $specificCurrencyMapping;

    }

    /**
     * Apply product category level wholesale discount. Only applies when a product has no wholesale price set.
     *
     * @since 1.0.5
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @return mixed
     */
    public function applyProductCategoryWholesaleDiscount ( $wholesalePrice , $productID , $userWholesaleRole ) {

        if ( empty( $wholesalePrice ) && !empty( $userWholesaleRole ) ) {

            if ( function_exists( 'wc_get_product' ) )
                $product = wc_get_product( $productID );
            else
                $product = WWPP_WC_Functions::wc_get_product( $productID );

            $post_id = null;
            $product_price = $product->get_price(); // Already is the calculated price

            if ( $product->product_type == 'simple' )
                $post_id = $productID;
            elseif ( $product->product_type == 'variation' )
                $post_id = $product->parent->id;

            if ( !is_null( $post_id ) ) {

                $terms = get_the_terms( $post_id , 'product_cat' );
                if ( !is_array( $terms ) )
                    $terms = array();

                $lowest_discount = null;
                $highest_discount = null;

                foreach ( $terms as $term ) {

                    $category_wholesale_prices = get_option( 'taxonomy_' . $term->term_id );

                    if ( is_array( $category_wholesale_prices ) && array_key_exists( $userWholesaleRole[ 0 ] . '_wholesale_discount' , $category_wholesale_prices ) ) {

                        $curr_discount = $category_wholesale_prices[ $userWholesaleRole[ 0 ] . '_wholesale_discount' ];

                        if ( !empty( $curr_discount ) ) {

                            if ( is_null( $lowest_discount ) || $curr_discount < $lowest_discount )
                                $lowest_discount = $curr_discount;

                            if ( is_null( $highest_discount ) || $curr_discount > $highest_discount )
                                $highest_discount = $curr_discount;

                        }

                    }

                }

                $category_wholsale_price_logic = get_option( 'wwpp_settings_multiple_category_wholesale_discount_logic' );

                if ( $category_wholsale_price_logic == 'highest' ) {

                    if ( !is_null( $highest_discount ) )
                        $wholesalePrice = $product_price - ( $product_price * ( $highest_discount / 100 ) );

                } else {

                    if ( !is_null( $lowest_discount ) )
                        $wholesalePrice = $product_price - ( $product_price * ( $lowest_discount / 100 ) );

                }

                if ( $wholesalePrice < 0 )
                    $wholesalePrice = 0;

            }

        }

        return $wholesalePrice;

    }

    /**
     * Apply wholesale role general discount to the product being purchased by this user.
     * Only applies if
     * General discount is set for this wholesale role
     * No category level discount is set
     * No wholesale price is set
     *
     * @since 1.2.0
     * @param $wholesalePrice
     * @param $productID
     * @param $userWholesaleRole
     * @return string
     */
    public function applyWholesaleRoleGeneralDiscount ( $wholesalePrice , $productID , $userWholesaleRole ) {

        if ( empty( $wholesalePrice ) && !empty( $userWholesaleRole ) ) {

            $roleDiscount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING );
            if ( !is_array( $roleDiscount ) )
                $roleDiscount = array();

            if ( array_key_exists( $userWholesaleRole[ 0 ] , $roleDiscount ) && is_numeric( $roleDiscount[ $userWholesaleRole[ 0 ] ] ) ) {

                if ( function_exists( 'wc_get_product' ) )
                    $product = wc_get_product( $productID );
                else
                    $product = WWPP_WC_Functions::wc_get_product( $productID );

                $product_price = $product->get_price(); // Already is the calculated price

                $wholesalePrice = $product_price - ( $product_price * ( $roleDiscount[ $userWholesaleRole[ 0 ] ] / 100 ) );

            }

        }

        return $wholesalePrice;

    }




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Helper Functions
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Get wholesale price of a product ( Per Product, Per Category and Per General Discount of a Wholesale Role ).
     *
     * @param $productID
     * @param $userWholesaleRole
     * @return mixed
     *
     * @since 1.4.0
     */
    public static function getProductWholesalePrice ( $productID , $userWholesaleRole ) {

        $wholesalePrice = WWP_Wholesale_Prices::getProductWholesalePrice( $productID , $userWholesaleRole );

        return $wholesalePrice;

    }

    /**
     * Get inner html of a DOMNode object.
     *
     * @param $node
     * @return string
     *
     * @since 1.4.1
     */
    public function getNodeInnerHTML( $node ) {

        $innerHTML= '';
        $children = $node->childNodes;
        foreach ( $children as $child )
            $innerHTML .= $child->ownerDocument->saveXML( $child );

        return $innerHTML;

    }

}