jQuery( document ).ready( function( $ ) {

    // Variable Declaration And Selector Caching
    var $registration_form  = $( "#wwlc-registration-form" );

    // Events
    $registration_form.find( "select#wwlc_country" ).chosen();
    $( "#wwlc_country" ).live( "change", function(){

        var cc = $(this).val();

        if( cc != "" ){

            wwlcFrontEndAjaxServices.getStates( cc )
                .done( function( data, textStatus, jqXHR ){

                    if ( data.status == 'success' ) {
                        
                        wwlcFormActions.displayStatesDropdownField( $registration_form, data.states );
                        $registration_form.find( "select#wwlc_state" ).chosen();

                    } else {

                        wwlcFormActions.displayStatesTextField( $registration_form );

                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){

                    console.log( jqXHR.responseText );
                    console.log( textStatus );
                    console.log( errorThrown );
                    console.log( '----------' );

                });
        }
    });

    

    $( "#register" ).click(function(){

        if ( $registration_form.find("#honeypot-field").val() != "" ) {

            window.location = RegistrationVars.registrationThankYouPage;
            return false;

        }

        wwlcFormActions.deactivateFormControls( $registration_form );

        var errorNoticeDuration = "8000",
            successNoticeDuration = "5000",
            formFields = [],
            checkpoint = true;

        $registration_form
            .find( ".form_field" )
            .each( function( idx ){

                formFields.push( $(this) );

            } );

        wwlcFormValidator.trimFieldValues( formFields );

        checkpoint = wwlcFormValidator.validateRequiredField( formFields );

        if ( !checkpoint ) {

            toastr.error( RegistrationVars.fill_form_appropriately_message , RegistrationVars.failed_registration_process_message , { "closeButton" : true , "showDuration" : errorNoticeDuration } );
            wwlcFormActions.activateFormControls( $registration_form );
            return false;

        }

        var userData = wwlcFormValidator.constructUserData( formFields );

        $registration_form.find( ".wwlc-loader" ).show();
        wwlcFrontEndAjaxServices.createUser( userData , $("#wwlc_register_user_nonce_field").val() )
            .done(function(data, textStatus, jqXHR){

                if ( data.status == 'success' ) {

                    wwlcFormActions.resetForm( $registration_form );
                    window.location = RegistrationVars.registrationThankYouPage;
                    $registration_form.find( ".wwlc-loader" ).hide();

                } else {

                    $registration_form.find( ".wwlc-loader" ).hide();
                    toastr.error( data.error_message , RegistrationVars.registration_failed_message , { "closeButton" : true , "showDuration" : errorNoticeDuration } );

                    console.log( RegistrationVars.registration_failed_message );
                    console.log( data.error_message );
                    console.log( '----------' );

                }

                wwlcFormActions.activateFormControls( $registration_form );

            })
            .fail(function(jqXHR, textStatus, errorThrown){

                toastr.error( jqXHR.responseText , RegistrationVars.registration_failed_message , { "closeButton" : true , "showDuration" : errorNoticeDuration } );

                console.log( RegistrationVars.settings_save_failed_message );
                console.log( jqXHR );
                console.log( '----------' );

                wwlcFormActions.activateFormControls( $registration_form );
                $registration_form.find( ".wwlc-loader" ).hide();
            });

        return false;

    });

    // On load
    wwlcFormActions.resetForm( $registration_form );
    $registration_form.find( "select" ).find( "option:first" ).attr( 'selected' , 'selected' );

});