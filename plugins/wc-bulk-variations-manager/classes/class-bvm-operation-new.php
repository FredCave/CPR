<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BVM_Operation_New' ) ) {

	class BVM_Operation_New {

		function __construct() {

			add_option( 'bvm_operation_id' );
			add_option( 'bvm_last_operation' );

			add_action( 'admin_init', array( $this, 'query_args_action' ) );
			add_action( 'admin_footer', array( $this, 'bvm_new_admin_styles_and_scripts' ) );

            add_action( 'wp_ajax_bvm_check_permission', array( $this, 'bvm_check_permission' ) );

            add_action( 'wp_ajax_sa_bulk_add_update_attributes', array( $this, 'bulk_add_update_attributes' ) );
            add_action( 'wp_ajax_get_product_ids_from_categories', array( $this, 'get_product_ids_from_categories' ) );
            add_action( 'wp_ajax_get_variation_ids', array( $this, 'get_variation_ids' ) );
            add_action( 'wp_ajax_get_additional_data', array( $this, 'get_additional_data' ) );
            add_action( 'wp_ajax_add_update_product_attributes', array( $this, 'add_update_product_attributes' ) );
            add_action( 'wp_ajax_get_possible_variations', array( $this, 'get_possible_variations' ) );
            add_action( 'wp_ajax_create_update_variation', array( $this, 'create_update_variation' ) );
            add_action( 'wp_ajax_collect_variation_post_meta', array( $this, 'collect_variation_post_meta' ) );
            add_action( 'wp_ajax_save_variation_post_meta', array( $this, 'save_variation_post_meta' ) );
            add_action( 'wp_ajax_sync_variable_product_price', array( $this, 'sync_variable_product_price' ) );
            add_action( 'wp_ajax_finalize_bulk_create_update_variations', array( $this, 'finalize_bulk_create_update_variations' ) );
            add_action( 'wp_ajax_bvm_delete_variations', array( $this, 'bvm_delete_variations' ) );

		}

		/**
         * to handle WC compatibility related function call from appropriate class
         * 
         * @param $function_name string
         * @param $arguments array of arguments passed while calling $function_name
         * @return result of function call
         * 
         */
        public function __call( $function_name, $arguments = array() ) {

            if ( ! is_callable( 'SA_WC_Compatibility_2_4', $function_name ) ) return;

            if ( ! empty( $arguments ) ) {
                return call_user_func_array( 'SA_WC_Compatibility_2_4::'.$function_name, $arguments );
            } else {
                return call_user_func( 'SA_WC_Compatibility_2_4::'.$function_name );
            }

        }

        function open_file( $table_name = '', $new = false, $close = false ) {
			if ( empty( $table_name ) ) return false;
			$upload_dir = wp_upload_dir();
			$mode = ( $new ) ? 'w+' : 'a+';
			$file = @fopen( $upload_dir['basedir'] . '/'.$table_name.'.csv', $mode );
			if ( $close ) {
				return @fclose( $file );
			} else {
				return $file;
			}
		}

		function close_file( $file_pointer ) {
			@fclose( $file_pointer );
		}

		function load_data_infile( $table_name = '', $is_backup_file = false ) {
			if ( empty( $table_name ) ) return;
			global $wpdb;
			$bvm_db = clone $wpdb;
			if ( $bvm_db->use_mysqli ) {
				$bvm_db->dbh = mysqli_init();

				$port = null;
				$socket = null;
				$host = $bvm_db->dbhost;
				$port_or_socket = strstr( $host, ':' );
				if ( ! empty( $port_or_socket ) ) {
					$host = substr( $host, 0, strpos( $host, ':' ) );
					$port_or_socket = substr( $port_or_socket, 1 );
					if ( 0 !== strpos( $port_or_socket, '/' ) ) {
						$port = intval( $port_or_socket );
						$maybe_socket = strstr( $port_or_socket, ':' );
						if ( ! empty( $maybe_socket ) ) {
							$socket = substr( $maybe_socket, 1 );
						}
					} else {
						$socket = $port_or_socket;
					}
				}

				if ( WP_DEBUG ) {
					mysqli_real_connect( $bvm_db->dbh, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket, 128 );
				} else {
					@mysqli_real_connect( $bvm_db->dbh, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket, 128 );
				}
			} else {
				if ( WP_DEBUG ) {
					$bvm_db->dbh = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, false, 128 );
				} else {
					$bvm_db->dbh = @mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, false, 128 );
				}
				$bvm_db->set_charset( $bvm_db->dbh );
				$bvm_db->ready = true;
				$bvm_db->select( $bvm_db->dbname, $bvm_db->dbh );
			}
			$upload_dir = wp_upload_dir();
			$suffix = ( $is_backup_file ) ? '_backup' : '';
			$file = str_replace( '\\', '/', $upload_dir['basedir'] . '/'.$table_name.$suffix.'.csv' );
			$is_local_infile = false;
			$is_granted = false;
			$infile_variable = $bvm_db->get_row( "SHOW VARIABLES LIKE 'local_infile';", 'ARRAY_A' );
			if ( strtolower( $infile_variable['Value'] ) === 'on' ) {
				$is_local_infile = true;
			}
			if ( $is_local_infile ) {
				$all_grants = $wpdb->get_results( "SHOW GRANTS FOR '".DB_USER."'@'".DB_HOST."'", 'ARRAY_A' );
				if ( ! empty( $all_grants ) ) {
					foreach ( $all_grants as $grants ) {
						foreach ( $grants as $key => $value ) {
							if ( stripos( $value, 'all' ) !== false || stripos( $value, 'file' ) !== false ) {
								$is_granted = true;
								break 2;
							}
						}
					}
				}
			}

			if ( $is_local_infile && $is_granted ) {
				if ( $table_name == 'term_relationships' ) {
					$max_allowed_packet = $bvm_db->get_row( "SHOW VARIABLES LIKE 'max_allowed_packet';", 'ARRAY_A' );
					$values = array_map( 'str_getcsv', file( $file ) );
					$this->delete_product_type_terms( $values, $bvm_db, $max_allowed_packet );
				}
				$bvm_db->query( "LOAD DATA LOCAL INFILE '{$file}' REPLACE INTO TABLE {$bvm_db->prefix}{$table_name} FIELDS TERMINATED BY ',' ENCLOSED BY '" . '"' . "' LINES TERMINATED BY '\\n'" );
			} else {
				$max_allowed_packet = $bvm_db->get_row( "SHOW VARIABLES LIKE 'max_allowed_packet';", 'ARRAY_A' );
				$max_len = (int)$max_allowed_packet['Value'];
				$max_len = round( $max_len, -3 );
				$values = array_map( 'str_getcsv', file( $file ) );
				if ( $table_name == 'term_relationships' ) {
					$this->delete_product_type_terms( $values, $bvm_db, $max_allowed_packet );
				}
				$query_start = "REPLACE INTO {$bvm_db->prefix}{$table_name} VALUES ";
				$main_query = $temp_query = $query_values = '';
				$temp_query = $query_start;
				$run_temp_query = false;
				foreach ( $values as $value ) {
					$main_query = $temp_query;
					$query_values = '';
					$query_values .= "('";
					$query_values .= implode( "','", $value );
					$query_values .= "'),";
					$temp_query .= $query_values;
					$run_temp_query = true;
					if ( strlen( $temp_query ) > $max_len  ) {
						$bvm_db->query( trim( $main_query, "," ) );
						$temp_query = $query_start . $query_values;
						$run_temp_query = true;
					}
				}
				if ( $run_temp_query ) {
					$bvm_db->query( trim( $temp_query, "," ) );
				}
			}
			unlink( $file );
			unset( $bvm_db );
		}

		function delete_product_type_terms( $term_relationships_data, $bvm_db, $max_allowed_packet ) {
			$max_len = (int)$max_allowed_packet['Value'];
			$max_len = round( $max_len, -3 );
			$product_type_terms = get_terms( 'product_type', array( 'slug' => array( 'simple', 'variable' ), 'fields' => 'ids' ) );
			if ( empty( $product_type_terms ) ) return;
			$query_start = "DELETE FROM {$bvm_db->prefix}term_relationships WHERE term_taxonomy_id IN ( " . implode( ',', $product_type_terms ) . " ) ";
			$main_query = $query_values = '';
			$object_ids = array();
			foreach ( $term_relationships_data as $value ) {
				$object_ids[] = current( $value );
			}
			if ( ! empty( $object_ids ) ) {
				$object_ids = array_unique( $object_ids );
				$query_values = " AND object_id IN ( " . implode( ',', $object_ids ) . " )";
				$main_query = $query_start . $query_values;
				$bvm_db->query( trim( $main_query, "," ) );
			}
		}

		function woocommerce_variations_page() {
			global $wpdb;

			$bvm_is_permission_available = get_option( 'bvm_is_permission_available', 'yes' );

			if ( empty( $bvm_is_permission_available ) || $bvm_is_permission_available == 'no' ) {
				?>
				<div class="wrap">
					<div id="icon-index" class="icon32"><br/></div>
					<h2><?php _e( 'WooCommerce Bulk Variations Manager', SA_Bulk_Variations::$text_domain ); ?></h2>
					<div id="bvm_permission_status">
						<h3><i><?php echo __( 'For creating/updating/deleting variations in fastest way, Bulk Variations Manager uses internal import & export of file. Therefore it needs some additional settings. These settings are related to file write, file import & export permissions. Currently these settings are not found.', SA_Bulk_Variations::$text_domain ); ?></i></h3>
						<p><label for="bvm_only_earlier_version"><input type="checkbox" id="bvm_only_earlier_version" name="bvm_only_earlier_version" value="yes" /> <?php echo __( 'I\'ll use slower method always', SA_Bulk_Variations::$text_domain ); ?></label>
						<p>
							<button id="bvm_switch_to_earlier_version" class="button-primary"><?php echo __( 'Switch to slower method', SA_Bulk_Variations::$text_domain ); ?></button>
							<button id="bvm_recheck_permission_status" class="button" onClick="bvmCheckPermission();"><?php echo __( 'Recheck settings', SA_Bulk_Variations::$text_domain ); ?></button>
						</p>
					</div>
				</div>
				<?php
			} else {

				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	            $assets_path    = str_replace( array( 'http:', 'https:' ), '', $this->global_wc()->plugin_url() ) . '/assets/';

	            if ( $this->is_wc_gte_21() ) {

	                // Register scripts
	                wp_register_script( 'woocommerce_admin', $this->global_wc()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), $this->global_wc()->version );
	                wp_register_script( 'woocommerce_admin_meta_boxes', $this->global_wc()->plugin_url() . '/assets/js/admin/meta-boxes' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'accounting', 'round' ), $this->get_wc_version() );

	                $params = array(
								'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', SA_Bulk_Variations::$text_domain ),
								'ajax_url' 					=> admin_url( 'admin-ajax.php' ),
								'search_products_nonce' 	=> wp_create_nonce( "search-products" )
							);

	                if ( $this->is_wc_gte_23() ) {

						if ( ! wp_script_is( 'select2', 'registered' ) ) {
	                        wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/admin/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
	                    }
	                    if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
	                        wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
	                    }
	                    
						wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', $params );

						$locale  = localeconv();
						$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

						$woocommerce_admin_params = array(
							'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', SA_Bulk_Variations::$text_domain ), $decimal ),
							'i18n_mon_decimal_error'            => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', SA_Bulk_Variations::$text_domain ), wc_get_price_decimal_separator() ),
							'i18n_country_iso_error'            => __( 'Please enter in country code with two capital letters.', SA_Bulk_Variations::$text_domain ),
							'i18_sale_less_than_regular_error'  => __( 'Please enter in a value less than the regular price.', SA_Bulk_Variations::$text_domain ),
							'decimal_point'                     => $decimal,
							'mon_decimal_point'                 => wc_get_price_decimal_separator()
						);

						wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $woocommerce_admin_params );
					} else {
						wp_register_script( 'ajax-chosen', $this->global_wc()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . $suffix . '.js', array('jquery', 'chosen'), $this->global_wc()->version );
		                wp_register_script( 'chosen', $this->global_wc()->plugin_url() . '/assets/js/chosen/chosen.jquery' . $suffix . '.js', array('jquery'), $this->global_wc()->version );
		            }
	                
	                wp_enqueue_script( 'woocommerce_admin' );
	                wp_enqueue_script( 'woocommerce_admin_meta_boxes' );

	                if ( $this->is_wc_gte_23() ) {
						wp_enqueue_script( 'select2' );
						wp_enqueue_script( 'wc-enhanced-select' );
					} else {
						wp_enqueue_script( 'ajax-chosen' );
		                wp_enqueue_script( 'chosen' );
		            }

	                wp_localize_script( 'woocommerce_admin_meta_boxes', 'woocommerce_admin_meta_boxes', $params );
	                                
	                if ( $this->is_wc_gte_23() ) {
						wp_enqueue_style( 'select2', $assets_path . 'css/select2.css' );
					} else {
						wp_enqueue_style( 'woocommerce_chosen_styles', $assets_path . 'css/chosen.css' );
					}
	                
	            } else {

	                // Register scripts
	                wp_register_script( 'woocommerce_admin', $this->global_wc()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array ('jquery', 'jquery-ui-widget', 'jquery-ui-core' ), '1.0' );
	                wp_register_script( 'woocommerce_writepanel', $this->global_wc()->plugin_url() . '/assets/js/admin/write-panels' . $suffix . '.js', array ('jquery' ) );
	                wp_register_script( 'ajax-chosen', $this->global_wc()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . $suffix . '.js', array ('jquery' ), '1.0' );
	                
	                wp_enqueue_script( 'woocommerce_admin' );
	                wp_enqueue_script( 'woocommerce_writepanel' );
	                wp_enqueue_script( 'ajax-chosen' );
	                
	                $woocommerce_witepanel_params = array ('ajax_url' => admin_url( 'admin-ajax.php' ), 'search_products_nonce' => wp_create_nonce( "search-products" ) );
	                
	                wp_localize_script( 'woocommerce_writepanel', 'woocommerce_writepanel_params', $woocommerce_witepanel_params );
	                
	                wp_enqueue_style( 'woocommerce_chosen_styles', $this->global_wc()->plugin_url() . '/assets/css/chosen.css' );
	                
	            }

	            wp_enqueue_style( 'woocommerce_admin_styles', $this->global_wc()->plugin_url() . '/assets/css/admin.css' );
	            wp_enqueue_style( 'jquery-ui-style', (is_ssl()) ? 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' : 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	                            
	            // Adding style for help tip for WC 2.0
	            if ( $this->is_wc_gte_20() ) {
	                $style = "width:16px;height=16px;" ; 
	            } else {
	                $style = '';
	            }

	            $wpdb->query ( "SET SESSION group_concat_max_len=999999" );// To increase the max length of the Group Concat Functionality

				$query = "SELECT wat.attribute_label AS attribute_label, tt.taxonomy AS taxonomy, tt.term_taxonomy_id AS term_taxonomy_id, t.name AS term_name, t.slug AS term_slug
							FROM {$wpdb->prefix}woocommerce_attribute_taxonomies AS wat
							LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON ( tt.taxonomy = CONCAT( 'pa_', wat.attribute_name ) )
							LEFT JOIN {$wpdb->prefix}terms AS t ON ( t.term_id = tt.term_id )
							";
				$attribute_results = $wpdb->get_results($query, 'ARRAY_A');
				
				$attributes = array();
				$attributes_to_terms = array();
				foreach ( $attribute_results as $attribute_result ) {
					if ( !in_array( $attribute_result['attribute_label'], $attributes, true ) ) {
						$attributes[$attribute_result['taxonomy']] = $attribute_result['attribute_label'];
					}
					if ( !isset( $attributes_to_terms[$attribute_result['taxonomy']] ) ) {
						$attributes_to_terms[$attribute_result['taxonomy']] = array();
					}
					$attributes_to_terms[$attribute_result['taxonomy']][$attribute_result['term_taxonomy_id']] = array(
																				'term_name' => $attribute_result['term_name'],
																				'term_slug' => $attribute_result['term_slug']
																			);
				}

				$attributes_to_terms = apply_filters( 'bvm_attributes_to_terms', $attributes_to_terms );

				if ( !wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
					wp_enqueue_style( 'jquery' );
				}

	            if ( !wp_script_is( 'jquery-ui-progressbar' ) ) {
	                wp_enqueue_script( 'jquery-ui-progressbar' );
	            }

				?>
				<div class="wrap">
				<div id="icon-index" class="icon32"><br/></div>
				<h2><?php _e( 'WooCommerce Bulk Variations Manager', SA_Bulk_Variations::$text_domain ); ?></h2>
				<?php
					$request_uri = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

					$bvm_is_take_backup = get_option( 'bvm_is_take_backup', 'yes' );

					if ( $bvm_is_take_backup == 'yes' ) {

						$bvm_operation_id = get_option( 'bvm_operation_id' );

						if ( !empty( $bvm_operation_id ) ) {
							
							$post = $wpdb->get_col( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_content_filtered LIKE '" . $bvm_operation_id . "'" );
							$postmeta = $wpdb->get_col( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'bvm_operation_id' AND meta_value LIKE '" . $bvm_operation_id . "'" );

							if ( count( $post ) > 0 || count( $postmeta ) > 0 ) {
								?>
								<div class="updated fade">
									<p>
										<?php echo sprintf( __( 'Having problem after last bulk update? %s %s', SA_Bulk_Variations::$text_domain ), '<a href="' . add_query_arg( "bvm_undo", "1", $request_uri ) . '">' . __( 'Click to undo & revert changes', SA_Bulk_Variations::$text_domain ) . '</a>', '<a id="about_undo">' . __( '[?]', SA_Bulk_Variations::$text_domain ) . '</a>' ); ?>
									</p>
								</div>
								<p id="undo_description" class="description" style="display: none;">
									<?php echo __( 'Before every bulk update, a snapshot of selected products are created. When you\'ll click \'undo\' it will restore to previous snapshot. Remember while reverting: All changes made after taking snapshot from outside of this plugin will get lost', SA_Bulk_Variations::$text_domain ); ?>
								</p>
								<?php
							}

						}

					}
				?>
	            <div id="bvm_addiational_link">
					<p style="text-align: right;">
	                    <a href="<?php echo add_query_arg( 'bvm_version', 'old', admin_url( 'edit.php?'.$_SERVER['QUERY_STRING'] ) ); ?>" title="<?php _e( 'Switch to earlier version', SA_Bulk_Variations::$text_domain ); ?>"><?php echo __( 'Problem? Switch to earlier version', SA_Bulk_Variations::$text_domain ); ?></a>
	                    | <a href="<?php echo admin_url() . '#TB_inline?inlineId=sa_bulk_variations_post_query_form&height=550&width=600'; ?>" class="thickbox" title="<?php _e( 'Send your query', SA_Bulk_Variations::$text_domain ); ?>" target="_blank"><?php echo __( 'Need Help?', SA_Bulk_Variations::$text_domain ); ?></a>
	                    | <a href="http://www.storeapps.org/support/documentation/bulk-variations-manager/" title="<?php _e( 'Documentation', SA_Bulk_Variations::$text_domain ); ?>" target="_blank"><?php echo __( 'Docs', SA_Bulk_Variations::$text_domain ); ?></a>
	                    | <a href="http://demo.storeapps.org/?demo=bvm" title="Bulk Variations Manager Demo" target="_blank"><?php echo __( 'Demo', SA_Bulk_Variations::$text_domain ); ?></a>
					</p>
	            </div>
	            <form id="bulk_variations_manager_form" action="" method="post">
				<style> 
	 			form#bulk_variations_manager_form {
	 				padding-bottom: 5em;
	 			}
	 			a#about_undo {
	 				cursor: pointer;
	 			}
				td.col1 {
					width: 25%;
				}
				td.col2 {
					width: 75%;
				}
				textarea#product_names {
					vertical-align: top;
				}
				div#product_names,
				div#search,
				div#search_result,
				div#categories {
					display: none;
				}
				#product_names_table th {
					text-align: left;
				}
				div#search_result,
				div#categories {
					max-height: 300px;
					margin-top: 10px;
					overflow-y: scroll;
				}
				div#additional_field {
					margin-top: 10px;
				}
				input[id^="price_"] {
					float: right;
				}
				ul.terms_list,
				div#attribute_header {
					width: 40%;
				}
				ul.categorychecklist li {
					line-height: 2em;
				}
				div#search_result ul,
				ul#product_catchecklist {
					padding: 0em 1.3em;
				}
				.bvm_link_style {
					text-decoration: underline;
					color: #0000ff;
				}
				div#categories p {
					margin-left: 1.3em;
				}
				div#search_result,
				div#categories {
					border-style: solid;
					border-width: 2px;
					border-color: lightgrey;
				}
				div#attribute_header .right {
					float: right;
				}
				div#attribute_header label {
					font-size: 1.1em;
				}
				ul.terms_list li label {
					/*max-width: 10%;*/
				}
				img.help_tip{
					<?php echo $style; ?>
				}
				div#price {
					display: none;
				}
				input.add_row,
				input.remove_row {
					width: 30px;
				}
	            table.attributes_to_price {
	                width: 100%;

	            }
	            table.attributes_to_price tr td {
	                vertical-align: top;
	            }
	            .close:before {
	            	content: "\f153";
					display: inline-block;
					-webkit-font-smoothing: antialiased;
					font: normal 30px/1 'dashicons';
					vertical-align: top;
					float: right;
	            }
	            .ui-progressbar {
	                position: relative;
	            }
	            .progress-label {
	                position: absolute;
	                height: 50%;
	                left: 40%;
	                top: 4px;
	                font-weight: bold;
	                text-shadow: 1px 1px 0 #fff;
	                padding-left: 20px;
	                padding-bottom: 5px;
	            }
	            #modal {
	                position: absolute;
	                top: 0%;
	                left: 0%;
	                width: 150%;
	                height: 100%;
	                margin-top: 0; 
	                margin-left: -50%; 
	                z-index: 99;
	                background-color: black;
	                opacity: 0.6;
	                display: none;
	            }
	            #progressbar {
	                position: fixed;
	                bottom: 50%;
	                left: 25%;
	                width: 50%;
	                border: 0px solid #ccc;
	                background-color: white;
	                z-index: 100;
	                display: none;
	            }
	            #progressbar .status {
	                position: absolute;
	                overflow-wrap: break-word;
	                width: 96%;
	                height: 50%;
	                left: 2%;
	                top: 125%;
	                color: white;
	                display: none;
	            }
	            </style>
				<script type="text/javascript">
					jQuery(function(){

						jQuery('#about_undo').on('click', function(){
							jQuery('#undo_description').slideToggle();
						});

						jQuery('input#search_button').on('click', function(){
							var search_text = jQuery('input#search_text').val();
							jQuery('img#loader').show();
							jQuery.ajax({
								url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
								type: 'GET',
								dataType: 'json',
								data: {
									action: 'woocommerce_json_search_products',
									security: '<?php echo wp_create_nonce("search-products"); ?>',
									term: search_text
								},
								success: function( data ) {
									var search_content = '';
									if ( jQuery.isEmptyObject(data) ) {
										search_content += '<?php _e( "No match found", SA_Bulk_Variations::$text_domain ); ?>';
									} else {
										search_content += '<ul>';
										jQuery.each( data, function( index, value ){
											search_content += '<li><input type="checkbox" id="product-'+index+'" name="product[]" value="'+index+'"> <label for="product-'+index+'">'+value+'</label></li>';
										});
										search_content += '</ul>';
									}
									jQuery('div#search_result').text('');
									jQuery('div#search_result').append(search_content);
									jQuery('div#search_result').show();
									jQuery('img#loader').hide();
								}
							});
						});

						var isShowBasePrice = function( isShow ) {
							if ( isShow ) {
								jQuery('div#price').show();
							} else {
								jQuery('div#price').hide();
							}
						};

						var showHideStep34 = function( target_action ) {
							if ( target_action == 'hide' ) {
								jQuery('#bvm_step_3').slideUp( 400 );
								jQuery('#bvm_step_4').slideUp( 400 );
								jQuery('#product_names').parent('p').slideUp( 400 );
								jQuery('div#product_names').slideUp( 400 );
							} else {
								jQuery('#bvm_step_3').slideDown( 400 );
								jQuery('#bvm_step_4').slideDown( 400 );
								jQuery('#product_names').parent('p').slideDown( 400 );
								if ( jQuery('#product_names').is(':checked') ) {
									jQuery('div#product_names').slideDown( 400 );
								}
							}

						}

						var showHideDifferentialPrice = function( target_action ) {
							if ( target_action == 'hide' ) {
								jQuery('input[id^="price_"]').hide();
								jQuery('div#attribute_header label.right').hide();
								jQuery('div#bvm_step_3 h3').html('<?php echo __( "Step 3: Select Attributes & Terms", SA_Bulk_Variations::$text_domain ); ?>');
								jQuery('div#bvm_step_3 p.description').hide();
							} else {
								jQuery('input[id^="price_"]').show();
								jQuery('div#attribute_header label.right').show();
								jQuery('div#bvm_step_3 h3').html('<?php echo __( "Step 3: Setup Variations & Prices", SA_Bulk_Variations::$text_domain ); ?>');
								jQuery('div#bvm_step_3 p.description').show();
							}
						}

						jQuery('input[name=selected_option]').on('click', function(){
							jQuery('div#search_result').text('');
							var selected_value = jQuery(this).val();
							var bvm_action = jQuery('input[name=bvm_action]:checked').val();
							var is_only_delete = ( bvm_action != undefined && bvm_action == 'only_delete' ) ? true : false;
							switch ( selected_value ) {
								case 'product_names':
									jQuery('div#search').slideUp();
									jQuery('div#categories').slideUp();
									jQuery('div#product_names').slideDown();
									jQuery('input#only_delete').attr('checked', false);
									isShowBasePrice( false );
									break;
								case 'search':
									jQuery('div#product_names').slideUp();
									jQuery('div#categories').slideUp();
									jQuery('div#search').slideDown();
									if ( is_only_delete ) {
										isShowBasePrice( false );
									} else {
										isShowBasePrice( true );
									}
									break;
								case 'categories':
									jQuery('div#search').slideUp();
									jQuery('div#product_names').slideUp();
									jQuery('div#categories').slideDown();
									if ( is_only_delete ) {
										isShowBasePrice( false );
									} else {
										isShowBasePrice( true );
									}
									break;
								default:
									jQuery('div#search').slideUp();
									jQuery('div#product_names').slideUp();
									jQuery('div#categories').slideUp();
									isShowBasePrice( false );
									break;
							}
						});

						jQuery('input[name=bvm_action]').on('click', function(){
							var selected_action = jQuery(this).val();
							if ( selected_action == 'only_create_update' ) {
								jQuery('input#use_for_variations').attr('checked', 'checked');
								jQuery('input#use_for_variations').attr('disabled', 'disabled');
								showHideStep34( 'show' );
								showHideDifferentialPrice( 'show' );
								isShowBasePrice( true );
							} else if ( selected_action == 'only_delete' ) {
								showHideStep34( 'hide' );
								showHideDifferentialPrice( 'show' );
								isShowBasePrice( false );
							} else {
								jQuery('input#use_for_variations').removeAttr('checked');
								jQuery('input#use_for_variations').removeAttr('disabled');
								showHideStep34( 'show' );
								showHideDifferentialPrice( 'hide' );
								isShowBasePrice( true );
							}
						});

						jQuery('input[name=selected_option],input[name=bvm_action]').on('click', function(){
							var selected_option = jQuery('input[name=selected_option]:checked').val();
							var bvm_action = jQuery('input[name=bvm_action]:checked').val();
							if ( selected_option == undefined || selected_option == '' || bvm_action == undefined || bvm_action == '' ) {
								return;
							}
							if ( selected_option == 'product_names' && bvm_action == 'only_attributes' ) {
								jQuery('input#use_for_variations').closest('p').slideUp();
							} else {
								jQuery('input#use_for_variations').closest('p').slideDown();
							}
						});

						jQuery('input.attribute').on('click', function(){
			                var isChecked = jQuery(this).is(':checked');
			                if ( isChecked == true ) {
			                    jQuery(this).parents('li').find('input.term').attr('checked', 'checked');
			                    jQuery(this).parents('li').find('input.price').removeAttr('readonly');
			                } else {
			                    jQuery(this).parents('li').find('input.term').removeAttr('checked');
			                    jQuery(this).parents('li').find('input.price').attr('readonly', 'readonly');
			                }
			            });
			            
			            jQuery('input.term').on('click', function(){
			                var isChecked = jQuery(this).is(':checked');
			                if ( isChecked == false ) {
			                    jQuery(this).parents('li').find('input.attribute').removeAttr('checked');
			                } else {
			                    var countCheckedItems = jQuery(this).parents('ul.terms_list').children().find('input.term:checked').length;
			                    var countTotalItems = jQuery(this).parents('ul.terms_list').children().find('input.term').length;
			                    
			                    if ( countCheckedItems == countTotalItems ) {
			                        jQuery(this).parents('li').find('input.attribute').attr( 'checked', 'checked' );
			                    }
			                }
			            });

			            jQuery('input[id^="terms_"]').on('click', function(){
			            	var term_id = jQuery(this).attr('id').substring(6);
			            	if ( jQuery(this).is(':checked') && ! jQuery('#only_attributes').is(':checked') ) {
			            		jQuery('input[id$="_'+term_id+'"]').removeAttr('readonly');
			            	} else {
			            		jQuery('input[id$="_'+term_id+'"]').attr('readonly', 'readonly');
			            	}
			            });

			            jQuery('input.add_row').on('click', function(){
			            	jQuery('table#product_names_table tbody').append(
			            			'<tr>\
										<td><input type="text" name="product_names[]" size="50" value="" placeholder="<?php _e( 'Enter a product&lsquo;s name', SA_Bulk_Variations::$text_domain ); ?>&hellip;" /></td>\
										<td><input type="number" step="any" name="base_price[]" min="0" value="" placeholder="0.00" /></td>\
										<td><input type="button" class="remove_row" value="&#215;" /></td>\
									</tr>'
			            		);
			            });

			            jQuery('input.remove_row').on('click', function(){
			            	jQuery(this).parent().parent().remove();
			            });

			            jQuery('#check_all_category').on('click', function(){
			            	jQuery('ul#product_catchecklist').find('input[type="checkbox"]').attr('checked', 'checked');
			            });

	                    jQuery('#uncheck_all_category').on('click', function(){
			            	jQuery('ul#product_catchecklist').find('input[type="checkbox"]').removeAttr('checked');
			            });

	                    <?php if ( $this->is_wc_gte_23() ) { ?>

							if ( typeof getEnhancedSelectFormatString == "undefined" ) {
								function getEnhancedSelectFormatString() {
									var formatString = {
										formatMatches: function( matches ) {
											if ( 1 === matches ) {
												return wc_enhanced_select_params.i18n_matches_1;
											}

											return wc_enhanced_select_params.i18n_matches_n.replace( '%qty%', matches );
										},
										formatNoMatches: function() {
											return wc_enhanced_select_params.i18n_no_matches;
										},
										formatAjaxError: function( jqXHR, textStatus, errorThrown ) {
											return wc_enhanced_select_params.i18n_ajax_error;
										},
										formatInputTooShort: function( input, min ) {
											var number = min - input.length;

											if ( 1 === number ) {
												return wc_enhanced_select_params.i18n_input_too_short_1
											}

											return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', number );
										},
										formatInputTooLong: function( input, max ) {
											var number = input.length - max;

											if ( 1 === number ) {
												return wc_enhanced_select_params.i18n_input_too_long_1
											}

											return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', number );
										},
										formatSelectionTooBig: function( limit ) {
											if ( 1 === limit ) {
												return wc_enhanced_select_params.i18n_selection_too_long_1;
											}

											return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', number );
										},
										formatLoadMore: function( pageNumber ) {
											return wc_enhanced_select_params.i18n_load_more;
										},
										formatSearching: function() {
											return wc_enhanced_select_params.i18n_searching;
										}
									};

									return formatString;
								}
							}

							// Ajax product search box
							jQuery( ':input.wc-product-with-status-search' ).filter( ':not(.enhanced)' ).each( function() {
								var select2_args = {
									allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
									placeholder: jQuery( this ).data( 'placeholder' ),
									minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
									escapeMarkup: function( m ) {
										return m;
									},
									ajax: {
								        url:         '<?php echo admin_url("admin-ajax.php"); ?>',
								        dataType:    'json',
								        quietMillis: 250,
								        data: function( term, page ) {
								            return {
												term:     term,
												action:   jQuery( this ).data( 'action' ) || 'json_search_products_with_status',
												status: 		'<?php echo serialize( array( "publish", "draft" ) ); ?>',
												security: 		'<?php echo wp_create_nonce( "ajax-search-products-with-status" ); ?>'
								            };
								        },
								        results: function( data, page ) {
								        	var terms = [];
									        if ( data ) {
												jQuery.each( data, function( id, text ) {
													terms.push( { id: id, text: text } );
												});
											}
								            return { results: terms };
								        },
								        cache: true
								    }
								};

								if ( jQuery( this ).data( 'multiple' ) === true ) {
									select2_args.multiple = true;
									select2_args.initSelection = function( element, callback ) {
										var data     = jQuery.parseJSON( element.attr( 'data-selected' ) );
										var selected = [];

										jQuery( element.val().split( "," ) ).each( function( i, val ) {
											selected.push( { id: val, text: data[ val ] } );
										});
										return callback( selected );
									};
									select2_args.formatSelection = function( data ) {
										return '<div class="selected-option" data-id="' + data.id + '">' + data.text + '</div>';
									};
								} else {
									select2_args.multiple = false;
									select2_args.initSelection = function( element, callback ) {
										var data = {id: element.val(), text: element.attr( 'data-selected' )};
										return callback( data );
									};
								}

								select2_args = jQuery.extend( select2_args, getEnhancedSelectFormatString() );

								jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
							});

						<?php } else { ?>

							jQuery("select.ajax_chosen_select_products_with_status").ajaxChosen({
							    method: 	'GET',
							    url: 		'<?php echo admin_url( "admin-ajax.php" ); ?>',
							    dataType: 	'json',
							    afterTypeDelay: 100,
							    data:		{
							    	action: 		'json_search_products_with_status',
							    	status: 		'<?php echo serialize( array( "publish", "draft" ) ); ?>',
									security: 		'<?php echo wp_create_nonce( "ajax-search-products-with-status" ); ?>'
							    }
							}, function (data) {

								var terms = {};

							    jQuery.each(data, function (i, val) {
							        terms[i] = val;
							    });

							    return terms;
							});

						<?php } ?>

			            var increment_progress = function( current, total ) {
	                        try{
	                            var progressbar = jQuery('#progressbar'),
	                              progressLabel = jQuery('.progress-label');
	                         
	                            progressbar.progressbar({
	                                value: false,
	                                change: function() {
	                                    progressLabel.text( '<?php echo __( "Step", SA_Bulk_Variations::$text_domain ); ?> ' + current + ' <?php echo __( "of", SA_Bulk_Variations::$text_domain ); ?> ' + total + ' <?php echo __( "completed", SA_Bulk_Variations::$text_domain ); ?>' );
	                                },
	                                complete: function() {
	                                    progressLabel.text('<?php echo __( "Completed!", SA_Bulk_Variations::$text_domain ); ?>');
	                                    setTimeout( function(){
	                                    	hideProgressbar();
	                                    	jQuery('div#form_options').find('input[type="text"], input[type="number"]').val('');
							      	  		document.title = "<?php _e( 'WooCommerce Bulk Variations Manager', SA_Bulk_Variations::$text_domain ); ?>";
	                                    }, 2000 );
	                                }
	                            });

	                            var new_value = current * 100 / total;

	                            if ( new_value.toFixed ) {
	                                new_value = Number(new_value.toFixed(2));
	                            } else {
	                                new_value = Math.round( new_value );
	                            }
	                            document.title = '<?php echo __( "Step", SA_Bulk_Variations::$text_domain ); ?> ' + current + ' <?php echo __( "of", SA_Bulk_Variations::$text_domain ); ?> ' + total + ' <?php echo __( "completed...", SA_Bulk_Variations::$text_domain ); ?>';
	                            progressbar.progressbar( 'value', new_value );
	                        } catch( error ) {
	                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
	                            jQuery('span.close').show();
	                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
	                            return false;
	                        }
	                    };

	                    var getAdditionalData = function( ajax_url, product_ids, post ) {
	                        var additional_data;
	                        try {
	                            jQuery.ajax({
	                                async: false,
	                                url: ajax_url,
	                                type: 'post',
	                                dataType: 'json',
	                                data: {
	                                    action: 'get_additional_data',
	                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
	                                    product_ids: product_ids,
	                                    post: post
	                                },
	                                success: function( response ) {
	                                    additional_data = response;
	                                }
	                            });
	                            return additional_data;
	                        } catch( error ) {
	                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
	                            jQuery('span.close').show();
	                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
	                            return false;
	                        }
	                    };

	                    var getPossibleVariations = function( ajax_url, variations ) {
	                        var possible_variations;
	                        try {
	                            jQuery.ajax({
	                                async: false,
	                                url: ajax_url,
	                                type: 'post',
	                                dataType: 'json',
	                                data: {
	                                    action: 'get_possible_variations',
	                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
	                                    variations: variations
	                                },
	                                success: function( response ) {
	                                    possible_variations = response;
	                                }
	                            });
	                            return possible_variations;
	                        } catch( error ) {
	                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
	                            jQuery('span.close').show();
	                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
	                            return false;
	                        }
	                    };

	                    var getProductCountFromCategories = function( ajax_url, form_data ) {
	                        try {
	                            var product_count;
	                            jQuery.ajax({
	                                url: ajax_url,
	                                type: 'post',
	                                dataType: 'json',
	                                async: false,
	                                data: {
	                                    action: 'get_product_ids_from_categories',
	                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
	                                    post: form_data
	                                },
	                                success: function( response ) {
	                                    product_count = response.length;
	                                }
	                            });
	                        } catch( error ) {
	                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
	                            jQuery('span.close').show();
	                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
	                            return false;
	                        }
	                        return product_count;
	                    };

	                    var getProductIdsFromCategories = function( ajax_url, form_data ) {
	                        try {
	                            var product_ids;
	                            jQuery.ajax({
	                                url: ajax_url,
	                                type: 'post',
	                                dataType: 'json',
	                                async: false,
	                                data: {
	                                    action: 'get_product_ids_from_categories',
	                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
	                                    post: form_data
	                                },
	                                success: function( response ) {
	                                    product_ids = response;
	                                }
	                            });
	                        } catch( error ) {
	                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
	                            jQuery('span.close').show();
	                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
	                            return false;
	                        }
	                        return product_ids;
	                    };

	                    var getVariationIds = function( ajax_url, form_data ) {
	                    	try {
	                    		var variation_ids;
	                    		jQuery.ajax({
	                                url: ajax_url,
	                                type: 'post',
	                                dataType: 'json',
	                                async: false,
	                                data: {
	                                    action: 'get_variation_ids',
	                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
	                                    post: form_data
	                                },
	                                success: function( response ) {
	                                    variation_ids = response;
	                                }
	                            });
	                    	} catch( error ) {
	                    		jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
	                            jQuery('span.close').show();
	                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
	                            return false;
	                    	}
	                    	return variation_ids;
	                    };

	                    var hideProgressbar = function() {
	                    	jQuery('#progressbar').hide();
	                        jQuery('.progress-label').hide();
	                        jQuery('#progressbar .status').hide();
	                        jQuery('#modal').hide();
	                        jQuery('span.close').hide();
	                    };

	                    jQuery('span.close').on('click', function(){
	                    	hideProgressbar();
	                    });

	                    jQuery('input#bvm_apply').on('click', function(){
	                        try {
	                            var form_data = jQuery('form#bulk_variations_manager_form').serialize();
	                            var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
	                            var selected_value = jQuery('input[name=selected_option]:checked').val();
	                            var multiplying_factor = 4;
	                            var product_count;
	                            var chosen_text = '<?php echo ( $this->is_wc_gte_21() ) ? "chosen" : "chzn" ?>';
	                            var progress = 0;
	                            
	                            if ( selected_value == 'product_names' ) {
	                                product_count = jQuery('table#product_names_table tbody tr').length - 1;
	                            } else if ( selected_value == 'categories' ) {
	                                product_count = getProductCountFromCategories( ajax_url, form_data );
	                            } else {
	                            	<?php if ( $this->is_wc_gte_23() ) { ?>
	                                	product_count = jQuery('div.wc-product-with-status-search ul.select2-choices li.select2-search-choice').length;
	                            	<?php } else { ?>
	                                	product_count = jQuery('div#product_ids_' + chosen_text + ' ul.' + chosen_text + '-choices li.search-choice span').length;
	                            	<?php } ?>
	                            }

	                            var selected_action = jQuery('input[name=bvm_action]:checked').val();
	                            var attribute_count = jQuery('ul.attribute_list li').length;
	                            var term_counts = [];
	                            var k = 0;
	                            var term_count;
	                            var possible_variation_count = 1;

	                            if ( selected_action == undefined && selected_value == undefined ) {
	                            	alert( "<?php echo __( 'Please select an action & base product.', SA_Bulk_Variations::$text_domain ); ?>" );
	                            	return false;
	                            }

	                            if ( selected_action == 'only_delete' ) {
		                            var answer = confirm( "<?php echo __( 'Are you sure you want to delete variations? This cannot be undone.', SA_Bulk_Variations::$text_domain ); ?>" );
	                            	if ( ! answer ) {
	                            		return false;
	                            	}
	                            }

	                            for ( k = 1; k <= attribute_count; k++ ) {
	                                term_count = jQuery('ul.attribute_list li:nth-child('+k+') ul.terms_list li table tbody tr td label input[id^="terms_"]:checked').length;   
	                                if ( term_count > 0 ) {
	                                    term_counts.push( term_count ); 
	                                    possible_variation_count *= term_count;
	                                }
	                            }
	                            
	                            if ( possible_variation_count > 1000 ) {
	                            	var answer = confirm( "<?php echo __( 'Your selection will create', SA_Bulk_Variations::$text_domain ); ?> " + possible_variation_count + " <?php echo __( 'variations per product. It may cause slower loading of products or sometimes unresponsive. Are you sure you want to continue?', SA_Bulk_Variations::$text_domain ); ?>" );
	                            	if ( ! answer ) {
	                            		return false;
	                            	}
	                            }

	                            jQuery('#progressbar').progressbar({
	                                value: 0
	                            }).show();
	                            jQuery('.progress-label').text('<?php _e( "Please wait...", SA_Bulk_Variations::$text_domain ) ?>').show();
	                            jQuery('#progressbar .status').show();
	                            jQuery('#modal').show();
	                            jQuery('span.close').hide();

	                            var l = 0;
	                            var variations_count = 1;
	                            var final_progress_value;

	                            for ( l = 0; l < term_counts.length; l++ ) {
	                                variations_count = Number( variations_count ) * Number( term_counts[l] );
	                            }

	                            if ( selected_action == 'only_create_update' || selected_action == 'only_attributes' ) {
	                            	
		                            if ( selected_action == 'only_attributes' ) {
		                            	final_progress_value = product_count + 5;
		                            } else {
		                            	final_progress_value = ( 4 * product_count ) + 5;
		                            }

		                            setTimeout( function(){
		                                jQuery.ajax({
		                                    async: false,
		                                    url: ajax_url,
		                                    type: 'post',
		                                    dataType: 'json',
		                                    data: {
		                                        action: 'sa_bulk_add_update_attributes',
		                                        security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
		                                        post: form_data
		                                    },
		                                    success: function( response ) {
		                                        try {
		                                            if ( response.error == 'true' ) {
		                                                jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
		                            					jQuery('span.close').show();
		                                                jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+response.data.msg);
		                                            } else {
		                                                progress++;
		                                                increment_progress( progress, final_progress_value );
		                                                var product_ids;
		                                                var next_action;
		                                                var additional_data;
		                                                var post;
		                                                var found_attributes;
		                                                var post_terms;
		                                                var slug_to_tt_id;
		                                                var available_variations;
		                                                var id_to_variations;
		                                                var term_product_type;
		                                                var possible_variations;
		                                                var _product;
		                                                var variations;
		                                                var old_post_meta;
		                                                var old_meta_id;
		                                                var parent_product_price;
		                                                var new_variation_data;

		                                                product_ids = response.data.product_ids;
		                                                next_action = response.data.next_action;
		                                                post = response.data.post;

		                                                additional_data = getAdditionalData( ajax_url, product_ids, post );
		                                                progress++;
		                                                increment_progress( progress, final_progress_value );

		                                                found_attributes =  additional_data.data.found_attributes;
		                                                post_terms =  additional_data.data.post_terms;
		                                                slug_to_tt_id =  additional_data.data.slug_to_tt_id;
		                                                available_variations =  additional_data.data.available_variations;
		                                                id_to_variations =  additional_data.data.id_to_variations;
		                                                term_product_type =  additional_data.data.term_product_type;
		                                                
		                                                var product_id;

		                                                for ( var i = 0; i < product_ids.length; i++ ) {
		                                                    product_id = product_ids[i];
		                                                    
		                                                    jQuery.ajax({
		                                                        async: false,
		                                                        url: ajax_url,
		                                                        type: 'post',
		                                                        dataType: 'json',
		                                                        data: {
		                                                            action: 'add_update_product_attributes',    // add_update_product_attributes
		                                                            security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
		                                                            post: post,
		                                                            product_id: product_id,
		                                                            found_attributes: found_attributes[product_id],
		                                                            post_terms: post_terms[product_id],
		                                                            slug_to_tt_id: slug_to_tt_id,
		                                                            available_variations: available_variations[product_id],
		                                                            id_to_variations: id_to_variations[product_id],
		                                                            term_product_type: term_product_type
		                                                        },
		                                                        success: function( response ) {
		                                                            try {

		                                                            	if ( selected_action == 'only_attributes' ) {
		                                                            		progress++;
		                                                                	increment_progress( progress, final_progress_value );
		                                                            	} else {
			                                                                progress++;
			                                                                increment_progress( progress, final_progress_value );
			                                                                _product = response.data._product;
			                                                                variations = response.data.variations;
			                                                                old_post_meta = response.data.old_post_meta;
			                                                                old_meta_id = response.data.old_meta_id;
			                                                                parent_product_price = response.data.parent_product_price;
			                                                                next_action = response.data.next_action;

			                                                                possible_variations = getPossibleVariations( ajax_url, variations );
			                                                                possible_variations = possible_variations.data.possible_variations;
			                                                                progress++;
			                                                                increment_progress( progress, final_progress_value );

			                                                                jQuery.ajax({
			                                                                    async: false,
			                                                                    url: ajax_url,
			                                                                    type: 'post',
			                                                                    dataType: 'json',
			                                                                    data: {
			                                                                        action: 'create_update_variation',    // create_update_variation
			                                                                        security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
			                                                                        post: post,
			                                                                        product_id: product_id,
			                                                                        found_attributes: found_attributes[product_id],
			                                                                        slug_to_tt_id: slug_to_tt_id,
			                                                                        available_variations: available_variations[product_id],
			                                                                        id_to_variations: id_to_variations[product_id],
			                                                                        term_product_type: term_product_type,
			                                                                        possible_variations: JSON.stringify( possible_variations ),
			                                                                        _product: _product,
			                                                                        variations: variations,
			                                                                        old_post_meta: old_post_meta,
			                                                                        old_meta_id: old_meta_id,
			                                                                        parent_product_price: parent_product_price
			                                                                    },
			                                                                    success: function( response ) {
			                                                                        try {

			                                                                            progress++;
			                                                                            increment_progress( progress, final_progress_value );
			                                                                            new_variation_data = response.data.new_variation_data;
			                                                                            next_action = response.data.next_action;
			                                                                            jQuery.ajax({
			                                                                                async: false,
			                                                                                url: ajax_url,
			                                                                                type: 'post',
			                                                                                dataType: 'json',
			                                                                                data: {
			                                                                                    action: 'collect_variation_post_meta',    // collect_variation_post_meta
			                                                                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
			                                                                                    post: post,
			                                                                                    new_variation_data: new_variation_data,
			                                                                                    parent_product_price: parent_product_price
			                                                                                },
			                                                                                success: function( response ) {
			                                                                                    try {
			                                                                                        if ( response.error == 'false' ) {
			                                                                                            progress++;
			                                                                                            increment_progress( progress, final_progress_value );
			                                                                                        }
			                                                                                    } catch( error ) {
			                                                                                        jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
			                            															jQuery('span.close').show();
			                                                                                        jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
			                                                                                        return false;
			                                                                                    }
			                                                                                }
			                                                                            });
			                                                                        } catch( error ) {
			                                                                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
			                            												jQuery('span.close').show();
			                                                                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
			                                                                            return false;
			                                                                        }
			                                                                    }
			                                                                });
																		}
		                                                            } catch( error ) {
		                                                                jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
		                            									jQuery('span.close').show();
		                                                                jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
		                                                                return false;
		                                                            }
		                                                        }
		                                                    });
		                                                }   // End for loop

		                                                jQuery.ajax({
		                                                    async: false,
		                                                    url: ajax_url,
		                                                    type: 'post',
		                                                    dataType: 'json',
		                                                    data: {
		                                                        action: 'save_variation_post_meta',
		                                                        security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>'
		                                                    },
		                                                    success: function( response ) {
		                                                        try {

		                                                            if ( response.error == 'false' ) {
		                                                                progress++;
		                                                                increment_progress( progress, final_progress_value );
		                                                            }

		                                                            if ( selected_action == 'only_attributes' ) {
		                                                            	progress++;
		                                                                increment_progress( progress, final_progress_value );
		                                                            } else {
			                                                            jQuery.ajax({
			                                                                async: false,
			                                                                url: ajax_url,
			                                                                type: 'post',
			                                                                dataType: 'json',
			                                                                data: {
			                                                                    action: 'sync_variable_product_price',
			                                                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
			                                                                    product_ids: product_ids
			                                                                },
			                                                                success: function( response ) {
			                                                                    try {
			                                                                        if ( response.error == 'false' ) {
			                                                                            progress++;
			                                                                            increment_progress( progress, final_progress_value );
			                                                                        }
			                                                                    } catch( error ) {
			                                                                        jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
			                            											jQuery('span.close').show();
			                                                                        jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
			                                                                        return false;
			                                                                    }
			                                                                }
			                                                            });
		                                                            }

		                                                            jQuery.ajax({
		                                                                async: false,
		                                                                url: ajax_url,
		                                                                type: 'post',
		                                                                dataType: 'json',
		                                                                data: {
		                                                                    action: 'finalize_bulk_create_update_variations',
		                                                                    security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
		                                                                    post: post
		                                                                },
		                                                                success: function( response ) {
		                                                                    try {
		                                                                        if ( response.error == 'false' ) {
		                                                                            progress++;
		                                                                            increment_progress( progress, final_progress_value );
		                                                                        }
		                                                                    } catch( error ) {
		                                                                        jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
		                            											jQuery('span.close').show();
		                                                                        jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
		                                                                        return false;
		                                                                    }
		                                                                }
		                                                            });
		                                                        } catch( error ) {
		                                                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
		                            								jQuery('span.close').show();
		                                                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
		                                                            return false;
		                                                        }
		                                                    }
		                                                });
		                                            }
		                                        } catch( error ) {
		                                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
		                            				jQuery('span.close').show();
		                                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
		                                            return false;
		                                        }
		                                    }
		                                });
		                            }, 10);

	                            } else if ( selected_action == 'only_delete' ) {
	                            	var all_variation_ids, variation_ids, i, j;
	                            	var chunk = 1000;
	                            	all_variation_ids = getVariationIds( ajax_url, form_data );
	                            	if ( all_variation_ids.length <= 0 ) {
	                            		increment_progress( 1, 1 );
	                            		return false;
	                            	}
	                            	final_progress_value = Math.ceil( all_variation_ids.length / chunk );
	                            	setTimeout( function() {
		                            	for ( i = 0, j = all_variation_ids.length; i < j; i += chunk ) {
		                            		variation_ids = all_variation_ids.slice( i, i + chunk );
				                            jQuery.ajax({
			                            		async: false,
			                                    url: ajax_url,
			                                    type: 'post',
			                                    dataType: 'json',
			                                    data: {
			                                        action: 'bvm_delete_variations',
			                                        security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>',
			                                        variation_ids: variation_ids
			                                    },
			                                    success: function( response ) {
			                                        try {
		                                                progress++;
		                                                increment_progress( progress, final_progress_value );
			                                        } catch( error ) {
			                                            jQuery('.progress-label').text('<?php echo __( "Failed!", SA_Bulk_Variations::$text_domain ); ?>');
														jQuery('span.close').show();
			                                            jQuery('#progressbar .status').text('<?php echo __( "Error:", SA_Bulk_Variations::$text_domain ); ?> '+error.toString());
			                                            return false;
			                                        }
			                                    }
			                            	});
		                            	}
	                            	}, 10);
	                            }

	                        } catch( error ) {
	                            console.log('<?php echo __( "Bulk Variations Manager error:", SA_Bulk_Variations::$text_domain ); ?> ', error.toString());
	                        }
	                    });
					});
				</script>
				<h3><?php _e( 'Step 1: I would like to', SA_Bulk_Variations::$text_domain ); ?></h3>
				<div id="bvm_action">
	            	<p class="form-fields"><label for="only_attributes"><input type="radio" id="only_attributes" name="bvm_action" value="only_attributes"> <?php _e( 'Set attributes in product/s', SA_Bulk_Variations::$text_domain ); ?></label></p>
	            	<p class="form-fields"><label for="only_create_update"><input type="radio" id="only_create_update" name="bvm_action" value="only_create_update"> <?php _e( 'Create / update variations in product/s', SA_Bulk_Variations::$text_domain ); ?></label></p>
	            	<p class="form-fields"><label for="only_delete"><input type="radio" id="only_delete" name="bvm_action" value="only_delete"> <?php _e( 'Delete variations from product/s', SA_Bulk_Variations::$text_domain ); ?></label></p>
				</div>
				<h3><?php _e( 'Step 2: Select Base Products', SA_Bulk_Variations::$text_domain ); ?></h3>
				<div id="form_options">
					<p>
						<input type="radio" id="product_names" name="selected_option" value="product_names" /> <label for="product_names"><?php _e( 'Create new base product/s', SA_Bulk_Variations::$text_domain ); ?></label>
					</p>
					<div id="product_names">
						<table id="product_names_table">
							<tbody>
								<tr>
									<td><strong><?php _e( 'Product\'s name', SA_Bulk_Variations::$text_domain ); ?></strong></td>
									<th><strong><?php echo __( 'Base Price', SA_Bulk_Variations::$text_domain ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></strong></th>
									<td></td>
								</tr>
								<tr>
									<td><input type="text" name="product_names[]" size="50" value="" placeholder="<?php _e( 'Enter a product\'s name', SA_Bulk_Variations::$text_domain ); ?>&hellip;" /></td>
									<td><input type="number" step="any" name="base_price[]" min="0" value="" placeholder="0.00" /></td>
									<td><input type="button" class="add_row" value="+" /></td>
								</tr>
							</tbody>
						</table>
					</div>
					<p>
						<input type="radio" id="categories" name="selected_option" value="categories" /> <label for="categories"><?php _e( 'Use all products from selected categories as base products', SA_Bulk_Variations::$text_domain ); ?></label>
					</p>
					<div id="categories" class="categorydiv">
						<?php
	                        $category_count = wp_count_terms( 'product_cat' );
	                        if ( $category_count > 0 ) {
	                    ?>
	                    <p><label id="check_all_category" class="bvm_link_style"><?php echo __( 'Select all', SA_Bulk_Variations::$text_domain ); ?></label> | <label id="uncheck_all_category" class="bvm_link_style"><?php echo __( 'Deselect all', SA_Bulk_Variations::$text_domain ); ?></label></p>
	                    <ul id="product_catchecklist" data-wp-lists="list:product_cat" class="categorychecklist form-no-clear">
						<?php
							wp_terms_checklist( 0, array( 'taxonomy' => 'product_cat' ) );
						?>
						</ul>
	                    <?php } else { ?>
	                        <ul id="product_catchecklist"><li><?php echo '<strong>'.__( 'No categories found. Please select other option', SA_Bulk_Variations::$text_domain ).'</strong>'; ?></li></ul>
	                    <?php } ?>
					</div>
					<p>
						<input type="radio" id="search" name="selected_option" value="search" /> <label for="search"><?php _e( 'Let me choose base products', SA_Bulk_Variations::$text_domain ); ?></label>
					</p>
					<div id="search">
						<div class="woocommerce_options_panel">
							<div class="options_group">
								<p class="form-field">
									<label for="product_ids"><?php _e( 'Products', SA_Bulk_Variations::$text_domain ) ?></label>
									<?php if ( $this->is_wc_gte_23() ) { ?>
										<input type="hidden" class="wc-product-with-status-search" data-multiple="true" style="width: 75%;" name="product_ids" data-placeholder="<?php _e( 'Search for a product&hellip;', SA_Bulk_Variations::$text_domain ); ?>" data-action="json_search_products_with_status" data-selected="" value="" />
									<?php } else { ?>
										<select id="product_ids" name="product_ids[]" class="ajax_chosen_select_products_with_status" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', SA_Bulk_Variations::$text_domain ); ?>"></select> 
									<?php } ?>
									<img class="help_tip" data-tip='<?php _e( 'Base products for which new variations will be added or existing will be updated', SA_Bulk_Variations::$text_domain ) ?>' src="<?php echo $this->global_wc()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
								</p>
							</div>
						</div>
					</div>
				</div>
				<div id="search_result"></div>
	            <div id="modal"></div>
	            <div id="progressbar">
	                <div class="progress-label"><?php _e( 'Starting', SA_Bulk_Variations::$text_domain ); ?>&hellip;</div>
	                <div class="status"></div>
	                <span class="close"></span>
	            </div>
				<div id="price"><label for="price"><?php echo __( 'Base Price', SA_Bulk_Variations::$text_domain ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label> <input type="number" step="any" min="0" id="price" name="price" placeholder="<?php _e( '0.00', SA_Bulk_Variations::$text_domain ); ?>" value="" /></div>
				<div id="bvm_step_3">
					<h3><?php _e( 'Step 3: Setup Variations & Prices', SA_Bulk_Variations::$text_domain ); ?></h3>
					<p class="description"><?php _e( 'Select attributes for variations & optionally enter differential price. Differential prices will be added to base price and the final price will be set as price of variation.', SA_Bulk_Variations::$text_domain ); ?></p>
					<br>
		            <?php if ( is_array( $attributes_to_terms ) && count( $attributes_to_terms ) > 0 ) { ?>
						<div id="attribute_header">
							<label><strong><?php _e( 'Attributes', SA_Bulk_Variations::$text_domain ); ?></strong></label>
							<label class="right"><strong><?php echo __( 'Differential price', SA_Bulk_Variations::$text_domain ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></strong></label>
						</div>
						<div id="attributes_hierarchy" class="categorydiv">
							<ul class="attribute_list categorychecklist">
								<?php foreach ( $attributes_to_terms as $attribute_slug => $attribute_terms ) { ?>
										<li>
											<input type="checkbox" id="attributes_<?php echo $attribute_slug; ?>"  class="attribute" name="attributes[]" value="<?php echo $attribute_slug; ?>" />
											<label for="attributes_<?php echo $attribute_slug; ?>"><?php echo ( isset( $attributes[$attribute_slug] ) && !empty( $attributes[$attribute_slug] ) ) ? $attributes[$attribute_slug] : substr( $attribute_slug, 3 ); ?></label>
										<?php if ( is_array( $attribute_terms ) && count( $attribute_terms ) > 0 ) { ?>
											<ul class="terms_list children">
											<?php foreach ( $attribute_terms as $term_taxonomy_id => $terms ) { ?>
												<li><table class="attributes_to_price"><tr>
													<td><label for="terms_<?php echo $term_taxonomy_id; ?>">
		                                                <input type="checkbox" id="terms_<?php echo $term_taxonomy_id; ?>"  class="term" name="<?php echo $attribute_slug . '[' . $term_taxonomy_id . ']'; ?>" value="<?php echo $terms['term_slug']; ?>" />
		                                                <span><?php echo $terms['term_name']; ?></span>
		                                            </label></td>
													<td><input type="number" step="any" id="price_<?php echo $term_taxonomy_id; ?>" class="price" name="<?php echo $attribute_slug . '-price[' . $term_taxonomy_id . ']'; ?>" placeholder="<?php _e( '0.00', SA_Bulk_Variations::$text_domain ); ?>" value="" readonly="readonly" /></td></tr></table>
												</li>
											<?php } ?>
											</ul>
										<?php } ?>
										</li>
									<?php } ?>
							</ul>
						</div>
		            <?php } else { ?>
		                <div id="notice" class="error">
		                    <p><?php echo '<strong>'.__( 'Important', SA_Bulk_Variations::$text_domain ).':</strong> '.__( 'Please add some attributes before creating product variations', SA_Bulk_Variations::$text_domain ) . ' <a href="'.admin_url( 'edit.php?post_type=product&page=' . ( ( $this->is_wc_gte_21() ) ? 'product_attributes' : 'woocommerce_attributes' ) ).'" target="_blank">'.__( 'Add Attributes', SA_Bulk_Variations::$text_domain ).'</a>'; ?></p>
		                </div>
		            <?php } ?>
	            </div>
	            <div id="bvm_step_4">
		            <h3><?php _e( 'Step 4: Set Additional Options (Optional)', SA_Bulk_Variations::$text_domain ); ?></h3>
		            <div id="bvm_additional_option">
		            	<p class="form-fields"><label for="show_on_product_page"><input type="checkbox" id="show_on_product_page" name="show_on_product_page" value="yes"> <?php _e( 'Visible on the product page', SA_Bulk_Variations::$text_domain ); ?></label></p>
		            	<p class="form-fields"><label for="use_for_variations"><input type="checkbox" id="use_for_variations" name="use_for_variations" value="yes"> <?php _e( 'Used for variations', SA_Bulk_Variations::$text_domain ); ?></label></p>
		            </div>
	            </div>
				<input id="bvm_apply" name="bvm_apply" type="button" class="button-primary" value="<?php _e( 'Apply', SA_Bulk_Variations::$text_domain ); ?>" />
				</form>
				</div>
				<?php
			}
		}

		function query_args_action() {
			$request_uri = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'woocommerce_variations' && isset( $_GET['bvm_undo'] ) && $_GET['bvm_undo'] == '1' ) {
				$bvm_last_operation = get_option( 'bvm_last_operation' );
				$this->bvm_undo_last_operation( $bvm_last_operation );
				$request_uri = remove_query_arg( 'bvm_undo', $request_uri );
				wp_safe_redirect( $request_uri );
				exit;
			}
		}

		function bvm_new_admin_styles_and_scripts() {
			global $pagenow;
			if ( empty( $pagenow ) || empty( $_GET['post_type'] ) || empty( $_GET['page'] ) ) {
				return;
			}
			if ( $pagenow == 'edit.php' && $_GET['post_type'] == 'product' && $_GET['page'] == 'woocommerce_variations' ) {
				$is_permission_available = get_option( 'bvm_is_permission_available', 'yes' );
				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
				}
				?>
				<script type="text/javascript">
					var bvmCheckPermission = function() {
						var html_string = '<p>';
							html_string += '<?php echo __( "Checking permission", SA_Bulk_Variations::$text_domain ) ?> ';
							html_string += '<img src="<?php echo includes_url( "images/spinner.gif" ) ?>" style="vertical-align: middle;" />';
							html_string += '</p>';
						jQuery('div#bvm_permission_status').html('');
						jQuery('div#bvm_permission_status').html( html_string );
						jQuery.ajax({
							url: '<?php echo admin_url("admin-ajax.php"); ?>',
							type: 'post',
							dataType: 'html',
							data: {
								action: 'bvm_check_permission',
								security: '<?php echo wp_create_nonce( "bulk-variations-manager-new" ) ?>'
							},
							success: function( response ) {
								if ( response != undefined && response != '' ) {
									if ( response == 'permitted' ) {
										location.reload( true );
									} else {
										jQuery('div#bvm_permission_status').html('');
										jQuery('div#bvm_permission_status').html( response );
									}
								}
							}
						});
					};
					jQuery(function(){
						<?php if ( empty( $is_permission_available ) ) { ?>
							jQuery(window).on('load', function(){
								bvmCheckPermission();
							});
						<?php } ?>
					});
				</script>
				<?php
			}
		}

		function bvm_check_permission() {

			check_ajax_referer( 'bulk-variations-manager-new', 'security' );

			global $wpdb;

			$is_granted = false;
			$messages = array();

			$pre_text = __( 'For creating/updating/deleting variations in fastest way, Bulk Variations Manager uses internal import & export of file. Therefore it needs some additional settings. These settings are related to file write, file import & export permissions. Current status of these settings are as follows:', SA_Bulk_Variations::$text_domain );

			$upload_dir = wp_upload_dir();
			if ( !wp_is_writable( $upload_dir['basedir'] ) ) {
				$messages[] = sprintf(__( 'Either path %s doesn\'t exists or it\'s not writable', SA_Bulk_Variations::$text_domain ), '<code>' . $upload_dir['basedir'] . '</code>' );
			}

			$is_local_infile = false;
			$infile_variable = $wpdb->get_row( "SHOW VARIABLES LIKE 'local_infile';", 'ARRAY_A' );
			if ( strtolower( $infile_variable['Value'] ) === 'on' ) {
				$is_local_infile = true;
			}
			if ( $is_local_infile ) {
				$all_grants = $wpdb->get_results( "SHOW GRANTS FOR '".DB_USER."'@'".DB_HOST."'", 'ARRAY_A' );
				if ( ! empty( $all_grants ) ) {
					foreach ( $all_grants as $grants ) {
						foreach ( $grants as $key => $value ) {
							if ( stripos( $value, 'all' ) !== false || stripos( $value, 'file' ) !== false ) {
								$is_granted = true;
								break 2;
							}
						}
					}
				}

				if ( ! $is_granted ) {
					$messages[] = sprintf(__( 'WordPress database user %s doesn\'t have permission for %s operation. %s', SA_Bulk_Variations::$text_domain ), '<code>' . DB_USER."@".DB_HOST . '</code>', '<code>FILE</code>', '<a href="http://www.storeapps.org/support/documentation/bulk-variations-manager/" target="_blank">' . __( 'What is this?', SA_Bulk_Variations::$text_domain ) . '</a>' );
				}
			}

			if ( ! empty( $messages ) ) {
				update_option( 'bvm_is_permission_available', 'no' );
				update_option( 'bvm_is_take_backup', 'no' );
				$return = '<h3><i>' . $pre_text . '</i></h3>';
				foreach ( $messages as $message ) {
					$return .= '<p>' . $message . '</p>';
				}
				$return .= '<p><label for="bvm_only_earlier_version"><input type="checkbox" id="bvm_only_earlier_version" name="bvm_only_earlier_version" value="yes" /> ' . __( 'I\'ll use slower method always', SA_Bulk_Variations::$text_domain ) . '</label></p>';
				$return .= '<p>';
				$return .= '<button id="bvm_switch_to_earlier_version" class="button-primary">' . __( 'Switch to slower method', SA_Bulk_Variations::$text_domain ) . '</button>';
				$return .= ' <button id="bvm_recheck_permission_status" class="button" onClick="bvmCheckPermission();">' . __( 'Recheck settings', SA_Bulk_Variations::$text_domain ) . '</button>';
				$return .= '</p>';
			} else {
				update_option( 'bvm_is_permission_available', 'yes' );
				$return = 'permitted';
			}

			echo $return;
			die();
		}

        function bulk_add_update_attributes() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );
              
            global $wpdb;

            if ( !isset( $_POST['post'] ) ) {
                die( json_encode( array( 'error' => 'true', 'data' => array( 'msg' => __( 'Form data not found' ) ) ) ) );
            }
            
            parse_str($_POST['post'], $post);

            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                $bvm_debug_data = get_option( 'bvm_debug_data' );
                if ( $bvm_debug_data === false || !is_array( $bvm_debug_data ) ) {
                    $bvm_debug_data = array();
                }
                if ( count( $bvm_debug_data ) >= 3 ) {
                    $bvm_debug_data = array_shift( $bvm_debug_data );
                }
                $bvm_debug_data = $post;
                update_option( 'bvm_debug_data', $bvm_debug_data );
            }

            $return = false;
            $reason = array();
            $product_attributes = array();
            $position = 0;
            foreach ( $post as $attribute_key => $attribute_value ) {
                if ( substr( $attribute_key, 0, 3 ) !== 'pa_' || strpos( $attribute_key, '-price' ) !== false ) continue;
                $product_attributes[$attribute_key] = array();
                $product_attributes[$attribute_key]['name'] = $attribute_key;
                $product_attributes[$attribute_key]['value'] = '';
                $product_attributes[$attribute_key]['position'] = "$position";
                $product_attributes[$attribute_key]['is_visible'] = ( ! empty( $post['show_on_product_page'] ) && $post['show_on_product_page'] == 'yes' ) ? 1 : 0;
                $product_attributes[$attribute_key]['is_variation'] = ( ( ! empty( $post['use_for_variations'] ) && $post['use_for_variations'] == 'yes' ) || ( ! empty( $post['bvm_action'] ) && $post['bvm_action'] == 'only_create_update' ) ) ? 1 : 0;
                $product_attributes[$attribute_key]['is_taxonomy'] = 1;
                $position++;
            }

            if ( count( $product_attributes ) <= 0 ) {
                $return = true;
                $reason[] = __( 'Please select some attributes', SA_Bulk_Variations::$text_domain );
            }

            $bvm_operation_id = get_option( 'bvm_operation_id' );
            if ( empty( $bvm_operation_id ) ) {
                $bvm_operation_id = 0;
            }
            $this->bvm_clear_last_operation_data( $bvm_operation_id );
            $bvm_operation_id++;
            update_option( 'bvm_operation_id', $bvm_operation_id );

            if ( !$this->open_file( 'posts', true, true ) ) {
                $return = true;
                $reason[] = sprintf(__( 'Could not create file %s', SA_Bulk_Variations::$text_domain ), 'posts');
            }
            if ( !$this->open_file( 'postmeta', true, true ) ) {
                $return = true;
                $reason[] = sprintf(__( 'Could not create file %s', SA_Bulk_Variations::$text_domain ), 'postmeta');
            }
            if ( !$this->open_file( 'term_relationships', true, true ) ) {
                $return = true;
                $reason[] = sprintf(__( 'Could not create file %s', SA_Bulk_Variations::$text_domain ), 'term_relationships');
            }
            
            switch( $post['selected_option'] ) {
                case 'product_names':
                    if ( isset($post['product_names'] ) && empty( $post['product_names'] ) ) {
                        $return = true;
                        $reason[] = __( 'Please add some product names', SA_Bulk_Variations::$text_domain );
                    } else {
                        $product_names = $post['product_names'];
                        $product_ids = array();
                        $post_names = array();
                        $file_posts = $this->open_file( 'posts' );

                        foreach ( $product_names as $index => $product_name ) {
                            $post_date = current_time('mysql');
                            $post_date_gmt = get_gmt_from_date($post_date);
                            $post_title = trim( $product_name );
                            $insert_data = array(
                                NULL,
                                get_current_user_id(),
                                $post_date,
                                $post_date_gmt,
                                '',
                                esc_sql( $post_title ),
                                '',
                                'publish',
                                get_option('default_comment_status'),
                                get_option('default_ping_status'),
                                '',
                                sanitize_title( $post_title ),
                                '',
                                '',
                                $post_date,
                                $post_date_gmt,
                                $bvm_operation_id,
                                0,
                                '',
                                0,
                                'product',
                                '',
                                0
                            );

                            @fputcsv( $file_posts, $insert_data );

                            // $post_titles[] = $post_title;

                        }
                        $this->close_file( $file_posts );
                        $this->load_data_infile( 'posts' );

                        $results = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_content_filtered LIKE '" . $bvm_operation_id . "'", 'ARRAY_A' );
                        $product_id_to_title = array();
                        
                        foreach ( $results as $result ) {
                            $product_id_to_title[$result['ID']] = $result['post_title'];
                        }
                        $product_ids = array_keys( $product_id_to_title );
                        $wpdb->query( "UPDATE {$wpdb->prefix}posts SET guid = CONCAT( '" . home_url() . "', '?', post_type, '=', post_name ) WHERE post_content_filtered LIKE '" . $bvm_operation_id . "'" );

                        $file_post_meta = $this->open_file( 'postmeta' );
                        
                        foreach ( $product_names as $index => $product_name ) {
                            $product_id = array_search( $product_name, $product_id_to_title );
                            if ( $product_id === false ) continue;
                            $price = ( isset( $post['base_price'][$index] ) && $post['base_price'][$index] !== '' ) ? $post['base_price'][$index] : '';
                            @fputcsv( $file_post_meta, array( NULL, $product_id, '_product_attributes', maybe_serialize( $product_attributes ) ) );
                            @fputcsv( $file_post_meta, array( NULL, $product_id, '_visibility', 'visible' ) );
                            @fputcsv( $file_post_meta, array( NULL, $product_id, '_regular_price', $price ) );
                            @fputcsv( $file_post_meta, array( NULL, $product_id, '_price', $price ) );
                            @fputcsv( $file_post_meta, array( NULL, $product_id, 'bvm_operation_id', $bvm_operation_id ) );
                        }
                        $this->close_file( $file_post_meta );
                        $this->load_data_infile( 'postmeta' );

                    }
                    break;

                case 'categories':
                    if ( isset( $post['tax_input']['product_cat'] ) && count( $post['tax_input']['product_cat'] ) > 0 ) {
                        $wpdb->query("UPDATE {$wpdb->prefix}posts SET post_content_filtered = '" . $bvm_operation_id . "' WHERE ID IN ( SELECT object_id FROM {$wpdb->prefix}term_relationships AS tr LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON ( tt.term_taxonomy_id = tr.term_taxonomy_id ) WHERE term_id IN ( " . implode( ',', $post['tax_input']['product_cat'] ) . " ) )");
                        $product_ids = $wpdb->get_col( "SELECT object_id FROM {$wpdb->prefix}term_relationships AS tr LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON ( tt.term_taxonomy_id = tr.term_taxonomy_id ) WHERE term_id IN ( " . implode( ',', $post['tax_input']['product_cat'] ) . " )" );
                    }
                    if ( count( $product_ids ) <= 0 ) {
                        $return = true;
                        $reason[] = __( 'No product found in the category', SA_Bulk_Variations::$text_domain );
                        break;
                    }
                    $this->update_product_attributes( $product_ids, $product_attributes );
                    break;

                case 'search':
                    $product_ids = ( $this->is_wc_gte_23() ) ? explode( ',', $post['product_ids'] ) : $post['product_ids'];
                    $wpdb->query("UPDATE {$wpdb->prefix}posts SET post_content_filtered = '" . $bvm_operation_id . "' WHERE ID IN ( " . implode( ',', $product_ids ) . " )");
                    if ( count( $product_ids ) <= 0 ) {
                        $return = true;
                        $reason[] = __( 'No product selected', SA_Bulk_Variations::$text_domain );
                        break;
                    }
                    $this->update_product_attributes( $product_ids, $product_attributes );
                    break;
            }

            update_option( 'bvm_last_operation', $post['selected_option'] );

            if ( $return ) {
                $return_data = array( 
                                    'error' => 'true',
                                    'data' => array( 'msg' => $reason )
                                );
            } else {
                $return_data = array( 
                                    'error' => 'false', 
                                    'data' => array( 
                                                    'next_action' => 'add_update_product_attributes', 
                                                    'post' => $post, 
                                                    'product_ids' => $product_ids
                                                ) 
                                );
            }
            echo json_encode( $return_data );
            die();

        }

        function get_product_ids_from_categories( $return = false ) {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

            global $wpdb;

            parse_str($_POST['post'], $post);
            
            if ( isset( $post['tax_input']['product_cat'] ) && count( $post['tax_input']['product_cat'] ) > 0 ) {
                $product_ids = $wpdb->get_col( "SELECT object_id FROM {$wpdb->prefix}term_relationships AS tr LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON ( tt.term_taxonomy_id = tr.term_taxonomy_id ) WHERE term_id IN ( " . implode( ',', $post['tax_input']['product_cat'] ) . " )" );
            }

            if ( $return ) {
            	return $product_ids;
            }

            echo json_encode( $product_ids );
            die();
        }

        function get_variation_ids() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

        	global $wpdb;
        	
        	parse_str($_POST['post'], $post);

        	$variation_ids = array();
        	if ( ! empty( $post['selected_option'] ) ) {
        		switch ( $post['selected_option'] ) {
        			case 'categories':
        				$product_ids = $this->get_product_ids_from_categories( $return = true );
        				break;
        			case 'search':
        				$product_ids = ( $this->is_wc_gte_23() ) ? explode( ',', $post['product_ids'] ) : $post['product_ids'];
        				break;
        			default:
        				$product_ids = array();
        				break;
        		}
        	}

            if ( ! empty( $product_ids ) ) {
                $variation_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'product_variation' AND post_status = 'publish' AND post_parent IN ( " . implode( ',', $product_ids ) . " )" );
            }

            echo json_encode( $variation_ids );
            die();
        }

        function bvm_delete_variations() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

        	global $wpdb;

        	if ( ! empty( $_POST['variation_ids'] ) ) {
	        	$imploded_variation_ids = implode( ',', $_POST['variation_ids'] );
        		$is_deleted = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID IN (" . $imploded_variation_ids . ")" );
        		if ( $is_deleted !== false && $this->is_wc_gte_24() ) {
	        		$product_ids = $wpdb->get_col( "SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND ID IN (" . $imploded_variation_ids . ")" );
	        		if ( ! empty( $product_ids ) ) {
	        			$this->delete_transient( $product_ids );
	        		}
        		}
        		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN (" . $imploded_variation_ids . ")" );
        	}

        }

        function get_additional_data() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

            global $wpdb;

            $product_ids = $_POST['product_ids'];
            $post = $_POST['post'];
            
            $results = $wpdb->get_results( "SELECT post_id, meta_value AS product_attribute FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_product_attributes' AND post_id IN ( " . implode( ',', $product_ids ) .  " )", 'ARRAY_A' );
            $found_attributes = array();
            
            foreach ( $results as $found_attribute ) {
                $found_attributes[$found_attribute['post_id']] = maybe_unserialize( $found_attribute['product_attribute'] );
            }
            $attribute_term_query = "SELECT tr.object_id AS product_id,
                                            tt.taxonomy AS taxonomy,
                                            GROUP_CONCAT( t.slug SEPARATOR '##' ) AS slugs
                                        FROM {$wpdb->prefix}term_taxonomy AS tt
                                            LEFT JOIN {$wpdb->prefix}terms AS t ON ( tt.term_id = t.term_id )
                                            LEFT JOIN {$wpdb->prefix}term_relationships AS tr ON ( tr.term_taxonomy_id = tt.term_taxonomy_id )
                                        WHERE tt.taxonomy LIKE 'pa_%'
                                        GROUP BY tr.object_id, tt.taxonomy";
            $attribute_term_results = $wpdb->get_results( $attribute_term_query, 'ARRAY_A' );
            $post_terms = array();
            
            foreach ( $attribute_term_results as $attribute_term_result ) {
                if ( empty( $attribute_term_result['product_id'] ) ) continue;
                if ( !isset( $post_terms[$attribute_term_result['product_id']] ) ) {
                    $post_terms[$attribute_term_result['product_id']] = array();
                }
                $post_terms[$attribute_term_result['product_id']][$attribute_term_result['taxonomy']] = explode( '##', $attribute_term_result['slugs'] );
            }

            $slug_to_tt_id_query = "SELECT tt.term_taxonomy_id AS term_taxonomy_id,
                                            tt.taxonomy AS taxonomy,
                                            t.slug AS slug
                                        FROM {$wpdb->prefix}term_taxonomy AS tt
                                            LEFT JOIN {$wpdb->prefix}terms AS t ON ( tt.term_id = t.term_id )
                                        WHERE tt.taxonomy LIKE 'pa_%'";
            $slug_to_tt_id_results = $wpdb->get_results( $slug_to_tt_id_query, 'ARRAY_A' );
            $slug_to_tt_id = array();
            
            foreach ( $slug_to_tt_id_results as $slug_to_tt_id_result ) {
                if ( !isset( $slug_to_tt_id[$slug_to_tt_id_result['taxonomy']] ) ) {
                    $slug_to_tt_id[$slug_to_tt_id_result['taxonomy']] = array();
                }
                $slug_to_tt_id[$slug_to_tt_id_result['taxonomy']][$slug_to_tt_id_result['slug']] = $slug_to_tt_id_result['term_taxonomy_id'];
            }

            $existing_product_variations_query = "SELECT p.post_parent AS product_id,
                                            pm.post_id AS variation_id,
                                            GROUP_CONCAT( pm.meta_key ORDER BY pm.meta_id SEPARATOR '##' ) AS meta_key,
                                            GROUP_CONCAT( pm.meta_value ORDER BY pm.meta_id SEPARATOR '##' ) AS meta_value
                                        FROM {$wpdb->prefix}posts AS p
                                            LEFT JOIN {$wpdb->prefix}postmeta AS pm ON ( p.ID = pm.post_id AND pm.meta_key LIKE 'attribute_%' )
                                        WHERE p.post_type LIKE 'product_variation'
                                            AND p.post_parent IN ( " . implode( ',', $product_ids ) . " )
                                        GROUP BY pm.post_id";
            $existing_product_variations_results = $wpdb->get_results( $existing_product_variations_query, 'ARRAY_A' );
            $available_variations = array();
            $id_to_variations = array();
            
            foreach ( $existing_product_variations_results as $existing_product_variations_result ) {
                $meta_keys = explode( '##', $existing_product_variations_result['meta_key'] );
                $meta_values = explode( '##', $existing_product_variations_result['meta_value'] );
                if ( count( $meta_keys ) != count( $meta_values ) ) {
                    continue;
                }
                $keys_values = array_combine( $meta_keys, $meta_values );
                if ( !isset( $available_variations[$existing_product_variations_result['product_id']] ) ) {
                    $available_variations[$existing_product_variations_result['product_id']] = array();
                }
                if ( !isset( $id_to_variations[$existing_product_variations_result['product_id']] ) ) {
                    $id_to_variations[$existing_product_variations_result['product_id']] = array();
                }
                $available_variations[$existing_product_variations_result['product_id']][] = $keys_values;
                $id_to_variations[$existing_product_variations_result['product_id']][$existing_product_variations_result['variation_id']] = $keys_values;
            }

            // if ( ! empty( $post['bvm_action'] ) && $post['bvm_action'] == 'only_attributes' ) {
            // 	$product_type = 'simple';
            // } else {
            // 	$product_type = 'variable';
            // }

            // if ( ! empty( $post['selected_option'] ) && $post['selected_option'] == 'product_names' && ! empty( $post['bvm_action'] ) && $post['bvm_action'] == 'only_attributes' ) {
            // 	$product_type = 'simple';
            // } elseif ( $post['bvm_action'] == 'only_create_update' ) {
            // 	$product_type = 'variable';
            // } else {
            // 	$product_type = false;
            // }

            $product_type = $this->get_product_type( $post );

            if ( ! empty( $product_type ) ) {
            	$term_product_type = get_term_by( 'slug', $product_type, 'product_type', 'ARRAY_A' );
            } else {
            	$term_product_type = array();
            }

            $file_term_relationships = $this->open_file( 'term_relationships' );

            $return_data = array( 
                                'error' => 'false', 
                                'data' => array( 
                                                'product_ids' => $product_ids,
                                                'found_attributes' => $found_attributes,
                                                'post_terms' => $post_terms,
                                                'slug_to_tt_id' => $slug_to_tt_id,
                                                'available_variations' => $available_variations,
                                                'id_to_variations' => $id_to_variations,
                                                'term_product_type' => $term_product_type
                                            ) 
                            );
            echo json_encode( $return_data );
            die();

        }

        function get_product_type( $post = array() ) {
        	if ( empty( $post ) ) {
        		return false;
        	}
        	if ( ! empty( $post['selected_option'] ) && $post['selected_option'] == 'product_names' ) {
            	if ( ! empty( $post['bvm_action'] ) && $post['bvm_action'] == 'only_attributes' ) {
            		$product_type = 'simple';
            	} elseif ( $post['bvm_action'] == 'only_create_update' ) {
            		$product_type = 'variable';
            	} else {
            		$product_type = false;
            	}
            } else {
            	if ( $post['bvm_action'] == 'only_create_update' ) {
            		$product_type = 'variable';
            	} else {
            		$product_type = false;
            	}
            }
            return $product_type;
        }

        function add_update_product_attributes() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

            global $wpdb;

            $post = $_POST['post'];
            $product_id = $_POST['product_id'];
            $found_attributes = ( !empty( $_POST['found_attributes'] ) ) ? $_POST['found_attributes'] : '';
            $post_terms = ( !empty( $_POST['post_terms'] ) ) ? $_POST['post_terms'] : '';
            $slug_to_tt_id = $_POST['slug_to_tt_id'];
            $available_variations = ( !empty( $_POST['available_variations'] ) ) ? $_POST['available_variations'] : '';
            $id_to_variations = ( !empty( $_POST['id_to_variations'] ) ) ? $_POST['id_to_variations'] : '';
            $term_product_type = $_POST['term_product_type'];

            $file_term_relationships = $this->open_file( 'term_relationships' );

            if ( ! empty( $term_product_type ) ) {
            	@fputcsv( $file_term_relationships, array( $product_id, $term_product_type['term_taxonomy_id'], NULL ) );
            }

            $product_type = $this->get_product_type( $post );

            if ( ! empty( $product_type ) ) {
            	$_product = $this->get_product( $product_id, array( 'product_type' => $product_type ) );
            } else {
            	$_product = $this->get_product( $product_id );
            }

            $variations = array();
            $update_attributes = false;
            
            if ( !empty( $found_attributes ) ) {
                foreach ( $found_attributes as $attribute ) {
                    $attribute_field_name = 'attribute_' . sanitize_title( $attribute['name'] );
                    $options = array();
                    if ( !empty( $post_terms[$attribute['name']] ) ) {
                        $options = $post_terms[$attribute['name']];
                    }
                    $new_options = array();
                    if ( isset( $post[$attribute['name']] ) && is_array( $post[$attribute['name']] ) && count( $post[$attribute['name']] ) > 0 ) {
                        $new_options = array_diff( array_values( $post[$attribute['name']] ), $options );
                    }
                    if ( empty( $new_options ) && empty( $post[$attribute['name']] ) ) {
                        continue;
                    }
                    if ( empty( $new_options ) ) {
                        $variations[ $attribute_field_name ] = array_values( $post[$attribute['name']] );
                    } else {
                        $variations[ $attribute_field_name ] = array_values( $new_options );
                        $update_attributes = true;
                    }
                }
            }

            if ( $update_attributes ) {
                
                foreach ( $variations as $attribute_name => $terms ) {
                    $taxonomy = substr( $attribute_name, 10 );
                    if ( !is_array( $terms ) && !empty( $terms ) ) {
                        $terms = array( $terms );
                    }
                    $existing_terms = ( !empty( $post_terms[$taxonomy] ) ) ? $post_terms[$taxonomy] : array();
                    $new_attribute_terms = array_unique( array_merge( $existing_terms, array_values( $terms ) ) );
                    
                    foreach ( $new_attribute_terms as $new_attribute_term ) {
                        @fputcsv( $file_term_relationships, array( $product_id, $slug_to_tt_id[$taxonomy][$new_attribute_term], NULL ) );
                    }
                }
            }

            if ( isset( $post['selected_option'] ) && $post['selected_option'] == 'product_names' ) {
                $parent_product_price = $_product->get_price();
            } else {
                $parent_product_price = ( !empty( $post['price'] ) ) ? $post['price'] : '';
            }

            $old_post_meta = array();
            $old_meta_id = array();
            
            if ( !empty( $id_to_variations ) ) {
                $old_variation_ids = array_keys( $id_to_variations );
                $old_post_meta_query = "SELECT meta_id, post_id, meta_key, meta_value
                                                    FROM {$wpdb->prefix}postmeta
                                                    WHERE meta_key IN ( '_regular_price', '_sale_price', '_price', '_sale_price_dates_from', '_sale_price_dates_to' )
                                                        AND post_id IN ( " . implode( ',', $old_variation_ids ) . " )";
                
                $old_post_meta_results = $wpdb->get_results( $old_post_meta_query, 'ARRAY_A' );
                foreach ( $old_post_meta_results as $old_post_meta_result ) {
                    if ( !isset( $old_post_meta[$old_post_meta_result['post_id']] ) || !is_array( $old_post_meta[$old_post_meta_result['post_id']] ) ) {
                        $old_post_meta[$old_post_meta_result['post_id']] = array();
                    }
                    $old_post_meta[$old_post_meta_result['post_id']][$old_post_meta_result['meta_key']] = $old_post_meta_result['meta_value'];
                    $old_meta_id[$old_post_meta_result['post_id'] . '_' . $old_post_meta_result['meta_key']] = $old_post_meta_result['meta_id'];
                }
            }
            $this->close_file( $file_term_relationships );

            $return_data = array( 
                                'error' => 'false', 
                                'data' => array( 
                                                'next_action' => 'create_update_variation', 
                                                '_product' => $_product, 
                                                'variations' => $variations, 
                                                'old_post_meta' => $old_post_meta, 
                                                'old_meta_id' => $old_meta_id, 
                                                'parent_product_price' => $parent_product_price
                                            ) 
                            );
            echo json_encode( $return_data );
            die();

        }

        function get_possible_variations() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

            $variations = $_POST['variations'];
            $possible_variations = SA_Bulk_Variations::array_cartesian( $variations );
            $return_data = array( 
                                'error' => 'false', 
                                'data' => array( 
                                                'possible_variations' => $possible_variations
                                            ) 
                            );
            echo json_encode( $return_data );
            die();
        }

        function create_update_variation() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

            global $wpdb;

            $post                   = $_POST['post'];
            $product_id             = ( !empty( $_POST['product_id'] ) ) ? $_POST['product_id'] : '';
            $found_attributes       = ( !empty( $_POST['found_attributes'] ) ) ? $_POST['found_attributes'] : '';
            $slug_to_tt_id          = ( !empty( $_POST['slug_to_tt_id'] ) ) ? $_POST['slug_to_tt_id'] : '';
            $available_variations   = ( !empty( $_POST['available_variations'] ) ) ? $_POST['available_variations'] : '';
            $id_to_variations       = ( !empty( $_POST['id_to_variations'] ) ) ? $_POST['id_to_variations'] : '';
            $term_product_type      = ( !empty( $_POST['term_product_type'] ) ) ? $_POST['term_product_type'] : '';
            $possible_variations    = ( !empty( $_POST['possible_variations'] ) ) ? json_decode( stripslashes( $_POST['possible_variations'] ), true ) : '';
            $_product               = ( !empty( $_POST['_product'] ) ) ? $_POST['_product'] : '';
            $variations             = ( !empty( $_POST['variations'] ) ) ? $_POST['variations'] : '';
            $old_post_meta          = ( !empty( $_POST['old_post_meta'] ) ) ? $_POST['old_post_meta'] : '';
            $old_meta_id            = ( !empty( $_POST['old_meta_id'] ) ) ? $_POST['old_meta_id'] : '';
            $parent_product_price   = ( !empty( $_POST['parent_product_price'] ) ) ? $_POST['parent_product_price'] : '';
            $bvm_operation_id       = get_option( 'bvm_operation_id' );
            
            $new_variation_data = array();

            $file_posts = $this->open_file( 'posts', true );
            $file_post_meta = $this->open_file( 'postmeta', true );

            $last_variation_post_name = $wpdb->get_var( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name LIKE '" . sanitize_title( 'Product #' . $product_id . ' Variation' ) . "%' AND ID = (SELECT MAX( ID ) FROM {$wpdb->prefix}posts WHERE post_name LIKE '" . sanitize_title( 'Product #' . $product_id . ' Variation' ) . "%')" );
            $variation_counter = (Integer)str_replace( sanitize_title( 'Product #' . $product_id . ' Variation' ) . '-', '', $last_variation_post_name );
            $variation_counter++;
            
            foreach ( $possible_variations as $variation ) {
                if ( !empty( $available_variations ) && in_array( $variation, $available_variations, true ) ) {
                    $is_update_price = false;
                    $variation_id = array_search( $variation, $id_to_variations, true );

                    $old_regular_price      = ( !empty( $old_post_meta[$variation_id]['_regular_price'] ) ) ? $old_post_meta[$variation_id]['_regular_price'] : '';
                    $old_sale_price         = ( !empty( $old_post_meta[$variation_id]['_sale_price'] ) ) ? $old_post_meta[$variation_id]['_sale_price'] : '';
                    $old_price              = ( !empty( $old_post_meta[$variation_id]['_price'] ) ) ? $old_post_meta[$variation_id]['_price'] : '';
                    $sale_price_dates_from  = ( !empty( $old_post_meta[$variation_id]['_sale_price_dates_from'] ) ) ? $old_post_meta[$variation_id]['_sale_price_dates_from'] : '';
                    $sale_price_dates_to    = ( !empty( $old_post_meta[$variation_id]['_sale_price_dates_to'] ) ) ? $old_post_meta[$variation_id]['_sale_price_dates_to'] : '';

                    $child_product_price = 0;
                    
                    foreach ( $variation as $attribute_name => $term ) {
                        $taxonomy = substr( $attribute_name, 10 );
                        if ( !isset( $post[$taxonomy] ) ) continue;
                        $term_id = array_search( $term, $post[$taxonomy], true );
                        if ( !empty( $term_id ) && isset( $post[$taxonomy . '-price'][$term_id] ) ) {
                            $child_product_price += (float)$post[$taxonomy . '-price'][$term_id];
                        }
                    }
                    if ( $old_price == $old_sale_price ) {
                        $update_field = '_sale_price';
                        if ( $parent_product_price === '' ) {
                            $sale_price = $child_product_price + $old_sale_price;
                        } else {
                            $sale_price = $child_product_price + $parent_product_price;
                        }
                        $new_variation_price = $sale_price;
                        $old_variation_price = $old_sale_price;
                        $regular_price = $old_regular_price;
                    } else {
                        $update_field = '_regular_price';
                        if ( $parent_product_price === '' ) {
                            $regular_price = $child_product_price + $old_regular_price;
                        } else {
                            $regular_price = $child_product_price + $parent_product_price;
                        }
                        $new_variation_price = $regular_price;
                        $old_variation_price = $old_regular_price;
                        $sale_price = $old_sale_price;
                    }
                    $price = SA_Bulk_Variations::get_price( $regular_price, $sale_price, $sale_price_dates_from, $sale_price_dates_to );
                    if ( $new_variation_price > 0 && $old_variation_price != $new_variation_price ) {
                        @fputcsv( $file_post_meta, array( $old_meta_id[$variation_id . '_' . $update_field], $variation_id, $update_field, $new_variation_price ) );
                        $is_update_price = true;
                    }
                    if ( $price > 0 && $old_price != $price ) {
                        @fputcsv( $file_post_meta, array( $old_meta_id[$variation_id . '_' . '_price'], $variation_id, '_price', $price ) );
                        $is_update_price = true;
                    }
                    @fputcsv( $file_post_meta, array( NULL, $variation_id, 'bvm_operation_id', $bvm_operation_id ) );
                    continue;
                }

                $post_date = current_time('mysql');
                $post_date_gmt = get_gmt_from_date($post_date);
                $post_title = 'Product #' . $product_id . ' Variation';
                $post_name = sanitize_title( $post_title ) . '-' . $variation_counter;
                $insert_data = array(
                    NULL,
                    get_current_user_id(),
                    $post_date,
                    $post_date_gmt,
                    '',
                    $post_title,
                    '',
                    'publish',
                    get_option('default_comment_status'),
                    get_option('default_ping_status'),
                    '',
                    $post_name,
                    '',
                    '',
                    $post_date,
                    $post_date_gmt,
                    $bvm_operation_id,
                    $product_id,
                    '',
                    0,
                    'product_variation',
                    '',
                    0
                );
                
                @fputcsv( $file_posts, $insert_data );
                
                $new_variation_data[$post_name] = array();
                $new_variation_data[$post_name]['variation'] = $variation;
                $new_variation_data[$post_name]['parent_product_price'] = $parent_product_price;
                $variation_counter++;

            }
            $this->close_file( $file_post_meta );
            $this->load_data_infile( 'postmeta' );
            $this->close_file( $file_posts );
            $this->load_data_infile( 'posts' );

            $return_data = array( 
                                'error' => 'false', 
                                'data' => array( 
                                                'next_action' => 'collect_variation_post_meta', 
                                                'new_variation_data' => ( !empty( $new_variation_data ) ) ? addslashes( json_encode( $new_variation_data ) ) : '', 
                                                'variations' => $variations,  
                                                'old_post_meta' => $old_post_meta, 
                                                'debug_queries' => $wpdb->queries, 
                                                'parent_product_price' => $parent_product_price
                                            ) 
                            );
            echo json_encode( $return_data );
            die();

        }

        function collect_variation_post_meta() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

            global $wpdb;
            if ( empty( $_POST['new_variation_data'] ) ) {
                echo json_encode( array( 'error' => 'false' ) );
                die();
            }
            $post = $_POST['post'];
            $new_variation_data = json_decode( stripslashes( stripslashes( $_POST['new_variation_data'] ) ), true );
            $bvm_operation_id = get_option( 'bvm_operation_id' );
            
            $post_names = array_keys( $new_variation_data );
            $new_saved_variations_results = $wpdb->get_results( "SELECT ID, post_name FROM {$wpdb->prefix}posts WHERE post_type LIKE 'product_variation' AND post_content_filtered  LIKE '" . $bvm_operation_id . "'", 'ARRAY_A' );
            $product_title_to_id = array();
            foreach ( $new_saved_variations_results as $new_saved_variations_result ) {
                $product_title_to_id[$new_saved_variations_result['post_name']] = $new_saved_variations_result['ID'];
            }
            $new_saved_variation_ids = array_values( $product_title_to_id );
            if ( !empty( $new_saved_variation_ids ) ) {
                $wpdb->query( "UPDATE {$wpdb->prefix}posts SET guid = CONCAT( '" . home_url() . "', '?', post_type, '=', post_name ) WHERE ID IN ( " . implode( ',', $new_saved_variation_ids ) . " )" );
            }
            $file_post_meta = $this->open_file( 'postmeta', true );
            
            foreach ( $new_variation_data as $post_name => $variation_data ) {
                if ( empty( $product_title_to_id[$post_name] ) ) continue;
                $variation_id = $product_title_to_id[$post_name];
                $variation = $variation_data['variation'];
                $parent_product_price = ( !empty( $variation_data['parent_product_price'] ) ) ? $variation_data['parent_product_price'] : 0;
                $child_product_price = 0;
                
                foreach ( $variation as $key => $value ) {
                    $taxonomy = substr( $key, 10 );
                    $term_id = array_search( $value, $post[$taxonomy], true );
                    if ( isset( $post[$taxonomy . '-price'][$term_id] ) ) {
                        $child_product_price += (float)$post[$taxonomy . '-price'][$term_id];
                    }
                    @fputcsv( $file_post_meta, array( NULL, $variation_id, $key, $value ) );
                }
                
                $final_price = $child_product_price + $parent_product_price;
                if ( $final_price > 0 ) {
                    @fputcsv( $file_post_meta, array( NULL, $variation_id, '_regular_price', $final_price ) );
                    @fputcsv( $file_post_meta, array( NULL, $variation_id, '_price', $final_price ) );
                    if ( $this->is_wc_gte_24() ) {
                    	@fputcsv( $file_post_meta, array( NULL, $variation_id, '_stock_status', 'instock' ) );
                    }
                    @fputcsv( $file_post_meta, array( NULL, $variation_id, 'bvm_operation_id', $bvm_operation_id ) );
                }
            }

            $this->close_file( $file_post_meta );
            $this->load_data_infile( 'postmeta' );
            echo json_encode( array( 'error' => 'false' ) );
            die();

        }

        function save_variation_post_meta() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

            $file_post_meta = $this->open_file( 'postmeta' );
            $this->close_file( $file_post_meta );
            $this->load_data_infile( 'postmeta' );
            $file_term_relationships = $this->open_file( 'term_relationships' );
            $this->close_file( $file_term_relationships );
            $this->load_data_infile( 'term_relationships' );
            echo json_encode( array( 'error' => 'false' ) );
            die();
        }

        function sync_variable_product_price() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );

        	$product_ids = $_POST['product_ids'];
        	foreach ( $product_ids as $product_id ) {
            	$_product = $this->get_product( $product_id, array( 'product_type' => 'variable' ) );
                $_product->variable_product_sync();
            }
            $this->delete_transient( $product_ids );
            echo json_encode( array( 'error' => 'false' ) );
            die();
        }

        function finalize_bulk_create_update_variations() {

        	check_ajax_referer( 'bulk-variations-manager-new', 'security' );
        	
            global $wpdb;

            $post = $_POST['post'];

            if ( !function_exists( '_woocommerce_term_recount' ) ) {
                require_once ( WP_PLUGIN_DIR . '/woocommerce/woocommerce-core-functions.php' );
            }
            
            foreach ( $post as $key => $value ) {
                if ( strpos( $key, 'pa_' ) !== 0 || strpos( $key, '-price' ) !== false ) continue;
                if ( !isset( $post[$key] ) ) continue;
                $taxonomy = get_taxonomy( $key );
                $terms = array_flip( $post[$key] );
                if ( $this->is_wc_gte_21() ) {
                    _wc_term_recount( $terms, $taxonomy );
                } else {
                    _woocommerce_term_recount( $terms, $taxonomy );
                }

            }
            echo json_encode( array( 'error' => 'false' ) );
            die();
        }

		function update_product_attributes( $product_ids, $product_attributes ) {
            if ( is_array( $product_ids ) && count( $product_ids ) > 0 ) {
            	if ( get_option( 'bvm_is_take_backup', 'yes' ) == 'yes' ) {
            		$this->bvm_take_backup_before_operation( $product_ids );
            	}
            	global $wpdb;
            	$results = $wpdb->get_results( "SELECT meta_id, post_id, meta_value AS product_attribute FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_product_attributes' AND post_id IN ( " . implode( ',', $product_ids ) .  " )", 'ARRAY_A' );
                $found_attributes = array();
            	$old_meta_id = array();
            	foreach ( $results as $found_attribute ) {
                    $found_attributes[$found_attribute['post_id']] = maybe_unserialize( $found_attribute['product_attribute'] );
            		$old_meta_id[$found_attribute['post_id'] . '_product_attribute'] = $found_attribute['meta_id'];
            	}
                $file_post_meta = $this->open_file( 'postmeta' );
				foreach ( $product_ids as $product_id ) {
                    if ( empty( $found_attributes[$product_id] ) ) continue;
					$old_product_attributes = $found_attributes[$product_id];		// get_post_meta( $product_id, '_product_attributes', true );
					$position = count( $old_product_attributes );
					foreach ( $product_attributes as $attribute_key => $product_attribute ) {
						if ( isset( $old_product_attributes[$attribute_key] ) ) {
							$old_product_attributes[$attribute_key]['is_visible'] = $product_attribute['is_visible'];
							if ( empty( $old_product_attributes[$attribute_key]['is_variation'] ) ) {
								$old_product_attributes[$attribute_key]['is_variation'] = $product_attribute['is_variation'];
							}
						} else {
							$product_attribute['position'] = "$position";
							$old_product_attributes[$attribute_key] = $product_attribute;
							$position++;
						}
					}
					@fputcsv( $file_post_meta, array( $old_meta_id[$product_id . '_product_attribute'], $product_id, '_product_attributes', maybe_serialize( $old_product_attributes ) ) );
                    @fputcsv( $file_post_meta, array( NULL, $product_id, 'bvm_operation_id', get_option( 'bvm_operation_id' ) ) );
				}
 				$this->close_file( $file_post_meta );
                $this->load_data_infile( 'postmeta' );
			}
		}

		function bvm_undo_last_operation( $bvm_last_operation = '' ) {

			if ( empty( $bvm_last_operation ) ) return;

			global $wpdb;

			$bvm_operation_id = get_option( 'bvm_operation_id' );

			if ( $bvm_last_operation == 'product_names' ) {
				$wpdb->query("DELETE FROM {$wpdb->prefix}term_relationships WHERE object_id IN ( SELECT ID FROM {$wpdb->prefix}posts WHERE post_content_filtered LIKE '" . $bvm_operation_id . "' )");
				$wpdb->query("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id IN ( SELECT ID FROM {$wpdb->prefix}posts WHERE post_content_filtered LIKE '" . $bvm_operation_id . "' )");
				$wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE post_parent IN ( SELECT ID FROM {$wpdb->prefix}posts WHERE post_content_filtered LIKE '" . $bvm_operation_id . "' )");
				$wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE post_content_filtered LIKE '" . $bvm_operation_id . "'");
			}

			if ( $bvm_last_operation == 'categories' || $bvm_last_operation == 'search' ) {
				$this->load_data_infile( 'posts', true );
				$this->load_data_infile( 'postmeta', true );
				$this->load_data_infile( 'term_relationships', true );
			}

			$bvm_operation_id++;
			update_option( 'bvm_operation_id', $bvm_operation_id );

		}

		function bvm_clear_last_operation_data( $bvm_operation_id = '' ) {
			if ( empty( $bvm_operation_id ) ) return;
			global $wpdb;

			$wpdb->query( "UPDATE {$wpdb->prefix}posts SET post_content_filtered = '' WHERE post_content_filtered LIKE '" . $bvm_operation_id . "'" );
            $wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'bvm_operation_id' AND meta_value LIKE '" . $bvm_operation_id . "'" );

		}

		function bvm_take_backup_before_operation( $product_ids = array() ) {
			if ( empty( $product_ids ) ) return;
			global $wpdb;
			$bvm_db = clone $wpdb;
			if ( $bvm_db->use_mysqli ) {
				$bvm_db->dbh = mysqli_init();

				$port = null;
				$socket = null;
				$host = $bvm_db->dbhost;
				$port_or_socket = strstr( $host, ':' );
				if ( ! empty( $port_or_socket ) ) {
					$host = substr( $host, 0, strpos( $host, ':' ) );
					$port_or_socket = substr( $port_or_socket, 1 );
					if ( 0 !== strpos( $port_or_socket, '/' ) ) {
						$port = intval( $port_or_socket );
						$maybe_socket = strstr( $port_or_socket, ':' );
						if ( ! empty( $maybe_socket ) ) {
							$socket = substr( $maybe_socket, 1 );
						}
					} else {
						$socket = $port_or_socket;
					}
				}

				if ( WP_DEBUG ) {
					mysqli_real_connect( $bvm_db->dbh, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket, 128 );
				} else {
					@mysqli_real_connect( $bvm_db->dbh, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket, 128 );
				}
			} else {
				if ( WP_DEBUG ) {
					$bvm_db->dbh = mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, false, 128 );
				} else {
					$bvm_db->dbh = @mysql_connect( DB_HOST, DB_USER, DB_PASSWORD, false, 128 );
				}
				$bvm_db->set_charset( $bvm_db->dbh );
				$bvm_db->ready = true;
				$bvm_db->select( $bvm_db->dbname, $bvm_db->dbh );
			}
			$upload_dir = wp_upload_dir();
			$is_granted = false;
			$posts_file = str_replace( '\\', '/', $upload_dir['basedir'] . '/posts_backup' );
			$postmeta_file = str_replace( '\\', '/', $upload_dir['basedir'] . '/postmeta_backup' );
			$term_relationships_file = str_replace( '\\', '/', $upload_dir['basedir'] . '/term_relationships_backup' );
			$all_grants = $bvm_db->get_results( "SHOW GRANTS FOR '".DB_USER."'@'".DB_HOST."'", 'ARRAY_A' );
			if ( ! empty( $all_grants ) ) {
				foreach ( $all_grants as $grants ) {
					foreach ( $grants as $key => $value ) {
						if ( stripos( $value, 'all' ) !== false || stripos( $value, 'file' ) !== false ) {
							$is_granted = true;
							break 2;
						}
					}
				}
			}
			if ( $is_granted ) {
				unlink( "'{$posts_file}.csv'" );
				unlink( "'{$postmeta_file}.csv'" );
				unlink( "'{$term_relationships_file}.csv'" );
				$bvm_db->query( "SELECT * INTO OUTFILE '{$posts_file}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '" . '"' . "' LINES TERMINATED BY '\\n' FROM {$bvm_db->prefix}posts WHERE ID IN ( " . implode( ',', $product_ids ) . " ) OR post_parent IN ( " . implode( ',', $product_ids ) . " )" );
				$bvm_db->query( "SELECT * INTO OUTFILE '{$postmeta_file}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '" . '"' . "' LINES TERMINATED BY '\\n' FROM {$bvm_db->prefix}postmeta WHERE post_id IN ( " . implode( ',', $product_ids ) . " ) OR post_id IN ( SELECT post_parent FROM {$bvm_db->prefix}posts WHERE ID IN ( " . implode( ',', $product_ids ) . " ) )" );
				$bvm_db->query( "SELECT * INTO OUTFILE '{$term_relationships_file}.csv' FIELDS TERMINATED BY ',' ENCLOSED BY '" . '"' . "' LINES TERMINATED BY '\\n' FROM {$bvm_db->prefix}term_relationships WHERE object_id IN ( " . implode( ',', $product_ids ) . " )" );
			} else {
				$posts_data = $bvm_db->get_results( "SELECT * FROM {$bvm_db->prefix}posts WHERE ID IN ( " . implode( ',', $product_ids ) . " ) OR post_parent IN ( " . implode( ',', $product_ids ) . " )", 'ARRAY_A' );
				$file_posts = $this->open_file( 'posts_backup', true );
				foreach ( $posts_data as $posts ) {
					if ( WP_DEBUG ) {
						fputcsv( $file_posts, $posts );
					} else {
						@fputcsv( $file_posts, $posts );
					}
				}
				$this->close_file( $file_posts );
				$postmeta_data = $bvm_db->get_results( "SELECT * FROM {$bvm_db->prefix}postmeta WHERE post_id IN ( " . implode( ',', $product_ids ) . " ) OR post_id IN ( SELECT ID FROM {$bvm_db->prefix}posts WHERE post_parent IN ( " . implode( ',', $product_ids ) . " ) )", 'ARRAY_A' );
				$file_postmeta = $this->open_file( 'postmeta_backup', true );
				foreach ( $postmeta_data as $postmeta ) {
					if ( WP_DEBUG ) {
						fputcsv( $file_postmeta, $postmeta );
					} else {
						@fputcsv( $file_postmeta, $postmeta );
					}
				}
				$this->close_file( $file_postmeta );
				$term_relationships_data = $bvm_db->get_results( "SELECT * FROM {$bvm_db->prefix}term_relationships WHERE object_id IN ( " . implode( ',', $product_ids ) . " )", 'ARRAY_A' );
				$file_term_relationships = $this->open_file( 'term_relationships_backup', true );
				foreach ( $term_relationships_data as $term_relationships ) {
					if ( WP_DEBUG ) {
						fputcsv( $file_term_relationships, $term_relationships );
					} else {
						@fputcsv( $file_term_relationships, $term_relationships );
					}
				}
				$this->close_file( $file_term_relationships );
			}
			unset( $bvm_db );
			
		}

		function delete_transient( $product_ids = array() ) {

			if ( ! $this->is_wc_gte_24() ) return;

			if ( $this->is_wc_greater_than( '2.4.6' ) ) {
				WC_Cache_Helper::get_transient_version( 'product', true );
				return;
			}

			if ( empty( $product_ids ) ) return;
			if ( ! is_array( $product_ids ) ) {
				$product_ids = array( $product_ids );
			}

			foreach ( $product_ids as $product_id ) {
				$product = $this->get_product( $product_id );
            	foreach ( array( false, true ) as $display ) {
                	$cache_key = 'wc_var_prices' . md5( json_encode( apply_filters( 'woocommerce_get_variation_prices_hash', array(
						$product->id,
						$display ? WC_Tax::get_rates() : '',
						WC_Cache_Helper::get_transient_version( 'product' )
					), $product, $display ) ) );
					delete_transient( $cache_key );
            	}
				delete_transient( 'wc_product_children_' . $product_id );
			}

		}

	}

}

global $bvm_operation_new;

$bvm_operation_new = new BVM_Operation_New();