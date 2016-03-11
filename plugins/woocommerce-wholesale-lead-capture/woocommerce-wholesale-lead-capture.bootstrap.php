<?php
/*
Plugin Name:    WooCommerce Wholesale Lead Capture
Plugin URI:     https://wholesalesuiteplugin.com/
Description:    WooCommerce extension to provide functionality of capturing wholesale leads.
Author:         Rymera Web Co
Version:        1.4.4
Author URI:     http://rymera.com.au/
Text Domain:    woocommerce-wholesale-lead-capture
*/

// This file is the main plugin boot loader

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// Include Necessary Files
	require_once ( 'woocommerce-wholesale-lead-capture.options.php' );
	require_once ( 'woocommerce-wholesale-lead-capture.plugin.php' );

	// Get Instance of Main Plugin Class
	$wc_wholesale_lead_capture = WooCommerce_Wholesale_Lead_Capture::getInstance();
	$GLOBALS[ 'wc_wholesale_lead_capture' ] = $wc_wholesale_lead_capture;

    // Load Plugin Text Domain
    add_action( 'plugins_loaded' , array( $wc_wholesale_lead_capture , 'loadPluginTextDomain' ) );

	// Register Activation Hook
	register_activation_hook( __FILE__ , array( $wc_wholesale_lead_capture , 'activate' ) );

	// Register Deactivation Hook
	register_deactivation_hook( __FILE__ , array( $wc_wholesale_lead_capture , 'deactivate' ) );

	// Plugin Initialization
	add_action( "init" , array( $wc_wholesale_lead_capture , 'initialize' ) );

	// Load Backend CSS and JS
	add_action( 'admin_enqueue_scripts' , array( $wc_wholesale_lead_capture , 'loadBackEndStylesAndScripts' ) );

	// Load Frontend CSS and JS
	add_action( "wp_enqueue_scripts" , array( $wc_wholesale_lead_capture , 'loadFrontEndStylesAndScripts' ) );




    /*
    |------------------------------------------------------------------------------------------------------------------
    | WooCommerce WholeSale Suit License Settings
    |------------------------------------------------------------------------------------------------------------------
    */

    // Add WooCommerce Wholesale Suit License Settings
    add_action( "admin_menu" , array( $wc_wholesale_lead_capture , 'registerWWSLicenseSettingsMenu' ) );

    // Add WWS License Settings Header Tab Item
    add_action( "wws_action_license_settings_tab" , array( $wc_wholesale_lead_capture , 'wwcLicenseSettingsHeader' ) );

    // Add WWS License Settings Page (WWLC)
    add_action( "wws_action_license_settings_wwlc" , array( $wc_wholesale_lead_capture , 'wwcLicenseSettingsPage' ) );




	/*
    |-------------------------------------------------------------------------------------------------------------------
    | Settings
    |-------------------------------------------------------------------------------------------------------------------
    */

	// Register Settings Page
	add_filter( "woocommerce_get_settings_pages" , array ( $wc_wholesale_lead_capture , 'initializePluginSettings' ) );




	/*
    |-------------------------------------------------------------------------------------------------------------------
    | Short Codes
    |-------------------------------------------------------------------------------------------------------------------
    */

	// Registration Form
	add_shortcode( 'wwlc_registration_form' , array( $wc_wholesale_lead_capture , 'scRegistrationForm' ) );

	// Login Form
	add_shortcode( 'wwlc_login_form' , array( $wc_wholesale_lead_capture , 'scLogInForm' ) );




	/*
    |-------------------------------------------------------------------------------------------------------------------
    | User Account
    |-------------------------------------------------------------------------------------------------------------------
    */

	// Authenticate User. Block Unapproved, Unmoderated, Inactive and Reject Users.
	add_filter( 'wp_authenticate_user' , array( $wc_wholesale_lead_capture , 'wholesaleLeadAuthenticate' ) , 10 , 2 );

    // Redirect Wholesale User Accordingly After Successful Login
    add_filter( 'login_redirect' , array( $wc_wholesale_lead_capture , 'wholesaleLeadLoginRedirect' ) , 10 , 3 );

    // Redirect Wholesale User To Specific Page After Logging Out.
    add_action( 'wp_logout' , array( $wc_wholesale_lead_capture , 'wholesaleLeadLogoutRedirect' ) );

	// Total Unmoderated Users Bubble Notification
	add_action( 'admin_menu' , array( $wc_wholesale_lead_capture , 'totalUnmoderatedUsersBubbleNotification' ) );

	// Total Unmoderated Users Admin Notice
	add_action( 'admin_notices' , array( $wc_wholesale_lead_capture , 'totalUnmoderatedUsersAdminNotice' ) );

	// Hide Total Unmoderated Users Admin Notice
	add_action( 'admin_init' , array( $wc_wholesale_lead_capture , 'hideTotalUnmoderatedUsersAdminNotice' ) );

    // Hide Important Notice About Properly Managing Wholesale Users.
    add_action( 'admin_init' , array( $wc_wholesale_lead_capture , 'hideImportantProperUserManagementNotice' ) );




	/*
	|-------------------------------------------------------------------------------------------------------------------
	| User Listing Custom Fields
	|-------------------------------------------------------------------------------------------------------------------
	*/

	// Custom Row Action UI
	add_filter( 'user_row_actions', array( $wc_wholesale_lead_capture , 'addUserListCustomRowActionUI' ), 10, 2 );

	// Custom Admin Notices Related To WWLC Actions
	add_action( 'admin_notices' , array( $wc_wholesale_lead_capture , 'customSubmissionsBulkActionNotices' ) );

	// Add Custom Column To User Listing Page
	add_filter( 'manage_users_columns' , array( $wc_wholesale_lead_capture , 'addUserListingCustomColumn' ) );

	// Add Content To Custom Column On User Listing Page
	add_filter( 'manage_users_custom_column' , array( $wc_wholesale_lead_capture , 'addUserListingCustomColumnContent' ) , 10 , 3 );

	// Add Custom Bulk Action Options On Actions Select Box. Done Via JS
	add_action( 'admin_footer-users.php' , array( $wc_wholesale_lead_capture , 'customUserListingBulkActionFooterJS' ) );

    // Add Custom Bulk Action
    add_action( 'load-users.php' , array( $wc_wholesale_lead_capture , 'customUserListingBulkAction' ) );

	// Add Custom Fields To Admin User Edit Page.
	add_action( 'show_user_profile' , array( $wc_wholesale_lead_capture , 'displayCustomFieldsOnUserAdminPage' ) );
	add_action( 'edit_user_profile' , array( $wc_wholesale_lead_capture , 'displayCustomFieldsOnUserAdminPage' ) );

	// Save Custom Fields On Admin User Edit Page.
	add_action( 'personal_options_update' , array( $wc_wholesale_lead_capture , 'saveCustomFieldsOnUserAdminPage' ) );
	add_action( 'edit_user_profile_update' , array( $wc_wholesale_lead_capture , 'saveCustomFieldsOnUserAdminPage' ) );




    /*
	|-------------------------------------------------------------------------------------------------------------------
	| Add Custom Plugin Listing Action Links
	|-------------------------------------------------------------------------------------------------------------------
	*/

    // Settings
    add_filter( 'plugin_action_links' , array( $wc_wholesale_lead_capture , 'addPluginListingCustomActionLinks' ) , 10 , 2 );




	/*
    |-------------------------------------------------------------------------------------------------------------------
    | AJAX
    |-------------------------------------------------------------------------------------------------------------------
    */

	//  Register AJAX Call Handlers
	add_action( 'init' , array( $wc_wholesale_lead_capture , 'registerAJAXCAllHandlers' ) );




    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Update Checker
    |-------------------------------------------------------------------------------------------------------------------
    */

    // Get license email and key
    $wwlc_option_license_key = get_option( WWLC_OPTION_LICENSE_KEY );
    $wwlc_option_license_email = get_option( WWLC_OPTION_LICENSE_EMAIL );

    if ( $wwlc_option_license_key && $wwlc_option_license_email ) {

        require 'plugin-updates/class-wws-plugin-update-checker.php';

        $wws_wwlc_update_checker = new WWS_Plugin_Update_Checker(
            'https://wholesalesuiteplugin.com/wp-admin/admin-ajax.php?action=wumGetUpdateInfo&plugin=lead-capture&licence=' . $wwlc_option_license_key . '&email=' . $wwlc_option_license_email,
            __FILE__,
            'woocommerce-wholesale-lead-capture',
            12,
            ''
        );

    } else {

        /**
         * Check if show notice if license details is not entered.
         *
         * @since 1.1.1
         */
        function wwlcAdminNotices () {

            global $current_user ;
            $user_id = $current_user->ID;
            global $wc_wholesale_lead_capture;

            /* Check that the user hasn't already clicked to ignore the message */
            if ( !get_user_meta( $user_id , 'wwlc_ignore_empty_license_notice' ) && !$wc_wholesale_lead_capture->checkIfInWWLCSettingsPage() ) {

                $current_url = $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ];

                if ( strpos( $current_url , '?' ) !== false )
                    $mod_current_url = '//' . $current_url . '&wwlc_ignore_empty_license_notice=0';
                else
                    $mod_current_url = '//' . $current_url . '?wwlc_ignore_empty_license_notice=0'; ?>

                <div class="error">
                    <p>
                        <?php echo sprintf( __( 'Please <a href="%1$s">enter your license details</a> for the <b>WooCommerce Wholesale Lead Capture</b> plugin to enable plugin updates.' , 'woocommerce-wholesale-lead-capture' ) , "options-general.php?page=wwc_license_settings&tab=wwlc" ); ?>
                        <a href="<?php echo $mod_current_url; ?>" style="float: right;" id="wwlc_ignore_empty_license_notice"><?php _e( 'Hide Notice' , 'woocommerce-wholesale-lead-capture' ); ?></a>
                    </p>
                </div>

            <?php }

        }

        add_action( 'admin_notices', 'wwlcAdminNotices' );

        /**
         * Ignore empty license notice.
         *
         * @since 1.1.1
         */
        function wwlcHideAdminNotices() {

            global $current_user;
            $user_id = $current_user->ID;

            /* If user clicks to ignore the notice, add that to their user meta */
            if ( isset( $_GET[ 'wwlc_ignore_empty_license_notice' ] ) && '0' == $_GET[ 'wwlc_ignore_empty_license_notice' ] )
                add_user_meta( $user_id , 'wwlc_ignore_empty_license_notice' , 'true' , true );

        }

        add_action( 'admin_init', 'wwlcHideAdminNotices' );

    }

} else {

	// TODO: do something here when this plugin is active and the required plugin dependencies are not present

}