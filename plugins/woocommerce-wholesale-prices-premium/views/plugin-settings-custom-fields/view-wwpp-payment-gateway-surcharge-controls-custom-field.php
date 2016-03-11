<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$paymentGatewaySurcharge = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
if ( !is_array( $paymentGatewaySurcharge ) )
    $paymentGatewaySurcharge = array();

$allWholesaleRoles = $this->wwppGetAllRegisteredWholesaleRoles( null , false );

$available_gateways = WC()->payment_gateways->payment_gateways();
if ( !is_array( $available_gateways ) )
    $available_gateways = array();

$surcharge_types = array(
                        'fixed_price'   =>  'Fixed Price',
                        'percentage'    =>  'Percentage'
                    ); ?>

<tr valign="top">
    <th colspan="2" scope="row" class="titledesc">
        <div class="surcharge-controls">

            <input type="hidden" id="wwpp-index" value=""/>

            <div class="field-container">

                <label for="wwpp-wholesale-roles"><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <select id="wwpp-wholesale-roles" data-placeholder="Choose wholesale role...">
                    <option value=""></option>
                    <?php foreach ( $allWholesaleRoles as $wholesaleRoleKey => $wholesaleRole ) { ?>
                        <option value="<?php echo $wholesaleRoleKey ?>"><?php echo $wholesaleRole[ 'roleName' ]; ?></option>
                    <?php } ?>
                </select>

            </div>

            <div class="field-container">

                <label for="wwpp-payment-gateway"><?php _e( 'Payment Gateway' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <select id="wwpp-payment-gateway" data-placeholder="Choose payment gateway...">
                    <option value=""></option>
                    <?php foreach ( $available_gateways as $gateway_key => $gateway ) { ?>
                        <option value="<?php echo $gateway_key ?>"><?php echo $gateway->title; ?></option>
                    <?php } ?>
                </select>

            </div>

            <div class="field-container">

                <label for="wwpp-surcharge-title"><?php _e( 'Surcharge Title' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <input type="text" id="wwpp-surcharge-title" class="regular-text" value=""/>

            </div>

            <div class="field-container">

                <label for="wwpp-surcharge-type"><?php _e( 'Surcharge Type' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <select id="wwpp-surcharge-type" data-placeholder="Choose surcharge type...">
                    <option value=""></option>
                    <?php foreach ( $surcharge_types as $surcharge_key => $surcharge_text ) { ?>
                        <option value="<?php echo $surcharge_key; ?>"><?php echo $surcharge_text; ?></option>
                    <?php } ?>
                </select>

            </div>

            <div class="field-container">

                <label for="wwpp-surcharge-amount"><?php _e( 'Surcharge Amount' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <input type="text" id="wwpp-surcharge-amount" class="regular-text wc_input_price" value=""/>
                <p class="desc"><?php _e( 'If surcharge type is percentage, then input amount n percent (%). Ex. 3 percent then input 3, 30 percent then input 30, 0.3 percent then input 0.3.' , 'woocommerce-wholesale-prices-premium' ); ?></p>

            </div>

            <div class="field-container">

                <label for="wwpp-surcharge-taxable"><?php _e( 'Taxable?' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                <select id="wwpp-surcharge-taxable">
                    <option value="yes"><?php _e( 'Yes' , 'woocommerce-wholesale-prices-premium' ); ?></option>
                    <option value="no"><?php _e( 'No' , 'woocommerce-wholesale-prices-premium' ); ?></option>
                </select>

            </div>

            <div style="clear: both; float: none; display: block;"></div>

        </div>

        <div class="button-controls add-mode">

            <input type="button" id="cancel-edit-surcharge" class="button button-secondary" value="<?php _e( 'Cancel' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
            <input type="button" id="save-surcharge" class="button button-primary" value="<?php _e( 'Save Surcharge' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
            <input type="button" id="add-surcharge" class="button button-primary" value="<?php _e( 'Add Surcharge' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
            <span class="spinner"></span>

            <div style="clear: both; float: none; display: block;"></div>

        </div>

        <table id="wholesale-payment-gateway-surcharge" class="wp-list-table widefat">
            <thead>
                <tr>
                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Payment Gateway' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Surcharge Title' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Surcharge Type' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Surcharge Amount' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Taxable' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th></th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Payment Gateway' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Surcharge Title' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Surcharge Type' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Surcharge Amount' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th><?php _e( 'Taxable' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                    <th></th>
                </tr>
            </tfoot>

            <tbody>
            <?php
            if ( $paymentGatewaySurcharge ) {

                $itemNumber =   0;

                foreach( $paymentGatewaySurcharge as $idx => $surcharge ) {
                    $itemNumber++;

                    if ( $itemNumber % 2 == 0 ) { // even  ?>
                        <tr class="even">
                    <?php } else { // odd ?>
                        <tr class="odd alternate">
                    <?php } ?>

                        <td class="meta hidden">
                            <span class="index"><?php echo $idx; ?></span>
                            <span class="wholesale-role"><?php echo $surcharge[ 'wholesale_role' ] ?></span>
                            <span class="payment-gateway"><?php echo $surcharge[ 'payment_gateway' ] ?></span>
                            <span class="surcharge-type"><?php echo $surcharge[ 'surcharge_type' ]; ?></span>
                        </td>
                        <td class="wholesale-role-text"><?php echo $allWholesaleRoles[ $surcharge[ 'wholesale_role' ] ][ 'roleName' ]; ?></td>
                        <td class="payment-gateway-text"><?php echo $available_gateways[ $surcharge[ 'payment_gateway' ] ]->title; ?></td>
                        <td class="surcharge-title"><?php echo $surcharge[ 'surcharge_title' ]; ?></td>
                        <td class="surcharge-type-text"><?php echo $surcharge_types[ $surcharge[ 'surcharge_type' ] ]; ?></td>
                        <td class="surcharge-amount"><?php echo $surcharge[ 'surcharge_amount' ]; ?></td>
                        <td class="taxable"><?php echo $surcharge[ 'taxable' ]; ?></td>
                        <td class="controls">
                            <a class="edit dashicons dashicons-edit"></a>
                            <a class="delete dashicons dashicons-no"></a>
                        </td>

                    </tr>
                <?php
                }

            } else { ?>

                <tr class="no-items">
                    <td class="colspanchange" colspan="6"><?php _e( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ); ?></td>
                </tr>

            <?php } ?>
            </tbody>

        </table>
    </th>
</tr>

<style>
    p.submit {
        display: none !important;
    }
</style>