<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WWPP_Settings {

    private static $_instance;

    public static function getInstance() {
        if (!self::$_instance instanceof self)
            self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * Change the title of the general settings section of the plugin's settings.
     *
     * @param $generalSectionTitle
     * @return mixed
     *
     * @since 1.0.3
     */
    public function pluginSettingsGeneralSectionTitle ( $generalSectionTitle ) {

        $generalSectionTitle = __( 'General' , 'woocommerce-wholesale-prices-premium' );
        return $generalSectionTitle;

    }

    /**
     * Plugin Settings Sections.
     *
     * @param $sections
     *
     * @return mixed
     * @since 1.0.0
     */
    public function pluginSettingsSections ( $sections ) {

        $sections[ 'wwpp_setting_tax_section' ]             =   __( 'Tax' , 'woocommerce-wholesale-prices-premium' );
        $sections[ 'wwpp_setting_shipping_section' ]        =   __( 'Shipping' , 'woocommerce-wholesale-prices-premium' );
        $sections[ 'wwpp_setting_discount_section' ]        =   __( 'Discount' , 'woocommerce-wholesale-prices-premium' );
        $sections[ 'wwpp_setting_payment_gateway_section' ] =   __( 'Payment Gateway' , 'woocommerce-wholesale-prices-premium' );
        $sections[ 'wwpp_setting_help_section' ]            =   __( 'Help' , 'woocommerce-wholesale-prices-premium' );

        return $sections;

    }

    /**
     * Plugin Settings Section Contents.
     *
     * @param $settings
     * @param $current_section
     *
     * @return mixed
     * @since 1.0.0
     */
    public function pluginSettingsSectionContent( $settings , $current_section ) {

        if ( $current_section == '' ) {

            // General Settings Section
            $wwppGeneralSettings = apply_filters( 'wwpp_settings_general_section_settings', $this->_get_general_section_settings() ) ;
            $settings = array_merge( $settings , $wwppGeneralSettings );

        } elseif ( $current_section == 'wwpp_setting_tax_section' ) {

            // Tax Settings Section
            $wwppTaxSettings = apply_filters( 'wwpp_settings_tax_section_settings' , $this->_get_tax_section_settings() );
            $settings = array_merge( $settings , $wwppTaxSettings );

        } elseif ( $current_section == 'wwpp_setting_shipping_section' ) {

            // Shipping Settings Section
            $wwppShippingSettings = apply_filters( 'wwpp_settings_shipping_section_settings' , $this->_get_shipping_section_settings() );
            $settings = array_merge( $settings , $wwppShippingSettings );

        } elseif ( $current_section == 'wwpp_setting_discount_section' ) {

            // Discount Settings Section
            $wwppDiscountSettings = apply_filters( 'wwpp_settings_discount_section_settings' , $this->_get_discount_section_settings() );
            $settings = array_merge( $settings , $wwppDiscountSettings );

        } elseif ( $current_section == 'wwpp_setting_payment_gateway_section' ) {

            // Payment Gateway Settings Section
            $wwppPaymentGatewaySettings = apply_filters( 'wwpp_settings_payment_gateway_section_settings' , $this->_get_payment_gateway_section_settings() );
            $settings = array_merge( $settings , $wwppPaymentGatewaySettings );

        } elseif ( $current_section == 'wwpp_setting_help_section' ) {

            // Help Settings Section
            $wwppHelpSettings = apply_filters( 'wwpp_settings_help_section_settings' , $this->_get_help_section_settings() );
            $settings = array_merge( $settings , $wwppHelpSettings );

        }

        return $settings;

    }

    /**
     * General Settings Section Content.
     *
     * @return array
     * @since 1.0.0
     */
    private function _get_general_section_settings() {

        return array(

            array(
                'name'  =>  __( 'General Options', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_section_title'
            ),

            array(
                'name'      =>  __( 'Only Show Wholesale Products To Wholesale Users', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'checkbox',
                'desc'      =>  __( 'This setting only affects wholesale users. Non-wholesale users (including users who are not logged in) will see the products with regular prices. "Wholesale products" are defined as products that have a wholesale price defined that is greater than zero.', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_only_show_wholesale_products_to_wholesale_users'
            ),

            array(
                'name'      =>  __( 'Disable Coupons For Wholesale Users', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'checkbox',
                'desc'      =>  __( 'If checked, this will prevent wholesale users from using coupons' , 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_disable_coupons_for_wholesale_users'
            ),

            array(
                'name'      =>  __( 'Category Wholesale Discount', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'select',
                'desc'      =>  __( 'In the event a single product belongs to multiple product category. Which category discount to apply?', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'This only applies to products who have no wholesale price set up', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_multiple_category_wholesale_discount_logic',
                'options'   =>  array(
                    'highest'   =>  'Highest',
                    'lowest'    =>  'Lowest'
                ),
                'default'   =>  'lowest'
            ),

            array(
                'name'      =>  __( 'Wholesale Price Text', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'text',
                'desc'      =>  '',
                'desc_tip'  =>  __( 'Default is "Wholesale Price:"', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_wholesale_price_title_text'
            ),

            array(
                'name'      =>  __( 'Hide Original Price' , 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'checkbox',
                'desc'      =>  __( 'Hide original price instead of showing a crossed out price if a wholesale price is present', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  '',
                'id'        =>  'wwpp_settings_hide_original_price'
            ),

            array(
                'name'      =>  __( 'Hide Quantity Discount Table' , 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'checkbox',
                'desc'      =>  __( 'When checked it will hide the quantity discount table on the front end' , 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  '',
                'id'        =>  'wwpp_settings_hide_quantity_discount_table'
            ),

            array(
                'name'      =>  __( 'Thank You Message', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'textarea',
                'desc'      =>  __( 'Message', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Custom Message To Display on Thank You Page (Leave Blank To Disable)', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_thankyou_message',
                'css'       =>  'min-width: 400px; min-height: 100px;'
            ),

            array(
                'name'      =>  __( '', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'select',
                'desc'      =>  __( 'Position', 'woocommerce-wholesale-prices-premium'),
                'desc_tip'  =>  __( 'Either Replace Original Thank You Message, or Append/Prepend Additional Message to the Original Thank You Message', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_thankyou_message_position',
                'options'   =>  array(
                    'replace'   =>  'Replace',
                    'append'    =>  'Append',
                    'prepend'   =>  'Prepend'
                ),
                'default'   =>  'replace'
            ),

            array(
                'name'      =>  __( 'Always Allow Backorders' , 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'checkbox',
                'desc'      =>  __( 'When checked, wholesale users can always do backorders.' , 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  '',
                'id'        =>  'wwpp_settings_always_allow_backorders_to_wholesale_users'
            ),

            array(
                'name'      =>  __( 'Minimum Order Requirements', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'number',
                'desc'      =>  __( 'Minimum order quantity', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Set as zero or leave blank to have no minimum quantity required.', 'woocommerce-wholesale-prices-premium' ),
                'default'   =>  0,
                'id'        =>  'wwpp_settings_minimum_order_quantity'
            ),

            array(
                'name'      =>  __( '', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'text',
                'desc'      =>  __( 'Minimum sub-total amount ('.get_woocommerce_currency_symbol().'). This ensures your wholesale customers order more than this threshold at the wholesale price.' , 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( "Calculated using the product's defined wholesale price (before tax and shipping). Set to zero or leave blank to disable." , 'woocommerce-wholesale-prices-premium' ),
                'default'   =>  0,
                'id'        =>  'wwpp_settings_minimum_order_price',
                'class'     =>  'wc_input_price'
            ),

            array(
                'name'      =>  __( '', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'select',
                'desc'      =>  __( 'Minimum order logic', 'woocommerce-wholesale-prices-premium'),
                'desc_tip'  =>  __( 'Either (minimum order quantity "AND" minimum order sub-total) or (minimum order quantity "OR" minimum order sub-total). Only applied if both minimum items and price is set', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_minimum_requirements_logic',
                'options'   =>  array(
                    'and'   =>  'AND',
                    'or'    =>  'OR'
                ),
                'default'   =>  'and'
            ),

            array(
                'name'      =>  '',
                'type'      =>  'checkbox',
                'desc'      =>  __( 'Override per wholesale role?' , 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Override minimum order requirements per wholesale role?' , '' ),
                'id'        =>  'wwpp_settings_override_order_requirement_per_role'
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_sectionend'
            )

        );

    }

    /**
     * Tax Settings Section Content.
     *
     * @since 1.4.2
     */
    private function _get_tax_section_settings() {

        return array(

            array(
                'name'  =>  __( 'Tax Options', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_tax_section_title'
            ),

            array(
                'name'      =>  __( 'Tax Exemption', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'checkbox',
                'desc'      =>  __( 'Do not apply tax to all wholesale users', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Removes tax for all wholesale users during checkout. This overrides the role based tax class settings.', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_tax_exempt_wholesale_users'
            ),

            array(
                'name'      =>  __( 'Display Prices in the Shop', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'select',
                'desc'      =>  __( 'Either to include/exclude tax on wholesale prices on shop pages for wholesale users.', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Note: If the option above of "Tax Exempting" wholesale users is enabled, then wholesale prices on shop pages will not include tax regardless the value of this option.', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_incl_excl_tax_on_wholesale_price',
                'options'   =>  array(
                    ''      =>  '--Use woocommerce default--',
                    'incl'  =>  'Including tax',
                    'excl'  =>  'Excluding tax'
                ),
                'default'   =>  ''
            ),

            array(
                'name'      =>  __( 'Display Prices During Cart and Checkout:', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'select',
                'desc'      =>  __( 'Either to include/exclude tax on wholesale prices on cart and checkout page for wholesale users.', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Note: If the option above of "Tax Exempting" wholesale users is enabled, then wholesale prices on cart and checkout page will not include tax regardless the value of this option.', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_wholesale_tax_display_cart',
                'options'   =>  array(
                    ''      =>  '--Use woocommerce default--',
                    'incl'  =>  'Including tax',
                    'excl'  =>  'Excluding tax'
                ),
                'default'   =>  ''
            ),

            array(
                'name'      =>  __( 'Override Price Suffix' , 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'text',
                'desc'      =>  __( 'Override the price suffix for wholesale users only' , 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Make this blank to use the default price suffix' ),
                'id'        =>  'wwpp_settings_override_price_suffix'
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_tax_divider1_sectionend'
            ),

            array(
                'name'  =>  __( 'Wholesale Role / Tax Exemption Mapping', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  __( 'Specify tax exemption per wholesale role. Overrides general <b>"Tax Exemption"</b> option above.' , 'woocommerce-wholesale-prices-premium' ),
                'id'    =>  'wwpp_settings_wholesale_role_tax_exemption_mapping_section_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'wholesale_role_tax_options_mapping_controls',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_shipping_section_shipping_controls',
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_tax_sectionend'
            )

        );

    }

    /**
     * Shipping Settings Section Content.
     *
     * @return array
     *
     * @since 1.0.3
     */
    private function _get_shipping_section_settings() {

        return array(

            array(
                'name'  =>  __( 'Shipping Options', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  __( '' , 'woocommerce-wholesale-prices-premium' ),
                'id'    =>  'wwpp_settings_shipping_section_title'
            ),

            array(
                'name'      =>  __( 'Free Shipping', 'woocommerce-wholesale-prices-premium' ),
                'type'      =>  'checkbox',
                'desc'      =>  __( 'All wholesale users use free shipping', 'woocommerce-wholesale-prices-premium' ),
                'desc_tip'  =>  __( 'Any user with a Wholesale Role assigned will automatically receive free shipping as defined by the Free Shipping shipping option.', 'woocommerce-wholesale-prices-premium' ),
                'id'        =>  'wwpp_settings_wholesale_users_use_free_shipping'
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_shipping_divider1_sectionend'
            ),

            array(
                'name'  =>  __( 'Wholesale User / Shipping Method Mapping', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  __( 'Options to set what shipping method to use per wholesale user role.<br/>
                                The options you set here has higher precedence compared to the <b>"Free Shipping"</b> option above.<br/><br/>
                                <strong>Important Note:</strong> Shipping options do not stack. Ex. if "wholesale_user" is set to use flat rate shipping,
                                but does not meet the requirements of flat rate shipping, it does not automatically use "free shipping" if free shipping option
                                above is enabled. Instead woocommerce will decide what shipping method to use depending on the shipping settings you have set up.' , 'woocommerce-wholesale-prices-premium' ),
                'id'    =>  'wwpp_settings_wholesale_shipping_section_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'shipping_controls',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_shipping_section_shipping_controls',
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_shipping_sectionend'
            )

        );

    }

    /**
     * Discount Settings Section Content.
     *
     * @return array
     *
     * @since 1.2.0
     */
    private function _get_discount_section_settings() {

        return array(

            array(
                'name'  =>  __( 'General Discount Options', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  __( 'This is where you set <b>"general discount"</b> for each wholesale role that will be applied to those users<br/>if a product they wish to purchase has no wholesale price set and no wholesale discount set at the product category level.' , 'woocommerce-wholesale-prices-premium' ),
                'id'    =>  'wwpp_settings_discount_section_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'discount_controls',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_discount_section_discount_controls',
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_discount_sectionend'
            )

        );

    }

    /**
     * Payment Gateway Surcharge Settings Section Content.
     *
     * @return array
     *
     * @since 1.3.0
     */
    private function _get_payment_gateway_section_settings() {

        return array(

            array(
                'name'  =>  __( 'Payment Gateway Options', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_payment_gateway_section_title'
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_payment_gateway_first_sectionend'
            ),

            array(
                'name'  =>  __( 'Wholesale Role / Payment Gateway', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  __( 'You can specify what payment gateways are available per wholesale role (Note that payment gateway need not be enabled)' , 'woocommerce-wholesale-prices-premium' ),
                'id'    =>  'wwpp_settings_payment_gateway_surcharge_section_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'wholesale_role_payment_gateway_controls',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_payment_gateway_wholesale_role_mapping',
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_payment_gateway_section_sectionend'
            ),

            array(
                'name'  =>  __( 'Wholesale Role / Payment Gateway Surcharge', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  __( 'You can specify extra cost per payment gateway per wholesale role' , 'woocommerce-wholesale-prices-premium' ),
                'id'    =>  'wwpp_settings_payment_gateway_surcharge_section_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'payment_gateway_surcharge_controls',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_payment_gateway_section_surcharge',
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_payment_gateway_sectionend'
            )

        );

    }

    /**
     * Help Settings Section Content.
     *
     * @since 1.3.0
     */
    private function _get_help_section_settings() {

        return array(

            array(
                'name'  =>  __( 'Help Options', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_help_section_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'help_resources_controls',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_help_resources',
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_help_devider1'
            ),

            array(
                'name'  =>  __( 'Debug Tools', 'woocommerce-wholesale-prices-premium' ),
                'type'  =>  'title',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_help_debug_tools_title'
            ),

            array(
                'name'  =>  '',
                'type'  =>  'initialize_product_visibility_meta_button',
                'desc'  =>  '',
                'id'    =>  'wwpp_settings_initialize_product_visibility_meta_button',
            ),

            array(
                'type'  =>  'sectionend',
                'id'    =>  'wwpp_settings_help_sectionend'
            )

        );

    }

}