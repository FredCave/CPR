jQuery( document ).ready( function ( $ ) {

    /*
     |---------------------------------------------------------------------------------------------------------------
     | Variable Declarations
     |---------------------------------------------------------------------------------------------------------------
     */
    var $custom_field_controls = $( ".custom-field-controls" ),
        $button_controls = $( ".button-controls" ),
        $wholesale_lead_capture_custom_fields = $( "#wholesale-lead-capture-custom-fields" ),
        $select_field_options_container = $( ".select-field-options-container" ),
        $radio_field_options_container = $( ".radio-field-options-container" ),
        $checkbox_field_options_container = $( ".checkbox-field-options-container" ),
        errorMessageDuration = '10000',
        successMessageDuration = '5000';

    /*
     |---------------------------------------------------------------------------------------------------------------
     | Helper Functions
     |---------------------------------------------------------------------------------------------------------------
     */
    function removeTableNoItemsPlaceholder ( $table ) {

        $table.find("tbody").find(".no-items").remove();

    }

    function resetTableRowStyling () {

        $wholesale_lead_capture_custom_fields
            .find( "tbody" )
            .find( "tr" )
            .each( function( index ) {

                index++; // we do this coz index is zero base

                if (index % 2 == 0) {
                    // even
                    $(this)
                        .removeClass("odd")
                        .removeClass("alternate")
                        .addClass("even");

                } else {
                    // odd
                    $(this)
                        .removeClass("even")
                        .addClass("odd")
                        .addClass("alternate");

                }

            } );

    }

    function resetFields () {

        $custom_field_controls.find( "#wwlc_cf_field_name" ).val( '' );
        $custom_field_controls.find( "#wwlc_cf_field_id" ).val( '' ).removeAttr( 'readonly' );
        $custom_field_controls.find( "#wwlc_cf_field_type" )
            .removeAttr( "disabled" )
            .find( "option").removeAttr( "disabled" ).end()
            .find( "option:first" ).attr( 'selected' , 'selected' );
        $custom_field_controls.find( "#wwlc_cf_required_field" ).removeAttr( 'checked' );
        $custom_field_controls.find( "#wwlc_cf_enabled_field" ).removeAttr( 'checked' );
        $custom_field_controls.find( "#wwlc_cf_field_order" ).val( '' );
        $custom_field_controls.find( "#wwlc_cf_field_placeholder" ).val( '' );

        $custom_field_controls.find( ".numeric-field-attributes-container" )
            .find( "#wwlc_cf_attrib_numeric_min" ).val( "" ).end()
            .find( "#wwlc_cf_attrib_numeric_max" ).val( "" ).end()
            .find( "#wwlc_cf_attrib_numeric_step" ).val( "" ).end()
            .css( "display" , "none" );

        $custom_field_controls.find( ".select-field-options-container" )
            .find( ".options-list" ).empty()
            .html( '<li>' +
                        '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                        '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                        '<span class="add dashicons dashicons-plus"></span>' +
                        '<span class="remove dashicons dashicons-no"></span>' +
                    '</li>' ).end()
            .css( 'display' , 'none' );

        $custom_field_controls.find( ".radio-field-options-container" )
            .find( ".options-list" ).empty()
            .html( '<li>' +
                        '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                        '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                        '<span class="add dashicons dashicons-plus"></span>' +
                        '<span class="remove dashicons dashicons-no"></span>' +
                    '</li>' ).end()
            .css( 'display' , 'none' );

        $custom_field_controls.find( ".checkbox-field-options-container" )
            .find( ".options-list" ).empty()
            .html( '<li>' +
                        '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                        '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                        '<span class="add dashicons dashicons-plus"></span>' +
                        '<span class="remove dashicons dashicons-no"></span>' +
                    '</li>' ).end()
            .css( 'display' , 'none' );

    }

    function isNumber( n ) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    /*
     |---------------------------------------------------------------------------------------------------------------
     | Events
     |---------------------------------------------------------------------------------------------------------------
     */
    $button_controls.find( "#add-custom-field" ).click( function () {

        var $this = $( this ),
            $errFields = [];

        $button_controls.addClass( 'processing' );
        $this.attr( 'disabled' , 'disabled' );

        var field_name = $.trim( $custom_field_controls.find( "#wwlc_cf_field_name" ).val() ),
            field_id = $.trim( $custom_field_controls.find( "#wwlc_cf_field_id" ).val() ),
            field_type = $.trim( $custom_field_controls.find( "#wwlc_cf_field_type" ).val() ),
            field_order = $.trim( $custom_field_controls.find( "#wwlc_cf_field_order" ).val() ),
            field_placeholder = $.trim( $custom_field_controls.find( "#wwlc_cf_field_placeholder" ).val() ),
            attributes = [],
            options = [];

        if ( field_name == '' )
            $errFields.push( 'Field Name' );

        if ( field_id == '' )
            $errFields.push( 'Field ID' );

        if ( field_type == '' )
            $errFields.push( 'Field Type' );

        if ( field_order == '' )
            $errFields.push( 'Field Order' );

        if ( $errFields.length > 0 ) {

            var errFieldsStr = '';
            for ( var i = 0 ; i < $errFields.length ; i++ ) {

                if ( errFieldsStr != '' )
                    errFieldsStr += ', ';

                errFieldsStr += $errFields[ i ];

            }

            toastr.error( errFieldsStr , WWLCCustomFieldsControlVars.empty_fields_error_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

            $button_controls.removeClass( 'processing' );
            $this.removeAttr( 'disabled' );

            return false;

        }

        // Get number field attributes
        if ( field_type != '' && field_type == 'number' ) {

            var number_field_attrib = $( ".numeric-field-attributes-container" ),
                min = $.trim( number_field_attrib.find( "#wwlc_cf_attrib_numeric_min" ).val() ),
                max = number_field_attrib.find( "#wwlc_cf_attrib_numeric_max" ).val(),
                step = number_field_attrib.find( "#wwlc_cf_attrib_numeric_step" ).val();

            if ( !isNumber( min ) )
                min = 0;

            if ( !isNumber( max ) )
                max = '';

            if ( !isNumber( step ) )
                step = 1;

            attributes = {
                min : min,
                max : max,
                step : step
            }

        } else if ( field_type != '' && field_type == 'select' ) {

            $select_field_options_container.find( ".options-list" ).find( "li" ).each( function () {

                var $this = $( this );

                options.push( {
                    'value' :   $.trim( $this.find( ".option_value" ).val() ),
                    'text'  :   $.trim( $this.find( ".option_text" ).val() )
                } );

            } );

        } else if ( field_type != '' && field_type == 'radio' ) {

            $radio_field_options_container.find( ".options-list" ).find( "li" ).each( function () {

                var $this = $( this );

                options.push( {
                    'value' :   $.trim( $this.find( ".option_value" ).val() ),
                    'text'  :   $.trim( $this.find( ".option_text" ).val() )
                } );

            } );

        } else if ( field_type != '' && field_type == 'checkbox' ) {

            $checkbox_field_options_container.find( ".options-list" ).find( "li" ).each( function () {

                var $this = $( this );

                options.push( {
                    'value' :   $.trim( $this.find( ".option_value" ).val() ),
                    'text'  :   $.trim( $this.find( ".option_text" ).val() )
                } );

            } );

        }

        var customField = {
            field_name : field_name,
            field_id : 'wwlc_cf_' + field_id,
            field_type : field_type,
            field_order : field_order,
            required : ( $( "#wwlc_cf_required_field").is( ':checked' ) ? 1 : 0 ),
            field_placeholder : field_placeholder,
            enabled : ( $( "#wwlc_cf_enabled_field").is( ':checked' ) ? 1 : 0 ),
            attributes : attributes,
            options : options
        };

        wwlcBackEndAjaxServices.addRegistrationFormCustomField( customField )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    toastr.success( '' , WWLCCustomFieldsControlVars.success_save_message , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    removeTableNoItemsPlaceholder( $wholesale_lead_capture_custom_fields );

                    var tr_class = "";

                    if( $wholesale_lead_capture_custom_fields.find( "tr" ).length % 2 == 0 )
                        tr_class = "odd alternate";
                    else
                        tr_class = "even";

                    $wholesale_lead_capture_custom_fields.find( "tbody" )
                        .append('<tr class="'+tr_class+' edited">' +
                                    '<td class="meta hidden"></td>' +
                                    '<td class="wwlc_cf_td_field_name">' + customField.field_name + '</td>' +
                                    '<td class="wwlc_cf_td_field_id">' + customField.field_id + '</td>' +
                                    '<td class="wwlc_cf_td_field_type">' + customField.field_type + '</td>' +
                                    '<td class="wwlc_cf_td_required">' + ( customField.required ? 'true' : 'false' ) + '</td>' +
                                    '<td class="wwlc_cf_td_field_order">' + customField.field_order + '</td>' +
                                    '<td class="wwlc_cf_td_field_placeholder">' + customField.field_placeholder + '</td>' +
                                    '<td class="wwlc_cf_td_enabled">' + ( customField.required ? 'true' : 'false' ) + '</td>' +
                                    '<td class="controls">' +
                                        '<a class="edit dashicons dashicons-edit"></a>' +
                                        '<a class="delete dashicons dashicons-no"></a>' +
                                    '</td>' +
                                '</tr>');

                    resetFields();

                    setTimeout(function(){
                        $wholesale_lead_capture_custom_fields
                            .find("tr.edited")
                            .removeClass("edited");
                    },2000);

                } else {

                    toastr.error( data.error_message , WWLCCustomFieldsControlVars.failed_save_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( WWLCCustomFieldsControlVars.failed_save_message );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , WWLCCustomFieldsControlVars.failed_save_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( WWLCCustomFieldsControlVars.failed_save_message );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function () {

                $button_controls.removeClass( 'processing' );
                $this.removeAttr( 'disabled' );

            } );

    } );

    $button_controls.find( "#save-custom-field" ).click( function () {

        var $this = $( this ),
            $errFields = [];

        $button_controls.addClass( 'processing' );
        $this
            .attr( 'disabled' , 'disabled' )
            .siblings( '#cancel-edit-custom-field' )
                .attr( 'disabled' , 'disabled' );

        var field_name = $.trim( $custom_field_controls.find( "#wwlc_cf_field_name" ).val() ),
            field_id = $.trim( $custom_field_controls.find( "#wwlc_cf_field_id" ).val() ),
            field_type = $.trim( $custom_field_controls.find( "#wwlc_cf_field_type" ).val() ),
            field_order = $.trim( $custom_field_controls.find( "#wwlc_cf_field_order" ).val() ),
            field_placeholder = $.trim( $custom_field_controls.find( "#wwlc_cf_field_placeholder" ).val() ),
            attributes = [],
            options = [];

        if ( field_name == '' )
            $errFields.push( 'Field Name' );

        if ( field_id == '' )
            $errFields.push( 'Field ID' );

        if ( field_type == '' )
            $errFields.push( 'Field Type' );

        if ( field_order == '' )
            $errFields.push( 'Field Order' );

        if ( $errFields.length > 0 ) {

            var errFieldsStr = '';
            for ( var i = 0 ; i < $errFields.length ; i++ ) {

                if ( errFieldsStr != '' )
                    errFieldsStr += ', ';

                errFieldsStr += $errFields[ i ];

            }

            toastr.error( errFieldsStr , WWLCCustomFieldsControlVars.empty_fields_error_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

            $button_controls.removeClass( 'processing' );
            $this.removeAttr( 'disabled' );

            return false;

        }

        // Get number field attributes
        if ( field_type != '' && field_type == 'number' ) {

            var number_field_attrib = $( ".numeric-field-attributes-container" ),
                min = $.trim( number_field_attrib.find( "#wwlc_cf_attrib_numeric_min" ).val() ),
                max = number_field_attrib.find( "#wwlc_cf_attrib_numeric_max" ).val(),
                step = number_field_attrib.find( "#wwlc_cf_attrib_numeric_step" ).val();

            if ( !isNumber( min ) )
                min = 0;

            if ( !isNumber( max ) )
                max = '';

            if ( !isNumber( step ) )
                step = 1;

            attributes = {
                min : min,
                max : max,
                step : step
            }

        } else if ( field_type != '' && field_type == 'select' ) {

            $select_field_options_container.find( ".options-list" ).find( "li" ).each( function () {

                var $this = $( this );

                options.push( {
                    'value' :   $.trim( $this.find( ".option_value" ).val() ),
                    'text'  :   $.trim( $this.find( ".option_text" ).val() )
                } );

            } );

        } else if ( field_type != '' && field_type == 'radio' ) {

            $radio_field_options_container.find( ".options-list" ).find( "li" ).each( function () {

                var $this = $( this );

                options.push( {
                    'value' :   $.trim( $this.find( ".option_value" ).val() ),
                    'text'  :   $.trim( $this.find( ".option_text" ).val() )
                } );

            } );

        } else if ( field_type != '' && field_type == 'checkbox' ) {

            $checkbox_field_options_container.find( ".options-list" ).find( "li" ).each( function () {

                var $this = $( this );

                options.push( {
                    'value' :   $.trim( $this.find( ".option_value" ).val() ),
                    'text'  :   $.trim( $this.find( ".option_text" ).val() )
                } );

            } );

        }

        var customField = {
            field_name : field_name,
            field_id : 'wwlc_cf_' + field_id,
            field_type : field_type,
            field_order : field_order,
            field_placeholder : field_placeholder,
            required : ( $( "#wwlc_cf_required_field").is( ':checked' ) ? 1 : 0 ),
            enabled : ( $( "#wwlc_cf_enabled_field").is( ':checked' ) ? 1 : 0 ),
            attributes : attributes,
            options : options
        };

        wwlcBackEndAjaxServices.editRegistrationFormCustomField( customField )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    toastr.success( '' , WWLCCustomFieldsControlVars.success_edit_message , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    $wholesale_lead_capture_custom_fields.find( "tr.edited" )
                        .find( ".wwlc_cf_td_field_name" ).text( customField.field_name ).end()
                        .find( ".wwlc_cf_td_field_id" ).text( customField.field_id ).end()
                        .find( ".wwlc_cf_td_field_type" ).text( customField.field_type ).end()
                        .find( ".wwlc_cf_td_field_order" ).text( customField.field_order).end()
                        .find( ".wwlc_cf_td_field_placeholder" ).text( customField.field_placeholder).end()
                        .find( ".wwlc_cf_td_required" ).text( ( customField.required ? 'true' : 'false' ) ).end()
                        .find( ".wwlc_cf_td_enabled" ).text( ( customField.enabled ? 'true' : 'false' ) );

                    resetFields();

                    $button_controls
                        .removeClass( 'edit-mode' )
                        .addClass( 'add-mode' );

                    $wholesale_lead_capture_custom_fields
                        .find( ".edit" ).css( "display" , "inline-block").end()
                        .find( ".delete" ).css( "display" , "inline-block" );

                    setTimeout(function(){
                        $wholesale_lead_capture_custom_fields
                            .find("tr.edited")
                            .removeClass("edited");
                    },1000);

                } else {

                    toastr.error( data.error_message , WWLCCustomFieldsControlVars.failed_edit_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( WWLCCustomFieldsControlVars.failed_edit_message );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , WWLCCustomFieldsControlVars.failed_edit_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( WWLCCustomFieldsControlVars.failed_edit_message );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function () {

                $button_controls.removeClass( "processing" );

                $this
                    .removeAttr( 'disabled' )
                    .siblings( '#cancel-edit-custom-field' )
                        .removeAttr( 'disabled' );

            } );

    } );

    $button_controls.find( "#cancel-edit-custom-field" ).click( function () {

        resetFields();

        $button_controls
            .removeClass( 'processing' )
            .removeClass( 'edit-mode' )
            .addClass( 'add-mode' );

        $wholesale_lead_capture_custom_fields
            .find( "tr" ).removeClass( "edited" )
            .find( ".edit" ).css( "display" , "inline-block").end()
            .find( ".delete" ).css( "display" , "inline-block" );

    } );

    $wholesale_lead_capture_custom_fields.delegate( ".edit" , "click" , function () {

        var $this = $( this ),
            $current_tr = $this.closest( "tr" ),
            field_id = $.trim( $current_tr.find( ".wwlc_cf_td_field_id" ).text());

        $current_tr.addClass( "edited" );

        $wholesale_lead_capture_custom_fields
            .find( ".edit" ).css( "display" , "none" ).end()
            .find( ".delete" ).css( "display" , "none" );

        wwlcBackEndAjaxServices.getRegistrationFormCustomFieldByID( field_id )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    resetFields();

                    $custom_field_controls
                        .find( "#wwlc_cf_field_name" ).val( data.custom_field.field_name ).end()
                        .find( "#wwlc_cf_field_id" ).val( data.custom_field.field_id.replace( 'wwlc_cf_' , '' ) ).attr( 'readonly' , 'readonly' ).end()
                        .find( "#wwlc_cf_field_type" ).val( data.custom_field.field_type ).end()
                        .find( "#wwlc_cf_field_order" ).val( data.custom_field.field_order ).end()
                        .find( "#wwlc_cf_field_placeholder" ).val( data.custom_field.field_placeholder ).end();

                    // When editing fields, checkbox can't change to any other fields and vice versa
                    // It is because of the nature of a check box which can have multiple values
                    // All other fields have single value
                    if ( data.custom_field.field_type == 'checkbox' )
                        $custom_field_controls.find( "#wwlc_cf_field_type" ).attr( "disabled" , "disabled" );
                    else
                        $custom_field_controls.find( "#wwlc_cf_field_type").find( "option[value='checkbox']").attr( 'disabled' , 'disabled' );

                    if ( data.custom_field.required == "1" )
                        $custom_field_controls.find( "#wwlc_cf_required_field" ).attr( 'checked' , 'checked' );
                    else
                        $custom_field_controls.find( "#wwlc_cf_required_field" ).removeAttr( 'checked' );

                    if ( data.custom_field.enabled == "1" )
                        $custom_field_controls.find( "#wwlc_cf_enabled_field" ).attr( 'checked' , 'checked' );
                    else
                        $custom_field_controls.find( "#wwlc_cf_enabled_field" ).removeAttr( 'checked' );

                    $custom_field_controls.find( "#wwlc_cf_field_placeholder" ).val( data.custom_field.field_placeholder );

                    // Get number field attributes
                    if ( data.custom_field.field_type == 'number' ) {

                        var $numeric_field_attributes_container = $( ".numeric-field-attributes-container" );

                        $numeric_field_attributes_container
                            .find( "#wwlc_cf_attrib_numeric_min" ).val( data.custom_field.attributes.min ).end()
                            .find( "#wwlc_cf_attrib_numeric_max" ).val( data.custom_field.attributes.max ).end()
                            .find( "#wwlc_cf_attrib_numeric_step" ).val( data.custom_field.attributes.step ).end()
                            .css( "display" , "block" );

                    } else if ( data.custom_field.field_type == 'select' ) {

                        var li_html = '';
                        for ( var i = 0 ; i < data.custom_field.options.length ; i++ ) {

                            li_html += '<li>' +
                                            '<input type="text" class="option_value" placeholder="Option Value" value="' + data.custom_field.options[ i ].value + '"/>' +
                                            '<input type="text" class="option_text" placeholder="Option Text" value="' + data.custom_field.options[ i ].text + '"/>' +
                                            '<span class="add dashicons dashicons-plus"></span>' +
                                            '<span class="remove dashicons dashicons-no"></span>' +
                                        '</li>';

                        }

                        $select_field_options_container.find( ".options-list" ).html( li_html ).end().css( "display" , "block" );

                    } else if ( data.custom_field.field_type == 'radio' ) {

                        var li_html = '';
                        for ( var i = 0 ; i < data.custom_field.options.length ; i++ ) {

                            li_html += '<li>' +
                                            '<input type="text" class="option_value" placeholder="Option Value" value="' + data.custom_field.options[ i ].value + '"/>' +
                                            '<input type="text" class="option_text" placeholder="Option Text" value="' + data.custom_field.options[ i ].text + '"/>' +
                                            '<span class="add dashicons dashicons-plus"></span>' +
                                            '<span class="remove dashicons dashicons-no"></span>' +
                                        '</li>';
                            
                        }

                        $radio_field_options_container.find( ".options-list" ).html( li_html ).end().css( "display" , "block" );

                    } else if ( data.custom_field.field_type == 'checkbox' ) {

                        var li_html = '';
                        for ( var i = 0 ; i < data.custom_field.options.length ; i++ ) {

                            li_html += '<li>' +
                                            '<input type="text" class="option_value" placeholder="Option Value" value="' + data.custom_field.options[ i ].value + '"/>' +
                                            '<input type="text" class="option_text" placeholder="Option Text" value="' + data.custom_field.options[ i ].text + '"/>' +
                                            '<span class="add dashicons dashicons-plus"></span>' +
                                            '<span class="remove dashicons dashicons-no"></span>' +
                                        '</li>';

                        }

                        $checkbox_field_options_container.find( ".options-list" ).html( li_html ).end().css( "display" , "block" );

                    }

                    $button_controls
                        .removeClass( 'processing' )
                        .removeClass( 'add-mode' )
                        .addClass( 'edit-mode' );

                } else {

                    toastr.error( data.error_message , WWLCCustomFieldsControlVars.failed_retrieve_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( WWLCCustomFieldsControlVars.failed_retrieve_message );
                    console.log( data );
                    console.log( '----------' );

                    $current_tr.removeClass( "edited" );

                    $wholesale_lead_capture_custom_fields
                        .find( ".edit" ).css( "display" , "inline-block" ).end()
                        .find( ".delete" ).css( "display" , "inline-block" );

                }

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , WWLCCustomFieldsControlVars.failed_retrieve_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( WWLCCustomFieldsControlVars.failed_retrieve_message );
                console.log( jqXHR );
                console.log( '----------' );

                $current_tr.removeClass( "edited" );

                $wholesale_lead_capture_custom_fields
                    .find( ".edit" ).css( "display" , "inline-block" ).end()
                    .find( ".delete" ).css( "display" , "inline-block" );

            } );

    } );

    $wholesale_lead_capture_custom_fields.delegate( ".delete" , "click" , function () {

        var $this = $( this ),
            $current_tr = $this.closest( "tr" );

        $current_tr.addClass( "edited" );

        if ( confirm( WWLCCustomFieldsControlVars.confirm_box_message ) ) {

            var field_id = $.trim( $current_tr.find( ".wwlc_cf_td_field_id" ).text() );

            $wholesale_lead_capture_custom_fields
                .find( ".edit" ).css( "display" , "none" ).end()
                .find( ".delete" ).css( "display" , "none" );

            wwlcBackEndAjaxServices.deleteRegistrationFormCustomField( field_id )
                .done( function ( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        $current_tr.fadeOut( "fast" , function () {

                            $current_tr.remove();

                            resetTableRowStyling();

                            if ( $wholesale_lead_capture_custom_fields.find( "tbody" ).find( "tr" ).length <= 0 ) {

                                $wholesale_lead_capture_custom_fields
                                    .find("tbody")
                                    .html(  '<tr class="no-items">' +
                                    '<td class="colspanchange" colspan="7">' + WWLCCustomFieldsControlVars.no_custom_field_message + '</td>' +
                                    '</tr>');

                            }

                        } );

                        toastr.success( '' , WWLCCustomFieldsControlVars.success_delete_message , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    } else {

                        toastr.error( data.error_message , WWLCCustomFieldsControlVars.failed_delete_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                        console.log( 'Failed To Delete Custom Field' );
                        console.log( data );
                        console.log( '----------' );

                        $current_tr.removeClass( "edited" );

                    }

                } )
                .fail( function ( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , WWLCCustomFieldsControlVars.failed_delete_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( WWLCCustomFieldsControlVars.failed_delete_message );
                    console.log( jqXHR );
                    console.log( '----------' );

                    $current_tr.removeClass( "edited" );

                } )
                .always( function () {

                    $wholesale_lead_capture_custom_fields
                        .find( ".edit" ).css( "display" , "inline-block" ).end()
                        .find( ".delete" ).css( "display" , "inline-block" );

                } );

        } else {

            $current_tr.removeClass( "edited" );

        }

    } );

    $custom_field_controls.find( "#wwlc_cf_field_type" ).change( function () {

        var $this = $( this );

        switch ( $this.val() ) {

            case 'number':
                $custom_field_controls.find( ".numeric-field-attributes-container" ).css( 'display' , 'block' );
                $custom_field_controls.find( ".select-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".radio-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".checkbox-field-options-container" ).css( 'display' , 'none' );
                break;
            case 'select':
                $custom_field_controls.find( ".select-field-options-container" ).css( 'display' , 'block' );
                $custom_field_controls.find( ".numeric-field-attributes-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".radio-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".checkbox-field-options-container" ).css( 'display' , 'none' );
                break;
            case 'radio':
                $custom_field_controls.find( ".radio-field-options-container" ).css( 'display' , 'block' );
                $custom_field_controls.find( ".select-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".numeric-field-attributes-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".checkbox-field-options-container" ).css( 'display' , 'none' );
                break;
            case 'checkbox':
                $custom_field_controls.find( ".checkbox-field-options-container" ).css( 'display' , 'block' );
                $custom_field_controls.find( ".radio-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".select-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".numeric-field-attributes-container" ).css( 'display' , 'none' );
                break;
            default:
                $custom_field_controls.find( ".checkbox-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".radio-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".select-field-options-container" ).css( 'display' , 'none' );
                $custom_field_controls.find( ".numeric-field-attributes-container" ).css( 'display' , 'none' );

        }

    } );

    $select_field_options_container.find( ".options-list" ).delegate( ".add" , "click" , function () {

        var $this = $( this ),
            $current_li = $this.closest( "li" );

        $current_li.after( '<li>' +
                                '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                                '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                                '<span class="add dashicons dashicons-plus"></span>' +
                                '<span class="remove dashicons dashicons-no"></span>' +
                            '</li>' );

    } );

    $radio_field_options_container.find( ".options-list" ).delegate( ".add" , "click" , function () {

        var $this = $( this ),
            $current_li = $this.closest( "li" );

        $current_li.after( '<li>' +
                                '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                                '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                                '<span class="add dashicons dashicons-plus"></span>' +
                                '<span class="remove dashicons dashicons-no"></span>' +
                            '</li>' );

    } );

    $checkbox_field_options_container.find( ".options-list" ).delegate( ".add" , "click" , function () {

        var $this = $( this ),
            $current_li = $this.closest( "li" );

        $current_li.after( '<li>' +
                                '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                                '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                                '<span class="add dashicons dashicons-plus"></span>' +
                                '<span class="remove dashicons dashicons-no"></span>' +
                            '</li>' );

    } );

    $select_field_options_container.find( ".options-list" ).delegate( ".remove" , "click" , function () {

        var $this = $( this ),
            $current_li = $this.closest( "li" );

        $current_li.fadeOut( "fast" , function () {

            $current_li.remove();

        } );

    } );

    $radio_field_options_container.find( ".options-list" ).delegate( ".remove" , "click" , function () {

        var $this = $( this ),
            $current_li = $this.closest( "li" );

        $current_li.fadeOut( "fast" , function () {

            $current_li.remove();

        } );

    } );

    $checkbox_field_options_container.find( ".options-list" ).delegate( ".remove" , "click" , function () {

        var $this = $( this ),
            $current_li = $this.closest( "li" );

        $current_li.fadeOut( "fast" , function () {

            $current_li.remove();

        } );

    } );

    /*
     |---------------------------------------------------------------------------------------------------------------
     | On Load
     |---------------------------------------------------------------------------------------------------------------
     */
    $custom_field_controls.find( ".select-field-options-container" )
        .find( ".options-list" ).empty()
        .html( '<li>' +
                    '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                    '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                    '<span class="add dashicons dashicons-plus"></span>' +
                    '<span class="remove dashicons dashicons-no"></span>' +
                '</li>' );

    $custom_field_controls.find( ".radio-field-options-container" )
        .find( ".options-list" ).empty()
        .html( '<li>' +
                    '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                    '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                    '<span class="add dashicons dashicons-plus"></span>' +
                    '<span class="remove dashicons dashicons-no"></span>' +
                '</li>' );

    $custom_field_controls.find( ".checkbox-field-options-container" )
        .find( ".options-list" ).empty()
        .html( '<li>' +
                    '<input type="text" class="option_value" placeholder="Option Value" value=""/>' +
                    '<input type="text" class="option_text" placeholder="Option Text" value=""/>' +
                    '<span class="add dashicons dashicons-plus"></span>' +
                    '<span class="remove dashicons dashicons-no"></span>' +
                '</li>' );

} );