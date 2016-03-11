var wwlcFormValidator = function(){

    var trimFieldValues = function( formFields ){

            for ( var i = 0 ; i < formFields.length ; i++ ) {

                formFields[i].val( jQuery.trim( formFields[i].val() ) );

            }

        },
        validateRequiredField = function( formFields ){

            var checkpoint = true,
                radioFields = {},
                radioCheckpoints = {},
                checkboxFields = {},
                checkboxCheckpoints = {},
                generalRadioCheckpoint = true,
                generalCheckboxCheckpoint = true;

            for ( var i = 0 ; i < formFields.length ; i++ ) {

                if ( formFields[i].attr( 'type' ) == 'checkbox' ) {

                    if ( checkboxFields[ formFields[i].attr( 'name' ) ] === undefined )
                        checkboxFields[ formFields[i].attr( 'name' ) ] = [];

                    checkboxFields[ formFields[i].attr( 'name' ) ].push( formFields[i].is( ":checked" ) ? 1 : 0 );
                    checkboxCheckpoints[ formFields[i].attr( 'name' ) ] = false;

                } else if (formFields[i].attr( 'type' ) == 'radio' ) {

                    if ( radioFields[ formFields[i].attr( 'name' ) ] === undefined )
                        radioFields[ formFields[i].attr( 'name' ) ] = [];

                    radioFields[ formFields[i].attr( 'name' ) ].push( formFields[i].is( ":checked" ) ? 1 : 0 );
                    radioCheckpoints[ formFields[i].attr( 'name' ) ] = false;

                } else {

                    if ( formFields[i].attr('data-required') == 'yes' && formFields[i].val() == '' ) {

                        formFields[i].addClass('err');
                        checkpoint = false;

                    } else
                        formFields[i].removeClass('err');

                }

            }

            // This is the section where we validate checkbox fields
            for ( var key in checkboxFields ) {

                if ( checkboxFields.hasOwnProperty( key ) ) {

                    var checkboxField = checkboxFields[ key ];

                    for( var i = 0; i < checkboxField.length ; i++ ) {

                        if ( checkboxField[ i ] )
                            checkboxCheckpoints[ key ] = true;

                    }

                    if ( !checkboxCheckpoints[ key ] )
                        jQuery( "input[ name = '" + key + "' ]" ).closest( ".field-set" ).addClass( "err" );
                    else
                        jQuery( "input[ name = '" + key + "' ]" ).closest( ".field-set" ).removeClass( "err" );

                    generalCheckboxCheckpoint = generalCheckboxCheckpoint && checkboxCheckpoints[ key ];

                }

            }

            // This is the section where we validate radio fields
            for ( var key in radioFields ) {

                if ( radioFields.hasOwnProperty( key ) ) {

                    var radioField = radioFields[ key ];

                    for( var i = 0; i < radioField.length ; i++ ) {

                        if ( radioField[ i ] )
                            radioCheckpoints[ key ] = true;

                    }

                    if ( !radioCheckpoints[ key ] )
                        jQuery( "input[ name = '" + key + "' ]" ).closest( ".field-set" ).addClass( "err" );
                    else
                        jQuery( "input[ name = '" + key + "' ]" ).closest( ".field-set" ).removeClass( "err" );

                    generalRadioCheckpoint = generalRadioCheckpoint && radioCheckpoints[ key ];

                }

            }

            return checkpoint && generalRadioCheckpoint && generalCheckboxCheckpoint;

        },
        constructUserData = function( formFields ){

            var userData = {},
                radioFields = {},
                checkboxFields = {};

            for ( var i = 0 ; i < formFields.length ; i++ ) {

                switch ( formFields[i].attr( "id" ) ) {

                    case 'first_name':
                        userData.first_name =  formFields[i].val();
                        break;

                    case 'last_name':
                        userData.last_name = formFields[i].val();
                        break;

                    case 'wwlc_phone':
                        userData.wwlc_phone = formFields[i].val();
                        break;

                    case 'user_email':
                        userData.user_email = formFields[i].val();
                        break;

                    case 'wwlc_company_name':
                        userData.wwlc_company_name = formFields[i].val();
                        break;

                    case 'wwlc_address':
                        userData.wwlc_address = formFields[i].val();
                        break;

                    default:

                        if ( formFields[i].attr( 'type' ) == 'checkbox' ) {

                            if ( formFields[i].is( ":checked" ) ) {

                                if ( checkboxFields[ formFields[i].attr( 'name' ) ] === undefined )
                                    checkboxFields[ formFields[i].attr( 'name' ) ] = [];

                                checkboxFields[ formFields[i].attr( 'name' ) ].push( formFields[i].val() );

                            }

                        } else if ( formFields[i].attr( 'type' ) == 'radio' ) {

                            if ( formFields[i].is( ":checked" ) )
                                if ( radioFields[ formFields[i].attr( 'name' ) ] === undefined )
                                    radioFields[ formFields[i].attr( 'name' ) ] = formFields[i].val();

                        } else
                            userData[ formFields[i].attr( "id" ) ] = formFields[i].val();

                        break;
                }

            }

            for ( var key in checkboxFields )
                if ( checkboxFields.hasOwnProperty( key ) )
                    userData[ key ] = checkboxFields[ key ];

            for ( var key in radioFields )
                if ( radioFields.hasOwnProperty( key ) )
                    userData[ key ] = radioFields[ key ];

            return userData;

        };

    return {

        validateRequiredField   :   validateRequiredField,
        trimFieldValues         :   trimFieldValues,
        constructUserData       :   constructUserData

    };

}();