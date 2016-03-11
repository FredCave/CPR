<?php
/**
 * WooCommerce Wholesale Lead Capture Settings
 *
 * @author      Rymera Web
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WWLC_Settings' ) ) {

	class WWLC_Settings extends WC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'wwlc_settings';
			$this->label = __( 'Wholesale Lead' , 'woocommerce-wholesale-lead-capture' );

			add_filter( 'woocommerce_settings_tabs_array' , array( $this, 'add_settings_page' ), 30 ); // 30 so it is after the emails tab
			add_action( 'woocommerce_settings_' . $this->id , array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id , array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id , array( $this, 'output_sections' ) );

			add_action( 'woocommerce_admin_field_wwlc_button' , array( $this, 'render_wwlc_button' ) );
			add_action( 'woocommerce_admin_field_wwlc_custom_fields_control' , array( $this, 'render_wwlc_custom_fields_control' ) );
			add_action( 'woocommerce_admin_field_wwlc_help_resources_controls' , array( $this , 'render_wwlc_help_resources_controls' ) );

			add_action( 'woocommerce_admin_field_wwlc_email_wysiwyg' , array( $this , 'render_email_wysiwyg_content' ) );

		}

		/**
		 * Get sections.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_sections() {

			$sections = array(
				''                                      =>  __( 'General' , 'woocommerce-wholesale-lead-capture' ),
				'wwlc_setting_fields_section'           =>  __( 'Built In Fields' , 'woocommerce-wholesale-lead-capture' ),
				'wwlc_setting_custom_fields_section'    =>  __( 'Custom Fields' , 'woocommerce-wholesale-lead-capture' ),
				'wwlc_setting_email_section'            =>  __( 'Emails' , 'woocommerce-wholesale-lead-capture' ),
				'wwlc_settings_help_section'            =>  __( 'Help', 'woocommerce-wholesale-lead-capture' ),
			);

			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );

		}

		/**
		 * Output the settings.
		 *
		 * @since 1.0.0
		 */
		public function output() {

			global $current_section;

			$settings = $this->get_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );

		}

		/**
		 * Save settings.
		 *
		 * @since 1.0.0
		 */
		public function save() {

			global $current_section;

			$settings = $this->get_settings( $current_section );

           	// Filter wysiwyg content so it gets stored properly after sanitization
           	if( !empty( $_POST[ 'EmailContent' ] ) && isset( $_POST[ 'EmailContent' ] ) ){

	           	foreach ( $_POST[ 'EmailContent' ] as $index => $content ) {

	         		$_POST[$index] = htmlentities (wpautop( $content ) );

	           	}

           	}

			WC_Admin_Settings::save_fields( $settings );

		}

		/**
		 * Get settings array.
		 *
		 * @param string $current_section
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function get_settings( $current_section = '' ) {

			if ( $current_section == 'wwlc_settings_help_section' ) {

				// Help Section
				$settings = apply_filters( 'wwlc_settings_help_section_settings', $this->_get_help_section_settings() );

			} elseif ( $current_section == 'wwlc_setting_fields_section' ) {

				// Built In Fields Section
				$settings = apply_filters( 'wwlc_settings_fields_section_settings', $this->_get_fields_section_settings() );

			} elseif ( $current_section == 'wwlc_setting_custom_fields_section' ) {

                // Custom Fields Section
                $settings = apply_filters( 'wwlc_setting_custom_fields_section_settings' , $this->_get_custom_fields_section_settings() );

            } elseif ( $current_section == 'wwlc_setting_email_section' ) {

				// Email Section
				$settings = apply_filters( 'wwlc_settings_email_section_settings', $this->_get_email_section_settings() );

			} else {

				// General Settings
				$settings = apply_filters( 'wwlc_settings_general_section_settings', $this->_get_general_section_settings() );

			}

			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

		}




		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | Section Settings
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Get general section settings.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		private function _get_general_section_settings() {

			// Get all user roles
			global $wp_roles;

			if( !isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

			$allUserRoles = $wp_roles->get_names();

			// Get all pages
			$allPages = array( '' => '' );
			$allPagesQuery = new WP_Query( array(
										'post_type'			=>	'page',
										'post_status'		=>	'publish',
										'posts_per_page'	=>	-1
									) );
			while ( $allPagesQuery->have_posts() ) {

				$allPagesQuery->the_post();
				$allPages[ get_permalink() ] = get_the_title();

			}

			return array(

				array(
					'title' =>  __( 'General Options' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  '',
					'id'    =>  'wwlc_general_main_title'
				),

				array(
					'title'             =>  __( 'New Lead Role' , 'woocommerce-wholesale-lead-capture' ),
					'type'              =>  'select',
					'desc'              =>  __( 'User role that will be assigned to new approved users. Newly registered users will have role of unapproved' , 'woocommerce-wholesale-lead-capture' ),
					'desc_tip'          =>  true,
					'id'                =>  'wwlc_general_new_lead_role',
					'class'             =>  'chosen_select',
					'css'               =>  'min-width:300px;',
					'custom_attributes'	=>	array(
													'data-placeholder'  =>  __( 'Select Some User Roles...' , 'woocommerce-wholesale-lead-capture' )
												),
					'options'           =>  $allUserRoles
				),

				array(
					'title' =>  __( 'Show Terms & Conditions' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'checkbox',
					'desc'  =>  __( 'If checked, it will show a link to Terms & Conditions page on registration form. Please make sure to provide value to <b>Terms & Conditions Page</b> option below if you are going to enable this option' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_general_show_terms_and_conditions'
				),

				array(
					'title' =>  __( 'Auto Approve New Leads' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'checkbox',
					'desc'  =>  __( 'If checked, it will auto-approve all new registrations' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_general_auto_approve_new_leads'
				),

				array(
					'title'     =>  __( 'Log In Redirect Page' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'select',
					'desc'      =>  __( 'The page where wholesale users get redirected after successful login' , 'woocommerce-wholesale-lead-capture' ),
					'default'	=>	'',
					'desc_tip'  =>  true,
					'id'        =>  'wwlc_general_login_redirect_page',
					'css'		=>	'min-width: 350px',
					'options'   =>  $allPages
				),

				array(
					'title'     =>  __( 'Log Out Redirect Page' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'select',
					'desc'      =>  __( 'The page where wholesale users get redirected after logging out' , 'woocommerce-wholesale-lead-capture' ),
					'default'	=>	'',
					'desc_tip'  =>  true,
					'id'        =>  'wwlc_general_logout_redirect_page',
					'css'		=>	'min-width: 350px',
					'options'   =>  $allPages
				),

				array(
					'title'     =>  __( 'Log In Page' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'select',
					'desc'      =>  __( 'The log in page' , 'woocommerce-wholesale-lead-capture' ),
					'default'	=>	'',
					'desc_tip'  =>  true,
					'id'        =>  'wwlc_general_login_page',
					'css'		=>	'min-width: 350px',
					'options'   =>  $allPages
				),

				array(
					'title'     =>  __( 'Registration Page' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'select',
					'desc'      =>  __( 'The registration page' , 'woocommerce-wholesale-lead-capture' ),
					'default'	=>	'',
					'desc_tip'  =>  true,
					'id'        =>  'wwlc_general_registration_page',
					'css'		=>	'min-width: 350px',
					'options'   =>  $allPages
				),

				array(
					'title'     =>  __( 'Registration Thank You Page' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'select',
					'desc'      =>  __( 'The page in which users who successfully registers gets redirected to. You will have to create this page' , 'woocommerce-wholesale-lead-capture' ),
					'default'	=>	'',
					'desc_tip'  =>  true,
					'id'        =>  'wwlc_general_registration_thankyou',
					'css'		=>	'min-width: 350px',
					'options'   =>  $allPages
				),

				array(
					'title'     =>  __( 'Terms & Conditions Page', 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'select',
					'desc'      =>  __( 'The terms & conditions page. You will have to create this page' , 'woocommerce-wholesale-lead-capture' ),
					'default'	=>	'',
					'desc_tip'  =>  true,
					'id'        =>  'wwlc_general_terms_and_condition_page_url',
					'css'		=>	'min-width: 350px',
					'options'   =>  $allPages
				),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwlc_general_sectionend'
				)

			);

		}

		/**
		 * Get fields section settings.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		private function _get_fields_section_settings() {

			return array(

				array(
					'title' =>  __( 'Fields Options', 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  '',
					'id'    =>  'wwlc_fields_main_title'
				),

				array(
					'title' =>  __( 'Registration Form Fields', 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  '',
					'id'    =>  'wwlc_fields_registration_fields_main_title'
				),

                array(
                    'title' =>  __( 'First Name' , 'woocommerce-wholesale-lead-capture' ),
                    'type'  =>  'number',
                    'desc'  =>  __( 'First name field form order' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_first_name_field_order',
                    'css'   =>  'width:50px;',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1
                    )
                ),

                array(
                    'title' =>  '',
                    'type'  =>  'text',
                    'desc'  =>  __( 'First Name placeholder' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_first_name_field_placeholder',
                    'css'   =>  'width:300px;'
                ),

                array(
                    'title' =>  __( 'Last Name' , 'woocommerce-wholesale-lead-capture' ),
                    'type'  =>  'number',
                    'desc'  =>  __( 'Last name field form order' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_last_name_field_order',
                    'css'   =>  'width:50px;',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1
                    )
                ),

				array(
                    'title' =>  '',
                    'type'  =>  'text',
                    'desc'  =>  __( 'Last Name placeholder' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_last_name_field_placeholder',
                    'css'   =>  'width:300px;'
                ),

                array(
                    'title' =>  __( 'Phone Field' , 'woocommerce-wholesale-lead-capture' ),
                    'type'  =>  'checkbox',
                    'desc'  =>  __( 'Make phone field required' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_require_phone_field'
                ),

                array(
                    'title' =>  '',
                    'type'  =>  'number',
                    'desc'  =>  __( 'Phone field form order' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_phone_field_order',
                    'css'   =>  'width:50px;',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1
                    )
                ),

				array(
                    'title' =>  '',
                    'type'  =>  'text',
                    'desc'  =>  __( 'First name placeholder' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_phone_field_placeholder',
                    'css'   =>  'width:300px;'
                ),

                array(
                    'title' =>  __( 'Email Field' , 'woocommerce-wholesale-lead-capture' ),
                    'type'  =>  'number',
                    'desc'  =>  __( 'Email field form order' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_email_field_order',
                    'css'   =>  'width:50px;',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1
                    )
                ),

				array(
                    'title' =>  '',
                    'type'  =>  'text',
                    'desc'  =>  __( 'Email placeholder' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_email_field_placeholder',
                    'css'   =>  'width:300px;'
                ),

				array(
					'title' =>  __( 'Company Name Field' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'checkbox',
					'desc'  =>  __( 'Add company name field to registration form' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_fields_activate_company_name_field'
				),

				array(
					'title' =>  '',
					'type'  =>  'checkbox',
					'desc'  =>  __( 'Make company name field required. Only used if company name field is active.' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_fields_require_company_name_field'
				),

                array(
                    'title' =>  '',
                    'type'  =>  'number',
                    'desc'  =>  __( 'Company Name field form order' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_company_name_field_order',
                    'css'   =>  'width:50px;',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1
                    )
                ),

				array(
                    'title' =>  '',
                    'type'  =>  'text',
                    'desc'  =>  __( 'Company placeholder' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_company_field_placeholder',
                    'css'   =>  'width:300px;'
                ),

				array(
					'title' =>  __( 'Address Field' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'checkbox',
					'desc'  =>  __( 'Add address field to registration form' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_fields_activate_address_field'
				),

				array(
					'title' =>  '',
					'type'  =>  'checkbox',
					'desc'  =>  __( 'Make address field required. Only used if address field is active.' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_fields_require_address_field'
				),

                array(
                    'title' =>  '',
                    'type'  =>  'number',
                    'desc'  =>  __( 'Address field form order' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_address_field_order',
                    'css'   =>  'width:50px;',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1
                    )
                ),

				array(
					'title' =>  __( 'Password' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'checkbox',
					'desc'  =>  __( 'Add password field to registration form' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_fields_activate_password_field'
				),

				array(
					'title' =>  '',
					'type'  =>  'checkbox',
					'desc'  =>  __( 'Make password field required. Only used if password field is active.' , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_fields_require_password_field'
				),

                array(
                    'title' =>  '',
                    'type'  =>  'number',
                    'desc'  =>  __( 'Password field form order' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_password_field_order',
                    'css'   =>  'width:50px;',
                    'custom_attributes' => array(
                        'min'  => 0,
                        'step' => 1
                    )
                ),

				array(
                    'title' =>  '',
                    'type'  =>  'text',
                    'desc'  =>  __( 'Password placeholder' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_fields_password_field_placeholder',
                    'css'   =>  'width:300px;'
                ),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwlc_fields_sectionend'
				)

			);

		}

        /**
         * Get custom fields section settings.
         *
         * @return array
         * @since 1.1.0
         */
        private function _get_custom_fields_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'Custom Fields Options' , 'woocommerce-wholesale-lead-capture' ),
                    'type'  =>  'title',
                    'desc'  =>  __( 'Here you can add additional fields to be added on the lead capture registration form.' , 'woocommerce-wholesale-lead-capture' ),
                    'id'    =>  'wwlc_custom_fields_main_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'wwlc_custom_fields_control',
                    'desc'  =>  '',
                    'id'    =>  'wwlc_custom_fields_custom_control',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwlc_custom_fields_sectionend'
                )

            );

        }

		/**
		 * Get email section settings.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		private function _get_email_section_settings() {

			global $newUserAdminNotificationEmailDefault, $newUserAdminNotificationEmailAutoApprovedDefault, $newUserEmailDefault, $approvedEmailDefault, $rejectedEmailDefault;

			return array(

				array(
					'title' =>  __( 'Emails Options' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  '',
					'id'    =>  'wwlc_emails_main_title'
				),

                array(
                    'title'     =>  __( 'Wrap emails with WooCommerce email header and footer?', 'woocommerce-wholesale-lead-capture' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  'If enabled, the emails will be wrapped with WooCommerce email header and footer.',
                    'id'        =>  'wwlc_email_wrap_wc_header_footer'
                ),

				array(
					'title'	=>  __( 'Admin Email Recipients' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  'Affects admin emails only',
					'id'    =>  'wwlc_admin_emails_recipients_title',
				),

				array(
					'title'     =>  __( 'Main Recipient' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'text',
					'desc'      =>  'If blank, then WordPress admin email will be used',
					'id'        =>  'wwlc_emails_main_recipient',
					'css'		=>	'min-width:600px'
				),

				array(
					'title'     =>  __( 'Carbon Copy (CC)' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'text',
					'desc'      =>  '',
					'id'        =>  'wwlc_emails_cc',
					'css'		=>	'min-width:600px'
				),

				array(
					'title'     =>  __( 'Blind Carbon Copy (BCC)' , 'woocommerce-wholesale-lead-capture' ),
					'type'      =>  'text',
					'desc'      =>  '',
					'id'        =>  'wwlc_emails_bcc',
					'css'		=>	'min-width:600px'
				),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwof_emails_template_divider1_sectionend'
				),

				array(
					'title' =>  __( 'New User Admin Notification Email Template' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  __( 'Email sent to admin on every successful new user registration. <br><br>You can use these templates tags: <b>{user_management_url}</b>, <b>{wholesale_login_url}</b>, <b>{site_name}</b>, <b>{full_name}</b>, <b>{first_name}</b>, <b>{last_name}</b>, <b>{username}</b>, <b>{password}</b>, <b>{email}</b>, <b>{phone}</b>, <b>{company_name}</b>, <b>{address}</b> '.$this->display_custom_field_template_tags() , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_emails_new_user_admin_notification_template_title'
				),

				array(
					'title'		=>	__( 'Subject' , 'woocommerce-wholesale-lead-capture' ),
					'type'		=>	'text',
					'desc'		=>	'',
					'id'		=>	'wwlc_emails_new_user_admin_notification_subject',
					'default'	=>	__( 'New User Registration' , 'woocommerce-wholesale-lead-capture' ),
					'css'		=>	'min-width: 600px'
				),

                array(
                    'title'     => '',
                    'type'      => 'wwlc_email_wysiwyg',
                    'desc'      => '',
                    'id'        => 'wwlc_emails_new_user_admin_notification_template',
                    'css'       => '',
                    'default'   => $newUserAdminNotificationEmailDefault
                ),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwof_emails_template_divider2_sectionend'
				),

				array(
					'title' =>  __( 'New User Admin Notification Email Template ( Auto Approved )' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  __( 'Email sent to admin on every successful new user registration and is auto approved. <br><br>You can use these templates tags: <b>{user_management_url}</b>, <b>{wholesale_login_url}</b>, <b>{site_name}</b>, <b>{full_name}</b>, <b>{first_name}</b>, <b>{last_name}</b>, <b>{username}</b>, <b>{password}</b>, <b>{email}</b>, <b>{phone}</b>, <b>{company_name}</b>, <b>{address}</b> '.$this->display_custom_field_template_tags() , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_emails_new_user_admin_notification_auto_approved_template_title'
				),

				array(
					'title'		=>	__( 'Subject' , 'woocommerce-wholesale-lead-capture' ),
					'type'		=>	'text',
					'desc'		=>	'',
					'id'		=>	'wwlc_emails_new_user_admin_notification_auto_approved_subject',
					'default'	=>	__( 'New User Registered And Approved' , 'woocommerce-wholesale-lead-capture' ),
					'css'		=>	'min-width: 600px'
				),

                array(
                    'title'     => '',
                    'type'      => 'wwlc_email_wysiwyg',
                    'desc'      => '',
                    'id'        => 'wwlc_emails_new_user_admin_notification_auto_approved_template',
                    'css'       => '',
                    'default'   => $newUserAdminNotificationEmailAutoApprovedDefault
                ),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwof_emails_template_divider3_sectionend'
				),

				array(
					'title' =>  __( 'New User Email Template', 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  __( 'Email sent to new users after successful registration. <br><br>You can use these templates tags: <b>{wholesale_login_url}</b>, <b>{site_name}</b>, <b>{full_name}</b>, <b>{first_name}</b>, <b>{last_name}</b>, <b>{username}</b>, <b>{email}</b>, <b>{phone}</b>, <b>{company_name}</b>, <b>{address}</b> '.$this->display_custom_field_template_tags() , 'woocommerce-wholesale-lead-capture' ), 'id'    =>  'wwlc_emails_new_user_template_title'
				),

				array(
					'title'		=>	__( 'Subject' , 'woocommerce-wholesale-lead-capture' ),
					'type'		=>	'text',
					'desc'		=>	'',
					'id'		=>	'wwlc_emails_new_user_subject',
					'default'	=>	__( 'Registration Successful' , 'woocommerce-wholesale-lead-capture' ),
					'css'		=>	'min-width: 600px'
				),

                array(
                    'title'     => '',
                    'type'      => 'wwlc_email_wysiwyg',
                    'desc'      => '',
                    'id'        => 'wwlc_emails_new_user_template',
                    'css'       => '',
                    'default'   => $newUserEmailDefault
                ),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwof_emails_template_divider4_sectionend'
				),

				array(
					'title' =>  __( 'Approval Email Template' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  __( 'Email sent to users whose been approved. <br><br>You can use these templates tags: <b>{wholesale_login_url}</b>, <b>{site_name}</b>, <b>{full_name}</b>, <b>{first_name}</b>, <b>{last_name}</b>, <b>{username}</b>, <b>{password}</b>, <b>{email}</b>, <b>{phone}</b>, <b>{company_name}</b>, <b>{address}</b> '.$this->display_custom_field_template_tags() , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_emails_approval_email_template_title'
				),

				array(
					'title'		=>	__( 'Subject' , 'woocommerce-wholesale-lead-capture' ),
					'type'		=>	'text',
					'desc'		=>	'',
					'id'		=>	'wwlc_emails_approval_email_subject',
					'default'	=>	__( 'Registration Approved' , 'woocommerce-wholesale-lead-capture' ),
					'css'		=>	'min-width: 600px'
				),

                array(
                    'title'     => '',
                    'type'      => 'wwlc_email_wysiwyg',
                    'desc'      => '',
                    'id'        => 'wwlc_emails_approval_email_template',
                    'css'       => '',
                    'default'   => $approvedEmailDefault
                ),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwof_emails_template_divider5_sectionend'
				),

				array(
					'title' =>  __( 'Rejected Email Template' , 'woocommerce-wholesale-lead-capture' ),
					'type'  =>  'title',
					'desc'  =>  __( 'Email sent to users whose been rejected. <br><br>You can use these templates tags: <b>{wholesale_login_url}</b>, <b>{site_name}</b>, <b>{full_name}</b>, <b>{first_name}</b>, <b>{last_name}</b>, <b>{email}</b>, <b>{phone}</b>, <b>{company_name}</b>, <b>{address}</b> '.$this->display_custom_field_template_tags() , 'woocommerce-wholesale-lead-capture' ),
					'id'    =>  'wwlc_emails_rejected_email_template_title'
				),

				array(
					'title'		=>	__( 'Subject' , 'woocommerce-wholesale-lead-capture' ),
					'type'		=>	'text',
					'desc'		=>	'',
					'id'		=>	'wwlc_emails_rejected_email_subject',
					'default'	=>	__( 'Registration Rejected' , 'woocommerce-wholesale-lead-capture' ),
					'css'		=>	'min-width: 600px'
				),

                array(
                    'title'     => '',
                    'type'      => 'wwlc_email_wysiwyg',
                    'desc'      => '',
                    'id'        => 'wwlc_emails_rejected_email_template',
                    'css'       => '',
                    'default'   => $rejectedEmailDefault
                ),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwlc_emails_sectionend'
				)

			);

		}

		/**
		 * Get help section settings.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		private function _get_help_section_settings () {

			return array(

				array(
					'title' =>  __( 'Help Options' , 'woocommerce-wholesale-order-form' ),
					'type'  =>  'title',
					'desc'  =>  '',
					'id'    =>  'wwlc_help_main_title'
				),

				array(
					'name'  =>  '',
					'type'  =>  'wwlc_help_resources_controls',
					'desc'  =>  '',
					'id'    =>  'wwlc_help_help_resources',
				),

				array(
					'title' =>  __( 'Create Necessary Pages' , 'woocommerce-wholesale-order-form' ),
					'type'  =>  'wwlc_button',
					'desc'  =>  __( 'Registration, Log In Form and Thank You Page' , 'woocommerce-wholesale-order-form' ),
					'id'    =>  'wwlc_help_create_wwlc_pages',
					'class' =>  'button button-primary'
				),

				array(
					'type'  =>  'sectionend',
					'id'    =>  'wwlc_help_sectionend'
				)

			);

		}




		/*
         |--------------------------------------------------------------------------------------------------------------
         | Custom Settings Fields
         |--------------------------------------------------------------------------------------------------------------
         */

		/**
		 * Render custom setting field ( wwlc button )
		 *
		 * @param $value
		 * @since 1.0.0
		 */
		public function render_wwlc_button( $value ) {

			// Change type accordingly
			$type = $value[ 'type' ];
			if ( $type == 'wwlc_button' )
				$type = 'button';

			// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $value[ 'custom_attributes' ] ) && is_array( $value[ 'custom_attributes' ] ) ) {
				foreach ( $value[ 'custom_attributes' ] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling
			if ( true === $value[ 'desc_tip' ] ) {
				$description = '';
				$tip = $value[ 'desc' ];
			} elseif ( ! empty( $value[ 'desc_tip' ] ) ) {
				$description = $value[ 'desc' ];
				$tip = $value[ 'desc_tip' ];
			} elseif ( ! empty( $value[ 'desc' ] ) ) {
				$description = $value[ 'desc' ];
				$tip = '';
			} else {
				$description = $tip = '';
			}

			ob_start(); ?>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
					<?php echo $tip; ?>
				</th>
				<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
					<input
						name="<?php echo esc_attr( $value['id'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						type="<?php echo esc_attr( $type ); ?>"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						value="<?php echo esc_attr( 'Create Lead Pages' ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>"
						<?php echo implode( ' ', $custom_attributes ); ?>
						/>
					<span class="spinner" style="margin-top: 3px; float: none;"></span>
					<span class="desc"><?php echo $description; ?></span>

				</td>
			</tr>

			<?php echo ob_get_clean();

		}

        /**
         * Render custom fields control ( custom fields section )
         *
         * @since 1.1.0
         */
        public function render_wwlc_custom_fields_control () {

            $custom_fields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $custom_fields ) )
                $custom_fields = array();

            ?>
            <tr valign="top">
                <th colspan="2" scope="row" class="titledesc">
                    <div class="custom-field-controls">

                        <div class="field-container text-field-container">

                            <label for="wwlc_cf_field_name"><?php _e( 'Field Name' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                            <input type="text" id="wwlc_cf_field_name"/>

                        </div>

                        <div class="field-container text-field-container">

                            <label for="wwlc_cf_field_id"><?php _e( 'Field ID' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                            <span>wwlc_cf_</span><input type="text" id="wwlc_cf_field_id"/>
                            <p class="desc"><?php _e( 'Must be unique. Letters, numbers and underscores only. Value will be automatically prepended with "wwlc_cf_"' , 'woocommerce-wholesale-lead-capture' ); ?></p>

                        </div>

                        <div class="field-container select-field-container">

                            <label for="wwlc_cf_field_type"><?php _e( 'Field Type' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                            <select id="wwlc_cf_field_type">
                                <option value="text"><?php _e( 'Text' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                                <option value="textarea"><?php _e( 'Text Area' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                                <option value="number"><?php _e( 'Number' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                                <option value="email"><?php _e( 'Email' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                                <option value="url"><?php _e( 'Url' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                                <option value="select"><?php _e( 'Select' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                                <option value="radio"><?php _e( 'Radio' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                                <option value="checkbox"><?php _e( 'Checkbox' , 'woocommerce-wholesale-lead-capture' ); ?></option>
                            </select>

                        </div>

                        <div class="field-container attributes-container numeric-field-attributes-container">
                            <div>
                                <label for="wwlc_cf_attrib_numeric_min"><?php _e( 'Min:' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                                <input type="number" id="wwlc_cf_attrib_numeric_min" class="wwlc_cf_attrib_numeric" />
                            </div>

                            <div>
                                <label for="wwlc_cf_attrib_numeric_max"><?php _e( 'Max:' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                                <input type="number" id="wwlc_cf_attrib_numeric_max" class="wwlc_cf_attrib_numeric" />
                            </div>

                            <div>
                                <label for="wwlc_cf_attrib_numeric_step"><?php _e( 'Step:' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                                <input type="number" id="wwlc_cf_attrib_numeric_step" class="wwlc_cf_attrib_numeric" />
                            </div>
                        </div>

                        <div class="field-container options-container select-field-options-container">
                            <strong><?php _e( 'Options' , 'woocommerce-wholesale-lead-capture' ); ?></strong>
                            <ul class="options-list"></ul>
                        </div>

                        <div class="field-container options-container radio-field-options-container">
                            <strong><?php _e( 'Options' , 'woocommerce-wholesale-lead-capture' ); ?></strong>
                            <ul class="options-list"></ul>
                        </div>

                        <div class="field-container options-container checkbox-field-options-container">
                            <strong><?php _e( 'Options' , 'woocommerce-wholesale-lead-capture' ); ?></strong>
                            <ul class="options-list"></ul>
                        </div>

                        <div class="field-container number-field-container">

                            <label for="wwlc_cf_field_order"><?php _e( 'Field Order' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                            <input type="number" min="0" step="1" id="wwlc_cf_field_order"/>

                        </div>

                        <div class="field-container check-field-container">

                            <label for="wwlc_cf_required_field"><?php _e( 'Required' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                            <input type="checkbox" id="wwlc_cf_required_field"/>

                        </div>

                        <div class="field-container number-field-container">

                            <label for="wwlc_cf_placeholder"><?php _e( 'Placeholder' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                            <input type="text" id="wwlc_cf_field_placeholder"/>

                        </div>

                        <div class="field-container check-field-container">

                            <label for="wwlc_cf_enabled_field"><?php _e( 'Enabled' , 'woocommerce-wholesale-lead-capture' ); ?></label>
                            <input type="checkbox" id="wwlc_cf_enabled_field"/>

                        </div>

                        <div style="clear: both; float: none; display: block;"></div>

                    </div>

                    <div class="button-controls add-mode">

                        <input type="button" id="cancel-edit-custom-field" class="button button-secondary" value="<?php _e( 'Cancel' , 'woocommerce-wholesale-lead-capture' ); ?>"/>
                        <input type="button" id="save-custom-field" class="button button-primary" value="<?php _e( 'Save Custom Field' , 'woocommerce-wholesale-lead-capture' ); ?>"/>
                        <input type="button" id="add-custom-field" class="button button-primary" value="<?php _e( 'Add Custom Field' , 'woocommerce-wholesale-lead-capture' ); ?>"/>
                        <span class="spinner"></span>

                        <div style="clear: both; float: none; display: block;"></div>

                    </div>

                    <table id="wholesale-lead-capture-custom-fields" class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th><?php _e( 'Field Name' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Field ID' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Field Type' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Required' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Field Order' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Placeholder' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Enabled' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th></th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th><?php _e( 'Field Name' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Field ID' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Field Type' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Required' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Field Order' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Placeholder' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th><?php _e( 'Enabled' , 'woocommerce-wholesale-lead-capture' ); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>

                        <tbody>

                        <?php
                        if ( $custom_fields ) {

                            $itemNumber =   0;

                            foreach( $custom_fields as $custom_field_id => $custom_field ) {
                                $itemNumber++;

                                if ( $itemNumber % 2 == 0 ) { // even  ?>
                                    <tr class="even">
                                <?php } else { // odd ?>
                                    <tr class="odd alternate">
                                <?php } ?>

                                    <td class="meta hidden"></td>
                                    <td class="wwlc_cf_td_field_name"><?php echo $custom_field[ 'field_name' ]; ?></td>
                                    <td class="wwlc_cf_td_field_id"><?php echo $custom_field_id; ?></td>
                                    <td class="wwlc_cf_td_field_type"><?php echo $custom_field[ 'field_type' ]; ?></td>
                                    <td class="wwlc_cf_td_required"><?php echo $custom_field[ 'required' ] ? 'true' : 'false'; ?></td>
                                    <td class="wwlc_cf_td_field_order"><?php echo $custom_field[ 'field_order' ]; ?></td>
                                    <td class="wwlc_cf_td_field_placeholder"><?php echo $custom_field[ 'field_placeholder' ]; ?></td>
                                    <td class="wwlc_cf_td_enabled"><?php echo $custom_field[ 'enabled' ] ? 'true' : 'false'; ?></td>
                                    <td class="controls">
                                        <a class="edit dashicons dashicons-edit"></a>
                                        <a class="delete dashicons dashicons-no"></a>
                                    </td>

                                </tr>
                            <?php
                            }

                        } else { ?>
                            <tr class="no-items">
                                <td class="colspanchange" colspan="7"><?php _e( 'No Custom Fields Found' , 'woocommerce-wholesale-lead-capture' ); ?></td>
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
            <?php
        }

		/**
		 * Render help resource controls.
		 *
		 * @param $value
		 *
		 * @since 1.3.1
		 */
		public function render_wwlc_help_resources_controls ( $value ) {
			?>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for=""><?php _e( 'Knowledge Base' , 'woocommerce-wholesale-lead-capture' ); ?></label>
				</th>
				<td class="forminp forminp-<?php echo sanitize_title( $value[ 'type' ] ); ?>">
					<?php echo sprintf( __( 'Looking for documentation? Please see our growing <a href="%1$s" target="_blank">Knowledge Base</a>' , 'woocommerce-wholesale-lead-capture' ) , "https://wholesalesuiteplugin.com/knowledge-base/?utm_source=Lead%20Capture%20Plugin&utm_medium=Settings&utm_campaign=Knowledge%20Base%20" ); ?>
				</td>
			</tr>

			<?php
		}

		/**
		 * Display custom field template tags.
		 *
		 * @since 1.4.0
		 */
		public function display_custom_field_template_tags () {
			
			$customFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );

			if ( is_array( $customFields ) && !empty( $customFields ) ){

				$content = "</p> <div class='wwlc_custom_field_tags_wrapper'><a href='#' class='wwlc_custom_field close'><b>+ Show Custom Field Template Tags</b></a>";
				$content .= '<div class="wwlc_custom_field_template_tags" style="margin-top: 6px; padding: 10px; background: #e5e5e5;">';
				$content .= "Below is a list of template tags you can use to present data from custom fields:<br/><br/>";

				foreach ( $customFields as $field_id => $field ) {
					$content .= "<b>{custom_field:".$field_id."}</b><br/>";
				}

				$content .= '</div></div>';
				
				return $content;

			}
		}

        /**
         * Render custom setting wysiwyg field for email content
         *
         * @param $data
         * @since 1.4.0
		 * @since 1.4.1 Bug Fix. Do not use expression inside empty function, on php version prior to 5.5 this will trigger a fatal error.
         */
        public function render_email_wysiwyg_content( $data ) { ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for=""><?php _e( 'Email Content' , 'email-cart-for-woocommerce-premium' ); ?></label>
                </th>
                <td class="forminp forminp-<?php echo sanitize_title( $data[ 'type' ] ); ?>">
                	<style type="text/css"><?php echo "div#wp-" . $data[ 'id' ] . "-wrap{width: 70% !important;}"; ?></style>
                    <?php
						$data_id_option = get_option( $data['id'] );

                        $ecwpEditorVal = !empty( $data_id_option ) ? $data_id_option : $data[ 'default' ];
                        wp_editor( html_entity_decode( $ecwpEditorVal ), $data[ 'id' ], array(
                            'wpautop' 		=> true,
                            'textarea_name'	=> "EmailContent[" . $data[ 'id' ] . "]"
                        ) );
                    ?>
                </td>
            </tr>

            <?php
        }
	}
}

return new wwlc_Settings();