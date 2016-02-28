<?php
/*
 * Plugin Name: Excel Like Product Attribute And Tag Manager
 * Plugin URI: http://holest.com/index.php/holest-outsourcing/free-stuff/excel-like-price-changer-for-woocommerce-and-wp-e-commerce-light-free.html
 * Description:An WooCommerce / WP E-commerce 'MS excel' like fast input spreadsheet editor for product attribute(up to 3) and tag management. Web-form spreadsheet edit or export / import form CSV. It supports both WooCommerce and WP E-commerce. UI behaves same as in MS Excel. This is right thin for you if you need to change existing or apply new attributes to large number of products.;EDITABLE / IMPORTABLE FIELDS: Attributes, Tags; VIEWABLE / EXPORTABLE FIELDS: WooCommerce: Price, Sales Price, Attributes (Each pivoted as column), SKU, Category, Shipping class, Name, Slug, Stock, Featured, Status, Weight, Height, Width, Length, Tax status, Tax class; WP E-commerce: Price, Sales Price, Tags, SKU, Category, Name, Slug, Stock, Status, Weight, Height, Width, Length, Taxable, local and international shipping costs; Allows custom fields you can configure to view/export any property
 * Version: 1.0.12
 * Author: Holest Engineering
 * Author URI: http://www.holest.com
 * Requires at least: 3.6
 * Tested up to: 4.0.1
 * License: GPLv2
 * Tags: excel, fast, woo, woocommerce, wpsc, wp e-commerce, attributes, tags, products, editor, spreadsheet, import, export 
 * Text Domain: excellikeattributeandtagmanagerforwoocommerceandwpecommercelight
 * Domain Path: /languages/
 *
 * @package excellikeattributeandtagmanagerforwoocommerceandwpecommercelight
 * @category Core
 * @author Holest Engineering
 */

/*

Copyright (c) holest.com

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

if ( !function_exists('add_action') ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if ( ! class_exists( 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ) {

   class excellikeattributeandtagmanagerforwoocommerceandwpecommercelight{
        var $settings          = array();
		var $plugin_path       = '';
		var $is_internal       = false;  
		var $saved             = false;
		var $aux_settings_path = '';
		var $shops             = array();    
		
		function excellikeattributeandtagmanagerforwoocommerceandwpecommercelight(){
			$this->load_plugin_textdomain();
			$this->aux_settings_path = dirname(__FILE__). DIRECTORY_SEPARATOR . 'settings.dat';
			add_action('admin_menu',array( $this, 'register_plugin_menu_item'));
		    add_action('admin_init', array( $this,'admin_utils'));
			$this->loadOptions();
			
			if(isset($_REQUEST['plem_do_save_settings']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST'){
				$this->settings["fixedColumns"] = $_REQUEST["fixedColumns"]; 
				
				if(isset($_REQUEST['wooc_at_fileds']))
					$this->settings["wooc_at_fileds"] = implode(",",$_REQUEST['wooc_at_fileds']);
				
				if(isset($_REQUEST['wpsc_at_fileds']))
					$this->settings["wpsc_at_fileds"] = implode(",",$_REQUEST['wpsc_at_fileds']);
				
				for($I = 0 ; $I < 8 ; $I++){
				    $n = $I + 1;
					
					if(isset($_REQUEST['wooc_at_fileds'])){
						$this->settings["wooccf_at_title".$n]    = isset($_REQUEST["wooccf_at_title".$n]) ? $_REQUEST["wooccf_at_title".$n] : "" ;
						$this->settings["wooccf_at_editoptions".$n] = isset($_REQUEST["wooccf_at_editoptions".$n]) ? $_REQUEST["wooccf_at_editoptions".$n] : "";
						$this->settings["wooccf_at_type".$n]     = isset($_REQUEST["wooccf_at_type".$n]) ? $_REQUEST["wooccf_at_type".$n] : "";
						$this->settings["wooccf_at_source".$n]   = isset($_REQUEST["wooccf_at_source".$n]) ? $_REQUEST["wooccf_at_source".$n] : "";
						$this->settings["wooccf_at_enabled".$n]  = isset($_REQUEST["wooccf_at_enabled".$n]) ? $_REQUEST["wooccf_at_enabled".$n] : "";
						$this->settings["wooccf_at_varedit".$n]  = isset($_REQUEST["wooccf_at_varedit".$n]) ? $_REQUEST["wooccf_at_varedit".$n] : "";
					}

					if(isset($_REQUEST['wpsc_at_fileds'])){ 	
						$this->settings["wpsccf_at_title".$n]    = isset($_REQUEST["wpsccf_at_title".$n]) ? $_REQUEST["wpsccf_at_title".$n] : "" ;
						$this->settings["wpsccf_at_editoptions".$n] = isset($_REQUEST["wpsccf_at_editoptions".$n]) ? $_REQUEST["wpsccf_at_editoptions".$n] : "";
						$this->settings["wpsccf_at_type".$n]     = isset($_REQUEST["wpsccf_at_type".$n]) ? $_REQUEST["wpsccf_at_type".$n] : "";
						$this->settings["wpsccf_at_source".$n]   = isset($_REQUEST["wpsccf_at_source".$n]) ? $_REQUEST["wpsccf_at_source".$n] : "";
						$this->settings["wpsccf_at_enabled".$n]  = isset($_REQUEST["wpsccf_at_enabled".$n]) ? $_REQUEST["wpsccf_at_enabled".$n] : "";
					}
				}
				
				$this->saveOptions();
			}
			
			if(!isset($this->settings["fixedColumns"])){
				$this->settings["fixedColumns"] = 2;
				
				$this->settings["wooccf_at_title1"]       = "Content";
				$this->settings["wooccf_at_editoptions1"] = "{}";
				$this->settings["wooccf_at_type1"]        = "post";
				$this->settings["wooccf_at_source1"]      = "post_content";
				$this->settings["wooccf_at_enabled1"]     = "0";
				$this->settings["wooccf_at_varedit1"]     = "0";
				
				$this->settings["wpsccf_at_title1"]       = "Content";
				$this->settings["wpsccf_at_editoptions1"] = "{}";
				$this->settings["wpsccf_at_type1"]        = "post";
				$this->settings["wpsccf_at_source1"]      = "post_content";
				$this->settings["wpsccf_at_enabled1"]     = "0";
			}
			
			
			if(isset($_REQUEST['page'])){
				if( strpos($_REQUEST['page'],"excellikeattributeandtagmanagerforwoocommerceandwpecommercelight") !== false && isset($_REQUEST["elpm_shop_com"])){
					add_action('wp_ajax_pelm_frame_display',array( $this,'internal_display'));
				}
			}
		}
		
		public function saveOptions(){
			update_option('ELPCL_SETTINGS',(array)$this->settings);
			$this->saved = true;
			if(!isset($this->settings["fixedColumns"])){
				$check = get_option('ELPCL_SETTINGS',array());
				if(!isset($check['fixedColumns'])){
                    file_put_contents($this->aux_settings_path , json_encode($this->settings));
				}
			}
		}
		
		public function loadOptions(){
			$this->settings = get_option('ELPCL_SETTINGS',array());
			if(!isset($this->settings["fixedColumns"])){
			   if(file_exists($this->aux_settings_path )){
				$this->settings = (array)json_decode(file_get_contents($this->aux_settings_path));
				if(!$this->settings)
					$this->settings = array();
			   }
			}
		}
	
		public function admin_utils(){
				wp_enqueue_style( 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-style', plugins_url('/assets/admin.css', __FILE__));
		}
		
		public function register_plugin_menu_item(){
			$supported_shops = array();
			$shops_dir_path = dirname(__FILE__). DIRECTORY_SEPARATOR . 'shops';
			$sd_handle = opendir(dirname(__FILE__). DIRECTORY_SEPARATOR . 'shops');
			while(false !== ( $file = readdir($sd_handle)) ) {
				if (( $file != '.' ) && ( $file != '..' )) { 
				    $name_parts = explode('.', $file);
				    $ext = strtolower(end($name_parts));
					if($ext == 'php'){
					    $last = array_pop($name_parts);
						$shop = new stdClass();
						$shop->uid   = implode('.',$name_parts);
						$shop->path  = $shops_dir_path . DIRECTORY_SEPARATOR . $file;
						$source_content = file_get_contents( $shop->path , NULL , NULL , -1 ,512);
						
						$out = array();
						
						preg_match_all("/Origin plugin\s*:\s*(.*)/i", $source_content , $out);
						if(isset($out[1][0])){
						    $shop->originPlugin = trim($out[1][0]);
							if(!is_plugin_active($shop->originPlugin))
								continue;
						}else
						   continue;
						   
						preg_match_all("/Title\s*:\s*(.*)/i", $source_content , $out);
						if(isset($out[1][0])){
							$shop->title = trim($out[1][0]);
						}else{
						    $shop->title = $shop->uid;
						}
						$supported_shops[] = $shop;
						$this->shops[] = $shop->uid;
					}
				}
			}
		
			$self = $this;
			add_menu_page( __( 'Excel-Like Attribute/Tag Manager', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) . (count($supported_shops) == 1 ? " ".$supported_shops[0]->uid : "" )
			             , __( 'Excel-Like Attribute/Tag Manager', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) . (count($supported_shops) == 1 ? " ".$supported_shops[0]->uid : "" )
						 , 'manage_options'
						 , 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-root'
						 , array( $this,'callDisplayLast')
						 ,'dashicons-list-view'
			);
			
			
			
			
			foreach($supported_shops as $sh){
			    add_submenu_page( "excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-root", __( 'Excel-Like Attribute/Tag Manager', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) . " - " . $sh->title  , $sh->title, 'manage_options', "excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-".$sh->uid, 
					array( $this,'callDisplayShop')
				);
			}
			
			add_submenu_page( "excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-root", __( 'Settings', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ), __( 'Settings', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ), 'manage_options', "excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-settings", 
				array( $this,'callDisplaySettings')
			);
			
		}
		
		public function callDisplayLast(){
		  if(count($GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->shops) > 1){
			  $GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->display(
				  $_COOKIE["excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-last-shop-component"] 
					? 
				  $_COOKIE["excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-last-shop-component"] 
					:
				  'wooc'
			  );
		  }else if(count($GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->shops) == 0){
			  $GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->display("noshop");
		  }else{
			  $GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->display($GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->shops[0]);
		  }
		}
		
		public function callDisplayShop(){
			$GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->display("auto");
		}
		
		public function callDisplaySettings(){
			$GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight']->display("settings");
		}
		
		public function plugin_path() {
			if ( $this->plugin_path ) return $this->plugin_path;
			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' );
			load_textdomain( 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight', WP_LANG_DIR . "/excel-like-product-attribute-and-tag-manager-for-woocommerce-and-wp-e-commerce-light/excel-like-aat-$locale.mo" );
			load_textdomain( 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight', $this->plugin_path() . "/languages/excel-like-aat-$locale.mo" );
		    load_plugin_textdomain( 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight', false, dirname( plugin_basename( __FILE__ ) ) . "/languages" );
		}
		
		public function internal_display(){
		    error_reporting(0);
		    $this->is_internal = true;
		    $this->display("");
			die();
		}
		
		public function display($elpm_shop_com){
			error_reporting(0);
		    if(isset($_REQUEST["elpm_shop_com"])){
			   $elpm_shop_com = $_REQUEST["elpm_shop_com"];
			}elseif($elpm_shop_com == 'auto'){
			   $elpm_shop_com = explode('-',$_REQUEST["page"]);
			   $elpm_shop_com = $elpm_shop_com[1];
			}
			
			if($elpm_shop_com == "settings"){
					?>
					    <script type="text/javascript">
							var PLEM_INIT = false;
							var PLEM_BASE = '<?php echo get_home_url(); ?>';
						</script>
						<div class="excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-settings">
						    
							
							<h2 style="text-align:center;"><?php echo __('Excel-Like Attribute/Tag Manager for WooCommerce and WP E-commerce (Light)', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></h2>

							       
								
								   
								   <form style="text-align:center;" method="post" class="plem-form" >
								    <input type="hidden" name="plem_do_save_settings" value="1" /> 
							        <table>
							            <tr>
										  <td><h3><?php echo __('Fixed columns count:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></h3></td>
										  <td> <input style="width:50px;text-align:center;" type="text" name="fixedColumns" value="<?php echo isset($this->settings["fixedColumns"]) ? $this->settings["fixedColumns"] : ""; ?>" /></td>
										  <td><?php echo __('(To make any column fixed move it to be within first [fixed columns count] columns)', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
										</tr>
							            <?php if(in_array('wooc',$this->shops)){?>
										<tr>
											<td><h3><?php echo __('WooCommerce columns visibility:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></h3></td>
											<td colspan="2">
											
											  <div class="checkbox-list">
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='name' checked='checked' /><label><?php echo __('Name', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='slug' checked='checked' /><label><?php echo __('Slug', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='sku' checked='checked' /><label> <?php echo __('SKU', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='categories' checked='checked' /><label><?php echo __('Categories', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='featured' checked='checked' /><label><?php echo __('Featured', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <br/>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='stock_status' checked='checked' /><label><?php echo __('Stock Status', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='stock' checked='checked' /><label><?php echo __('Stock', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='price' checked='checked' /><label><?php echo __('Price', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='override_price' checked='checked' /><label><?php echo __('Sales Price', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='attributes' checked='checked' /><label><?php echo __('Pivoted attributes', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <br/>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='status' checked='checked' /><label><?php echo __('Status', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='weight' checked='checked' /><label><?php echo __('Weight', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='height' checked='checked' /><label><?php echo __('Height', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='width' checked='checked' /><label><?php echo __('Width', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='length' checked='checked' /><label><?php echo __('Length', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <br/>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='backorders' checked='checked' /><label><?php echo __('Backorders', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='shipping_class' checked='checked' /><label><?php echo __('Shipping Class', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='tax_status' checked='checked' /><label><?php echo __('Tax Status', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='tax_class' checked='checked' /><label><?php echo __('Tax Class', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='image' /><label><?php echo __('Image', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <br/>
												  <span><input name="wooc_at_fileds[]" type='checkbox' value='tags' checked='checked' /><label><?php echo __('Tags', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <script type="text/javascript">
													 var woo_fileds = "<?php echo $this->settings["wooc_at_fileds"] ? $this->settings["wooc_at_fileds"] : "" ; ?>";
													 
													 if(jQuery.trim(woo_fileds)){
													     woo_fileds = woo_fileds.split(',');
														 if(woo_fileds.length > 0){
															 jQuery('INPUT[name="wooc_at_fileds[]"]').each(function(){
																if(jQuery.inArray(jQuery(this).val(), woo_fileds) < 0)
																	jQuery(this).removeAttr('checked');
																else
																	jQuery(this).attr('checked','checked');
																
															 });
														 }
														 
													 }
												  </script>
											  </div>
											</td>
										</tr>
										<?php } ?>
										
										<?php if(in_array('wpsc',$this->shops)){?>
										<tr>
											<td><h3><?php echo __('WP E-Commerce columns visibility:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></h3></td>
											<td colspan="2">
											  <div class="checkbox-list">
											      <span><input name="wpsc_at_fileds[]" type='checkbox' value='name' checked='checked' /><label><?php echo __('Name', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='slug' checked='checked' /><label><?php echo __('Slug', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='sku' checked='checked' /><label> <?php echo __('SKU', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='categories' checked='checked' /><label><?php echo __('Categories', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='tags' checked='checked' /><label><?php echo __('Tags', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <br/>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='stock' checked='checked' /><label><?php echo __('Stock', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='price' checked='checked' /><label><?php echo __('Price', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='override_price' checked='checked' /><label><?php echo __('Sales Price', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='status' checked='checked' /><label><?php echo __('Status', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='weight' checked='checked' /><label><?php echo __('Weight', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <br/>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='height' checked='checked' /><label><?php echo __('Height', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='width' checked='checked' /><label><?php echo __('Width', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='length' checked='checked' /><label><?php echo __('Length', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='taxable' checked='checked' /><label><?php echo __('Taxable', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span> 
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='loc_shipping' checked='checked' /><label><?php echo __('Local Shipping', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <br/>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='int_shipping' checked='checked' /><label><?php echo __('International Shipping', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>
												  <span><input name="wpsc_at_fileds[]" type='checkbox' value='image' /><label><?php echo __('Image', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label></span>												  
												  <script type="text/javascript">
													 var wpsc_at_fileds = "<?php echo $this->settings["wpsc_at_fileds"] ? $this->settings["wpsc_at_fileds"] : ""; ?>";
													 if(jQuery.trim(wpsc_at_fileds)){
													     wpsc_at_fileds = wpsc_at_fileds.split(',');
														 if(wpsc_at_fileds.length > 0){
															 jQuery('INPUT[name="wpsc_at_fileds[]"]').each(function(){
																if(jQuery.inArray(jQuery(this).val(), wpsc_at_fileds) < 0)
																	jQuery(this).removeAttr('checked');
																else
																	jQuery(this).attr('checked','checked');
															 });
														 }
														 
													 }
												  </script>
											  </div>
											</td>
										</tr>
										<?php } ?>
										
										<?php if(in_array('wooc',$this->shops)){?>
										<tr>   
											<td colspan="3">
											  <br/>
											  <p><?php echo __("** If metavalue contains sub-value you want to show you can use ! to access object you indexer key and . to acces prperty like: <code> _some_meta.subprop!subsubval </code> would correspond to: <code>$_some_meta->subprop['subsubval']</code>", 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?> </p>
											   
											   <br/>
											  <h3><?php echo __('WooCommerce custom Fileds', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></h3>
											  <table class="table" >
											    <tr>
												  <td></td>
											      <td><?php echo __('Enabled', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
												  <td><?php echo __('Column title', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
											      <td><?php echo __('Source type', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
												  <td><?php echo __('Source type value', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
												  <td><?php echo __('Edit options', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
												  <td><?php echo __('Editable for variation', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
											    </tr>
												<?php 
												for($I = 0; $I < 8; $I++){ 
													$n = $I + 1;
												?>
													<tr>
													  <td><?php echo $n; ?></td>
													  <td style="text-align:center;" ><input type="checkbox" name="wooccf_at_enabled<?php echo $n; ?>" value="1"  <?php echo $this->settings["wooccf_at_enabled".$n] ? " checked='checked' " : "" ; ?> /></td>
													  <td><input type="text" name="wooccf_at_title<?php echo $n; ?>" value="<?php echo $this->settings["wooccf_at_title".$n];?>" /></td>
													  <td>
													  <select class="value-source-type" name="wooccf_at_type<?php echo $n; ?>" >
														<option value="post" ><?php echo __('Post filed', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
														<option value="meta" ><?php echo __('Meta value', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
														<option value="term" ><?php echo __('Term taxonomy', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
													  </select>			
                                                      <script type="text/javascript">
														jQuery(document).ready(function(){
															jQuery('SELECT[name="<?php echo 'wooccf_at_type'.$n; ?>"]').val('<?php echo $this->settings["wooccf_at_type".$n];?>' || 'post');
														});
													  </script> 													  
													  </td>
													  <td><input class="auto-source" type="text" name="wooccf_at_source<?php echo $n; ?>" value="<?php echo $this->settings["wooccf_at_source".$n];?>" /></td>
													  <td>
													      <input type="hidden" name="wooccf_at_editoptions<?php echo $n; ?>" value="" />
														  <div class="editor-options">
														  
														  </div>
													  </td>
													  <td style="text-align:center;" ><input type="checkbox" name="wooccf_at_varedit<?php echo $n; ?>" value="1"  <?php echo $this->settings["wooccf_at_varedit".$n] ? " checked='checked' " : "" ; ?> /></td>
													</tr>
												<?php } ?>
											  </table>
											</td>
										</tr>
										<?php } ?>
										<?php if(in_array('wpsc',$this->shops)){?>
										<tr>   
											<td colspan="3">
											  <h3><?php echo __('WP E-Commerce custom Fileds', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></h3>
											  <table class="table" >
											    <tr>
												  <td></td>
											      <td><?php echo __('Enabled', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
												  <td><?php echo __('Column title', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
											      <td><?php echo __('Source type', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
												  <td><?php echo __('Source type value', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
												  <!-- <td>Editor</td> -->
												  <td><?php echo __('Edit options', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></td>
											    </tr>
												<?php 
												for($I = 0; $I < 8; $I++){ 
													$n = $I + 1;
												?>
													<tr>
													  <td><?php echo $n; ?></td>
													  <td style="text-align:center;"><input type="checkbox" name="wpsccf_at_enabled<?php echo $n; ?>" value="1" <?php echo $this->settings["wpsccf_at_enabled".$n] ? " checked='checked' " : "" ; ?> /></td>
													  <td><input type="text" name="wpsccf_at_title<?php echo $n; ?>" value="<?php echo $this->settings["wpsccf_at_title".$n];?>" /></td>
													  <td>
													  <select class="value-source-type" name="wpsccf_at_type<?php echo $n; ?>" >
													    <option value="post" ><?php echo __('Post filed', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
														<option value="meta" ><?php echo __('Meta value', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
														<option value="term" ><?php echo __('Term taxonomy', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
													  </select>	
													  <script type="text/javascript">
													     jQuery(document).ready(function(){
															jQuery('SELECT[name="<?php echo 'wpsccf_at_type'.$n; ?>"]').val('<?php echo $this->settings["wpsccf_at_type".$n];?>' || 'post');
														 });
                                                      </script> 													  
													  </td>
													  <td><input class="auto-source" type="text" name="wpsccf_at_source<?php echo $n; ?>" value="<?php echo $this->settings["wpsccf_at_source".$n];?>" /></td>
													  <td>
													      <input type="hidden" name="wpsccf_at_editoptions<?php echo $n; ?>" value="" />
														  <div class="editor-options">
														  
														  </div>
													  </td>
													</tr>
												<?php } ?>
											  </table>
											</td>
										</tr>
										<?php } ?>
										
									    
							        </table>
									<?php
									  global $wpdb;
									  $metas        = $wpdb->get_col("select DISTINCT pm.meta_key from $wpdb->postmeta as pm LEFT JOIN $wpdb->posts as p ON p.ID = pm.post_id where p.post_type in ('product','product_variation','wpsc-product')");
									  $terms        = $wpdb->get_col("select DISTINCT tt.taxonomy from $wpdb->posts as p LEFT JOIN $wpdb->term_relationships as tr on tr.object_id = p.ID LEFT JOIN $wpdb->term_taxonomy as tt on tt.term_taxonomy_id = tr.term_taxonomy_id where p.post_type in ('product','product_variation','wpsc-product')");
	                                  $post_fields  = $wpdb->get_results("SHOW COLUMNS FROM $wpdb->posts;");
									  $autodata = array();
									  
									  foreach($post_fields as $key =>$val){
									    if($val->Field == "ID")
											continue;
										$obj = new stdClass();
										$obj->category = 'Post field';
										$obj->label    = __($val->Field, 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' );
										$autodata[] = $obj;
									  }
									  
									  foreach($terms as $key =>$val){
										$obj = new stdClass();
										$obj->category = 'Term taxonomy';
										$obj->label    = __($val, 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' );
										$autodata[] = $obj;
									  }
									  
									  foreach($metas as $key =>$val){
										$obj = new stdClass();
										$obj->category = 'Meta key';
										$obj->label    = __($val, 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' );
										$autodata[] = $obj;
									  }
									  
									  
									?>
									
									 <script type="text/javascript">
									        <?php
											for($I = 0; $I < 5; $I++){ 
												$n = $I + 1;
												if(isset($this->settings["wooccf_at_editoptions".$n])){
													if($this->settings["wooccf_at_editoptions".$n]){
													?>
														jQuery('INPUT[name="<?php echo "wooccf_at_editoptions".$n; ?>"]').val(JSON.stringify(<?php echo $this->settings["wooccf_at_editoptions".$n]; ?>));
													<?php	
													}
												}
												
												if(isset($this->settings["wpsccf_at_editoptions".$n])){
													if($this->settings["wpsccf_at_editoptions".$n]){
													?>
														jQuery('INPUT[name="<?php echo "wpsccf_at_editoptions".$n; ?>"]').val(JSON.stringify(<?php echo $this->settings["wpsccf_at_editoptions".$n]; ?>));
													<?php	
													}
												}
											}
											       
											 
											?>
									 
											jQuery.widget( "custom.catcomplete", jQuery.ui.autocomplete, {
												_renderMenu: function( ul, items ) {
												  var that = this,currentCategory = "";
												  
												  var catV = jQuery(this.element).closest('TR').find('SELECT.value-source-type').val();
												  var Filter = "Post field";
												  if(catV == "meta")
													Filter = "Meta key";
												  else if(catV == "term")
													Filter = "Term taxonomy";
												 	
												  jQuery.each( items, function( index, item ) {
													if(item.category == Filter){
														if ( item.category != currentCategory ) {
														  ul.append( "<li style='font-weight: bold; padding: .2em .4em;  margin: .8em 0 .2em; line-height: 1.5;' class='ui-autocomplete-category'>" + item.category + "</li>" );
														  currentCategory = item.category;
														}
														that._renderItemData( ul, item );
													}
												  });
												}
											});
  
											jQuery(document).ready(function(){
											   jQuery('INPUT.auto-source').catcomplete({
												  delay: 0,
												  source: <?php echo json_encode($autodata); ?>
												});
												
											   jQuery("SELECT.value-source-type").change(function(){
											       jQuery(this).closest('TR').find().val('auto-source');
											   });	
											});
											
											var plem_vst_initLoad = true;
											jQuery('SELECT.value-source-type').change(function(){
											    var type = jQuery(this).val();
												if(type == "post")
													metapostEditor(jQuery(this).closest('TR').find('.editor-options'),plem_vst_initLoad);
												else if(type == "meta")
													metapostEditor(jQuery(this).closest('TR').find('.editor-options'),plem_vst_initLoad);
												else if(type == "term")
													termEditor(jQuery(this).closest('TR').find('.editor-options'),plem_vst_initLoad);
											});
											
											function metapostEditor(container,load){
											   var value_input = container.parent().find('> INPUT');
											   container.find('> *').remove();
											   
											   if(!load)
												   value_input.val('{}');
												   
											   var values = eval("(" + (value_input.val() || "{}")  + ")");	   
											   
											   jQuery('.postmetaOptModel > *').clone().appendTo(container);
											   var formatSelector = container.find('SELECT.formater-selector');
											   formatSelector.change(function(){
											       value_input.val('{"formater":"' + jQuery(this).val() + '"}');
												   container.find('.sub-options > *').remove();
											       jQuery('.sub-option.' +  formatSelector.val() + " > *").clone().appendTo(container.find('.sub-options'));
												   
												   //container.find('*[pname]').each(function(i))
											   
											       if(formatSelector.attr('init')){
													formatSelector.removeAttr('init');
													for(var prop in values){
												       	var item = container.find('.sub-options *[name="' + prop + '"]');
														if(item.is('.rdo, .chk') || item.length > 1){
														  item.each(function(ind){
														     if(jQuery(this).val() == values[prop])
                                                               	jQuery(this).attr('checked','checked');														 
														  });
														}else
															item.val(values[prop]);
													}
												   }
											   
												   container.find('.sub-options INPUT, .sub-options SELECT, .sub-options TEXTAREA').change(function(){
														var obj = {};
														container.find('INPUT, SELECT, TEXTAREA').each(function(i){
														    if(!jQuery(this).is('.rdo,.chk') || (jQuery(this).is('.rdo,.chk') && jQuery(this).attr('checked')))
																obj[jQuery(this).attr("name")] = jQuery(this).val();
														});
														value_input.val(JSON.stringify(obj));
												   });
											   });
											   
											   if(load){
											       if(values.formater){ 
														formatSelector.attr('init',1); 
														formatSelector.val(values.formater);
												   }
											   }
											   
											   formatSelector.trigger('change');
											}
											
											function termEditor(container,load){
											   var value_input = container.parent().find('> INPUT');
											   container.find('> *').remove();
											   
											   if(!load)
												   value_input.val('{}');
											 
											   
											   
											   jQuery('.termOptModel > *').clone().appendTo(container);
											   container.find('INPUT, SELECT, TEXTAREA').change(function(){
											        var obj = {};
											        container.find('INPUT, SELECT, TEXTAREA').each(function(i){
													    if(!jQuery(this).is('.rdo,.chk') || (jQuery(this).is('.rdo,.chk') && jQuery(this).attr('checked')))
															obj[jQuery(this).attr("name")] = jQuery(this).val();
													});
													value_input.val(JSON.stringify(obj));
											   });
											   
											   if(load){
												var values = eval("(" + (value_input.val() || "{}") + ")");											 
												for(var prop in values){
												       	var item = container.find('*[name="' + prop + '"]');
														
														if(item.is('.rdo, .chk') || item.length > 1){
														  item.each(function(ind){
														     if(jQuery(this).val() == values[prop])
                                                               	jQuery(this).attr('checked','checked');														 
														  });
														}else
															item.val(values[prop]);
													}
											   }
											}
											
											jQuery(document).ready(function(){
											  jQuery('SELECT.value-source-type').trigger('change');
											  plem_vst_initLoad = false;
											});
											
									 </script>
									
									
									<input class="cmdSettingsSave plem_button" type="submit" value="Save" />
							       </form>
								   
								   <script type="text/javascript">
								     jQuery('.cmdSettingsSave').click(function(e){
										e.preventDefault();
										jQuery('.excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-settings .editor-options *').remove();
										jQuery('.excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-settings .cmdSettingsSave').closest('form').submit();
									 });
								   </script>
							
							
							<div style="display:none;">
							  <div class="termOptModel" >
							    <label><?php echo __('Can have multiple values:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label><input name="multiple" class="chk chk-multiple" type="checkbox" value="1" />
								<label><?php echo __('Allow new values:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label><input  name="allownew" class="chk chk-newvalues" type="checkbox" value="1" />
							  </div>
							  
							  <div class="postmetaOptModel" >
							    <label><?php echo __('Edit formater:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label>
								<select name="formater" class="formater-selector">
								  <option value="text" ><?php echo __('Simple', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
								  <option value="content" ><?php echo __('Content', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
								  <option value="checkbox" ><?php echo __('Checkbox', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
								  <option value="dropdown" ><?php echo __('Dropdown', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></option>
								</select>
								<span class="sub-options">
								
								</span>
							  </div>
							  
							  <div class="sub-option text">
							     <form style="display:inline;">
								 <label><?php echo __('Text', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label>   <input class="rdo" type="radio" name="format" value="" checked="checked">
								 <label><?php echo __('Integer', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label><input class="rdo" type="radio" name="format" value="integer">
								 <label><?php echo __('Decimal', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label><input class="rdo" type="radio" name="format" value="decimal">
								 </form>
							  </div>
							  
							  <div class="sub-option content">
							  </div>
							  
							  <div class="sub-option checkbox">
							     <label><?php echo __('Checked value:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label> <input placeholder="1"  style="width:80px;" type="text" name="checked_value" value="">
								 <label><?php echo __('Un-checked value:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label> <input placeholder=""  style="width:80px;" type="text" name="unchecked_value" value="">
							  </div>
							  
							  <div class="sub-option dropdown">
							    <label><?php echo __('Values(val1,val2...):', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label><input style="width:300px;" name="values" type="text" value="" />
								<label><?php echo __('Strict:', 'excellikeattributeandtagmanagerforwoocommerceandwpecommercelight' ) ?></label><input name="strict" class="chk chk-strict" type="checkbox" value="1" />
							  </div>
							  
							</div>
						</div>
					<?php	
			}else if( $elpm_shop_com != "noshop") { 
			    if(!$this->is_internal){
				?>
				
				<a class="upgrade_component" href="http://holest.com/index.php/holest-outsourcing/joomla-wordpress/excel-like-manager-for-woocommerce-and-wp-e-commerce.html" >Pro version enables update and csv-import of all fileds, check it out &gt;&gt;</a>
				<iframe style="width:100%;position:absolute;" id="elpm_shop_frame" src="admin-ajax.php?action=pelm_frame_display&page=excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-root&elpm_shop_com=<?php echo $elpm_shop_com; ?>" ></iframe>
				<button style="z-index: 999999;position:fixed;bottom:0;color:white;border:none;background-color:#9b4f96;left:48%;cursor:pointer;" onclick="window.location = document.getElementById('elpm_shop_frame').src + '&pelm_full_screen=1'; return false;" >[Full screen mode]</button>
				<script type="text/javascript">
					(function(c_name,value,exdays) {
						var exdate = new Date();
						exdate.setDate(exdate.getDate() + exdays);
						var c_value = escape(value) + ((exdays==null) ? "" : ";expires="+exdate.toUTCString());
						document.cookie=c_name + "=" + c_value;
					})("excellikeattributeandtagmanagerforwoocommerceandwpecommercelight-last-shop-component","<?php echo $elpm_shop_com; ?>", 30);
					
					function onElpmShopFrameResize(){
						jQuery('#elpm_shop_frame').outerHeight( window.innerHeight - 10 - (jQuery("#wpadminbar").outerHeight() + jQuery("#wpfooter").outerHeight()));
					}
					
					jQuery(window).resize(function(){
						onElpmShopFrameResize();
					});
					
					jQuery(document).ready(function(){
						onElpmShopFrameResize();
					});
					
					jQuery(window).load(function(){
						onElpmShopFrameResize();
					});
					
					onElpmShopFrameResize();
				</script>
				<?php 
				}else{
				    $plem_settings = $this->settings;  
					$excellikeattributeandtagmanagerforwoocommerceandwpecommercelight_baseurl = plugins_url('/',__FILE__); 
					require_once(dirname(__FILE__). DIRECTORY_SEPARATOR . 'shops' . DIRECTORY_SEPARATOR .  $elpm_shop_com.'.php');
				}
			}
		}
   }
   $GLOBALS['excellikeattributeandtagmanagerforwoocommerceandwpecommercelight'] = new excellikeattributeandtagmanagerforwoocommerceandwpecommercelight();


}

?>