<?php
if (! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WWLC_Forms' ) ) {

	class WWLC_Forms {

		private static $_instance;

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Initialization stuff ...

		}

		/**
		 * Singleton Pattern.
		 *
		 * @return WWLC_Forms
		 * @since 1.0.0
		 */
		public static function getInstance() {

			if ( ! self::$_instance instanceof self ) {
				self::$_instance = new self;
			}

			return self::$_instance;

		}

		/**
		 * Render registration form.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function registrationForm() {

			ob_start();

			global $WWLC_REGISTRATION_FIELDS;

            $custom_fields = $this->_getFormattedCustomFields();

            $registration_form_fields = array_merge( $WWLC_REGISTRATION_FIELDS , $custom_fields );

            usort( $registration_form_fields , array( $this , 'usortCallback' ) );

			// Load product listing template
			$this->_loadTemplate(
									'wwlc-registration-form.php',
									array(
										'formProcessor' =>  self::getInstance(),
										'formFields'    =>  $registration_form_fields
									),
									WWLC_TEMPLATES_ROOT_DIR
								);

			return ob_get_clean();

		}

        /**
         * Return formatted custom fields. ( Abide to the formatting of existing fields ).
         *
         * @return array
         *
         * @since 1.1.0
         */
        private function _getFormattedCustomFields () {

            $registrationFormCustomFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registrationFormCustomFields ) )
                $registrationFormCustomFields = array();

            $formattedRegistrationFormCustomFields = array();

            foreach ( $registrationFormCustomFields as $field_id => $customField ) {

                $formattedRegistrationFormCustomFields[] = array(
                    'label'         =>  $customField[ 'field_name' ],
                    'name'          =>  $field_id,
                    'id'            =>  $field_id,
                    'class'         =>  "wwlc_registration_field form_field wwlc_custom_field " . $customField[ 'field_type' ] . "_wwlc_custom_field",
                    'type'          =>  $customField[ 'field_type' ],
                    'required'      =>  ( $customField[ 'required' ] == '1' ) ? true : false,
                    'custom_field'  =>  true,
                    'active'        =>  ( $customField[ 'enabled' ] == '1' ) ? true : false,
                    'validation'    =>  array(),
                    'field_order'   =>  $customField[ 'field_order' ],
                    'attributes'    =>  isset( $customField[ 'attributes' ] ) ? $customField[ 'attributes' ] : array(),
                    'options'       =>  isset( $customField[ 'options' ] ) ? $customField[ 'options' ] : array(),
                    'placeholder'	=> 	isset( $customField[ 'field_placeholder' ] ) ? $customField[ 'field_placeholder' ] : "",
                );

            }

            return $formattedRegistrationFormCustomFields;

        }

        /**
         * Usort callback for sorting associative arrays.
         * Used for sorting field ordering on the form. (Registration form).
         *
         * @param $arr1
         * @param $arr2
         * @return int
         *
         * @since 1.1.0
         */
        public function usortCallback ( $arr1 , $arr2 ) {

            if ( $arr1[ 'field_order' ] == $arr2[ 'field_order' ] )
                return 0;

            return ( $arr1[ 'field_order' ] < $arr2[ 'field_order' ] ) ? -1 : 1;

        }

		/**
		 * Render log in form.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function loginForm() {

			ob_start();

			if ( is_user_logged_in() ) {

				$this->_loadTemplate(
					'wwlc-logout-page.php',
					array(),
					WWLC_TEMPLATES_ROOT_DIR
				);

			} else {

				$logInForm = wp_login_form( array(
					'echo'           	=> 	false,
					//'redirect'       => site_url( $_SERVER['REQUEST_URI'] ),
					'redirect'       	=>	get_permalink( woocommerce_get_page_id( 'shop' ) ),
					'form_id'        	=>	'wwlc_loginform',
					'label_username'	=>	apply_filters( 'wwlc_filter_login_field_label_username' , __( 'Username' , 'woocommerce-wholesale-lead-capture' ) ),
					'label_password' 	=>	apply_filters( 'wwlc_filter_login_field_label_password' , __( 'Password' , 'woocommerce-wholesale-lead-capture' ) ),
					'label_remember' 	=>	apply_filters( 'wwlc_filter_login_field_label_remember_me' , __( 'Remember Me' , 'woocommerce-wholesale-lead-capture' ) ),
					'label_log_in'   	=>	apply_filters( 'wwlc_filter_login_field_label_login' , __( 'Log In' , 'woocommerce-wholesale-lead-capture' ) ),
					'id_username'    	=>	'user_login',
					'id_password'    	=>	'user_pass',
					'id_remember'    	=>	'rememberme',
					'id_submit'      	=>	'wp-submit',
					'remember'       	=>	true,
					'value_username' 	=>	NULL,
					'value_remember' 	=>	false
				) );

				$this->_loadTemplate(
					'wwlc-login-form.php',
					array(
						'formProcessor' =>  self::getInstance(),
						'logInForm'     =>  $logInForm
					),
					WWLC_TEMPLATES_ROOT_DIR
				);

			}

			return ob_get_clean();

		}

		/**
		 * Get field label markup.
		 *
		 * @param $field
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function getLabel( $field ) {

			$requiredIndicator = '';

			if ( $field[ 'required' ] )
				$requiredIndicator = '<span class="required">*</span>';
			
			if( !empty( $field[ 'sub_fields' ] ) && $field[ 'id' ] == "wwlc_address" )
				$label = '<label for="wwlc_address">Address' . $requiredIndicator . '</label>';
			else
				$label = '<label for="' . $field[ 'id' ] . '">' . $field[ 'label' ] . $requiredIndicator . '</label>';

			$label = apply_filters( 'wwlc_filter_registration_form_label' , $label , $field );
			$label = apply_filters( 'wwlc_filter_registration_form_label_' . $field[ 'type' ] , $label , $field );
			$label = apply_filters( 'wwlc_filter_registration_form_label_' . $field[ 'id' ] , $label , $field );

			return $label;

		}

		/**
		 * Get field markup.
		 *
		 * @param $field
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function getField( $field ) {

			$formField = '';
            $requiredAttr = '';

            $placeholder = !empty( $field[ 'placeholder' ] ) ? "placeholder=' " . $field[ 'placeholder' ] . "'" : "";

            if ( $field[ 'required' ] )
                $requiredAttr = 'data-required="yes"';

			if ( $field[ 'type' ] == 'text' || $field[ 'type' ] == 'email' || $field[ 'type' ] == 'url' || $field[ 'type' ] == 'password' ) {

				$formField = '<input ' . $placeholder . ' type="' . $field[ 'type' ] . '" id="' . $field[ 'id' ] . '" class="input ' . $field[ 'class' ] . '" ' . $requiredAttr . ' >';

				if( !empty( $field[ 'sub_fields' ] ) && $field[ 'id' ] == "wwlc_address" ){

					foreach ( $field[ 'sub_fields' ] as $field ) {
						
						$placeholder = !empty( $field[ 'placeholder' ] ) ? "placeholder=' " . $field[ 'placeholder' ] . "'" : "";

						$reqAttr = $field[ 'required' ] ? 'data-required="yes"' : '';

						$formField .= '<br><br><input ' . $placeholder . ' type="' . $field[ 'type' ] . '" id="' . $field[ 'id' ] . '" class="input ' . $field[ 'class' ] . '" ' . $reqAttr . ' >';

					}

				}

			} elseif ( $field[ 'type' ] == 'textarea' ) {

				$formField = '<textarea ' . $placeholder . ' id="' . $field[ 'id' ] . '" class="' . $field[ 'class' ] . '" ' . $requiredAttr . ' cols="30" rows="5" ></textarea>';

			} elseif ( $field[ 'type' ] == 'number' ) {

                $formField = '<input ' . $placeholder . ' type="' . $field[ 'type' ] . '" min="' . $field[ 'attributes' ][ 'min' ] . '" max="' . $field[ 'attributes' ][ 'max' ] . '" step="' . $field[ 'attributes' ][ 'step' ] . '" id="' . $field[ 'id' ] . '" class="input ' . $field[ 'class' ] . '" ' . $requiredAttr . ' >';

            } elseif ( $field[ 'type' ] == 'select' ) {

                $formField = '<select id="' . $field[ 'id' ] . '" class="' . $field[ 'class' ] . '" ' . $requiredAttr . '>';

                foreach ( $field[ 'options' ] as $option )
                    $formField .= '<option value="' . $option[ 'value' ] . '" >' . $option[ 'text' ] . '</option>';

                $formField .= '</select>';

            } elseif ( $field[ 'type' ] == 'radio' ) {

                foreach ( $field[ 'options' ] as $option )
                    $formField .= '<span class="radio_options"><input type="radio" name="' . $field[ 'id' ] . '" class="' . $field[ 'class' ] . '" value="'. $option[ 'value' ] .'"><span>' . $option[ 'text' ] . '</span></span>';

            } elseif ( $field[ 'type' ] == 'checkbox' ) {

                foreach ( $field[ 'options' ] as $option )
                    $formField .= '<span class="checkbox_options"><input type="checkbox" name="' . $field[ 'id' ] . '" class="' . $field[ 'class' ] . '" value="'. $option[ 'value' ] .'"><span>' . $option[ 'text' ] . '</span></span>';

            }

			$formField = apply_filters( 'wwlc_filter_registration_form_field' , $formField , $field );
			$formField = apply_filters( 'wwlc_filter_registration_form_field_'.$field[ 'type' ] , $formField , $field );
			$formField = apply_filters( 'wwlc_filter_registration_form_field_'.$field[ 'id' ] , $formField , $field );

			return $formField;

		}

		/**
		 * Get registration form controls.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function getFormControls() {

			$termsAndCondition = '';
			if ( get_option( 'wwlc_general_show_terms_and_conditions' ) == 'yes' ) {

				$termsAndConditionPageUrl = trim( get_option( 'wwlc_general_terms_and_condition_page_url' ) );
				$termsAndCondition = '<p class="terms-and-condition-container">' . sprintf( __( 'By clicking register, you agree to the <a href="%1$s" target="_blank">Terms & Conditions</a>' , 'woocommerce-wholesale-lead-capture' ) , $termsAndConditionPageUrl ) . '</p>';

			}

			$register = '<p class="register-button-container"><input type="button" class="form-control button button-primary" id="register" value="' . __( 'Register' , 'woocommerce-wholesale-lead-capture' ) . '" ><span class="wwlc-loader"></span></p>';
			$register = apply_filters( 'wwlc_filter_registration_form_register_control' , $register );

			$logIn = '<a class="form-control" id="log-in" href="' . get_option( 'wwlc_general_login_page' ) . '" >' . __( 'Log In' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
			$logIn = apply_filters( 'wwlc_filter_registration_form_login_control' , $logIn );

			$lostPassword = '<a class="form-control" id="lost-password" href="' . wp_lostpassword_url() . '" >' . __( 'Lost Password' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
			$lostPassword = apply_filters( 'wwlc_filter_registration_form_lost_password_control' , $lostPassword );

			return $termsAndCondition . $register . $logIn . " " . $lostPassword;

		}

		/**
		 * Check if field is active.
		 *
		 * @param $field
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function isFieldActive( $field ) {

			return $field[ 'active' ];

		}

		/**
		 * Do registration form initialization. Adding nonces and honey pot.
		 *
		 * @since 1.0.0
		 */
		public function initializeRegistrationForm() {

			// echo nonce fields
			wp_nonce_field( 'wwlc_register_user', 'wwlc_register_user_nonce_field' );

			// echo honeypot fields
			$honeyPotFields = '<div style="display: none !important;" class="honeypot">'.
			                    '<label for="honeypot-field">' . __( 'Please Leave This Empty:' , 'woocommerce-wholesale-lead-capture' ) . '</label>'.
			                    '<input type="text" id="honeypot-field" name="honeypot-field" val="">'.
			                  '</div>';

			echo $honeyPotFields;

		}

		/**
		 * Create lead pages. Necessary pages for the plugin to work correctly.
		 *
		 * @param null $dummyArg
		 * @param bool $ajaxCall
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_createLeadPages( $dummyArg = null , $ajaxCall = true ) {

			$registrationPageCreationStatus = $this->createRegistrationPage();
			$logInPageCreationStatus = $this->createLogInPage();
			$thankYouPageCreationStatus = $this->createThankYouPage();

			if ( $registrationPageCreationStatus && $logInPageCreationStatus && $thankYouPageCreationStatus ) {

				if ( $ajaxCall === true ) {

					header( 'Content-Type: application/json' ); // specify we return json
					echo json_encode( array(
						'status'    =>  'success'
					) );
					die();

				} else
					return true;

			} else {

				if ( $ajaxCall === true ) {

					header( 'Content-Type: application/json' ); // specify we return json
					echo json_encode( array(
						'status'            =>  'failed',
						'error_message'     =>  __( 'Failed to create some or all wholesale lead pages' , 'woocommerce-wholesale-lead-capture' ),
						'registration_page' =>  $registrationPageCreationStatus,
						'login_page'        =>  $logInPageCreationStatus
					) );
					die();

				} else
					return false;

			}

		}

		/**
		 * Create registration page.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function createRegistrationPage() {

			if ( get_post_status( get_option( WWLC_OPTIONS_REGISTRATION_PAGE_ID ) ) !== 'publish' && !get_page_by_title( 'Wholesale Registration Page' ) ) {

				$wholesale_page = array(
										'post_content'  =>  '[wwlc_registration_form]',// The full text of the post.
										'post_title'    =>  __( 'Wholesale Registration Page' , 'woocommerce-wholesale-lead-capture' ),// The title of your post.
										'post_status'   =>  'publish',
										'post_type'     =>  'page'
									);

				$result = wp_insert_post( $wholesale_page );

				if ( $result === 0 || is_wp_error( $result ) ) {

					return false;

				} else {

					update_option( WWLC_OPTIONS_REGISTRATION_PAGE_ID , $result );
					return true;

				}

			} else
				return true;

		}

		/**
		 * Create log in page.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function createLogInPage() {

			if ( get_post_status( get_option( WWLC_OPTIONS_LOGIN_PAGE_ID ) ) !== 'publish' && !get_page_by_title( 'Wholesale Log In Page' ) ) {

				$wholesale_page = array(
					'post_content'  =>  '[wwlc_login_form]',// The full text of the post.
					'post_title'    =>  __( 'Wholesale Log In Page' , 'woocommerce-wholesale-lead-capture' ),// The title of your post.
					'post_status'   =>  'publish',
					'post_type'     =>  'page'
				);

				$result = wp_insert_post( $wholesale_page );

				if ( $result === 0 || is_wp_error( $result ) ) {

					return false;

				} else {

					update_option( WWLC_OPTIONS_LOGIN_PAGE_ID , $result );
					return true;

				}

			} else
				return true;

		}


		/**
		 * Create Thank You page.
		 *
		 * @return bool
		 * @since 1.4.0
		 */
		public function createThankYouPage() {

			if ( get_post_status( get_option( WWLC_OPTIONS_THANK_YOU_PAGE_ID ) ) !== 'publish' && !get_page_by_title( 'Wholesale Thank You Page' ) ) {

				$wholesale_page = array(
					'post_content'  =>  'Thank you for your registration. We will be in touch shortly to discuss your account.',// The full text of the post.
					'post_title'    =>  __( 'Wholesale Thank You Page' , 'woocommerce-wholesale-lead-capture' ),// The title of your post.
					'post_status'   =>  'publish',
					'post_type'     =>  'page'
				);

				$result = wp_insert_post( $wholesale_page );

				if ( $result === 0 || is_wp_error( $result ) ) {

					return false;

				} else {

					update_option( WWLC_OPTIONS_THANK_YOU_PAGE_ID , $result );
					return true;

				}

			} else
				return true;

		}

		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Utility Functions
	     |--------------------------------------------------------------------------------------------------------------
	     */

		/**
		 * @param $template String template path
		 * @param $options Array array of options
		 * @param $defaultTemplatePath String default template path
		 *
		 * @since 1.0.0
		 */
		private function _loadTemplate( $template , $options , $defaultTemplatePath ) {

			woocommerce_get_template( $template , $options , '' , $defaultTemplatePath );

		}

	}

}