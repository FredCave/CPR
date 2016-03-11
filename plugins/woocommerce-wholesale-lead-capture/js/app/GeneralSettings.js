jQuery( document ).ready( function ( $ ) {

    // We do this to achieve deselecting a single value. WooCommerce uses select 2.
    $( "#wwlc_general_login_redirect_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_logout_redirect_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_login_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_registration_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_registration_thankyou" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_terms_and_condition_page_url" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );

} );