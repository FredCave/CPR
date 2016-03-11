<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WWLC_User_Custom_Fields' ) ) {

	class WWLC_User_Custom_Fields {

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
		 * Add custom row action to user listing page.
		 *
		 * @param $actions
		 * @param $user_object
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function addUserListCustomRowActionUI( $actions, $user_object ) {

			// Admins and Shop managers can manage wholesale users
			if ( ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) && get_current_user_id() != $user_object->ID ) {

				$user = get_userdata( $user_object->ID );

				if ( in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) ) {

					$actions[ 'wwlc_user_row_action_approve' ] = '<a class="wwlc_approve wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Approve' , 'woocommerce-wholesale-lead-capture' ) .'</a>';
					$actions[ 'wwlc_user_row_action_reject' ] = '<a class="wwlc_reject wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Reject' , 'woocommerce-wholesale-lead-capture' ) .'</a>';

				} elseif ( in_array( WWLC_REJECTED_ROLE , $user->roles ) ) {

					$actions[ 'wwlc_user_row_action_approve' ] = '<a class="wwlc_approve wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Approve' , 'woocommerce-wholesale-lead-capture' ) .'</a>';

				} elseif ( in_array( WWLC_INACTIVE_ROLE , $user->roles ) ) {

					$actions[ 'wwlc_user_row_action_activate' ] = '<a class="wwlc_activate wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Activate' , 'woocommerce-wholesale-lead-capture' ) . '</a>';

				} else {

					$actions[ 'wwlc_user_row_action_deactivate' ] = '<a class="wwlc_deactivate wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Deactivate' , 'woocommerce-wholesale-lead-capture' ) . '</a>';

				}

			}

			return $actions;

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

			$arrayKeys = array_keys( $columns );
			$lastIndex = $arrayKeys[ count( $arrayKeys ) - 1 ];
			$lastValue = $columns[ $lastIndex ];
			array_pop( $columns );

			$columns[ 'wwlc_user_status' ] = __( 'Status' , 'woocommerce-wholesale-lead-capture' );
			$columns[ 'wwlc_registration_date' ] = __( 'Registration Date' , 'woocommerce-wholesale-lead-capture' );
			$columns[ 'wwlc_approval_date' ] = __( 'Approval Date' , 'woocommerce-wholesale-lead-capture' );
			$columns[ 'wwlc_rejection_date' ] = __( 'Rejection Date' , 'woocommerce-wholesale-lead-capture' );

			$columns[ $lastIndex ] = $lastValue;

			return $columns;

		}

		/**
		 * Add content to custom column to user listing page.
		 *
		 * @param $val
		 * @param $column_name
		 * @param $user_id
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function addUserListingCustomColumnContent( $val , $column_name , $user_id ) {

			$user = get_userdata( $user_id );

			if ( $column_name == 'wwlc_user_status' ) {

				if ( in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) )
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: black; display: inline-block; padding: 0 6px;'>" . __( 'Unapproved' , 'woocommerce-wholesale-lead-capture' ) . "</span>";
				elseif ( in_array( WWLC_REJECTED_ROLE , $user->roles ) )
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: orange; display: inline-block; padding: 0 6px;'>" . __( 'Rejected' , 'woocommerce-wholesale-lead-capture' ) . "</span>";
				elseif ( in_array( WWLC_INACTIVE_ROLE , $user->roles ) )
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: grey; display: inline-block; padding: 0 6px;'>" . __( 'Inactive' , 'woocommerce-wholesale-lead-capture' ) . "</span>";
				else
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: green; display: inline-block; padding: 0 6px;'>" . __( 'Active' , 'woocommerce-wholesale-lead-capture' ) . "</span>";

			} elseif ( $column_name == 'wwlc_registration_date' ) {

				return "<span class='wwlc_registration_date' >" . $user->user_registered . "</span>";

			} elseif ( $column_name == 'wwlc_approval_date' ) {

				if ( !in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) && !in_array( WWLC_REJECTED_ROLE , $user->roles ) ) {

					$approval_date = get_user_meta( $user->ID , 'wwlc_approval_date' , true );

					// For older versions of this plugin (prior to 1.3.1) we don't save approval dates.
					// If approval date is not present, we will use the registration date by default.
					if ( !$approval_date )
						$approval_date = $user->user_registered;

					return "<span class='wwlc_approval_date'>" . $approval_date . "</span>";

				}

			} elseif ( $column_name == 'wwlc_rejection_date' ) {

				if ( in_array( WWLC_REJECTED_ROLE , $user->roles ) ) {

					$rejection_date = get_user_meta( $user->ID , 'wwlc_rejection_date' , true );

					return "<span class='wwlc_rejection_date'>" . $rejection_date . "</span>";

				}

			}

		}

		/**
		 * Add custom admin notices on user listing page. WWLC related.
		 *
		 * @since 1.0.0
		 */
		public function customSubmissionsBulkActionNotices() {

			global $post_type, $pagenow;

			if ( $pagenow == 'users.php' ) {

				if ( ( isset( $_REQUEST[ 'users_approved' ] ) && (int) $_REQUEST[ 'users_approved' ] ) ||
					( isset( $_REQUEST[ 'users_rejected' ] ) && (int) $_REQUEST[ 'users_rejected' ] ) ||
					( isset( $_REQUEST[ 'users_activated' ] ) && (int) $_REQUEST[ 'users_activated' ] ) ||
					( isset( $_REQUEST[ 'users_deactivated' ] ) && (int) $_REQUEST[ 'users_deactivated' ] ) ) {

					if ( $_REQUEST[ 'users_approved' ] ) {

						$action = "approved";
						$affected = $_REQUEST[ 'users_approved' ];

					} if ( $_REQUEST[ 'users_rejected' ] ) {

						$action = "rejected";
						$affected = $_REQUEST[ 'users_rejected' ];

					} if ( $_REQUEST[ 'users_activated' ] ) {

						$action = "activated";
						$affected = $_REQUEST[ 'users_activated' ];


					} if ( $_REQUEST[ 'users_deactivated' ] ){

						$action = "deactivated";
						$affected = $_REQUEST[ 'users_deactivated' ];

					}

					$message = sprintf( _n( 'User %2$s.' , '%1$s users %2$s.' , $affected , 'woocommerce-wholesale-lead-capture' ) , number_format_i18n( $affected ) , $action );
					echo "<div class=\"updated\"><p>{$message}</p></div>";

				} elseif (  isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_approve" ||
					isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_reject" ||
					isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_activate" ||
					isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_deactivate" ) {

					if ( isset( $_REQUEST[ 'users' ] ) ) {

						if ( count( $_REQUEST[ 'users' ] ) > 0 ) {

							if ( $_REQUEST[ 'action' ] == "wwlc_approve" )
								$action = "approved";
							if ( $_REQUEST[ 'action' ] == "wwlc_reject" )
								$action = "rejected";
							if ( $_REQUEST[ 'action' ] == "wwlc_activate" )
								$action = "activated";
							if ( $_REQUEST[ 'action' ] == "wwlc_deactivate" )
								$action = "deactivated";

							$message = sprintf( _n( 'User %2$s.' , '%1$s users %2$s.' , count( $_REQUEST[ 'users' ] ) , 'woocommerce-wholesale-lead-capture' ) , number_format_i18n( count( $_REQUEST[ 'users' ] ) ) , $action );
							echo "<div class=\"updated\"><p>{$message}</p></div>";

						}

					}

				}

			}

		}

		/**
		 * Add custom user listing bulk action items on the action select boxes. Done via JS.
		 *
		 * @since 1.0.0
		 */
		public function customUserListingBulkActionFooterJS () {

			global $pagenow;

			if ( $pagenow == 'users.php' && ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) ) { ?>

				<script type="text/javascript">

					jQuery( document ).ready( function() {

						jQuery( '<option>' ).val( 'wwlc_approve' ).text( '<?php _e( 'Approve' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_approve' ).text( '<?php _e( 'Approve' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2']" );

						jQuery( '<option>' ).val( 'wwlc_reject' ).text( '<?php _e( 'Reject' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_reject' ).text( '<?php _e( 'Reject' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2']" );

						jQuery( '<option>' ).val( 'wwlc_activate' ).text( '<?php _e( 'Activate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_activate' ).text( '<?php _e( 'Activate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2']" );

						jQuery( '<option>' ).val( 'wwlc_deactivate' ).text( '<?php _e( 'Deactivate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_deactivate' ).text( '<?php _e( 'Deactivate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2'] ");

					});

				</script>

			<?php }

		}

		/**
		 * Add custom user listing bulk action.
		 *
		 * @param WWLC_User_Account $userProcessor
		 * @param WWLC_Emails $emailProcessor
		 *
		 * @since 1.3.3
		 */
		public function customUserListingBulkAction( WWLC_User_Account $userProcessor , WWLC_Emails $emailProcessor ) {

			global $pagenow;

			if ( $pagenow == 'users.php' && ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) ) {

				// get the current action
				$wp_list_table = _get_list_table( 'WP_Users_List_Table' );  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
				$action = $wp_list_table->current_action();

				// set allowed actions, and check if current action is in allowed actions
				$allowed_actions = array( "wwlc_approve" , "wwlc_reject" , "wwlc_activate" , "wwlc_deactivate" );
				if ( !in_array( $action , $allowed_actions ) ) return;

				// security check
				check_admin_referer('bulk-users');

				// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids' or 'users'
				if ( isset( $_REQUEST[ 'users' ] ) )
					$user_ids = $_REQUEST[ 'users' ];

				if ( empty( $user_ids ) ) return;

				// this is based on wp-admin/edit.php
				$sendback = remove_query_arg( array( 'wwlc_approve' , 'wwlc_reject' , 'wwlc_activate' , 'wwlc_deactivate' , 'untrashed' , 'deleted' , 'ids' ), wp_get_referer() );
				if ( ! $sendback )
					$sendback = admin_url( "users.php" );

				$pagenum = $wp_list_table->get_pagenum();
				$sendback = add_query_arg( 'paged', $pagenum, $sendback );

				switch( $action ) {

					case 'wwlc_approve':

						$users_activated = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $userProcessor->approveUser( array( 'userID' => $user_id ) , $emailProcessor ) )
									$users_activated++;

						}

						$sendback = add_query_arg( array( 'users_approved' => $users_activated , 'ids' => join( ',' , $user_ids ) ), $sendback );
						break;

					case 'wwlc_reject':

						$users_rejected = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $userProcessor->rejectUser( array( 'userID' => $user_id ) , $emailProcessor ) )
									$users_rejected++;

						}

						$sendback = add_query_arg( array( 'users_rejected' => $users_rejected , 'ids' => join( ',' , $user_ids ) ), $sendback );
						break;

					case 'wwlc_activate':
						// if we set up user permissions/capabilities, the code might look like:
						//if ( !current_user_can($post_type_object->cap->export_post, $post_id) )
						//    wp_die( __('You are not allowed to export this post.') );

						$users_activated = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $userProcessor->activateUser( array( 'userID' => $user_id ) ) )
									$users_activated++;

						}

						$sendback = add_query_arg( array( 'users_activated' => $users_activated , 'ids' => join( ',' , $user_ids ) ), $sendback );
						break;

					case 'wwlc_deactivate':

						$users_deactivated = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $userProcessor->deactivateUser( array( 'userID' => $user_id ) ) )
									$users_deactivated++;

						}

						$sendback = add_query_arg( array( 'users_deactivated' => $users_deactivated , 'ids' => join( ',' , $user_ids) ), $sendback );
						break;

					default: return;

				}

				$sendback = remove_query_arg( array( 'action' , 'action2' , 'tags_input' , 'post_author' , 'comment_status', 'ping_status' , '_status',  'post', 'bulk_edit', 'post_view'), $sendback );

				wp_redirect( $sendback );
				exit();

			}

		}

		/**
		 * Display custom fields on user admin.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		public function displayCustomFieldsOnUserAdminPage( $user ) {

            global $WWLC_REGISTRATION_FIELDS;

            $custom_fields = $this->_getFormattedCustomFields();

            $registration_form_fields = array_merge( $WWLC_REGISTRATION_FIELDS , $custom_fields );

            usort( $registration_form_fields , array( $this , 'usortCallback' ) );

			require_once ( 'views/view-wwlc-custom-fields-on-user-admin.php' );

		}

        /**
         * Return formatted custom fields. ( Abide to the formatting of existing fields ).
         *
         * @return array
         *
         * @since 1.1.0
         */
        private function _getFormattedCustomFields() {

            $registrationFormCustomFields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registrationFormCustomFields ) )
                $registrationFormCustomFields = array();

            $formattedRegistrationFormCustomFields = array();

            foreach ( $registrationFormCustomFields as $field_id => $customField ) {

                $formattedRegistrationFormCustomFields[] = array(
                    'label'         =>  $customField[ 'field_name' ],
                    'name'          =>  $field_id,
                    'id'            =>  $field_id,
                    'class'         =>  'wwlc_registration_field form_field wwlc_custom_field',
                    'type'          =>  $customField[ 'field_type' ],
                    'required'      =>  ( $customField[ 'required' ] == '1' ) ? true : false,
                    'custom_field'  =>  true,
                    'active'        =>  ( $customField[ 'enabled' ] == '1' ) ? true : false,
                    'validation'    =>  array(),
                    'field_order'   =>  $customField[ 'field_order' ],
                    'attributes'    =>  $customField[ 'attributes' ],
                    'options'       =>  $customField[ 'options' ]
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
		 * Save custom fields on user admin.
		 *
		 * @param $user_id
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function saveCustomFieldsOnUserAdminPage( $user_id ) {

			if ( !current_user_can( 'edit_user', $user_id ) )
				return false;

            global $WWLC_REGISTRATION_FIELDS;

            $custom_fields = $this->_getFormattedCustomFields();

            $registration_form_fields = array_merge( $WWLC_REGISTRATION_FIELDS , $custom_fields );

            usort( $registration_form_fields , array( $this , 'usortCallback' ) );

			foreach( $registration_form_fields as $field ) {

				if ( ! $field[ 'custom_field' ] )
					continue;

                if ( array_key_exists( $field[ 'id' ] , $_POST ) )
                    update_usermeta( $user_id , $field[ 'id' ] , $_POST[ $field[ 'id' ] ] );
                elseif ( $field[ 'type' ] == 'checkbox' && $field[ 'custom_field' ] )
                    update_usermeta( $user_id , $field[ 'id' ] , array() );

			}

		}

	}

}
