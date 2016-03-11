var wwlcFormActions = function(){

    var resetForm = function( $form ) {

            $form.find( ".form_field").each( function () {

                var $this = jQuery( this );

                if (  $this.attr( 'type' ) !== undefined && $this.attr( 'type' ) != 'radio' && $this.attr( 'type' ) != 'checkbox' )
                    $this.val( "" ).removeClass( "err" ).removeAttr( "disabled" );
                else if ( $this.attr( 'type' ) == 'radio' )
                    $this.removeAttr( "checked" ).removeAttr( "disabled" ).closest( ".field-set" ).removeClass( "err" );
                else if ( $this.attr( 'type' ) == 'checkbox' )
                    $this.removeAttr( "checked" ).removeAttr( "disabled" ).closest( ".field-set" ).removeClass( "err" );
                else
                    $this.removeClass( "err" ).removeAttr( "disabled" ).find( "option:first" ).attr( "selected" , "selected" );

            } );

        },
        deactivateFormControls = function( $form ) {

            $form.find( ".form-control" ).attr( "disabled" , "disabled" );

        },
        activateFormControls = function( $form ){

            $form.find( ".form-control" ).removeAttr( "disabled" );

        },
        displayStatesDropdownField = function( $form, states, stateSelected ){

            var selectField = '<select id="wwlc_state" name="wwlc_state" class="wwlc_registration_field form_field">';
                        selectField += '<option value="">Select an option...</option>';
                jQuery.each( states, function( key, value ) {
                    if( stateSelected === key )
                        selectField += '<option value="' + key + '" selected>' + value + '</option>';
                    else
                        selectField += '<option value="' + key + '">' + value + '</option>';
                });
            selectField += "</select>";

            if( $form.find( "select#wwlc_state" ).length != 0 ){
                
                $form.find( "#wwlc_state" ).siblings( "br" ).remove();
                $form.find( "select#wwlc_state" ).after( selectField );
                $form.find( "select#wwlc_state" ).siblings( ".chosen-container" ).remove();
                $form.find( "select#wwlc_state" ).siblings( ".select2-container" ).remove();
                $form.find( "select#wwlc_state" ).first().remove();                

            }else if( $form.find( "input#wwlc_state" ).length != 0 ){

                $form.find( "#wwlc_state" ).siblings( "br" ).remove();
                $form.find( "input#wwlc_state" ).after( selectField );
                $form.find( "input#wwlc_state" ).remove();

            }

        },
        displayStatesTextField = function( $form ){

            var id = $form.attr( "id" );
            if( id == "your-profile" ){
                // if in profile section
                var inputField = '<input type="text" name="wwlc_state" id="wwlc_state" value="" class="regular-text"><br>';
            }else{
                // if in registration form section
                var inputField = '<input type="text" id="wwlc_state" class="input wwlc_registration_field form_field">';
            }

            if( $form.find( "select#wwlc_state" ).length != 0 ){

                $form.find( "select#wwlc_state" ).after( inputField );
                $form.find( "select#wwlc_state" ).siblings( ".chosen-container" ).remove();
                $form.find( "select#wwlc_state" ).siblings( ".select2-container" ).remove();
                $form.find( "select#wwlc_state" ).remove();

            }
        };

    return {

        resetForm                   :   resetForm,
        deactivateFormControls      :   deactivateFormControls,
        activateFormControls        :   activateFormControls,
        displayStatesDropdownField  :   displayStatesDropdownField,
        displayStatesTextField      :   displayStatesTextField

    };

}();