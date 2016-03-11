<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_Emails' ) ) {

	class WWLC_Emails {

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
		 * Get password reset url.
		 *
		 * @param $user_login
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		private function _getResetPasswordURL( $user_login ) {

			global $wpdb, $wp_hasher;

			$user_login = sanitize_text_field( $user_login );

			if ( empty( $user_login ) ) {

				return false;

			} elseif ( strpos( $user_login, '@' ) ) {

				$user_data = get_user_by( 'email' , trim( $user_login ) );
				if ( empty( $user_data ) )
					return false;

			} else {

				$login = trim( $user_login );
				$user_data = get_user_by( 'login' , $login );

			}

			do_action( 'lostpassword_post' );


			if ( !$user_data ) return false;

			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;

			do_action( 'retrieve_password' , $user_login );

			$allow = apply_filters( 'allow_password_reset' , true , $user_data->ID );

			if ( !$allow )
				return false;
			elseif ( is_wp_error( $allow ) )
				return false;

			$key = wp_generate_password( 20 , false );
			do_action( 'retrieve_password_key' , $user_login, $key );

			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8 , true );
			}

			$hashed = $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users , array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

			return network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ) , 'login' );

		}

		/**
		 * Parse email contents, replace email template tags with appropriate values.
		 *
		 * @param      $userID
		 * @param      $content
		 * @param null $password
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		private function _parseEmailContent( $userID , $content , $password = null ) {

			$newUser = get_userdata( $userID );
			$customFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );

			$findReplace[ 'wholesale_login_url' ] = get_option( 'wwlc_general_login_page' );
			$findReplace[ 'reset_password_url' ] = $this->_getResetPasswordURL( $newUser->data->user_login );
			$findReplace[ 'site_name' ] = get_bloginfo( 'name' );
			$findReplace[ 'full_name' ] = $newUser->first_name . ' ' . $newUser->last_name ;
			$findReplace[ 'user_management_url' ] = get_admin_url( null , 'users.php' );

			$capability = maybe_unserialize( get_user_meta( $userID, "wp_capabilities", true) );
			// If {password} tag is used in "New User Email Template"
			if ( isset( $capability ) && ( isset( $capability[ 'wwlc_unapproved' ] ) && $capability[ 'wwlc_unapproved' ] == true ) && ( isset( $capability[ 'wwlc_unmoderated' ] ) && $capability[ 'wwlc_unmoderated' ] == true ) )
				$findReplace[ 'password' ] = "Password will be generated and sent on approval.";
			else
				$findReplace[ 'password' ] = $password;

			$findReplace[ 'email' ] = $newUser->user_email;
			$findReplace[ 'first_name' ] = $newUser->first_name;
			$findReplace[ 'last_name' ] = $newUser->last_name;
			$findReplace[ 'username' ] = $newUser->user_login;
			$findReplace[ 'phone' ] = $newUser->wwlc_phone;
			$findReplace[ 'company_name' ] = $newUser->wwlc_company_name;
			$findReplace[ 'address' ] = $newUser->wwlc_address;

			if ( is_array( $customFields ) && !empty( $customFields ) ){
				foreach ( $customFields as $field_id => $field ) {
					$findReplace[ 'custom_field:'.$field_id ] = $newUser->$field_id;
				}
			}

			foreach ( $findReplace as $find => $replace ) {

				if ( is_array( $replace ) ) {

					$replace_str = implode( ", " , $replace );
					$content = str_replace( "{" . $find . "}" , $replace_str , $content );

				} else
					$content = str_replace( "{" . $find . "}" , $replace , $content );

			}

			return $content;

		}




		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | Admin Emails
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Email sent to admin on new user registration.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 */
		public function sendNewUserAdminNoticeEmail ( $userID , $subject , $message , $password ) {
			
			$wcEmails = WC_Emails::instance();

			$to = $this->_getAdminEmailRecipients();
			$to = apply_filters( 'wwlc_filter_new_user_admin_notice_email_recipients' , $to );

			$cc = $this->_getAdminEmailCC();
			$cc = apply_filters( 'wwlc_filter_new_user_admin_notice_email_cc' , $cc );

			$bcc = $this->_getAdminEmailBCC();
			$bcc = apply_filters( 'wwlc_filter_new_user_admin_notice_email_bcc' , $bcc );

			$fromName = $this->_getFromName();
			$fromEmail = $this->_getFromEmail();

			if ( !$subject )
				$subject = __( 'New User Registration' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parseEmailContent( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_new_user_admin_email_subject' , $subject );

			if ( !$message ) {

				global $newUserAdminNotificationEmailDefault;
				$message = $newUserAdminNotificationEmailDefault;

			}

			$message = $this->_parseEmailContent( $userID , $message , $password );

			$wrapEmailWithWooHeaderAndFooter = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrapEmailWithWooHeaderAndFooter == "yes" )
				$message = $wcEmails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_new_user_admin_email_content' , html_entity_decode( $message ) );

			$headers = $this->_constructEmailHeader( $fromName , $fromEmail , $cc , $bcc );

			$wcEmails->send( $to , $subject , $message , $headers );

		}

		/**
		 * Email sent to admin on new user registration that is auto approved.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 */
		public function sendNewUserAdminNoticeEmailAutoApproved ( $userID , $subject , $message , $password ) {

			$wcEmails = WC_Emails::instance();

			$to = $this->_getAdminEmailRecipients();
			$to = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_recipients' , $to );

			$cc = $this->_getAdminEmailCC();
			$cc = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_cc' , $cc );

			$bcc = $this->_getAdminEmailBCC();
			$bcc = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_bcc' , $bcc );

			$fromName = $this->_getFromName();
			$fromEmail = $this->_getFromEmail();

			$headers = $this->_constructEmailHeader( $fromName , $fromEmail , $cc , $bcc );

			if ( !$subject )
				$subject = __( 'New User Registered And Approved' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parseEmailContent( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_subject' , $subject );

			if ( !$message ) {

				global $newUserAdminNotificationEmailAutoApprovedDefault;
				$message = $newUserAdminNotificationEmailAutoApprovedDefault;

			}

			$message = $this->_parseEmailContent( $userID , $message , $password );

			$wrapEmailWithWooHeaderAndFooter = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrapEmailWithWooHeaderAndFooter == "yes" )
				$message = $wcEmails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_content' , html_entity_decode( $message ) );

			$wcEmails->send( $to , $subject , $message , $headers );

		}




		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | User Emails
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Email sent to user on successful registration.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 */
		public function sendNewUserEmail ( $userID , $subject , $message , $password ) {

			$wcEmails = WC_Emails::instance();

			$newUser = get_userdata( $userID );
			$to = $newUser->data->user_email;

			$fromName = $this->_getFromName();
			$fromEmail = $this->_getFromEmail();

			$headers = $this->_constructEmailHeader( $fromName , $fromEmail );

			if ( !$subject )
				$subject = __( 'Registration Successful' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parseEmailContent( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_new_user_user_notice_email_subject' , $subject );

			if ( !$message ) {

				global $newUserEmailDefault;
				$message = $newUserEmailDefault;

			}

			$message = $this->_parseEmailContent( $userID , $message , $password );

			$wrapEmailWithWooHeaderAndFooter = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrapEmailWithWooHeaderAndFooter == "yes" )
				$message = $wcEmails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_new_user_user_notice_email_content' , html_entity_decode( $message ) );

			$wcEmails->send( $to , $subject , $message , $headers );

		}

		/**
		 * Email sent to user on account approval.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 */
		public function sendRegistrationApprovalEmail ( $userID , $subject , $message , $password ) {

			$wcEmails = WC_Emails::instance();

			$newUser = get_userdata( $userID );
			$to = $newUser->data->user_email;

			$fromName = $this->_getFromName();
			$fromEmail = $this->_getFromEmail();

			$headers = $this->_constructEmailHeader( $fromName , $fromEmail );

			if ( !$subject )
				$subject = __( 'Registration Approved' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parseEmailContent( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_registration_approved_user_notice_email_subject' , $subject );

			if ( !$message ) {

				global $approvedEmailDefault;
				$message = $approvedEmailDefault;

			}

			$message = $this->_parseEmailContent( $userID , $message , $password );

			$wrapEmailWithWooHeaderAndFooter = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrapEmailWithWooHeaderAndFooter == "yes" )
				$message = $wcEmails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_registration_approved_user_notice_email_content' , html_entity_decode( $message ) );

			$wcEmails->send( $to , $subject , $message , $headers );

		}

		/**
		 * Email sent to user on account rejection.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 *
		 * @since 1.0.0
		 */
		public function sendRegistrationRejectionEmail ( $userID , $subject , $message ) {

			$wcEmails = WC_Emails::instance();

			$newUser = get_userdata( $userID );
			$to = $newUser->data->user_email;

			$fromName = $this->_getFromName();
			$fromEmail = $this->_getFromEmail();

			$headers = $this->_constructEmailHeader( $fromName , $fromEmail );

			if ( !$subject )
				$subject = __( 'Registration Rejected' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parseEmailContent( $userID ,$subject );

			$subject = apply_filters( 'wwlc_filter_registration_rejected_user_notice_email_subject' , $subject );

			if ( !$message ) {

				global $rejectedEmailDefault;
				$message = $rejectedEmailDefault;

			}

			$message = $this->_parseEmailContent( $userID , $message );

			$wrapEmailWithWooHeaderAndFooter = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrapEmailWithWooHeaderAndFooter == "yes" )
				$message = $wcEmails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_registration_rejected_user_notice_email_content' , html_entity_decode( $message ) );

			$wcEmails->send( $to, $subject , $message , $headers );

		}




		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | Helper Functions
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Get admin email recipients.
		 *
		 * @return array|string
		 *
		 * @since 1.3.0
		 */
		private function _getAdminEmailRecipients () {

			$to = trim( get_option( 'wwlc_emails_main_recipient' ) );

			if ( $to )
				$to = explode( ',' , $to );
			else
				$to = array( get_option( 'admin_email' ) );

			return $to;

		}

		/**
		 * Get admin email cc.
		 *
		 * @return array|string
		 *
		 * @since 1.3.0
		 */
		private function _getAdminEmailCC () {

			$cc = trim( get_option( 'wwlc_emails_cc' ) );

			if ( $cc )
				$cc = explode( ',' , $cc );

			if ( !is_array( $cc ) )
				$cc = array();

			return $cc;

		}

		/**
		 * Get admin email bcc.
		 *
		 * @return array|string
		 *
		 * @since 1.3.0
		 */
		private function _getAdminEmailBCC () {

			$bcc = trim( get_option( 'wwlc_emails_bcc' ) );

			if ( $bcc )
				$bcc = explode( ',' , $bcc );

			if ( !is_array( $bcc ) )
				$bcc = array();

			return $bcc;

		}

		/**
		 * Get email from name.
		 *
		 * @return mixed
		 *
		 * @since 1.3.0
		 */
		private function _getFromName () {

			$fromName = trim( get_option( "woocommerce_email_from_name" ) );

			if ( !$fromName )
				$fromName = get_bloginfo( 'name' );

			return apply_filters( 'wwlc_filter_from_name' , $fromName );

		}

		/**
		 * Get from email.
		 *
		 * @return mixed
		 *
		 * @since 1.3.0
		 */
		private function _getFromEmail () {

			$fromEmail = trim( get_option( "woocommerce_email_from_address" ) );

			if ( !$fromEmail )
				$fromEmail = get_option( 'admin_email' );

			return apply_filters( 'wwlc_filter_from_email' , $fromEmail );

		}

		/**
		 * Construct email headers.
		 *
		 * @param $fromName
		 * @param $fromEmail
		 * @param array $cc
		 * @param array $bcc
		 * @return array
		 *
		 * @since 1.3.0
		 */
		private function _constructEmailHeader ( $fromName , $fromEmail , $cc = array() , $bcc = array() ) {

			$headers[] = 'From: ' . $fromName  . ' <' . $fromEmail . '>';

			if ( is_array( $cc ) )
				foreach ( $cc as $c )
					$headers[] = 'Cc: ' . $c;

			if ( is_array( $bcc ) )
				foreach ( $bcc as $bc )
					$headers[] = 'Bcc: ' . $bc;

			$headers[] = 'Content-Type: text/plain; charset=UTF-8';

			return $headers;

		}

	}

}