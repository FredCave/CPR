<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WooCommerce_Wholesale_Lead_Capture' ) ) {

	require_once ( "includes/class-wwlc-forms.php" );
	require_once ( "includes/class-wwlc-user-account.php" );
	require_once ( "includes/class-wwlc-user-custom-fields.php" );
	require_once ( "includes/class-wwlc-emails.php" );
    require_once ( "includes/class-wwlc-wws-license-settings.php" );
    require_once ( "includes/class-wwlc-registration-form-custom-fields.php" );

	class WooCommerce_Wholesale_Lead_Capture {

		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Class Members
	     |--------------------------------------------------------------------------------------------------------------
	     */

		private static $_instance;

		private $_wwlcForms;
		private $_wwlcUserAccount;
		private $_wwlcUserCustomFields;
		private $_wwlcEmails;
        private $_wwlcWWSLicenseSetting;

		const VERSION = '1.4.4';




		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Mesc Functions
	     |--------------------------------------------------------------------------------------------------------------
	     */

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			$this->_wwlcForms = WWLC_Forms::getInstance();
			$this->_wwlcUserAccount = WWLC_User_Account::getInstance();
			$this->_wwlcUserCustomFields = WWLC_User_Custom_Fields::getInstance();
			$this->_wwlcEmails = WWLC_Emails::getInstance();
            $this->_wwlcWWSLicenseSetting = WWLC_WWS_License_Settings::getInstance();
            $this->_wwlcWWLCCustomFeildsControl = WWLC_Registration_Form_Custom_Fields::getInstance();

		}

		/**
		 * Singleton Pattern.
		 *
		 * @return WooCommerce_Wholesale_Lead_Capture
		 * @since 1.0.0
		 */
		public static function getInstance() {

			if ( !self::$_instance instanceof self )
				self::$_instance = new self;

			return self::$_instance;

		}




		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | Internationalization and Localization
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Load plugin text domain.
		 *
		 * @since 1.3.1
		 */
		public function loadPluginTextDomain () {

			load_plugin_textdomain( 'woocommerce-wholesale-lead-capture' , false , WWLC_PLUGIN_BASE_PATH . 'languages/' );

		}




		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Bootstrap/Shutdown Functions
	     |--------------------------------------------------------------------------------------------------------------
	     */

		/**
		 * Plugin activation hook callback.
		 *
		 * @since 1.0.0
		 */
		public function activate() {

			// Add inactive user role
			add_role( WWLC_UNAPPROVED_ROLE , 'Unapproved' , array() );
			add_role( WWLC_UNMODERATED_ROLE , 'Unmoderated' , array() );
			add_role( WWLC_REJECTED_ROLE , 'Rejected' , array() );
			add_role( WWLC_INACTIVE_ROLE , 'Inactive' , array() );

			// On activation, create registration, thank you and login page
			// Then save these pages on the general settings of this plugin
			// relating to log in and registration page options.
			// But only do this if, the user has not yet set a login, thank you and registration page ( Don't overwrite the users settings )

			if ( !get_option( 'wwlc_general_login_page' ) && !get_option( 'wwlc_general_registration_page' ) && !get_option( 'wwlc_general_registration_thankyou' ) ) {

				if ( $this->_wwlcForms->wwlc_createLeadPages( null , false ) ) {

					$loginPageUrl = get_permalink( (int) get_option( WWLC_OPTIONS_LOGIN_PAGE_ID ) );
					$registrationPageUrl = get_permalink( (int) get_option( WWLC_OPTIONS_REGISTRATION_PAGE_ID ) );
					$thankYouPageUrl = get_permalink( (int) get_option( WWLC_OPTIONS_THANK_YOU_PAGE_ID ) );

					update_option( 'wwlc_general_login_page' , $loginPageUrl );
					update_option( 'wwlc_general_registration_page' , $registrationPageUrl );
					update_option( 'wwlc_general_registration_thankyou' , $thankYouPageUrl );

				}

			}

			// On activation, assign New Lead Role to Wholesale Customer role, if not present default to Customer
			// Get all user roles
			global $wp_roles;

			if( !isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

			$allUserRoles = $wp_roles->get_names();
			if( array_key_exists( "wholesale_customer" , $allUserRoles ) )
				update_option( "wwlc_general_new_lead_role", "wholesale_customer" );
			else
				update_option( "wwlc_general_new_lead_role", "customer" );


			flush_rewrite_rules();

		}

        /**
         * Plugin initialization.
         *
         * @since 1.0.0
         */
        public function initialize() {

        }

		/**
		 * Plugin deactivation hook callback.
		 *
		 * @since 1.0.0
		 */
		public function deactivate() {

			// Remove inactive user role
			remove_role( WWLC_INACTIVE_ROLE );
			remove_role( WWLC_REJECTED_ROLE );
			remove_role( WWLC_UNMODERATED_ROLE );
			remove_role( WWLC_UNAPPROVED_ROLE );

			flush_rewrite_rules();

		}
		



        /*
        |---------------------------------------------------------------------------------------------------------------
        | WooCommerce WholeSale Suit License Settings
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Register general wws license settings page.
         *
         * @since 1.0.1
         */
        public function registerWWSLicenseSettingsMenu() {

            /*
             * Since we don't have a primary plugin to add this license settings, we have to check first if other plugins
             * belonging to the WWS plugin suite has already added a license settings page.
             */
            if ( !defined( 'WWS_LICENSE_SETTINGS_PAGE' ) ) {

                if ( !defined( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' ) )
                    define( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' , 'wwlc' );

                // Register WWS Settings Menu
                add_submenu_page(
                    'options-general.php', // Settings
                    __( 'WooCommerce WholeSale Suit License Settings' , 'woocommerce-wholesale-lead-capture' ),
                    __( 'WWS License' , 'woocommerce-wholesale-lead-capture' ),
                    'manage_options',
                    'wwc_license_settings',
                    array( self::getInstance() , "wwcGeneralLicenseSettingsPage" )
                );

                /*
                 * We define this constant with the text domain of the plugin who added the settings page.
                 */
                define( 'WWS_LICENSE_SETTINGS_PAGE' , 'woocommerce-wholesale-lead-capture' );

            }

        }

        public function wwcGeneralLicenseSettingsPage() {

            require_once( 'views/wws-license-settings/view-wwlc-general-wws-settings-page.php' );

        }

        public function wwcLicenseSettingsHeader() {

            ob_start();

            if ( isset( $_GET[ 'tab' ] ) )
                $tab = $_GET[ 'tab' ];
            else
                $tab = WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN;

            global $wp;
            $current_url = add_query_arg( $wp->query_string , '?' , home_url( $wp->request ) );
            $wwlc_license_settings_url = $current_url . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwlc"; ?>

			<a href="<?php echo $wwlc_license_settings_url; ?>" class="nav-tab <?php echo ( $tab == "wwlc" ) ? "nav-tab-active" : ""; ?>"><?php _e( 'Wholesale Lead' , 'woocommerce-wholesale-lead-capture' ); ?></a>

			<?php echo ob_get_clean();

        }

        public function wwcLicenseSettingsPage() {

            ob_start();

            require_once( "views/wws-license-settings/view-wwlc-wss-settings-page.php" );

            echo ob_get_clean();

        }




		/*
	    |---------------------------------------------------------------------------------------------------------------
	    | Admin Functions
	    |---------------------------------------------------------------------------------------------------------------
	    */

		/**
		 * Load Admin or Backend Related Styles and Scripts.
		 *
		 * @param $handle
		 *
		 * @since 1.0.0
		 */
		public function loadBackEndStylesAndScripts( $handle ) {

			$screen = get_current_screen();

			// Only load styles and js on the right time and on the right place

			if ( $handle == 'users.php' ) {

				// User listing page

				// Styles
				wp_enqueue_style( 'wwlc_toastr_css' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );

				wp_enqueue_style( 'wwlc_Users_css' , WWLC_CSS_ROOT_URL.'Users.css', array(), self::VERSION, 'all' );

				// Scripts
				wp_enqueue_script( 'wwlc_toastr_js' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION );

				wp_enqueue_script( 'wwlc_BackEndAjaxServices_js' , WWLC_JS_ROOT_URL.'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
				wp_enqueue_script( 'wwlc_UserListing_js' , WWLC_JS_ROOT_URL.'app/UserListing.js' , array( 'jquery' ) , self::VERSION );
				wp_localize_script( 'wwlc_UserListing_js',
									'UserListingVars',
									array(
										'approving_failed_message'		=>	__( 'Approving User Failed' , 'woocommerce-wholesale-lead-capture' ),
										'rejecting_failed_message'		=>	__( 'Rejecting User Failed' , 'woocommerce-wholesale-lead-capture' ),
										'activating_failed_message'		=>	__( 'Activating User Failed' , 'woocommerce-wholesale-lead-capture' ),
										'deactivating_failed_message'	=>	__( 'Deactivating User Failed' , 'woocommerce-wholesale-lead-capture' )
									) );

			} if ( in_array( $screen->id, array( 'woocommerce_page_wc-settings' ) ) ) {

				if ( !isset( $_GET['section'] )  || ( isset( $_GET['section'] ) && $_GET[ 'section' ] == '' ) ) {

					// General Section

					// Styles
					wp_enqueue_style( 'wwlc_chosen_css' , WWLC_JS_ROOT_URL . 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );

					// Scripts
					wp_enqueue_script( 'wwlc_chosen_js' , WWLC_JS_ROOT_URL . 'lib/chosen/chosen.jquery.min.js' , array( 'jquery' ) , self::VERSION );
					wp_enqueue_script( 'wwlc_GeneralSettings_js' , WWLC_JS_ROOT_URL . 'app/GeneralSettings.js' , array( 'jquery' ) , self::VERSION );
					wp_localize_script( 'wwlc_GeneralSettings_js' , 'GeneralSettingsVars' , array( 'select_placeholder_text' => __( 'Select a Page' , 'woocommerce-wholesale-lead-capture' ) ) );

				} elseif ( isset( $_GET['section'] ) && $_GET[ 'section' ] == 'wwlc_settings_help_section' ) {

                    // Help Section

					// Styles
					wp_enqueue_style( 'wwlc_toastr_css' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );
					wp_enqueue_style( 'wwlc_HelpSettings_css' , WWLC_CSS_ROOT_URL.'HelpSettings.css', array(), self::VERSION, 'all' );

					// Scripts
					wp_enqueue_script( 'wwlc_toastr_js' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION );
					wp_enqueue_script( 'wwlc_BackEndAjaxServices_js' , WWLC_JS_ROOT_URL.'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
					wp_enqueue_script( 'wwlc_HelpSettings_js' , WWLC_JS_ROOT_URL.'app/HelpSettings.js' , array( 'jquery' ) , self::VERSION );
					wp_localize_script( 'wwlc_HelpSettings_js',
										'HelpSettingsVars',
										array(
											'success_message' => __( 'Lead Pages Created Successfully' , 'woocommerce-wholesale-lead-capture' ),
											'error_message' => __( 'Failed To Create Lead Pages' , 'woocommerce-wholesale-lead-capture' )
										) );

				} elseif ( isset( $_GET['section'] ) && $_GET[ 'section' ] == 'wwlc_setting_custom_fields_section' ) {

                    // Custom Fields Section

                    // CSS
                    wp_enqueue_style( 'wwlc_toastr_css' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );
                    wp_enqueue_style( 'wwlc_WWLCCustomFieldsControl_css' , WWLC_CSS_ROOT_URL.'WWLCCustomFieldsControl.css' , array() , self::VERSION , 'all' );

                    // JS
                    wp_enqueue_script( 'wwlc_toastr_js' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION );
                    wp_enqueue_script( 'wwlc_BackEndAjaxServices_js' , WWLC_JS_ROOT_URL.'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
                    wp_enqueue_script( 'wwlc_WWLCCustomFieldsControl_js' , WWLC_JS_ROOT_URL.'app/WWLCCustomFieldsControl.js' , array('jquery') , self::VERSION );
					wp_localize_script( 'wwlc_WWLCCustomFieldsControl_js',
										'WWLCCustomFieldsControlVars',
										array(
											'empty_fields_error_message'	=>	__( 'Please Fill The Form Properly. The following fields have empty values.' , 'woocommerce-wholesale-lead-capture' ),
											'success_save_message'			=>	__( 'Custom Field Successfully Saved' , 'woocommerce-wholesale-lead-capture' ),
											'failed_save_message'			=>	__( 'Failed To Save Custom Field' , 'woocommerce-wholesale-lead-capture' ),
											'success_edit_message'			=>	__( 'Custom Field Successfully Edited' , 'woocommerce-wholesale-lead-capture' ),
											'failed_edit_message'			=>	__( 'Failed To Edit Custom Field' , 'woocommerce-wholesale-lead-capture' ),
											'failed_retrieve_message'		=>	__( 'Failed Retrieve Custom Field Data' , 'woocommerce-wholesale-lead-capture' ),
											'confirm_box_message'			=>	__( 'Clicking OK will remove the current custom role' , 'woocommerce-wholesale-lead-capture' ),
											'no_custom_field_message'		=>	__( 'No Custom Fields Found' , 'woocommerce-wholesale-lead-capture' ),
											'success_delete_message'		=>	__( 'Successfully Deleted Custom Role' , 'woocommerce-wholesale-lead-capture' ),
											'failed_delete_message'			=>	__( 'Failed To Delete Custom Field' , 'woocommerce-wholesale-lead-capture' ),
										) );

                } elseif ( isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwlc_setting_email_section' ) {

					// CSS
					wp_enqueue_style( 'wwlc_selectize_default_css' , WWLC_JS_ROOT_URL . 'lib/selectize/selectize.default.css' , array() , self::VERSION , 'all' );
					wp_enqueue_style( 'wwlc_EmailSettings_css' , WWLC_CSS_ROOT_URL . 'EmailSettings.css' , array() , self::VERSION , 'all' );

					// JS
					wp_enqueue_script( 'wwlc_selectize_js' , WWLC_JS_ROOT_URL . 'lib/selectize/selectize.min.js' , array( 'jquery' ) , self::VERSION );
					wp_enqueue_script( 'wwlc_EmailSettings_js' , WWLC_JS_ROOT_URL . 'app/EmailSettings.js' , array( 'jquery' ) , self::VERSION );

				}

            } elseif ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' &&
                       ( ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwlc' ) || WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN == 'wwlc' ) ) {

                // CSS
                wp_enqueue_style( 'wwlc_toastr_css' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );
                wp_enqueue_style( 'wwlc_WWSLicenseSettings_css' , WWLC_CSS_ROOT_URL . 'WWSLicenseSettings.css' , array() , self::VERSION , 'all');

                // JS
                wp_enqueue_script( 'wwlc_toastr_js' , WWLC_JS_ROOT_URL.'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION );
                wp_enqueue_script( 'wwlc_BackEndAjaxServices_js' , WWLC_JS_ROOT_URL.'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
                wp_enqueue_script( 'wwlc_WWSLicenseSettings_js' , WWLC_JS_ROOT_URL.'app/WWSLicenseSettings.js' , array('jquery') , self::VERSION );
				wp_localize_script( 'wwlc_WWSLicenseSettings_js',
									'WWSLicenseSettingsVars',
									array(
										'success_save_message'	=>	__( 'Wholesale Lead License Details Successfully Saved' , 'woocommerce-wholesale-lead-capture' ),
										'failed_save_message'	=>	__( 'Failed To Save Wholesale Lead License Details' , 'woocommerce-wholesale-lead-capture' ),
									) );

            }elseif( $screen->id == "profile" || $screen->id == "user-edit" ){
				
				// CSS
				wp_enqueue_style( 'wwlc_Users_css' , WWLC_CSS_ROOT_URL.'Users.css', array(), self::VERSION, 'all' );
				
				// Scripts
				wp_enqueue_script( 'wwlc_user_update_js' , WWLC_JS_ROOT_URL . 'app/UserUpdate.js' , array( 'jquery' ) , self::VERSION );
				wp_enqueue_script( 'wwlc_BackEndAjaxServices_js' , WWLC_JS_ROOT_URL.'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
				wp_enqueue_script( 'wwlc_FormActions_js' , WWLC_JS_ROOT_URL . 'app/modules/FormActions.js' , array( 'jquery' ) , self::VERSION );
            }

		}

		/**
		 * Load Frontend Related Styles and Scripts.
		 *
		 * @since 1.0.0
		 */
		public function loadFrontEndStylesAndScripts() {

			// Only load styles and js on the right time and on the right place

			global $post;

			if ( isset( $post->post_content ) && has_shortcode( $post->post_content , 'wwlc_registration_form' ) ) {

				/*
				 * Loading via cdn with local script fallback

					$get_the_url = 'http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js';

					$cdnIsUp = get_transient( 'cnd_is_up' );

					if ( $cdnIsUp ) {

					    $load_source = 'load_external_bootstrap';

					} else {

					    $cdn_response = wp_remote_get( $get_the_url );

					    if( is_wp_error( $cdn_response ) || wp_remote_retrieve_response_code($cdn_response) != '200' ) {

					        $load_source = 'load_local_bootstrap';

					    }
					    else {

					        $cdnIsUp = set_transient( 'cnd_is_up', true, MINUTE_IN_SECONDS * 20 );
					        $load_source = 'load_external_bootstrap';
					    }
					 }

					add_action('wp_enqueue_scripts', $load_source );

					function load_external_bootstrap() {
					    wp_register_script( 'bootstrap', 'http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js', array('jquery'), 3.3, true);
					    wp_enqueue_script('bootstrap');
					}

					function load_local_bootstrap() {
					    wp_register_script('bootstrap', get_bloginfo('template_url').'/js/bootstrap.min.js', __FILE__, array('jquery'), 3.3, true);
					    wp_enqueue_script('bootstrap');
					}
				*/

				// Styles
				wp_enqueue_style( 'wwlc_chosen_css' , WWLC_JS_ROOT_URL . 'lib/chosen/chosen.min.css' , array() , self::VERSION , 'all' );

				wp_enqueue_style( 'wwlc_toastr_css' , WWLC_JS_ROOT_URL . 'lib/toastr/toastr.min.css' , array() , self::VERSION , 'all' );

				wp_enqueue_style( 'wwlc_RegistrationForm_css' , WWLC_CSS_ROOT_URL . 'RegistrationForm.css', array(), self::VERSION, 'all' );

				// Scripts
				wp_enqueue_script( 'wwlc_chosen_js' , WWLC_JS_ROOT_URL . 'lib/chosen/chosen.jquery.min.js' , array( 'jquery' ) , self::VERSION );

				wp_enqueue_script( 'wwlc_toastr_js' , WWLC_JS_ROOT_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , self::VERSION );

				wp_enqueue_script( 'wwlc_FrontEndAjaxServices_js' , WWLC_JS_ROOT_URL . 'app/modules/FrontEndAjaxServices.js' , array( 'jquery' ) , self::VERSION );
				wp_localize_script( 'wwlc_FrontEndAjaxServices_js' , 'Ajax' , array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				wp_enqueue_script( 'wwlc_FormActions_js' , WWLC_JS_ROOT_URL . 'app/modules/FormActions.js' , array( 'jquery' ) , self::VERSION );
				wp_enqueue_script( 'wwlc_FormValidator_js' , WWLC_JS_ROOT_URL . 'app/modules/FormValidator.js' , array( 'jquery' ) , self::VERSION );
				wp_enqueue_script( 'wwlc_RegistrationForm_js' , WWLC_JS_ROOT_URL . 'app/RegistrationForm.js' , array( 'jquery' ) , self::VERSION );
				wp_localize_script( 'wwlc_RegistrationForm_js',
									'RegistrationVars',
									array(
										'registrationThankYouPage' 				=>	trim( get_option( 'wwlc_general_registration_thankyou' ) ),
										'fill_form_appropriately_message'		=>	__( 'Please Fill The Form Appropriately' , 'woocommerce-wholesale-lead-capture' ),
										'failed_registration_process_message'	=>	__( 'Failed To Process Registration' , 'woocommerce-wholesale-lead-capture' ),
										'registration_failed_message'			=>	__( 'Registration Failed' , 'woocommerce-wholesale-lead-capture' ),
										'settings_save_failed_message'			=>	__( 'Failed To Save Settings' , 'woocommerce-wholesale-lead-capture' )
									) );

			}

		}

		/**
		 * Initialize plugin settings.
		 *
		 * @since 1.0.0
		 */
		public function initializePluginSettings() {

			$settings[] = include( WWLC_INCLUDES_ROOT_DIR . "class-wwlc-settings.php" );

			return $settings;

		}




		/*
	    |---------------------------------------------------------------------------------------------------------------
	    | User Account
	    |---------------------------------------------------------------------------------------------------------------
	    */

		/**
		 * WWLC authentication filter. It checks if user is inactive, unmoderated, unapproved or rejected and kick
		 * there asses.
		 *
		 * @param $user
		 * @param $password
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function wholesaleLeadAuthenticate( $user , $password ) {

			return $this->_wwlcUserAccount->wholesaleLeadAuthenticate( $user , $password );

		}

		/**
		 * Redirect wholesale users after successful login accordingly.
		 *
		 * @param $redirect_to
		 * @param $request
		 * @param $user
		 * @return mixed
		 *
		 * @since 1.2.0
		 */
		public function wholesaleLeadLoginRedirect( $redirect_to, $request, $user ) {

			return $this->_wwlcUserAccount->wholesaleLeadLoginRedirect( $redirect_to , $request , $user );

		}

		/**
		 * Redirect wholesale user to specific page after logging out.
		 *
		 * @since 1.3.3
		 */
		public function wholesaleLeadLogoutRedirect() {

			$this->_wwlcUserAccount->wholesaleLeadLogoutRedirect();

		}

		/**
		 * Total unmoderated users bubble notification.
		 *
		 * @since 1.0.0
		 */
		public function totalUnmoderatedUsersBubbleNotification() {

			$this->_wwlcUserAccount->totalUnmoderatedUsersBubbleNotification();

		}

		/**
		 * Total unmoderated user admin notice.
		 *
		 * @since 1.0.0
		 */
		public function totalUnmoderatedUsersAdminNotice() {

			$this->_wwlcUserAccount->totalUnmoderatedUsersAdminNotice();

		}

		/**
		 * Hide total unmoderated users admin notice.
		 *
		 * @since 1.0.0
		 */
		public function hideTotalUnmoderatedUsersAdminNotice() {

			$this->_wwlcUserAccount->hideTotalUnmoderatedUsersAdminNotice();

		}

		/**
		 * Hide important notice about properly managing wholesale users.
		 *
		 * @since 1.3.1
		 */
		public function hideImportantProperUserManagementNotice() {

			$this->_wwlcUserAccount->hideImportantProperUserManagementNotice();

		}




		/*
	    |---------------------------------------------------------------------------------------------------------------
	    | User Listing Custom Fields
	    |---------------------------------------------------------------------------------------------------------------
	    */

		/**
		 * Add custom row action to user listing page.
		 *
		 * @param $actions
		 * @param $user_object
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function addUserListCustomRowActionUI( $actions, $user_object ) {

			return $this->_wwlcUserCustomFields->addUserListCustomRowActionUI( $actions, $user_object );

		}

		/**
		 * Add custom admin notices on user listing page. WWLC related.
		 *
		 * @since 1.0.0
		 */
		public function customSubmissionsBulkActionNotices() {

			$this->_wwlcUserCustomFields->customSubmissionsBulkActionNotices();

		}

		/**
		 * Add custom column to user listing page.
		 *
		 * @param $columns
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function addUserListingCustomColumn( $columns ) {

			return $this->_wwlcUserCustomFields->addUserListingCustomColumn( $columns );

		}

		/**
		 * Add content to custom column to user listing page.
		 *
		 * @param $val
		 * @param $column_name
		 * @param $user_id
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function addUserListingCustomColumnContent( $val , $column_name , $user_id ) {

			return $this->_wwlcUserCustomFields->addUserListingCustomColumnContent( $val , $column_name , $user_id );

		}

		/**
		 * Add custom user listing bulk action items on the action select boxes. Done via JS.
		 *
		 * @since 1.0.0
		 */
		public function customUserListingBulkActionFooterJS() {

			$this->_wwlcUserCustomFields->customUserListingBulkActionFooterJS();

		}

		/**
		 * Add custom user listing bulk action.
		 *
		 * @since 1.3.3
		 */
		public function customUserListingBulkAction() {

			$this->_wwlcUserCustomFields->customUserListingBulkAction( $this->_wwlcUserAccount , $this->_wwlcEmails );

		}

		/**
		 * Display custom fields on user admin.
		 *
		 * @param $user
		 * @since 1.0.0
		 */
		public function displayCustomFieldsOnUserAdminPage( $user ) {

			$this->_wwlcUserCustomFields->displayCustomFieldsOnUserAdminPage( $user );

		}

		/**
		 * Save custom fields on user admin.
		 *
		 * @param $user_id
		 *
		 * @since 1.0.0
		 */
		public function saveCustomFieldsOnUserAdminPage( $user_id ) {

			$this->_wwlcUserCustomFields->saveCustomFieldsOnUserAdminPage ( $user_id );

		}

        /**
         * Add plugin listing custom action link ( settings ).
         *
         * @param $links
         * @param $file
         * @return mixed
         *
         * @since 1.0.2
         */
        public function addPluginListingCustomActionLinks( $links , $file ) {

            if ( $file == plugin_basename( WWLC_PLUGIN_DIR . 'woocommerce-wholesale-lead-capture.bootstrap.php' ) ) {

                $settings_link = '<a href="admin.php?page=wc-settings&tab=wwlc_settings">' . __( 'Plugin Settings' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
                $license_link = '<a href="options-general.php?page=wwc_license_settings&tab=wwlc">' . __( 'License Settings' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
                array_unshift( $links , $license_link );
                array_unshift( $links , $settings_link );

            }

            return $links;

        }




		/*
	    |---------------------------------------------------------------------------------------------------------------
	    | Short Codes
	    |---------------------------------------------------------------------------------------------------------------
	    */

		/**
		 * [wwlc_registration_form] shortcode callback. Render registration form.
		 *
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function scRegistrationForm( $atts , $content ) {

			return $this->_wwlcForms->registrationForm();

		}

		/**
		 * [wwlc_login_form] shortcode callback. Render login form.
		 *
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function scLogInForm( $atts , $content ) {

			return $this->_wwlcForms->loginForm();

		}




		/*
	    |---------------------------------------------------------------------------------------------------------------
	    | AJAX
	    |---------------------------------------------------------------------------------------------------------------
	    */

		/**
		 * Register AJAX interface callbacks.
		 *
		 * @since 1.0.0
		 */
		public function registerAJAXCAllHandlers() {

			// Note: You have to register your ajax interface to both wp_ajax_ and wp_ajax_nopriv_ if you want it to be
			// accessible to both logged in and unauthenticated users.

			// Authenticated user "ONLY" AJAX interfaces
			add_action( "wp_ajax_wwlc_createUser" , array( self::getInstance() , 'wwlc_createUser' ) );
			add_action( "wp_ajax_wwlc_approveUser" , array( self::getInstance() , 'wwlc_approveUser' ) );
			add_action( "wp_ajax_wwlc_rejectUser" , array( self::getInstance() , 'wwlc_rejectUser' ) );
			add_action( "wp_ajax_wwlc_activateUser" , array( self::getInstance() , 'wwlc_activateUser' ) );
			add_action( "wp_ajax_wwlc_deactivateUser" , array( self::getInstance() , 'wwlc_deactivateUser' ) );
			add_action( "wp_ajax_wwlc_createLeadPages" , array( $this->_wwlcForms , 'wwlc_createLeadPages' ) );
            add_action( "wp_ajax_wwlc_saveLicenseDetails" , array( $this->_wwlcWWSLicenseSetting , 'wwlc_saveLicenseDetails' ) );
            add_action( "wp_ajax_wwlc_addRegistrationFormCustomField" , array( $this->_wwlcWWLCCustomFeildsControl , 'wwlc_addRegistrationFormCustomField' ) );
            add_action( "wp_ajax_wwlc_editRegistrationFormCustomField" , array( $this->_wwlcWWLCCustomFeildsControl , 'wwlc_editRegistrationFormCustomField' ) );
            add_action( "wp_ajax_wwlc_deleteRegistrationFormCustomField" , array( $this->_wwlcWWLCCustomFeildsControl , 'wwlc_deleteRegistrationFormCustomField' ) );
            add_action( "wp_ajax_wwlc_getCustomFieldByID" , array( $this->_wwlcWWLCCustomFeildsControl , 'wwlc_getCustomFieldByID' ) );
            add_action( "wp_ajax_wwlc_getStates" , array( self::getInstance() , 'wwlc_getStates' ) );

			// Unauthenticated user "ONLY" AJAX interfaces
			add_action( "wp_ajax_nopriv_wwlc_createUser" , array( self::getInstance() , 'wwlc_createUser' ) );
			add_action( "wp_ajax_nopriv_wwlc_getStates" , array( self::getInstance() , 'wwlc_getStates' ) );

		}

		/**
		 * Get states by country code.
		 *
		 * @param null $cc
		 * @param bool $ajaxCall
		 *
		 * @return mixed
		 * @since 1.4.0
		 */
		public function wwlc_getStates( $cc = null , $ajaxCall = true ) {

			if ( $ajaxCall === true )
				$this->_wwlcUserAccount->getStates( $cc , $ajaxCall );
			else
				return $this->_wwlcUserAccount->getStates( $cc , $ajaxCall );

		}

		/**
		 * Create user ajax interface.
		 *
		 * @param null $userData
		 * @param bool $ajaxCall
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function wwlc_createUser( $userData = null , $ajaxCall = true ) {

			if ( $ajaxCall === true )
				$this->_wwlcUserAccount->createUser( $userData , $ajaxCall , $this->_wwlcEmails );
			else
				return $this->_wwlcUserAccount->createUser( $userData , $ajaxCall , $this->_wwlcEmails );

		}

		/**
		 * Approve user ajax interface.
		 *
		 * @param null $userID
		 * @param bool $ajaxCall
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_approveUser( $userID = null , $ajaxCall = true ) {

			if ( $ajaxCall === true )
				$userID = $_POST[ 'userID' ];

			$this->_wwlcUserAccount->approveUser( array( 'userID' => $userID ) , $this->_wwlcEmails );

			if ( $ajaxCall === true ) {

				header( 'Content-Type: application/json' ); // specify we return json
				echo json_encode( array(
					'status'        =>  'success',
					'redirect_url'  =>  home_url() . '/wp-admin/users.php?users_approved=1'
				) );
				die();

			} else
				return true;

		}

		/**
		 * Reject user ajax interface.
		 *
		 * @param null $userID
		 * @param bool $ajaxCall
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_rejectUser( $userID = null , $ajaxCall = true ) {

			if ( $ajaxCall === true )
				$userID = $_POST[ 'userID' ];

			$this->_wwlcUserAccount->rejectUser( array( 'userID' => $userID ) , $this->_wwlcEmails );

			if ( $ajaxCall === true ) {

				header( 'Content-Type: application/json' ); // specify we return json
				echo json_encode( array(
					'status'        =>  'success',
					'redirect_url'  =>  home_url() . '/wp-admin/users.php?users_rejected=1'
				) );
				die();

			} else
				return true;

		}

		/**
		 * Activate user ajax interface.
		 *
		 * @param null $userID
		 * @param bool $ajaxCall
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_activateUser( $userID = null , $ajaxCall = true ) {

			if ( $ajaxCall === true )
				$userID = $_POST[ 'userID' ];

			$this->_wwlcUserAccount->activateUser( array( 'userID' => $userID ) );

			if ( $ajaxCall === true ) {

				header( 'Content-Type: application/json' ); // specify we return json
				echo json_encode( array(
					'status'        =>  'success',
					'redirect_url'  =>  home_url() . '/wp-admin/users.php?users_activated=1'
				) );
				die();

			} else
				return true;

		}

		/**
		 * Deactivate user ajax interface.
		 *
		 * @param null $userID
		 * @param bool $ajaxCall
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_deactivateUser( $userID = null , $ajaxCall = true ) {

			if ( $ajaxCall === true )
				$userID = $_POST[ 'userID' ];

			$this->_wwlcUserAccount->deactivateUser( array( 'userID' => $userID ) );

			if ( $ajaxCall === true ) {

				header( 'Content-Type: application/json' ); // specify we return json
				echo json_encode( array(
					'status'        =>  'success',
					'redirect_url'  =>  home_url() . '/wp-admin/users.php?users_deactivated=1'
				) );
				die();

			} else
				return true;

		}

        /**
         * Check if in wwlc license settings page.
         *
         * @return bool
         *
         * @since 1.1.1
         */
        public function checkIfInWWLCSettingsPage() {

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwlc' )
                return true;
            else
                return false;

        }

	}

}