<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WWLC_User_Account' ) ) {

	class WWLC_User_Account {

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
		 * Generate random password.
		 *
		 * @param int $length
		 *
		 * @return string
		 * @since 1.0.0
		 */
		private function _generatePassword( $length = 16 ) {

			return substr( str_shuffle( MD5( microtime() ) ) , 0, $length );

		}

		/**
		 * WWLC authentication filter. It checks if user is inactive, unmoderated, unapproved or rejected and kick
		 * there asses.
		 *
		 * @param $user
		 * @param $password
		 *
		 * @return WP_Error
		 * @since 1.0.0
		 */
		public function wholesaleLeadAuthenticate( $user , $password ) {

			if ( in_array( WWLC_INACTIVE_ROLE , $user->roles ) ||
			     in_array( WWLC_UNMODERATED_ROLE , $user->roles ) ||
				 in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) ||
				 in_array( WWLC_REJECTED_ROLE , $user->roles ) )
				return new WP_Error( 'authentication_failed' , __( '<strong>ERROR</strong>: Invalid Request' , 'woocommerce-wholesale-lead-capture' ) );
			else
				return $user;

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
		public function wholesaleLeadLoginRedirect( $redirect_to , $request , $user ) {

			$wholesaleLoginRedirect = get_option( 'wwlc_general_login_redirect_page' );
			if ( !$wholesaleLoginRedirect )
				return $redirect_to;

			//is there a user to check?
			global $user;
			if ( isset( $user->roles ) && is_array( $user->roles ) ) {

				$allWholesaleRoles = unserialize( get_option( 'wwp_options_registered_custom_roles' ) );

				$wholesaleRoleKeys = array();

				foreach( $allWholesaleRoles as $roleKey => $roleName )
					$wholesaleRoleKeys[] = $roleKey;

				$userWholesaleRole = array_intersect( $user->roles , $wholesaleRoleKeys );

				if ( empty( $userWholesaleRole ) )
					return $redirect_to;
				else
					return $wholesaleLoginRedirect;

			} else
				return $redirect_to;

		}

		/**
		 * Redirect wholesale user to specific page after logging out.
		 *
		 * @since 1.3.3
		 */
		public function wholesaleLeadLogoutRedirect() {

			$wholesaleLogoutRedirect = get_option( 'wwlc_general_logout_redirect_page' );
			$user = wp_get_current_user();

			if ( $wholesaleLogoutRedirect && isset( $user->roles ) && is_array( $user->roles ) ) {

				$wholesaleLogoutRedirect = apply_filters( 'wwlc_filter_logout_redirect_url' , $wholesaleLogoutRedirect );

				$allWholesaleRoles = unserialize( get_option( 'wwp_options_registered_custom_roles' ) );

				$wholesaleRoleKeys = array();

				foreach( $allWholesaleRoles as $roleKey => $roleName )
					$wholesaleRoleKeys[] = $roleKey;

				$userWholesaleRole = array_intersect( $user->roles , $wholesaleRoleKeys );

				if ( !empty( $userWholesaleRole ) ) {

					wp_redirect( $wholesaleLogoutRedirect );
					exit();

				}

			}

		}

		/**
		 * Create New User.
		 *
		 * @param null        $userData
		 * @param bool        $ajaxCall
		 * @param WWLC_Emails $emailProcessor
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function createUser( $userData = null , $ajaxCall = true , WWLC_Emails $emailProcessor ) {

			if ( $ajaxCall === true ) {

				$userData = $_POST[ 'userData' ];

				if ( !isset( $_POST[ 'wwlc_register_user_nonce_field' ] ) ||
				     !wp_verify_nonce( $_POST[ 'wwlc_register_user_nonce_field' ] , 'wwlc_register_user' ) ) {

					header( 'Content-Type: application/json' ); // specify we return json
					echo json_encode( array(
						'status'        =>  'fail',
						'error_message'	=>	__( 'Security check fail' , 'woocommerce-wholesale-lead-capture' )
					) );
					die();

				}

			}

			// Generate password
			$password = ( isset( $userData[ 'wwlc_password' ] ) && !empty( $userData[ 'wwlc_password' ] ) ) ? $userData[ 'wwlc_password' ] : $this->_generatePassword();

			do_action( 'wwlc_action_before_create_user' , $userData );

			// $result will either be the new user id or a WP_Error object on failure
			$result = wp_create_user( $userData[ 'user_email' ] , $password , $userData[ 'user_email' ] );

			if ( !is_wp_error( $result ) ) {
				
				// Save user supplied password on a temporary option, to be used later on approval
				update_option( "wwlc_password_temp_" . $result , $password );

				// Get new user
				$newLead = new WP_User( $result );

				// Remove all associated roles
				$currentRoles = $newLead->roles;

				foreach ( $currentRoles as $role )
					$newLead->remove_role( $role );

				// Auto approve user?
				$autoApproveNewLeads = get_option( 'wwlc_general_auto_approve_new_leads' );

				// Update new user meta
				foreach ( $userData as $key => $val ) {

					if ( $key == 'user_email' )
						continue;

					// TODO: server side validation

					update_user_meta( $result , $key , $val );

				}

				// Save customer billing address
				$this->saveCustomerBillingAddress( $result );

				// Set user status correctly
				if ( $autoApproveNewLeads == 'yes' ) {

					$admin_email_subject = trim( get_option( 'wwlc_emails_new_user_admin_notification_auto_approved_subject' ) );
					$admin_email_template = trim( get_option( 'wwlc_emails_new_user_admin_notification_auto_approved_template' ) );

					$user_email_subject = trim( get_option( 'wwlc_emails_new_user_subject' ) );
					$user_email_template = trim( get_option( 'wwlc_emails_new_user_template' ) );

					$emailProcessor->sendNewUserAdminNoticeEmailAutoApproved( $newLead->ID , $admin_email_subject , $admin_email_template , $password );
					$emailProcessor->sendNewUserEmail( $newLead->ID , $user_email_subject , $user_email_template , $password );

					// Add unapprove role and unmoderated role. We still need to add this as approveUser
					// function checks if user has these roles before approving this user.
					$this->_addUnapprovedRole( $newLead );
					$this->_addUnmoderatedRole( $newLead );

					$this->approveUser( array( 'userObject' => $newLead ) , $emailProcessor );

				} else
					$this->newUser( array( 'userObject' => $newLead ) , $password , $emailProcessor );

                do_action( 'wwlc_action_after_create_user' , $newLead );

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
						'status'        =>  'fail',
						'error_message' =>  $result->get_error_message()
					) );
					die();

				} else
					return false;

			}

		}

		/**
		 * Save customer billing address.
		 *
		 * @param $userID
		 *
		 * @since 1.4.0
		 */
		public function saveCustomerBillingAddress ( $userID ) {

			// User Regisration Fields
			$fName 		= get_user_meta( $userID, "first_name", true );
			$lName 		= get_user_meta( $userID, "last_name", true );
			$company 	= get_user_meta( $userID, "wwlc_company_name", true );
			$addr1 		= get_user_meta( $userID, "wwlc_address", true );
			$addr2 		= get_user_meta( $userID, "wwlc_address_2", true );
			$city 		= get_user_meta( $userID, "wwlc_city", true );
			$postcode 	= get_user_meta( $userID, "wwlc_postcode", true );
			$country 	= get_user_meta( $userID, "wwlc_country", true );
			$state 		= get_user_meta( $userID, "wwlc_state", true );
			$phone 		= get_user_meta( $userID, "wwlc_phone", true );
			$email 		= get_user_meta( $userID, "nickname", true );

			if( !empty( $fName ) )
				update_user_meta( $userID, "billing_first_name", $fName );

			if( !empty( $lName ) )
				update_user_meta( $userID, "billing_last_name", $lName );

			if( !empty( $company ) )
				update_user_meta( $userID, "billing_company", $company );

			if( !empty( $addr1 ) )
				update_user_meta( $userID, "billing_address_1", $addr1 );

			if( !empty( $addr2 ) )
				update_user_meta( $userID, "billing_address_2", $addr2 );

			if( !empty( $city ) )
				update_user_meta( $userID, "billing_city", $city );

			if( !empty( $postcode ) )
				update_user_meta( $userID, "billing_postcode", $postcode );

			if( !empty( $country ) )
				update_user_meta( $userID, "billing_country", $country );

			if( !empty( $state ) )
				update_user_meta( $userID, "billing_state", $state );

			if( !empty( $phone ) )
				update_user_meta( $userID, "billing_phone", $phone );

			if( !empty( $email ) )
				update_user_meta( $userID, "billing_email", $email );

			
		}

		/**
		 * Get states by country code.
		 *
		 * @param $cc
		 * @param $ajaxCall
		 *
		 * @since 1.4.0
		 */
		public function getStates ( $cc , $ajaxCall ) {

			$states = new WC_Countries();
			$cc 	= $_POST['cc'];
			$list 	= $states->get_states( $cc );

			if ( $ajaxCall === true ) {

				if( !empty( $list ) ){

					header( 'Content-Type: application/json' ); // specify we return json
					echo json_encode( array(
						'status'        => 'success',
						'states'		=> $list
					) );
					die();

				}else{

					header( 'Content-Type: application/json' ); // specify we return json
					echo json_encode( array(
						'status'        => 'error'
					) );
					die();

				}

			} else
				return false;

		}

		/**
		 * Set new user status.
		 *
		 * @param             $userData
		 * @param             $password
		 * @param WWLC_Emails $emailProcessor
		 *
		 * @since 1.0.0
		 */
		public function newUser ( $userData , $password , WWLC_Emails $emailProcessor ) {

			if ( array_key_exists( 'userID' , $userData ) )
				$user = get_userdata( $userData[ 'userID' ] );
			else
				$user = &$userData[ 'userObject' ];

			$this->_addUnapprovedRole( $user );
			$this->_addUnmoderatedRole( $user );

			$admin_email_subject = trim( get_option( 'wwlc_emails_new_user_admin_notification_subject' ) );
			$admin_email_template = trim( get_option( 'wwlc_emails_new_user_admin_notification_template' ) );

			$user_email_subject = trim( get_option( 'wwlc_emails_new_user_subject' ) );
			$user_email_template = trim( get_option( 'wwlc_emails_new_user_template' ) );

			$emailProcessor->sendNewUserAdminNoticeEmail( $user->ID , $admin_email_subject , $admin_email_template , $password );
			$emailProcessor->sendNewUserEmail( $user->ID , $user_email_subject , $user_email_template , $password );

		}

		/**
		 * Set user as approved.
		 *
		 * @param $userData
		 * @param WWLC_Emails $emailProcessor
		 * @return bool
		 */
		public function approveUser ( $userData , WWLC_Emails $emailProcessor ) {

			if ( array_key_exists( 'userID' , $userData ) )
				$user = get_userdata( $userData[ 'userID' ] );
			else
				$user = &$userData[ 'userObject' ];

			if ( in_array( WWLC_UNAPPROVED_ROLE , (array) $user->roles ) ||
				 in_array( WWLC_UNMODERATED_ROLE , (array) $user->roles ) ||
				 in_array( WWLC_REJECTED_ROLE , (array) $user->roles ) ) {

				do_action( 'wwlc_action_before_approve_user' , $user );

				$newUserRole = trim( get_option( 'wwlc_general_new_lead_role' ) );

				if ( empty( $newUserRole ) || !$newUserRole )
					$newUserRole = 'customer'; // default to custom if new approved lead role is not set

				$this->_removeUnapprovedRole( $user );
				$this->_removeUnmoderatedRole( $user );
				$this->_removeRejectedRole( $user );
				$this->_removeInactiveRole( $user );

				if ( empty( $user->roles ) )
					$user->add_role( $newUserRole );

				// Get user supplied password that was saved on temporary option
				$password = trim( get_option( "wwlc_password_temp_" . $user->ID ) );

				if ( !$password ) {

					// None is set, meaning we need to generate our own
					// Since we generated a new password, then we need to assign this new password to this user
					$password = $this->_generatePassword();
					wp_set_password( $password , $user->ID );

				}

				// Save approval date
				update_user_meta( $user->ID , 'wwlc_approval_date' , current_time( 'mysql' ) );

				// Delete rejection date
				delete_user_meta( $user->ID , 'wwlc_rejection_date' );

				$user_email_subject = trim( get_option( 'wwlc_emails_approval_email_subject' ) );
				$user_email_template = trim( get_option( 'wwlc_emails_approval_email_template' ) );

				$emailProcessor->sendRegistrationApprovalEmail( $user->ID , $user_email_subject , $user_email_template , $password );

				// Remove temp user pass
				delete_option( "wwlc_password_temp_" . $user->ID );

				do_action( 'wwlc_action_after_approve_user' , $user );

				return true;

			} else
				return false;

		}

		/**
		 * Set user as rejected.
		 *
		 * @param $userData
		 * @param WWLC_Emails $emailProcessor
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public function rejectUser ( $userData , WWLC_Emails $emailProcessor ) {

			if ( array_key_exists( 'userID' , $userData ) )
				$user = get_userdata( $userData[ 'userID' ] );
			else
				$user = &$userData[ 'userObject' ];

			if ( !in_array( WWLC_REJECTED_ROLE , (array) $user->roles ) &&
				( in_array( WWLC_UNAPPROVED_ROLE , (array) $user->roles ) || in_array(  WWLC_UNMODERATED_ROLE , (array) $user->roles ) ) ) {

				do_action( 'wwlc_action_before_reject_user' , $user );

				$this->_removeUnapprovedRole( $user );
				$this->_removeUnmoderatedRole( $user );
				$this->_removeInactiveRole( $user );

				$this->_addRejectedRole( $user );

				// Save rejection date
				update_user_meta( $user->ID , 'wwlc_rejection_date' , current_time( 'mysql' ) );

				$user_email_subject = trim( get_option( 'wwlc_emails_rejected_email_subject' ) );
				$user_email_template = trim( get_option( 'wwlc_emails_rejected_email_template' ) );

				$emailProcessor->sendRegistrationRejectionEmail( $user->ID , $user_email_subject , $user_email_template );

				// Remove temp user pass
				delete_option( "wwlc_password_temp_" . $user->ID );

				do_action( 'wwlc_action_after_reject_user' , $user );

				return true;

			} else
				return false;

		}

		/**
		 * Activate user.
		 *
		 * @param $userData
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public function activateUser( $userData ) {

			if ( array_key_exists( 'userID' , $userData ) )
				$user = get_userdata( $userData[ 'userID' ] );
			else
				$user = &$userData[ 'userObject' ];

			if ( in_array( WWLC_INACTIVE_ROLE , (array) $user->roles ) ) {

				do_action( 'wwlc_action_before_activate_user' , $user );

				$newUserRole = trim( get_option( 'wwlc_general_new_lead_role' ) );

				if ( empty( $newUserRole ) || !$newUserRole )
					$newUserRole = 'customer'; // default to custom if new approved lead role is not set

				$this->_removeInactiveRole( $user );

				if ( empty( $user->roles ) )
					$user->add_role( $newUserRole );

				do_action( 'wwlc_action_after_activate_user' , $user );

				return true;

			} else
				return false;

		}

		/**
		 * Deactivate user.
		 *
		 * @param $userData
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public function deactivateUser( $userData ) {

			if ( array_key_exists( 'userID' , $userData ) )
				$user = get_userdata( $userData[ 'userID' ] );
			else
				$user = &$userData[ 'userObject' ];

			if ( !in_array( WWLC_INACTIVE_ROLE , (array) $user->roles ) ) {

				do_action( 'wwlc_action_before_deactivate_user' , $user );

				$this->_addInactiveRole( $user );

				do_action( 'wwlc_action_after_deactivate_user' , $user );

				return true;

			} else
				return false;

		}

		/**
		 * Add unapproved role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _addUnapprovedRole( &$user ) {

			if ( !in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) )
				$user->add_role( WWLC_UNAPPROVED_ROLE );

		}

		/**
		 * Remove unapproved role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _removeUnapprovedRole( &$user ) {

			if ( in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) )
				$user->remove_role( WWLC_UNAPPROVED_ROLE );

		}

		/**
		 * Add unmoderated role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _addUnmoderatedRole( &$user ) {

			if ( !in_array( WWLC_UNMODERATED_ROLE , $user->roles ) )
				$user->add_role( WWLC_UNMODERATED_ROLE );

		}

		/**
		 * Remove unmoderated role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _removeUnmoderatedRole( &$user ) {

			if ( in_array( WWLC_UNMODERATED_ROLE , $user->roles ) )
				$user->remove_role( WWLC_UNMODERATED_ROLE );

		}

		/**
		 * Add inactive role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _addInactiveRole( &$user ) {

			if ( !in_array( WWLC_INACTIVE_ROLE , $user->roles ) )
				$user->add_role( WWLC_INACTIVE_ROLE );

		}

		/**
		 * Remove inactive role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _removeInactiveRole( &$user ) {

			if ( in_array( WWLC_INACTIVE_ROLE , $user->roles ) )
				$user->remove_role( WWLC_INACTIVE_ROLE );

		}

		/**
		 * Add rejected role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _addRejectedRole( &$user ) {

			if ( !in_array( WWLC_REJECTED_ROLE , $user->roles ) )
				$user->add_role( WWLC_REJECTED_ROLE );

		}

		/**
		 * Remove rejected role to a user.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		private function _removeRejectedRole( &$user ) {

			if ( in_array( WWLC_REJECTED_ROLE , $user->roles ) )
				$user->remove_role( WWLC_REJECTED_ROLE );

		}

		/**
		 * Get total number of unmoderated users.
		 *
		 * @return int
		 * @since 1.0.0
		 */
		public function getTotalUnmoderatedUsers() {

			return count( get_users( array( 'role' => WWLC_UNMODERATED_ROLE ) ) );

		}

		/**
		 * Total unmoderated users bubble notification.
		 *
		 * @since 1.0.0
		 */
		public function totalUnmoderatedUsersBubbleNotification() {

			global $menu;
			$unmoderatedUsersTotal = $this->getTotalUnmoderatedUsers();

			if ( $unmoderatedUsersTotal ) {

				foreach ( $menu as $key => $value ) {

					if ( $menu[ $key ][2] == 'users.php' ) {

						$menu[ $key ][0] .= ' <span class="awaiting-mod count-'.$unmoderatedUsersTotal.'"><span class="unmoderated-count">' . $unmoderatedUsersTotal . '</span></span>';
						return;

					}

				}

			}

		}

		/**
		 * Total unmoderated user admin notice.
		 *
		 * @since 1.0.0
		 */
		public function totalUnmoderatedUsersAdminNotice() {

			global $current_user ;
			$user_id = $current_user->ID;

			if ( ! get_user_meta( $user_id , 'wwlc_ignore_unmoderated_users_notice' ) ) {

				$unmoderatedUsersTotal = $this->getTotalUnmoderatedUsers();

				if ( $unmoderatedUsersTotal ) {

					?>
					<div class="error">
						<p>
							<?php echo sprintf( __( '%1$s Unmoderated User/s | <a href="%2$s">View Users</a>' , 'woocommerce-wholesale-lead-capture' ) , $unmoderatedUsersTotal , get_admin_url( null , 'users.php' ) ); ?>
							<a href="?wwlc_ignore_unmoderated_users_notice=0" style="float: right;" id="wwof_dismiss_unmoderated_user_notice"><?php _e( 'Hide Notice' , 'woocommerce-wholesale-lead-capture' ); ?></a>
						</p>
					</div>
					<?php

				}

			}

		}

		/**
		 * Hide total unmoderated users admin notice.
		 *
		 * @since 1.0.0
		 */
		public function hideTotalUnmoderatedUsersAdminNotice () {

			global $current_user;
			$user_id = $current_user->ID;

			/* If user clicks to ignore the notice, add that to their user meta */
			if ( isset( $_GET[ 'wwlc_ignore_unmoderated_users_notice' ] ) && '0' == $_GET[ 'wwlc_ignore_unmoderated_users_notice' ] )
				add_user_meta( $user_id , 'wwlc_ignore_unmoderated_users_notice' , 'true' , true );

		}

		/**
		 * Hide important notice about properly managing wholesale users.
		 *
		 * @since 1.3.1
		 */
		public function hideImportantProperUserManagementNotice () {

			global $current_user;
			$user_id = $current_user->ID;

			/* If user clicks to ignore the notice, add that to their user meta */
			if ( isset( $_GET[ 'wwlc_dismiss_important_user_management_notice' ] ) && '0' == $_GET[ 'wwlc_dismiss_important_user_management_notice' ] )
				add_user_meta( $user_id , 'wwlc_dismiss_important_user_management_notice' , 'true' , true );

		}

	}

}