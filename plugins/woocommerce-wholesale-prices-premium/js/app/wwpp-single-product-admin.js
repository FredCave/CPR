/* globals jQuery */
jQuery( document ).ready( function( $ ) {

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Variable Declarations
     |------------------------------------------------------------------------------------------------------------------
     */

    var error_message_duration = '10000',
        success_message_duration = '5000';




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Helper Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    function validate_empty_fields( wholesale_role , min , max , price , currency ) {

        var err_fields = [];

        if ( wholesale_role == '' )
            err_fields.push( 'Wholesale Role' )

        if ( min == '' || isNaN( min ) )
            err_fields.push( 'Starting Qty' );

        if ( max != '' && isNaN( max ) )
            err_fields.push( 'Ending Qty' );

        if ( price == '' )
            err_fields.push( 'Wholesale Price' );

        if ( currency == '' )
            err_fields.push( 'Currency' );

        return err_fields;

    }

    function validate_quantity_fields( min , max ) {

        if ( max != '' && max < min )
            return false;
        else
            return true;

    }

    function remove_table_no_items_placeholder( $table ) {

        $table.find( "tbody" ).find( ".no-items" ).remove();

    }

    function reset_table_row_styling( $table ) {

        $table
            .find( "tbody" )
            .find( "tr" )
            .each( function( index ) {

                index++; // we do this coz index is zero base

                if ( index % 2 == 0 ) {

                    // even
                    $(this)
                        .removeClass( "odd" )
                        .removeClass( "alternate" )
                        .addClass( "even" );

                } else {

                    // odd
                    $(this)
                        .removeClass( "even" )
                        .addClass( "odd" )
                        .addClass( "alternate" );

                }

            } );

    }

    function reset_fields( $parent_fields_container ) {

        $parent_fields_container.find( ".mapping-index" ).val( '' );
        $parent_fields_container.find( ".pqbwp_registered_wholesale_roles" ).find( "option:first-child" ).attr( 'selected' , 'selected' );
        $parent_fields_container.find( ".pqbwp_minimum_order_quantity" ).val( '' );
        $parent_fields_container.find( ".pqbwp_maximum_order_quantity" ).val( '' );
        $parent_fields_container.find( ".pqbwp_wholesale_price" ).val( '' );

        if ( $parent_fields_container.find( ".pqbwp_enabled_currencies" ).length > 0 )
            $parent_fields_container.find( ".pqbwp_enabled_currencies" ).find( "option:contains('(Base Currency)')" ).attr( 'selected' , 'selected' );

    }

    function highlight_error_row( $tr ) {

        var addClass = true,
            interval = setInterval( function() {

                if ( addClass ) {

                    $tr.addClass( 'err-row' );
                    addClass = false;

                } else {

                    $tr.removeClass( 'err-row' );
                    addClass = true;

                }

            } , 1000 );

        setTimeout( function() {

            clearInterval( interval );
            $tr.removeClass( 'err-row' );

        } , 4000 );

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Events
     |------------------------------------------------------------------------------------------------------------------
     */

    $( 'body' ).delegate( '.pqbwp-enable' , 'click' , function() {

        var $this = $( this ),
            $parent_fields_container = $this.closest( '.product-quantity-based-wholesale-pricing' ),
            $processing_indicator = $parent_fields_container.find( ".processing-indicator" ),
            $pqbwp_controls = $parent_fields_container.find( ".pqbwp-controls" ),
            post_id = $.trim( $this.siblings( '.post-id' ).text() ),
            enable = $this.is( ":checked" ) ? 'yes' : 'no';

        $this.attr( 'disabled' , 'disabled' );

        if ( enable == 'yes' ) {

            $processing_indicator.css( "display" , "block" );

        } else {

            $pqbwp_controls.slideUp( "fast" , function() {

                $processing_indicator.css( "display" , "block" );

            } );

        }

        wwppBackendAjaxServices.toggle_product_quantity_based_wholesale_pricing( post_id , enable )
            .done( function( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    if ( enable == 'yes' ) {

                        $processing_indicator.css( "display" , "none" );
                        $pqbwp_controls.slideDown( "fast" );

                    } else {

                        $processing_indicator.css( "display" , "none" );

                    }

                } else {

                    var err_msg;

                    if ( enable == 'yes' )
                        err_msg = 'Failed to enable Product Quantity Based Wholesale Pricing options.';
                    else
                        err_msg = 'Failed to disable Product Quantity Based Wholesale Pricing options.';

                    toastr.error( '' , err_msg , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( err_msg );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function( jqXHR , textStatus , data ) {

                var err_msg;

                if ( enable == 'yes' )
                    err_msg = 'Failed to enable Product Quantity Based Wholesale Pricing options.';
                else
                    err_msg = 'Failed to disable Product Quantity Based Wholesale Pricing options.';

                toastr.error( '' , err_msg , { "closeButton" : true , "showDuration" : error_message_duration } );

                console.log( err_msg );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function() {

                $this.removeAttr( 'disabled' );

            } );

    } );

    $( 'body' ).delegate( '.pqbwp-add-rule' , 'click' , function() {

        var $this = $( this ),
            $parent_fields_container = $this.closest( '.product-quantity-based-wholesale-pricing' ),
            $parent_button_controls = $this.closest( '.button-controls' ),
            $table_mapping = $parent_fields_container.find( '.pqbwp-mapping' );

        $this.attr( 'disabled' , 'disabled' );
        $parent_button_controls.addClass( 'processing' );

        var wholesale_role = $.trim( $parent_fields_container.find( ".pqbwp_registered_wholesale_roles" ).val() ),
            start_qty = $.trim( $parent_fields_container.find( ".pqbwp_minimum_order_quantity" ).val() ),
            end_qty = $.trim( $parent_fields_container.find( ".pqbwp_maximum_order_quantity" ).val() ),
            wholesale_price = $.trim( $parent_fields_container.find( ".pqbwp_wholesale_price" ).val() ),
            currency_field = $parent_fields_container.find( ".pqbwp_enabled_currencies" ),
            currency = '',
            err_fields = '';

        if ( currency_field.length > 0 ) {

            currency = $.trim( currency_field.val() );
            err_fields = validate_empty_fields( wholesale_role , start_qty , end_qty , wholesale_price , currency );

        } else
            err_fields = validate_empty_fields( wholesale_role , start_qty , end_qty , wholesale_price );

        if ( err_fields.length > 0 ) {

            var err_msg = 'The following fields are not properly filled:<br/><br/></ul>';

            for ( var i = 0 ; i < err_fields.length ; i++ )
                err_msg += '<li>' + err_fields[ i ] + '</li>';

            err_msg += '</ul>';

            toastr.error( err_msg , 'Please fill the form properly' , { "closeButton" : true , "showDuration" : error_message_duration } );

            $this.removeAttr( 'disabled' );
            $parent_button_controls.removeClass( 'processing' );

            return false;

        }

        start_qty = parseInt( start_qty , 10 );
        end_qty = ( end_qty != '' ) ? parseInt( end_qty , 10 ) : '';

        if ( !validate_quantity_fields( start_qty , end_qty ) ) {

            toastr.error( '' , 'Ending Qty must not be less than Starting Qty' , { "closeButton" : true , "showDuration" : error_message_duration } );

            $this.removeAttr( 'disabled' );
            $parent_button_controls.removeClass( 'processing' );

            return false;

        }

        var post_id = $.trim( $parent_fields_container.find( ".post-id" ).text() ),
            rule = {
                    wholesale_role  :   wholesale_role,
                    start_qty       :   start_qty,
                    end_qty         :   end_qty,
                    wholesale_price :   wholesale_price
                };

        if ( currency != '' )
            rule[ 'currency' ] = currency;

        wwppBackendAjaxServices.addQuantityDiscountRule( post_id , rule )
            .done( function( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    toastr.success( '' , 'Successfully Added Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : success_message_duration } );

                    remove_table_no_items_placeholder( $table_mapping );

                    var tr_class = "";

                    if ( $table_mapping.find( "tr" ).length % 2 == 0 ) // currently even, next add (our add) would make it odd
                        tr_class = "odd alternate";
                    else // currently odd, next add (our add) would make it even
                        tr_class = "even";

                    var tr_currency = "";

                    if ( currency )
                        tr_currency = '<td class="currency">' + currency + '</td>';

                    $table_mapping.find( "tbody" )
                        .append( '<tr class="' + tr_class + ' edited">' +
                                    '<td class="meta hidden">' +
                                        '<span class="index">' + data.last_inserted_item_index + '</span>' +
                                        '<span class="wholesale-role">' + wholesale_role + '</span>' +
                                        '<span class="wholesale-price">' + wholesale_price + '</span>' +
                                    '</td>' +
                                    '<td class="wholesale-role-text">' + $parent_fields_container.find( ".pqbwp_registered_wholesale_roles" ).find( "option[value='" + wholesale_role + "']" ).text() + '</td>' +
                                    '<td class="start-qty">' + start_qty + '</td>' +
                                    '<td class="end-qty">' + end_qty + '</td>' +
                                    '<td class="wholesale-price-text">' + data.wholesale_price_text + '</td>' +
                                    tr_currency +
                                    '<td class="controls">' +
                                        '<a class="edit dashicons dashicons-edit"></a>' +
                                        '<a class="delete dashicons dashicons-no"></a>' +
                                    '</td>' +
                                '</tr>' ) ;

                    reset_fields( $parent_fields_container );

                    // Remove edited class to the recently added user field
                    setTimeout( function () {
                        $table_mapping
                            .find( "tr.edited" )
                            .removeClass( "edited" );
                    } , 500 );

                } else {

                    // Highlight dup and/or overlapping rows
                    for ( var i = 0; i < data.additional_data.dup_index.length; i++ )
                        highlight_error_row( $table_mapping.find( "td.meta .index:contains(" + data.additional_data.dup_index[ i ] + ")" ).closest( "tr" ) );

                    toastr.error( data.error_message , 'Failed To Add Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( 'Failed To Add Quantity Discount Rule Mapping' );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , 'Failed To Add Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : error_message_duration } );

                console.log( 'Failed To Add Quantity Discount Rule Mapping' );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function() {

                $this.removeAttr( 'disabled' );
                $parent_button_controls.removeClass( 'processing' );

            } );

    } );

    $( 'body' ).delegate( '.pqbwp-save-rule' , 'click' , function() {

        var $this = $( this ),
            $parent_fields_container = $this.closest( '.product-quantity-based-wholesale-pricing' ),
            $parent_button_controls = $this.closest( '.button-controls' ),
            $table_mapping = $parent_fields_container.find( '.pqbwp-mapping' );

        $parent_button_controls.find( ".button" ).attr( 'disabled' , 'disabled' );
        $parent_button_controls.addClass( 'processing' );

        var wholesale_role = $.trim( $parent_fields_container.find( ".pqbwp_registered_wholesale_roles" ).val() ),
            start_qty = $.trim( $parent_fields_container.find( ".pqbwp_minimum_order_quantity" ).val() ),
            end_qty = $.trim( $parent_fields_container.find( ".pqbwp_maximum_order_quantity" ).val() ),
            wholesale_price = $.trim( $parent_fields_container.find( ".pqbwp_wholesale_price" ).val() ),
            currency_field = $parent_fields_container.find( ".pqbwp_enabled_currencies" ),
            currency = '',
            err_fields = '';

        if ( currency_field.length > 0 ) {

            currency = $.trim( currency_field.val() );
            err_fields = validate_empty_fields( wholesale_role , start_qty , end_qty , wholesale_price , currency );

        } else
            err_fields = validate_empty_fields( wholesale_role , start_qty , end_qty , wholesale_price );

        if ( err_fields.length > 0 ) {

            var err_msg = 'The following fields are not properly filled:<br/><br/></ul>';

            for ( var i = 0 ; i < err_fields.length ; i++ )
                err_msg += '<li>' + err_fields[ i ] + '</li>';

            err_msg += '</ul>';

            toastr.error( err_msg , 'Please fill the form properly' , { "closeButton" : true , "showDuration" : error_message_duration } );

            $parent_button_controls.find( ".button" ).removeAttr( 'disabled' );
            $parent_button_controls.removeClass( 'processing' );

            return false;

        }

        start_qty = parseInt( start_qty , 10 );
        end_qty = ( end_qty != '' ) ? parseInt( end_qty , 10 ) : '';

        if ( !validate_quantity_fields( start_qty , end_qty ) ) {

            toastr.error( '' , 'Ending Qty must not be less than Starting Qty' , { "closeButton" : true , "showDuration" : error_message_duration } );

            $parent_button_controls.find( ".button" ).removeAttr( 'disabled' );
            $parent_button_controls.removeClass( 'processing' );

            return false;

        }

        var post_id = $.trim( $parent_fields_container.find( ".post-id" ).text() ),
            index = $.trim( $parent_fields_container.find( ".mapping-index" ).val() ),
            rule = {
                wholesale_role  :   wholesale_role,
                start_qty       :   start_qty,
                end_qty         :   end_qty,
                wholesale_price :   wholesale_price
            };

        if ( currency )
            rule[ 'currency' ] = currency;

        wwppBackendAjaxServices.saveQuantityDiscountRule( post_id , index , rule )
            .done( function( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    $table_mapping.find( "tr.edited" )
                        .find( ".meta" )
                            .find( ".wholesale-role" ).text( wholesale_role ).end()
                            .find( ".wholesale-price" ).text( wholesale_price ).end()
                            .end()
                        .find( ".wholesale-role-text" ).text( $parent_fields_container.find( ".pqbwp_registered_wholesale_roles" ).find( "option[value='" + wholesale_role + "']" ).text() ).end()
                        .find( ".start-qty" ).text( start_qty ).end()
                        .find( ".end-qty" ).text( end_qty ).end()
                        .find( ".wholesale-price-text" ).html( data.wholesale_price_text );

                    if ( currency ) {

                        $table_mapping.find( "tr.edited" )
                            .find( "currency" ).text( currency );

                    }

                    $table_mapping.find( "tr" )
                        .removeClass( "edited" )
                        .removeClass( "disabled" );

                    reset_fields( $parent_fields_container );

                    // Remove edited class to the recently added user field
                    setTimeout( function () {
                        $table_mapping
                            .find( "tr.edited" )
                            .removeClass( "edited" );
                    } , 500 );

                    $parent_button_controls
                        .removeClass( "edit-mode" )
                        .addClass( "add-mode" );

                    toastr.success( '' , 'Successfully Updated Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : success_message_duration } );

                } else {

                    // Highlight dup and/or overlapping rows
                    for ( var i = 0; i < data.additional_data.dup_index.length; i++ )
                        highlight_error_row( $table_mapping.find( "td.meta .index:contains(" + data.additional_data.dup_index[ i ] + ")" ).closest( "tr" ) );

                    toastr.error( data.error_message , 'Failed To Update Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( 'Failed To Update Quantity Discount Rule Mapping' );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , 'Failed To Update Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : error_message_duration } );

                console.log( 'Failed To Update Quantity Discount Rule Mapping' );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function() {

                $parent_button_controls.find( ".button" ).removeAttr( 'disabled' );
                $parent_button_controls.removeClass( 'processing' );

            } );

    } );

    $( 'body' ).delegate( '.pqbwp-cancel' , 'click' , function() {

        var $this = $( this ),
            $parent_fields_container = $this.closest( '.product-quantity-based-wholesale-pricing' ),
            $parent_button_controls = $this.closest( '.button-controls' ),
            $table_mapping = $parent_fields_container.find( '.pqbwp-mapping' );

        reset_fields( $parent_fields_container );

        $parent_button_controls
            .removeClass( 'edit-mode' )
            .addClass( 'add-mode' );

        $table_mapping.find( "tr" )
            .removeClass( "edited" )
            .removeClass( "disabled" );

    } );

    $( 'body' ).delegate( '.edit' , 'click' , function() {

        var $this = $( this ),
            $current_tr = $this.closest( 'tr' ),
            $table_mapping = $current_tr.closest( ".pqbwp-mapping" );
            $parent_fields_container = $this.closest( '.product-quantity-based-wholesale-pricing' ),
            $parent_button_controls = $parent_fields_container.find( '.button-controls' ),
            index = $.trim( $current_tr.find( ".meta" ).find( ".index" ).text() ),
            wholesale_role = $.trim( $current_tr.find( ".meta" ).find( ".wholesale-role" ).text() ),
            start_qty = $.trim( $current_tr.find( ".start-qty" ).text() ),
            end_qtry = $.trim( $current_tr.find( ".end-qty" ).text() ),
            wholesale_price = $.trim( $current_tr.find( ".meta" ).find( ".wholesale-price" ).text() ),
            currency = '';

        if ( $current_tr.find( ".currency" ).length > 0 )
            currency = $.trim( $current_tr.find( ".currency" ).text() );

        $parent_fields_container.find( ".mapping-index" ).val( index );
        $parent_fields_container.find( ".pqbwp_registered_wholesale_roles" ).find( "option[value='" + wholesale_role + "']" ).attr( 'selected' , 'selected' );
        $parent_fields_container.find( ".pqbwp_minimum_order_quantity" ).val( start_qty );
        $parent_fields_container.find( ".pqbwp_maximum_order_quantity" ).val( end_qtry );
        $parent_fields_container.find( ".pqbwp_wholesale_price" ).val( wholesale_price );

        if ( currency )
            $parent_fields_container.find( ".pqbwp_enabled_currencies" ).val( currency );

        $current_tr.addClass( "edited" );

        $table_mapping.find( "tr" ).addClass( "disabled" );

        $parent_button_controls
            .removeClass( 'add-mode' )
            .addClass( 'edit-mode' );

    } );

    $( 'body' ).delegate( '.delete' , 'click' , function() {

        var $this = $( this ),
            $current_tr = $this.closest( 'tr' ),
            $table_mapping = $current_tr.closest( ".pqbwp-mapping" ),
            $parent_fields_container = $this.closest( '.product-quantity-based-wholesale-pricing' ),
            post_id = $.trim( $parent_fields_container.find( ".post-id" ).text() ),
            index = $.trim( $current_tr.find( ".meta" ).find( ".index" ).text() );

        $current_tr.addClass( "edited" );

        $table_mapping.find( "tr" ).addClass( "disabled" );

        if ( confirm( 'Clicking OK will remove the current quantity discount rule mapping' ) ) {

            wwppBackendAjaxServices.deleteQuantityDiscountRule( post_id , index )
                .done( function ( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        $current_tr.fadeOut( "fast" , function () {

                            $current_tr.remove();

                            reset_table_row_styling( $table_mapping );

                            // If no more item then append the empty table placeholder
                            if ( $table_mapping.find( "tbody" ).find( "tr" ).length <= 0 ) {

                                $table_mapping
                                    .find( "tbody" )
                                    .html(  '<tr class="no-items">' +
                                                '<td class="colspanchange" colspan="5">No Quantity Discount Rules Found</td>' +
                                            '</tr>' );

                            }

                        } );

                        toastr.success( '' , 'Successfully Deleted Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : success_message_duration } );

                    } else {

                        toastr.error( data.error_message , 'Failed to Delete Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : error_message_duration } );

                        console.log( 'Failed to Delete Quantity Discount Rule Mapping' );
                        console.log( data );
                        console.log( '----------' );

                    }

                } )
                .fail( function ( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , 'Failed to Delete Quantity Discount Rule Mapping' , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( 'Failed to Delete Quantity Discount Rule Mapping' );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function () {

                    $table_mapping.find( "tr" )
                        .removeClass( "edited" )
                        .removeClass( "disabled" );

                } );

        } else {

            $table_mapping.find( "tr" )
                .removeClass( "edited" )
                .removeClass( "disabled" );

        }

    } );

} );