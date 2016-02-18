<?php
/*
Plugin Name: WooCommerce Wholesale Pricing
Plugin URI: http://www.woowholesale.com
Description: WooCommerce Wholesale Pricing - An extension for WooCommerce, which adds wholesale functionality to your store.
Version: 2.1.4
Author: Danyo Borg
Author URI: http://www.danyob.org
*/

require_once('options-page.php');
$v = '2.1.4';
update_option('wholesale_prices_version', $v);

function woo_get_user_role() {
	global $current_user;
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	return $user_role;
}

function woo_get_enabled_user_role() {
	global $current_user;
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
	return 'wwo_enable_'.$user_role;
}

function woo_add_wholesale_customer_role() {
  add_role('wholesale_customer', 'Wholesale Customer', array(
		'read' => true, 
		'edit_posts' => false,
		'delete_posts' => false, 
	));
}
register_activation_hook( __FILE__, 'woo_add_wholesale_customer_role' );

add_action( 'save_post', 'wwp_save_simple_wholesale_price' );
function wwp_save_simple_wholesale_price( $post_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
		$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
		if (isset($_POST['_inline_edit']) && wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce'))return;
		if (isset($_POST[$finalno])){$new_data = $_POST[$finalno];}
		if (isset($_POST['post_ID'])){$post_ID = $_POST['post_ID'];}
		update_post_meta($post_ID, $finaldata, $new_data) ;
	}
}

add_action( 'woocommerce_product_options_pricing', 'wwp_add_admin_simple_wholesale_price', 10, 2 );
function wwp_add_admin_simple_wholesale_price( $loop ){ 
	echo '<p class=""><strong>Wholesale Pricing Options</strong></p>';
	global $wpdb;
	$table_name = $wpdb->prefix . "options";
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
		$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
		$wholesale = get_post_meta( get_the_ID(), $finaldata, true );
		echo '<tr><td><div><p class="form-field _regular_price_field">';
		echo '<label>'.__(ucwords(str_replace(array("wwo_enable_", "_")," ",$result->option_name)).' Price', 'woocommerce' ) . ' ('.get_woocommerce_currency_symbol().')'.'</label>';
		echo '<input step="any" type="number" class="wc_input_price short" name="'.$finalno.'" value="'.$wholesale.'"/>';
		echo '</p></div></td></tr>';		
	}
}

add_action( 'woocommerce_get_price_html' , 'wwp_get_wholesale_price' );
function wwp_get_wholesale_price($price){
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		if($result->option_name == woo_get_enabled_user_role()){
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
		$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
		$wholesale = get_post_meta( get_the_ID(), $finaldata, true );
		$rrp = get_post_meta( get_the_ID(), '_price', true );
		$savings  = $rrp - $wholesale;
		$division = $rrp ? $savings / $rrp : 0;
		$wwo_percentage = get_option( 'wwo_percentage' );
		$wwo_savings = get_option( 'wwo_savings_label' );
		$wwo_rrp = get_option( 'wwo_rrp_label' );
		$wwo_wholesale_label = get_option( 'wwo_wholesale_label' );
		$res = $division * 100;
		$res = round($res, 0);
		$res = round($res, 1);
		$res = round($res, 2);
		if ($wholesale !=''){		
			if ($wwo_rrp != '') {			
				$price =  $wwo_rrp.': '.woocommerce_price($rrp).'</br>';		
			} else {
				$price = '';
			}	
			if ($wwo_wholesale_label != '') {
				$price .=  $wwo_wholesale_label.': '.woocommerce_price($wholesale);
			}
			if ($wwo_savings != '') {
				$price .=  '</br>'.$wwo_savings.': '.woocommerce_price($savings).' ('.$res.'%)';
			}
		} 
	}
}
return $price;	
}

add_action( 'woocommerce_product_after_variable_attributes', 'variation_settings_fields', 10, 3 );
function variation_settings_fields( $loop, $variation_data, $variation ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
		$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
		$get_variation_id = $variation->ID;
		$wholesale_price = get_post_meta($get_variation_id, $finaldata, true );		
		woocommerce_wp_text_input( 
			array( 
				'id'          => $finaldata.'[' . $variation->ID . ']', 
				'label'       => __( ucwords(str_replace(array("wwo_enable_", "_")," ",$result->option_name)).' Price', 'woocommerce' ) . ' ('.get_woocommerce_currency_symbol().')', 
				'placeholder' => '0.00',
				'desc_tip'    => 'false',
				'description' => __( '', 'woocommerce' ),
				'value'       => get_post_meta( $variation->ID, $finaldata, true )
			)
		);
	}	
}

add_action( 'woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2 );
function save_variation_settings_fields( $post_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';		
		$text_field = $_POST[$finaldata][ $post_id ];
		if( ! empty( $text_field ) ) {
			update_post_meta( $post_id, $finaldata, esc_attr( $text_field ) );
		}		
	}
}


add_filter('woocommerce_variable_price_html', 'wwp_custom_variation_price', 10, 2);
function wwp_custom_variation_price( $price, $product ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
		$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
		if($result->option_name == woo_get_enabled_user_role()){
			$variations = $product->get_available_variations();
			$lowestvar = array();
			foreach ($variations as $variation){
				$lowestvar[] = get_post_meta($variation['variation_id'], $finaldata, true);
				$lowestnor[] = get_post_meta($variation['variation_id'],'_price', true);
				array_multisort($lowestvar, SORT_ASC);
				array_multisort($lowestnor, SORT_ASC);
			}
	
			$lrrp = min($lowestnor);
			$hrrp = max($lowestnor);
			$lwrrp = min($lowestvar);
			$hwrrp = max($lowestvar);	
			
			$wwo_percentage = get_option( 'wwo_percentage' );
			$wwo_savings = get_option( 'wwo_savings_label' );
			$wwo_rrp = get_option( 'wwo_rrp_label' );
			$wwo_wholesale_label = get_option( 'wwo_wholesale_label' );
			
			
			if($lrrp == $hrrp){
				if($wwo_rrp !=''){
					$rrp_price = $wwo_rrp.': '.woocommerce_price($lrrp).'</br>';	
				}
			} else {
				if($wwo_rrp !=''){
					$rrp_price = $wwo_rrp.': '.woocommerce_price($lrrp).' - '.woocommerce_price($hrrp).'</br>';	
				}
			}
			
			if(get_post_meta($variation['variation_id'], $finaldata, true)){
				if($lwrrp == $hwrrp){
					if($wwo_wholesale_label !=''){
						$price =  $rrp_price.''.$wwo_wholesale_label.': '.woocommerce_price($lwrrp); 
					}
				} else {
					if($wwo_wholesale_label !=''){
						$price =  $rrp_price.''.$wwo_wholesale_label.': '.woocommerce_price($lwrrp).' - '.woocommerce_price($hwrrp); 
					}
				}
			} 

		}
	}
	return $price;
}

add_filter( 'woocommerce_available_variation', 'wwp_update_dropdown_variation_price', 10, 3);
function wwp_update_dropdown_variation_price( $data, $product, $variation ) {
	$data['price_html'] = '<span class="price">'.woocommerce_price(get_post_meta( $data['variation_id'], '_price', true )).'</span>';
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
		$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
		$wholesalep = get_post_meta( $data['variation_id'], $finaldata, true );
		if($result->option_name == woo_get_enabled_user_role()){
			if ($wholesalep !== ''){
   				$data['price_html'] = '<span class="price">'.woocommerce_price($wholesalep).'</span>'; 
			}
		}  
	} 
	return $data;
}

add_action( 'woocommerce_before_calculate_totals', 'wwp_simple_add_cart_price' );
function wwp_simple_add_cart_price( $cart_object ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){
		if($result->option_name == woo_get_enabled_user_role()){
			$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
			$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
			$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
			$wholesale = get_post_meta( get_the_ID(), $finaldata, true );
			foreach ( $cart_object->cart_contents as $key => $value ) {
				$wholesale = get_post_meta( $value['data']->id, $finaldata, true );
				$wholesalev = get_post_meta( $value['data']->variation_id, $finaldata, true );
				if ($wholesale){
					$value['data']->price = $wholesale;
				}
				if ($wholesalev){
					$value['data']->price = $wholesalev;
				}
			} 
		}
	}
}

add_filter('woocommerce_cart_item_price', 'wpp_mini_cart_prices', 10, 3);
function wpp_mini_cart_prices( $product_price, $values, $cart_item) {	
	global $woocommerce;
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		$ifwholesale = str_replace("wholesale_customer","wholesale",$result->option_name);
		$finaldata = str_replace("wwo_enable_","_",$ifwholesale).'_price';
		$finalno = str_replace("wwo_enable_","",$ifwholesale).'_price';
		$wholesalep = get_post_meta( $data['variation_id'], $finaldata, true );
		if($result->option_name == woo_get_enabled_user_role()){
			$varwp = get_post_meta( $values['variation_id'], $finaldata, true );
			$varnp = get_post_meta( $values['variation_id'], '_price', true );
			$simplewp = get_post_meta( $values['product_id'], $finaldata, true );
			$simplenp = get_post_meta( $values['product_id'], '_price', true );
			if ($values['variation_id'] > 0 ){
				if ($varwp == ''){return woocommerce_price($varnp);} else {return woocommerce_price($varwp);}
			} else {
				if ($simplewp == ''){return woocommerce_price($simplenp);} else {return woocommerce_price($simplewp);}
			}
		}
	}	
return $product_price;	
}

add_action( 'woocommerce_check_cart_items', 'wwo_global_min_quantity' );
function wwo_global_min_quantity(){	
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		if($result->option_name == woo_get_enabled_user_role()){
			$minorder = get_option( 'wwo_min_products_per_order' );
			if( is_cart() || is_checkout() && $minorder !='' ) {
				global $woocommerce;     
				$cart_num_products = WC()->cart->cart_contents_count;
				$cart_url = $woocommerce->cart->get_cart_url();
				if( $cart_num_products < $minorder ) {
					wc_add_notice( sprintf( '<strong>Minimum product count not met</strong><br/>You must have a minimum of <strong>'.$minorder.'</strong> products in your cart. You currently have <strong>'.$cart_num_products.'</strong> products.',
						$minorder,
						$cart_num_products ),
					'error' );
				}
			}
		}
	}
}

add_action( 'woocommerce_check_cart_items', 'wwo_global_max_quantity' );
function wwo_global_max_quantity() {
  global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		if($result->option_name == woo_get_enabled_user_role()){
			$maxorder = get_option( 'wwo_max_products_per_order' );
			if( is_cart() || is_checkout() && $maxorder !='' ) {
				global $woocommerce;
				$cart_num_products = WC()->cart->cart_contents_count;
				$cart_url = $woocommerce->cart->get_cart_url();
				if( $cart_num_products > $maxorder ) {
					wc_add_notice( sprintf( '<strong>Maximum product count exceeded</strong><br/>You must have a maximum of <strong>'.$maxorder.'</strong> products in your cart. You currently have <strong>'.$cart_num_products.'</strong> products.',
						$maxorder,
						$cart_num_products ),
					'error' );
				}
			}
		}
	}
}

add_action( 'woocommerce_check_cart_items', 'wwo_global_min_spend' );
function wwo_global_min_spend() {
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		if($result->option_name == woo_get_enabled_user_role()){
    	$minimum = get_option('wwo_min_spend_per_order');
			if( is_cart() || is_checkout() && $minimum !='' ) {
				global $woocommerce;
				$total = WC()->cart->subtotal;
				$cart_url = $woocommerce->cart->get_cart_url();
				if( $total < $minimum && $minimum !='' ) {
					$message = get_option('wwo_min_spend_per_order_message');
					if($message == ''){
						$message = '<strong>Minimum order total not met</strong><br/>You must have a minimum order total of <strong>%s</strong> before you can checkout. Your current order total is <strong>%s</strong>.';
					}
					wc_add_notice( 
						
							sprintf( $message , 
									wc_price( $minimum ), 
									wc_price( $total )
							), 'error' 
					);
				}
			} 
		}
	}
}

add_action( 'woocommerce_check_cart_items', 'wwo_global_max_spend' );
function wwo_global_max_spend() {
	global $wpdb;
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		if($result->option_name == woo_get_enabled_user_role()){
    	$maximum = get_option('wwo_max_spend_per_order');
			if( is_cart() || is_checkout() && $maximum !='' ) {
				global $woocommerce;
				$total = WC()->cart->subtotal;
				$cart_url = $woocommerce->cart->get_cart_url();
				if( $total > $maximum && $maximum !=''  ) {
					$message = get_option('wwo_max_spend_per_order_message');
					if($message == ''){
						$message = '<strong>Maximum order total exceeded</strong><br/>You must have a maximum order total of <strong>%s</strong> before you can checkout. Your current order total is <strong>%s</strong>.';
					}
					wc_add_notice( 
							sprintf( $message , 
									wc_price( $maximum ), 
									wc_price( $total )
							), 'error' 
					);
				}
			} 
		}
	}
}

add_filter( 'woocommerce_product_tax_class', 'wwo_remove_wholesale_taxes', 1, 2 );
function wwo_remove_wholesale_taxes( $tax_class, $product ) {
  global $wpdb;
	$enabled = get_option('wwo_remove_wholesale_taxes');
	$table_name = $wpdb->prefix . "options"; 	
	$results = $wpdb->get_results( 'SELECT * FROM '.$table_name.' WHERE option_value = "enable_role"' );
	foreach ($results as $result){	
		if($result->option_name == woo_get_enabled_user_role() && $enabled == 1){
    	$tax_class = 'Zero Rate';
			return $tax_class;
		}
		
	}
}

function wwp_get_notices() {
	$json = @file_get_contents('http://woowholesale.com/plugin-notices.php');
	$data = json_decode($json, TRUE);
	$version = get_option('wholesale_prices_version');	
	if($version != $data['version']){		
	   echo '<div class="error">';
	   echo '<p>'.$data['update'].'</p>';
		 echo '<p>'.$data['changelog'].'</p>';
	   echo '</div>';
	}
	
	if($data['notes']){		
	   echo '<div class="updated">';
	   echo '<p>'.$data['notes'].'</p>';
	   echo '</div>';
	}
	
	$code = get_option('wwo_purchase_code');
	if($code == ''){
		 echo '<div class="error">';
	   echo '<p><strong>WooCommerce Wholesale Prices</strong> - Please enter your purchase code to recieve support.</p>';
	   echo '</div>';	
	}
	
}
add_action( 'admin_notices', 'wwp_get_notices' );

/*
function woo_get_selected_roles(){
	$roles = get_option( 'wwo_roles_used_new', true );
}

function register_woo_wholesale_settings() {
	register_setting( 'woo_wholesale_options', 'wwo_savings' );
	register_setting( 'woo_wholesale_options', 'wwo_savings_label' );
	register_setting( 'woo_wholesale_options', 'wwo_percentage' );
	register_setting( 'woo_wholesale_options', 'wwo_rrp' );
	register_setting( 'woo_wholesale_options', 'wwo_rrp_label' );
	register_setting( 'woo_wholesale_options', 'wwo_wholesale_label' );
	register_setting( 'woo_wholesale_options', 'wwo_min_quantity' );
	register_setting( 'woo_wholesale_options', 'wwo_min_quantity_value' );
	register_setting( 'woo_wholesale_options', 'wwo_max_quantity' );
	register_setting( 'woo_wholesale_options', 'wwo_max_quantity_value' );
	register_setting( 'woo_wholesale_options', 'wwo_wholesale_role' );
}
*/