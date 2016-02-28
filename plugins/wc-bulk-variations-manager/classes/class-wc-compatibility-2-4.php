<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SA_WC_Compatibility_2_4' ) ) {
	
	/**
	 * Class to check for WooCommerce version & return variables accordingly
	 *
	 */
	class SA_WC_Compatibility_2_4 extends SA_WC_Compatibility_2_3 {

		/**
		 * Is WooCommerce Greater Than And Equal To 2.3
		 * 
		 * @return boolean 
		 */
		public static function is_wc_gte_24() {
			return self::is_wc_greater_than( '2.3.13' );
		}

	}

}