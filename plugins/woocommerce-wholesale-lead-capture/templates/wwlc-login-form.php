<?php
/**
 * The template for displaying registration form
 *
 * Override this template by copying it to yourtheme/woocommerce/wwlc-login-form.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleLeadCapture/Templates
 * @version     1.0.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.

?>
<div id="wwlc-login-form">

	<?php echo $logInForm; ?>

	<a class="register_link" href="<?php echo get_option( 'wwlc_general_registration_page' ); ?>" ><?php _e( 'Register' , 'woocommerce-wholesale-lead-capture' ); ?></a>
	<a class="lost_password_link" href="<?php echo wp_lostpassword_url(); ?>" ><?php _e( 'Lost Password' , 'woocommerce-wholesale-lead-capture' ); ?></a>

</div><!--#wwlc-login-form-->
