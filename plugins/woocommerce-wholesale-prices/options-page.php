<?php
$json = @file_get_contents('http://woowholesale.com/plugin-notices.php');
$data = json_decode($json, TRUE);
add_action( 'admin_menu', 'woo_wholesale_settings_page' );
function woo_wholesale_settings_page(){
	register_setting( 'fx_smb', 'wwo_savings_label', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_rrp_label', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_wholesale_label', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_purchase_code', 'fx_smb_basic_sanitize' );	
	register_setting( 'fx_smb', 'wwo_min_products_per_order', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_max_products_per_order', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_min_spend_per_order', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_max_spend_per_order', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_roles_enabled', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_roles_used', 'fx_smb_basic_sanitize' );
	register_setting( 'fx_smb', 'wwo_min_spend_per_order_message');
	register_setting( 'fx_smb', 'wwo_max_spend_per_order_message');
	register_setting( 'fx_smb', 'wwo_remove_wholesale_taxes', 'fx_smb_basic_sanitize' );
	update_option('wwo_enable_wholesale_customer', 'enable_role');
	global $wp_roles;
	$addroles = $wp_roles->get_names();
	foreach($addroles as $addrole) { 
		$lrole = strtolower(str_replace(' ', '_', $addrole));
		register_setting( 'fx_smb', 'wwo_enable_'.$lrole, 'fx_smb_basic_sanitize' );
	}
	$settings_page = add_menu_page(
		'WooComerce Wholesale Prices',
		'Wholesale Prices',                  
		'manage_options',          
		'fx_smb',                        
		'fx_smb_settings_page',          
		'dashicons-align-left'                                      
	);
	$page_hook_id = fx_smb_setings_page_id();
	if ( !empty( $settings_page ) ) {
		add_action( 'admin_enqueue_scripts', 'fx_smb_enqueue_scripts' );
		add_action( "admin_footer-{$page_hook_id}", 'fx_smb_footer_scripts' );
		add_filter( 'screen_layout_columns', 'fx_smb_screen_layout_column', 10, 2 );
	}
}

function fx_smb_setings_page_id(){
	return 'toplevel_page_fx_smb';
}

function fx_smb_enqueue_scripts( $hook_suffix ){
	$page_hook_id = fx_smb_setings_page_id();
	if ( $hook_suffix == $page_hook_id ){
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
	}
}

function fx_smb_footer_scripts(){
	$page_hook_id = fx_smb_setings_page_id();
?>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		postboxes.add_postbox_toggles( '<?php echo $page_hook_id; ?>' );
		$('#fx-smb-form').submit( function(){
			$('#publishing-action .spinner').css('display','inline');
		});
		$('#delete-action .submitdelete').on('click', function() {
			return confirm('Are you sure want to do this?');
		});
	});
	//]]>
</script>
<?php
}

function fx_smb_screen_layout_column( $columns, $screen ){
	$page_hook_id = fx_smb_setings_page_id();
	if ( $screen == $page_hook_id ){
		$columns[$page_hook_id] = 2;
	}
	return $columns;
}

function fx_smb_settings_page(){
	global $hook_suffix;
	do_action( 'fx_smb_settings_page_init' );
	do_action( 'add_meta_boxes', $hook_suffix );
	?>

	<div class="wrap">
		<h2>WooCommerce Wholesale Prices <a class="add-new-h2" target="_blank" href="http://codecanyon.net/item/woocommerce-wholesale-prices/5325378">Read Tutorial</a></h2>
		<?php settings_errors(); ?>
		<div class="fx-settings-meta-box-wrap">
			<form id="fx-smb-form" method="post" action="options.php">
				<?php settings_fields( 'fx_smb' ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( $hook_suffix, 'side', null ); ?>
						</div>
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( $hook_suffix, 'normal', null ); ?>
							<?php do_meta_boxes( $hook_suffix, 'advanced', null ); ?>
						</div>
					</div>
					<br class="clear">
				</div>
			</form>
		</div>
	</div>
	<?php
}

add_action( 'add_meta_boxes', 'fx_smb_submit_add_meta_box' );
function fx_smb_submit_add_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'submitdiv',             
		'Save Options',          
		'fx_smb_submit_meta_box',
		$page_hook_id,              
		'side',                
		'high'                   
	);
}

function fx_smb_submit_meta_box(){	
	$reset_url = add_query_arg( array(
			'page' => 'fx_smb',
			'action' => 'reset_settings',
			'_wpnonce' => wp_create_nonce( 'fx-smb-reset', __FILE__ ),
		),
		admin_url( 'admin.php' )
	);
?>
<div id="submitpost" class="submitbox">
	<div id="major-publishing-actions">
		<div id="delete-action">
			<a href="<?php echo esc_url( $reset_url ); ?>" class="submitdelete deletion">Reset Settings</a>
		</div>
		<div id="publishing-action">
			<span class="spinner"></span>
			<?php submit_button( esc_attr( 'Save' ), 'primary', 'submitwoo', false );?>
		</div>
		<div class="clear"></div>
	</div>
</div>

<?php
}

add_action( 'fx_smb_settings_page_init', 'fx_smb_reset_settings' );
function fx_smb_reset_settings(){
	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	if( 'reset_settings' == $action ){
		if( current_user_can( 'manage_options' ) ){
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
			if( wp_verify_nonce( $nonce, 'fx-smb-reset' ) ){
				global $new_whitelist_options;
				$option_names = $new_whitelist_options['fx_smb'];
				foreach( $option_names as $option_name ){
					delete_option( $option_name );
				}
				do_action( 'fx_smb_reset' );
				add_settings_error( "fx_smb", "", "Settings reset to defaults.", 'updated' );
			}	else {
				add_settings_error( "fx_smb", "", "Failed to reset settings. Please try again.", 'error' );
			}
		}	else{
			add_settings_error( "fx_smb", "", "Failed to reset settings. You do not capability to do this action.", 'error' );
		}
	}
}

add_action( 'add_meta_boxes', 'woo_wholesale_role_options_meta_box' );
function woo_wholesale_role_options_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'wholesale_roles',               
		'Wholesale Roles',           
		'woo_wholesale_role_options', 
		$page_hook_id,             
		'normal',              
		'default'           
	);
}

function woo_wholesale_role_options(){
	echo'<p class="howto">Please select which roles you would like to enable wholesale prices for. A seperate price field will be enabled for each role.</p>'; 	
	if (isset($_REQUEST["settings-updated"])) {
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT * FROM wp_cpr_options WHERE option_value = "enable_role"' );
		$roles = array();
		foreach ($results as $result){	
			array_push($roles, $result->option_name);
		}
		update_option('wwo_roles_enabled', $roles);	
	} 
	global $wp_roles;
	$roles = $wp_roles->get_names();
	foreach($roles as $role) { 
		$lrole = strtolower(str_replace(' ', '_', $role));		
		if($lrole == 'wholesale_customer'){
			echo '<p><label><input name="wwo_enable_wholesale_customer" type="checkbox" value="enable_role" checked="checked" disabled />Wholesale Customer</label></p>';
		} else {
			echo '<p><label><input name="wwo_enable_'.$lrole.'" type="checkbox" value="enable_role" '.checked( 'enable_role', get_option( 'wwo_enable_'.$lrole ), false ).' /> '.$role.'</label></p>';
		} 
	} 	
}

add_action( 'add_meta_boxes', 'woo_wholesale_price_options_meta_box' );
function woo_wholesale_price_options_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'wholesale_prices',  
		'Pricing Options',         
		'woo_wholesale_price_options',  
		$page_hook_id,     
		'normal',         
		'default'        
	);
}

function woo_wholesale_price_options(){
	echo '<p><strong>Show Wholesale Savings</strong></p>';
	echo '<p class="howto">Default: "You Save". Leave blank to not show Wholesale Savings label on the front-end. Enabling this will also show the percentage saved.</p>';
	echo '<input class="widefat" name="wwo_savings_label" type="text" value="'.sanitize_text_field( get_option( 'wwo_savings_label', '' ) ).'" />';
	echo '<p><strong>Show RRP</strong></p>';
	echo '<p class="howto">Default: "RRP". Leave blank to not show RRP label on the front-end.</p>';
	echo '<input class="widefat" name="wwo_rrp_label" type="text" value="'.get_option( 'wwo_rrp_label' ).'" />';
	echo '<p><strong>Show Current User Role Price</strong></p>';
	echo '<p class="howto">Default: "Your Price". Leave blank to not show Current users role price label on the front-end.</p>';
	echo '<input class="widefat" name="wwo_wholesale_label" type="text" value="'.get_option( 'wwo_wholesale_label' ).'" required />';
}

add_action( 'add_meta_boxes', 'woo_wholesale_tax_options_meta_box', 1 );
function woo_wholesale_tax_options_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'wholesale_tax',  
		'Tax Options',         
		'woo_wholesale_tax_options',  
		$page_hook_id,     
		'normal',         
		'default'        
	);
}

function woo_wholesale_tax_options(){
	echo '<p>Remove wholesale taxes.';
	echo '<p class="howto">Please select this option if you would like to remove taxes from all wholesale customers at the checkout.</p>';
	echo '<p><label><input name="wwo_remove_wholesale_taxes" type="checkbox" value="1" '.checked( '1', get_option( 'wwo_remove_wholesale_taxes' ), false ).' /> Remove Taxes?</label></p>';
}

add_action( 'add_meta_boxes', 'woo_wholesale_quantity_options_meta_box' );
function woo_wholesale_quantity_options_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'wholesale_quantity',  
		'Checkout Quantity Options',         
		'woo_wholesale_quantity_options',  
		$page_hook_id,     
		'normal',         
		'default'        
	);
}

function woo_wholesale_quantity_options(){
	echo '<p>Set a minimum / maximum amount of products the user must purchase.';
	echo '<p><strong>Minimum Products Per Order</strong></p>';
	echo'<p class="howto">Set a <strong>minimum</strong> number of products allowed per order.</p>';
	echo '<input class="widefat" name="wwo_min_products_per_order" type="text" value="'.get_option( 'wwo_min_products_per_order' ).'" />';
	echo '<p><strong>Maximum Products Per Order</strong></p>';
	echo'<p class="howto">Set a <strong>maximum</strong> number of products allowed per order.</p>';
	echo '<input class="widefat" name="wwo_max_products_per_order" type="text" value="'.get_option( 'wwo_max_products_per_order' ).'" />';
}

add_action( 'add_meta_boxes', 'woo_wholesale_order_amount_options_meta_box' );
function woo_wholesale_order_amount_options_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'wholesale_order_amount',  
		'Checkout Spend Options',         
		'woo_wholesale_order_amount_options',  
		$page_hook_id,     
		'normal',         
		'default'        
	);
}

function woo_wholesale_order_amount_options(){
	echo '<p>Set a minimum / maximum the user must spend.';
	echo '<p><strong>Minimum Spend Per Order</strong></p>';
	echo'<p class="howto">Set a <strong>minimum</strong> spend limit per order.</p>';
	echo '<input class="widefat" name="wwo_min_spend_per_order" type="text" value="'.get_option( 'wwo_min_spend_per_order' ).'" />';
	//custom checkout message
	echo'<p class="howto">Enter a custom message when the above minimum is not met ( leave empty to use default ).</p>';
	echo '<textarea rows="5" class="widefat" name="wwo_min_spend_per_order_message" >'.get_option( 'wwo_min_spend_per_order_message' ).' </textarea>';
	echo '<p><strong>Maximum Spend Per Order</strong></p>';
	echo'<p class="howto">Set a <strong>maximum</strong> spend limit per order.</p>';
	echo '<input class="widefat" name="wwo_max_spend_per_order" type="text" value="'.get_option( 'wwo_max_spend_per_order' ).'" />';
	//custom checkout message
	echo'<p class="howto">Enter a custom message when the above maximum is not met ( leave empty to use default ).</p>';
	echo '<textarea rows="5" class="widefat" name="wwo_max_spend_per_order_message" >'.get_option( 'wwo_max_spend_per_order_message' ).' </textarea>';	
}

//Wholesale Pricing Resgister//
if(function_exists('wwpr_settings_page') && $data['register_options'] == 1){
	add_action( 'add_meta_boxes', 'wwpr_wholesale_register_options_meta_box' );
	function wwpr_wholesale_register_options_meta_box(){
		$page_hook_id = fx_smb_setings_page_id();
		add_meta_box(
			'wholesale_register_options',  
			'Wholesale Pricing Register Options',         
			'wholesale_register_options',  
			$page_hook_id,     
			'normal',         
			'default'        
		);
	}
	function wholesale_register_options(){
		//not yet available
	}		
}

add_action( 'add_meta_boxes', 'wwo_purchase_code_meta_box' );
function wwo_purchase_code_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'purchasecode',             
		'Purchase Code',          
		'wwo_purchase_code_content',  
		$page_hook_id,              
		'side',               
		'high'              
	);
}

function wwo_purchase_code_content(){
	echo'<p class="howto">Please enter your purchase code.</p>';
	echo '<input class="widefat" name="wwo_purchase_code" type="text" value="'.sanitize_text_field( get_option( 'wwo_purchase_code', '' ) ).'" />';
}

add_action( 'add_meta_boxes', 'wwo_credit_meta_box' );
function wwo_credit_meta_box(){
	$page_hook_id = fx_smb_setings_page_id();
	add_meta_box(
		'creditdiv', 
		'Proudly Developed By',  
		'wwo_credit_content', 
		$page_hook_id,  
		'side',   
		'high' 
	);
}
function wwo_credit_content(){
	echo '<a target="_blank" href="http://www.codeden.co.uk/?site='.get_bloginfo('url').'"><img src="http://codeden.co.uk/wp-content/uploads/2015/04/codeden.png" width="250"/></a>';
}

function fx_smb_basic_sanitize( $settings  ){
	$settings = sanitize_text_field( $settings );
	return $settings ;
}
