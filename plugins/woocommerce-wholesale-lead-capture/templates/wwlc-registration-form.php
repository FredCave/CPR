<?php
/**
 * The template for displaying registration form
 *
 * Override this template by copying it to yourtheme/woocommerce/wwlc-registration-form.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleLeadCapture/Templates
 * @version     1.0.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.
?>

<div id="wwlc-registration-form">

	<?php $formProcessor->initializeRegistrationForm(); ?>

	<?php
	foreach ( $formFields as $field ) {

		if ( $formProcessor->isFieldActive( $field ) ) {
			?>
			<p class="field-set <?php echo $field[ 'type' ]; ?>-field-set">

				<?php echo $formProcessor->getLabel( $field ); ?>
				<?php echo $formProcessor->getField( $field ); ?>

			</p>
			<?php
		}
	}
	?>

	<div class="field-set form-controls-section">

		<?php echo $formProcessor->getFormControls(); ?>

	</div>

</div><!--#wwlc-registration-form-->