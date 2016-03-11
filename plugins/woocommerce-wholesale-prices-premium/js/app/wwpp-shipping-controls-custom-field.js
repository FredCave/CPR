jQuery( document ).ready( function ( $ ) {

    /*
     |-------------------------------------------------------------------------------------------------------------------
     | Variable Declarations
     |-------------------------------------------------------------------------------------------------------------------
     */

    var $shipping_method_controls = $( ".shipping-method-controls" ),
        $table_rate_sipping_type = $shipping_method_controls.find( "#table-rate-sipping-type" ),
        $index  =   $shipping_method_controls.find( "#index" ),
        $wwpp_wholesale_roles = $shipping_method_controls.find( "#wwpp-wholesale-roles" ),
        $wwpp_shipping_methods = $shipping_method_controls.find( "#wwpp-shipping-methods" ),

        // Table Rate Shipping ( Woo Themes )
        $shipping_zones_container = $shipping_method_controls.find( ".shipping-zones-container" ),
        $wwpp_shipping_zones = $shipping_method_controls.find( "#wwpp-shipping-zones" ),
        $shipping_zone_methods_container = $shipping_method_controls.find( ".shipping-zone-methods-container" ),
        $wwpp_shipping_zone_methods = $shipping_method_controls.find( "#wwpp-shipping-zone-methods" ),

        // Table Rate Shipping ( WooCommerce )
        $cc_shipping_zones_container = $shipping_method_controls.find( ".cc-shipping-zones-container" ),
        $cc_shipping_zones = $shipping_method_controls.find( "#wwpp-cc-shipping-zones" ),
        $cc_shipping_zone_table_rates_container = $shipping_method_controls.find( ".cc-shipping-zone-table-rates-container" ),
        $cc_shipping_zone_table_rates = $shipping_method_controls.find( "#wwpp-cc-shipping-zone-table-rates" ),

        // Table Rate Shipping Plus ( Mango Hour )
        $mh_shipping_zones_container = $shipping_method_controls.find( ".mh-shipping-zones-container" ),
        $mh_shipping_zones = $shipping_method_controls.find( "#wwpp-mh-shipping-zones" ),
        $mh_shipping_services_container = $shipping_method_controls.find( ".mh-shipping-services-container" ),
        $mh_shipping_services = $shipping_method_controls.find( "#wwpp-mh-shipping-services" ),

        $button_controls = $( ".button-controls" ),
        $wholesale_role_shipping_method_mapping = $( "#wholesale-role-shipping-method-mapping" ),
        errorMessageDuration = '10000',
        successMessageDuration = '5000';




    /*
     |-------------------------------------------------------------------------------------------------------------------
     | Helper Functions
     |-------------------------------------------------------------------------------------------------------------------
     */

    function removeTableNoItemsPlaceholder ( $table ) {

        $table.find("tbody").find(".no-items").remove();

    }

    function resetTableRowStyling () {

        $wholesale_role_shipping_method_mapping
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

        $wwpp_wholesale_roles.val( "" ).removeAttr( "disabled" ).trigger( "change" ).trigger( "chosen:updated" );
        $wwpp_shipping_methods.val( "" ).trigger( "change" ).trigger( "chosen:updated" );
        $index.val( "" );

    }

    function validateFields () {

        var error_fields = [];

        if ( $.trim( $wwpp_wholesale_roles.val() ) == "" )
            error_fields.push( "Wholesale Role" );

        if ( $.trim( $wwpp_shipping_methods.val() ) == "" )
            error_fields.push( "Shipping Method" );

        if ( $table_rate_sipping_type.val() == 'woo_themes' &&
             $wwpp_shipping_methods.val() == 'table_rate' &&
             $wwpp_shipping_zones.val() != '' ) {

            if ( $.trim( $wwpp_shipping_zone_methods.val() ) == "" )
                error_fields.push( "Shipping Zone Method" );

        }

        return error_fields;

    }




    /*
     |-------------------------------------------------------------------------------------------------------------------
     | Events
     |-------------------------------------------------------------------------------------------------------------------
     */

    $button_controls.find( "#add-mapping" ).click( function () {

        var $this = $( this );

        $this.attr( 'disabled' , 'disabled' );
        $button_controls.addClass( 'processing' );

        var error_fields = validateFields();

        if ( error_fields.length > 0 ) {

            var msg = "Please specify values for the following field/s:<br/><ul>";

            for ( var i = 0 ; i < error_fields.length ; i++ )
                msg += "<li>" + error_fields[ i ] + "</li>";

            msg += "</ul>";

            toastr.error( msg , 'Form Error' , { "closeButton" : true , "showDuration" : errorMessageDuration } );
            $this.removeAttr( 'disabled' );
            $button_controls.removeClass( 'processing' );
            return false;

        }

        var shipping_zone = "",
            shipping_zone_name = "",
            shipping_zone_method = "",
            shipping_zone_method_name = "",
            shipping_service = "",
            shipping_service_name = "",
            shipping_zone_table_rate = "",
            shipping_zone_table_rate_name = "";

        if ( $table_rate_sipping_type.val() == 'woo_themes' ) {

            var sz = '',
                szm = '';

            if ( !$wwpp_shipping_zones.is( ":disabled" ) )
                sz = $.trim( $wwpp_shipping_zones.val() );

            if ( sz != '' && !$wwpp_shipping_zone_methods.is( ":disabled" ) )
                szm = $.trim( $wwpp_shipping_zone_methods.val() );

            shipping_zone = sz;
            shipping_zone_name =  ( sz ) ? $.trim( $wwpp_shipping_zones.find( "option[value='" + sz + "']" ).text() ) : "";
            shipping_zone_method = szm;
            shipping_zone_method_name = ( szm ) ? $.trim( $wwpp_shipping_zone_methods.find( "option[value='" + szm + "']" ).text() ) : "";

        } else if ( $table_rate_sipping_type.val() == "code_canyon" ) {

            var sz = '',
                sztr = '';

            if ( !$cc_shipping_zones.is( ":disabled" ) )
                sz = $.trim( $cc_shipping_zones.val() );

            if ( !$cc_shipping_zone_table_rates.is( ":disabled" ) )
                sztr = $.trim( $cc_shipping_zone_table_rates.val() );

            shipping_zone = sz;
            shipping_zone_name = ( sz ) ? $.trim( $cc_shipping_zones.find( "option[value='" + sz + "']" ).text() ) : "";
            shipping_zone_table_rate = sztr;
            shipping_zone_table_rate_name = ( sztr ) ? $.trim( $cc_shipping_zone_table_rates.find( "option[value='" + sztr + "']" ).text() ) : "";

        } else if ( $table_rate_sipping_type.val() == 'mango_hour' ) {

            var sz = '',
                ss = '';

            if ( !$mh_shipping_zones.is( ":disabled" ) )
                sz = $.trim( $mh_shipping_zones.val() );

            if ( !$mh_shipping_services.is( ":disabled" ) )
                ss = $.trim( $mh_shipping_services.val() );

            shipping_zone = sz;
            shipping_zone_name = ( sz ) ? $.trim( $mh_shipping_zones.find( "option[value='" + sz + "']" ).text() ) : "";
            shipping_service = ss;
            shipping_service_name = ( ss ) ? $.trim( $mh_shipping_services.find( "option[value='" + ss + "']" ).text() ) : "";

        }

        var mapping = {
            'wholesale_role'                :   $.trim( $wwpp_wholesale_roles.val() ),
            'shipping_method'               :   $.trim( $wwpp_shipping_methods.val() ),
            'shipping_zone'                 :   shipping_zone,
            'shipping_zone_name'            :   shipping_zone_name,
            'shipping_zone_method'          :   shipping_zone_method,
            'shipping_zone_method_name'     :   shipping_zone_method_name,
            'shipping_service'              :   shipping_service,
            'shipping_service_name'         :   shipping_service_name,
            'shipping_zone_table_rate'      :   shipping_zone_table_rate,
            'shipping_zone_table_rate_name' :   shipping_zone_table_rate_name,
            'shipping_class'                :   ''
        };

        wwppBackendAjaxServices.addWholesaleRoleShippingMapping( mapping )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    toastr.success( '' , 'Successfully Added Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    // Remove empty items placeholder
                    removeTableNoItemsPlaceholder( $wholesale_role_shipping_method_mapping );

                    // Append new field to table
                    var tr_class    =   "";

                    if( $wholesale_role_shipping_method_mapping.find( "tr" ).length % 2 == 0 ) // currently even, next add (our add) would make it odd
                        tr_class    =   "odd alternate";
                    else // currently odd, next add (our add) would make it even
                        tr_class    =   "even";

                    var $shipping_zone_meta = '',
                        $shipping_zone_name = '';

                    if ( $table_rate_sipping_type.val() == 'woo_themes' ) {

                        $shipping_zone_meta = '<span class="shipping_zone">' + mapping.shipping_zone + '</span>' +
                                              '<span class="shipping_zone_method">' + mapping.shipping_zone_method + '</span>';

                        $shipping_zone_name = '<td class="shipping_zone_text">' + mapping.shipping_zone_name + '</td>' +
                                              '<td class="shipping_zone_method_text">' + mapping.shipping_zone_method_name + '</td>';

                    } else if ( $table_rate_sipping_type.val() == 'code_canyon' ) {

                        $shipping_zone_meta = '<span class="shipping_zone">' + mapping.shipping_zone + '</span>' +
                                              '<span class="shipping_zone_table_rate">' + mapping.shipping_zone_table_rate + '</span>'

                        $shipping_zone_name = '<td class="shipping_zone_text">' + mapping.shipping_zone_name + '</td>' +
                                              '<td class="shipping_zone_table_rate_text">' + mapping.shipping_zone_table_rate_name + '</td>';

                    } else if ( $table_rate_sipping_type.val() == 'mango_hour' ) {

                        $shipping_zone_meta = '<span class="shipping_zone">' + mapping.shipping_zone + '</span>' +
                                              '<span class="shipping_service">' + mapping.shipping_service + '</span>';

                        $shipping_zone_name = '<td class="shipping_zone_text">' + mapping.shipping_zone_name + '</td>' +
                                              '<td class="shipping_service_text">' + mapping.shipping_service_name + '</td>';

                    }

                    // Insert user field to table
                    $wholesale_role_shipping_method_mapping.find( "tbody" )
                        .append('<tr class="'+tr_class+' edited">' +
                                    '<td class="meta hidden">' +
                                        '<span class="index">' + data.latest_index + '</span>' +
                                        '<span class="wholesale_role">' + mapping.wholesale_role + '</span>' +
                                        '<span class="shipping_method">' + mapping.shipping_method + '</span>' +
                                        $shipping_zone_meta +
                                    '</td>' +
                                    '<td class="wholesale_role_text">' + $wwpp_wholesale_roles.find( "option[value='" + mapping.wholesale_role + "']" ).text() + '</td>' +
                                    '<td class="shipping_method_text">' + $wwpp_shipping_methods.find( "option[value='" + mapping.shipping_method + "']" ).text() + '</td>' +
                                    $shipping_zone_name +
                                    '<td class="shipping_class">' + mapping.shipping_class + '</td>' +
                                    '<td class="controls">' +
                                        '<a class="edit dashicons dashicons-edit"></a>' +
                                        '<a class="delete dashicons dashicons-no"></a>' +
                                    '</td>' +
                                '</tr>');

                    resetFields();

                    // Remove edited class to the recently added user field
                    setTimeout( function () {

                        $wholesale_role_shipping_method_mapping
                            .find( "tr.edited" )
                            .removeClass( "edited" );

                    } , 500 );

                } else
                    toastr.error( data.error_message , 'Failed To Add New Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

            } )
            .fail ( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , 'Failed To Add New Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( 'Failed To Add New Role/Shipping Mapping' );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function () {

                $this.removeAttr( 'disabled' );
                $button_controls.removeClass( 'processing' );

            } );

    } );

    $button_controls.find( "#save-mapping" ).click( function () {

        var $this = $( this );

        $this.attr( 'disabled' , 'disabled' );
        $button_controls.addClass( 'processing' );

        var error_fields = validateFields();

        if ( error_fields.length > 0 ) {

            var msg = "Please specify values for the following field/s:<br/><ul>";

            for ( var i = 0 ; i < error_fields.length ; i++ )
                msg += "<li>" + error_fields[ i ] + "</li>";

            msg += "</ul>";

            toastr.error( msg , 'Form Error' , { "closeButton" : true , "showDuration" : errorMessageDuration } );
            $this.removeAttr( 'disabled' );
            $button_controls.removeClass( 'processing' );
            return false;

        }

        var shipping_zone = "",
            shipping_zone_name = "",
            shipping_zone_method = "",
            shipping_zone_method_name = "",
            shipping_service = "",
            shipping_service_name = "",
            shipping_zone_table_rate = "",
            shipping_zone_table_rate_name = "";

        if ( $table_rate_sipping_type.val() == 'woo_themes' ) {

            var sz = '',
                szm = '';

            if ( !$wwpp_shipping_zones.is( ":disabled" ) )
                sz = $.trim( $wwpp_shipping_zones.val() );

            if ( sz != '' && !$wwpp_shipping_zone_methods.is( ":disabled" ) )
                szm = $.trim( $wwpp_shipping_zone_methods.val() );

            shipping_zone = sz;
            shipping_zone_name =  ( sz ) ? $.trim( $wwpp_shipping_zones.find( "option[value='" + sz + "']" ).text() ) : "";
            shipping_zone_method = szm;
            shipping_zone_method_name = ( szm ) ? $.trim( $wwpp_shipping_zone_methods.find( "option[value='" + szm + "']" ).text() ) : "";

        } else if ( $table_rate_sipping_type.val() == 'code_canyon' ) {

            var sz = '',
                sztr = '';

            if ( !$cc_shipping_zones.is( ":disabled" ) )
                sz = $.trim( $cc_shipping_zones.val() );

            if ( !$cc_shipping_zone_table_rates.is( ":disabled" ) )
                sztr = $.trim( $cc_shipping_zone_table_rates.val() );

            shipping_zone = sz;
            shipping_zone_name = ( sz ) ? $.trim( $cc_shipping_zones.find( "option[value='" + sz + "']" ).text() ) : "";
            shipping_zone_table_rate = sztr;
            shipping_zone_table_rate_name = ( sztr ) ? $.trim( $cc_shipping_zone_table_rates.find( "option[value='" + sztr + "']" ).text() ) : "";

        } else if ( $table_rate_sipping_type.val() == 'mango_hour' ) {

            var sz = '',
                ss = '';

            if ( !$mh_shipping_zones.is( ":disabled" ) )
                sz = $.trim( $mh_shipping_zones.val() );

            if ( !$mh_shipping_services.is( ":disabled" ) )
                ss = $.trim( $mh_shipping_services.val() );

            shipping_zone = sz;
            shipping_zone_name = ( sz ) ? $.trim( $mh_shipping_zones.find( "option[value='" + sz + "']" ).text() ) : "";
            shipping_service = ss;
            shipping_service_name = ( ss ) ? $.trim( $mh_shipping_services.find( "option[value='" + ss + "']" ).text() ) : "";

        }

        var index = $.trim( $index.val() ),
            mapping = {
            'wholesale_role'                :   $.trim( $wwpp_wholesale_roles.val() ),
            'shipping_method'               :   $.trim( $wwpp_shipping_methods.val() ),
            'shipping_zone'                 :   shipping_zone,
            'shipping_zone_name'            :   shipping_zone_name,
            'shipping_zone_method'          :   shipping_zone_method,
            'shipping_zone_method_name'     :   shipping_zone_method_name,
            'shipping_service'              :   shipping_service,
            'shipping_service_name'         :   shipping_service_name,
            'shipping_zone_table_rate'      :   shipping_zone_table_rate,
            'shipping_zone_table_rate_name' :   shipping_zone_table_rate_name,
            'shipping_class'                :   ''
        };

        wwppBackendAjaxServices.editWholesaleRoleShippingMapping( index , mapping )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    if ( $table_rate_sipping_type.val() == 'woo_themes' ) {

                        $wholesale_role_shipping_method_mapping.find( "tr.edited" )
                            .find( ".meta" )
                                .find( ".shipping_zone" ).text( mapping.shipping_zone ).end()
                                .find( ".shipping_zone_method" ).text( mapping.shipping_zone_method ).end()
                                .end()
                            .find( ".shipping_zone_text" ).text( mapping.shipping_zone_name ).end()
                            .find( ".shipping_zone_method_text" ).text( mapping.shipping_zone_method_name );

                    } else if ( $table_rate_sipping_type.val() == 'code_canyon' ) {

                        $wholesale_role_shipping_method_mapping.find( "tr.edited" )
                            .find( ".meta" )
                                .find( ".shipping_zone" ).text( mapping.shipping_zone ).end()
                                .find( ".shipping_zone_table_rate" ).text( mapping.shipping_zone_table_rate ).end()
                                .end()
                            .find( ".shipping_zone_text" ).text( mapping.shipping_zone_name ).end()
                            .find( ".shipping_zone_table_rate_text" ).text( mapping.shipping_zone_table_rate_name );

                    } else if ( $table_rate_sipping_type.val() == 'mango_hour' ) {

                        $wholesale_role_shipping_method_mapping.find( "tr.edited" )
                            .find( ".meta" )
                                .find( ".shipping_zone" ).text( mapping.shipping_zone ).end()
                                .find( ".shipping_service" ).text( mapping.shipping_service ).end()
                                .end()
                            .find( ".shipping_zone_text" ).text( mapping.shipping_zone_name ).end()
                            .find( ".shipping_service_text" ).text( mapping.shipping_service_name );

                    }

                    $wholesale_role_shipping_method_mapping.find( "tr.edited" )
                        .find( ".meta" )
                            .find( ".wholesale_role" ).text( mapping.wholesale_role ).end()
                            .find( ".shipping_method" ).text( mapping.shipping_method ).end()
                            .end()
                        .find( ".wholesale_role_text" ).text( $wwpp_wholesale_roles.find( "option[value='" + mapping.wholesale_role + "']" ).text() ).end()
                        .find( ".shipping_method_text" ).text( $wwpp_shipping_methods.find( "option[value='" + mapping.shipping_method + "']" ).text() ).end()
                        .find( ".shipping_class" ).text( mapping.shipping_class );

                    $wholesale_role_shipping_method_mapping.find( "tr .controls .dashicons" )
                        .css( "display" , "inline-block" );

                    resetFields();

                    // Remove edited class to the recently added user field
                    setTimeout( function() {
                        $wholesale_role_shipping_method_mapping
                            .find( "tr.edited" )
                            .removeClass( "edited" );
                    } , 500 );

                    $button_controls
                        .removeClass( 'edit-mode' )
                        .addClass( 'add-mode' );

                    toastr.success( '' , 'Successfully Updated Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : successMessageDuration } );

                } else
                    toastr.error( data.error_message , 'Failed To Update Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , 'Failed To Update Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( 'Failed To Update Role/Shipping Mapping' );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function () {

                $this.removeAttr( 'disabled' );
                $button_controls.removeClass( 'processing' );

            } );

    } );

    $button_controls.find( "#cancel-edit-mapping" ).click( function () {

        resetFields();

        $button_controls
            .removeClass( "edit-mode" )
            .addClass( "add-mode" );

        $wholesale_role_shipping_method_mapping
            .find( "tbody tr" )
                .removeClass( "edited" )
            .find( ".controls .dashicons" )
                .css( "display" , "inline-block" );

    } );

    $wholesale_role_shipping_method_mapping.delegate( '.edit' , 'click' , function () {

        var $this = $( this ),
            $currentTr = $this.closest( 'tr' );

        $currentTr.addClass( "edited" );
        $wholesale_role_shipping_method_mapping.find( ".controls .dashicons" )
            .css( "display" , "none" );

        $index.val( $.trim( $currentTr.find( ".meta" ).find( ".index" ).text() ) );
        $wwpp_wholesale_roles.val( $.trim( $currentTr.find( ".meta" ).find( ".wholesale_role" ).text() ) ).attr( "disabled" , "disabled" ).trigger( "change" ).trigger( "chosen:updated" );
        $wwpp_shipping_methods.val( $.trim( $currentTr.find( ".meta" ).find( ".shipping_method" ).text() ) ).trigger( "change" ).trigger( "chosen:updated" );

        if ( $table_rate_sipping_type.val() == 'woo_themes' && $.trim($currentTr.find( ".meta" ).find( ".shipping_method" ).text() ) == 'table_rate' ) {

            var sz = $.trim( $currentTr.find( ".meta" ).find( ".shipping_zone" ).text() ),
                szm = $.trim( $currentTr.find( ".meta" ).find( ".shipping_zone_method" ).text() );

            if ( sz && szm ) {

                $wwpp_shipping_zones
                    .val( sz )
                    .trigger( "change" , { 'shipping_zone_method' : szm } )
                    .trigger( "chosen:updated" );

            }

        } else if ( $table_rate_sipping_type.val() == 'code_canyon' ) {

            var sz = $.trim( $currentTr.find( ".meta" ).find( ".shipping_zone" ).text() ),
                sztr = $.trim( $currentTr.find( ".meta" ).find( ".shipping_zone_table_rate" ).text() );

            if ( sz ) {

                $cc_shipping_zones
                    .val( sz )
                    .trigger( "change" , { "shipping_zone_table_rate" : sztr } )
                    .trigger( "chosen:updated" );

            }

        } else if ( $table_rate_sipping_type.val() == 'mango_hour' && $.trim( $currentTr.find( ".meta" ).find( ".shipping_method" ).text() ) == 'mh_wc_table_rate_plus' ) {

            var sz = $.trim( $currentTr.find( ".meta" ).find( ".shipping_zone" ).text() ),
                ss = $.trim( $currentTr.find( ".meta" ).find( ".shipping_service" ).text() );

            $mh_shipping_zones.val( sz ).trigger( "change" ).trigger( "chosen:updated" );
            $mh_shipping_services.val( ss ).trigger( "change" ).trigger( "chosen:updated" );

        }

        $button_controls
            .removeClass( "add-mode" )
            .addClass( "edit-mode" );

    } );

    $wholesale_role_shipping_method_mapping.delegate( '.delete' , 'click' , function () {

        var $this = $( this ),
            $currentTr = $this.closest( 'tr' );

        $currentTr.addClass( "edited" );

        if ( confirm( 'Clicking OK will remove the current role/shipping mapping' ) ) {

            var index = $.trim( $currentTr.find( ".meta" ).find( ".index" ).text() );

            $wholesale_role_shipping_method_mapping.find( ".controls .dashicons" )
                .css( "display" , "none" );

            wwppBackendAjaxServices.deleteWholesaleRoleShippingMapping( index )
                .done( function ( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        $currentTr.fadeOut( "fast" , function () {

                            // Remove current row
                            $currentTr.remove();

                            // Restyle rows
                            resetTableRowStyling();

                            // If no more item then append the empty table placeholder
                            if ( $wholesale_role_shipping_method_mapping.find( "tbody" ).find( "tr" ).length <= 0 ) {

                                $wholesale_role_shipping_method_mapping
                                    .find( "tbody" )
                                    .html( '<tr class="no-items">' +
                                             '<td class="colspanchange" colspan="6">No Mappings Found</td>' +
                                           '</tr>' );

                            }

                        } );

                        toastr.success( '' , 'Successfully Deleted Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : successMessageDuration } );

                    } else {

                        toastr.error( data.error_message , 'Failed To Delete Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    }

                } )
                .fail( function ( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , 'Failed To Delete Role/Shipping Mapping' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( 'Failed To Delete Role/Shipping Mapping' );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function () {

                    $wholesale_role_shipping_method_mapping.find( ".controls .dashicons" )
                        .css( "display" , "inline-block" );

                } );

        } else
            $currentTr.removeClass( "edited" );

    } );

    if ( $table_rate_sipping_type.val() == 'woo_themes' ) {

        $wwpp_shipping_methods.change( function () {

            var $this = $( this );

            $wwpp_shipping_zones.val( "" ).trigger( "chosen:updated" );

            if ( $this.val() == 'table_rate' )
                $shipping_zones_container.removeClass( "wwpp-hidden" );
            else {

                $shipping_zones_container.addClass( "wwpp-hidden" );
                $shipping_zone_methods_container.addClass( "wwpp-hidden" );

            }

        } );

        $wwpp_shipping_zones.change( function ( event , extra_param ) {

            var $this = $( this),
                shippingZoneID = $this.val();

            $button_controls.find( ".button" ).attr( "disabled" , "disabled" );

            if ( shippingZoneID == "" ) {

                $wwpp_shipping_zone_methods.val( "" ).trigger( "chosen:updated" );
                $shipping_zone_methods_container.addClass( "wwpp-hidden" );
                $button_controls.find( ".button" ).removeAttr( "disabled" );
                return false;

            }

            $this
                .attr( "disabled" , "disabled" )
                .trigger( "chosen:updated" );

            $wwpp_shipping_zone_methods
                .attr( "disabled" , "disabled" )
                .trigger( "chosen:updated" );

            wwppBackendAjaxServices.getAllShippingZoneMethods( shippingZoneID )
                .done( function ( data , textStatus , jqXHR ) {

                    var options = '<option value=""></option>';
                    for ( var i = 0 ; i < data.length ; i++ )
                        options += '<option value="' + data[ i ].shipping_method_id + '">' + data[ i ].title + '</option>';

                    $wwpp_shipping_zone_methods.html(options);

                    if ( extra_param !== undefined && extra_param.shipping_zone_method !== undefined )
                        $wwpp_shipping_zone_methods.val( extra_param.shipping_zone_method );

                } )
                .fail( function ( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , 'Failed To Retrieve Table Rate Shipping Zone Methods' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( 'Failed To Retrieve Table Rate Shipping Zone Methods' );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function () {

                    $this
                        .removeAttr( "disabled" )
                        .trigger( "chosen:updated" );

                    $wwpp_shipping_zone_methods
                        .removeAttr( "disabled" )
                        .trigger( "chosen:updated" );

                    $shipping_zone_methods_container
                        .removeClass( "wwpp-hidden" );

                    $button_controls.find( ".button" ).removeAttr( "disabled" );

                } );

        } );

    } else if ( $table_rate_sipping_type.val() == 'code_canyon' ) {

        $wwpp_shipping_methods.change( function() {

            var $this = $( this );

            $cc_shipping_zones.val( "" );
            $cc_shipping_zone_table_rates.val( "" );

            if ( $this.val() == 'table_rate_shipping' ) {

                $cc_shipping_zones.removeAttr( "disabled" );

                $cc_shipping_zones_container.removeClass( "wwpp-hidden" );

            } else {

                $cc_shipping_zones.attr( "disabled" , "disabled" );
                $cc_shipping_zone_table_rates.attr( "disabled" , "disabled" );

                $cc_shipping_zones_container.addClass( "wwpp-hidden" );
                $cc_shipping_zone_table_rates_container.addClass( "wwpp-hidden" );

            }

            $cc_shipping_zones.trigger( "chosen:updated" );
            $cc_shipping_zone_table_rates.trigger( "chosen:updated" );

        } );

        $cc_shipping_zones.change( function( event , extra_param ) {

            var $this = $( this ),
                shippingZoneID = $this.val();

            $button_controls.find( ".button" ).attr( "disabled" , "disabled" );

            if ( shippingZoneID == "" ) {

                $cc_shipping_zone_table_rates_container.addClass( "wwpp-hidden" );
                $cc_shipping_zone_table_rates.val( "" ).attr( "disabled" , "disabled" ).trigger( "chosen:updated" );
                $button_controls.find( ".button" ).removeAttr( "disabled" );
                return false;

            }

            $this
                .attr( "disabled" , "disabled" )
                .trigger( "chosen:updated" );

            $cc_shipping_zone_table_rates
                .attr( "disabled" , "disabled" )
                .trigger( "chosen:updated" );

            wwppBackendAjaxServices.GetAllShippingZoneTableRates( shippingZoneID )
                .done( function( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        var options = '<option value=""></option>';
                        for ( var i = 0 ; i < data.shipping_zone_table_rates.length ; i++ )
                            options += '<option value="' + data.shipping_zone_table_rates[ i ][ 'identifier' ] + '">' + data.shipping_zone_table_rates[ i ][ 'title' ] + '</option>';

                        $cc_shipping_zone_table_rates.html( options );

                        if ( extra_param !== undefined && extra_param.shipping_zone_table_rate !== undefined )
                            $cc_shipping_zone_table_rates.val( extra_param.shipping_zone_table_rate );

                    } else {

                        toastr.error( '' , 'Failed To Retrieve Shipping Zone Table Rates' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                        console.log( 'Failed To Retrieve Shipping Zone Table Rates' );
                        console.log( data );
                        console.log( '----------' );

                    }

                } )
                .fail( function( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , 'Failed To Retrieve Shipping Zone Table Rates' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( 'Failed To Retrieve Shipping Zone Table Rates' );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function() {

                    $this
                        .removeAttr( "disabled" )
                        .trigger( "chosen:updated" );

                    $cc_shipping_zone_table_rates
                        .removeAttr( "disabled" )
                        .trigger( "chosen:updated" );

                    $cc_shipping_zone_table_rates_container
                        .removeClass( "wwpp-hidden" );

                    $button_controls.find( ".button" ).removeAttr( "disabled" );

                } );

        } );

    } else if ( $table_rate_sipping_type.val() == 'mango_hour' ) {

        $wwpp_shipping_methods.change( function() {

            var $this = $( this );

            if ( $this.val() == 'mh_wc_table_rate_plus' ) {

                $mh_shipping_zones.removeAttr( "disabled" ).trigger( "chosen:updated" );
                $mh_shipping_services.removeAttr( "disabled" ).trigger( "chosen:updated" );

                $mh_shipping_zones_container.removeClass( "wwpp-hidden" );
                $mh_shipping_services_container.removeClass( "wwpp-hidden" );

            } else {

                $mh_shipping_zones_container.addClass( "wwpp-hidden" );
                $mh_shipping_services_container.addClass( "wwpp-hidden" );

                $mh_shipping_zones.attr( 'disabled' , 'disabled' );
                $mh_shipping_services.attr( 'disabled' , 'disabled' );

            }

        } );

    }




    /*
     |-------------------------------------------------------------------------------------------------------------------
     | On Load
     |-------------------------------------------------------------------------------------------------------------------
     */

    if ( $table_rate_sipping_type.val() == 'woo_themes' ) {

        $wwpp_shipping_zones.attr( "disabled" , "disabled" );
        $wwpp_shipping_zone_methods.attr( "disabled" , "disabled" );

        wwppBackendAjaxServices.getAllShippingZones()
            .done( function ( data , textStatus , jqXHR ) {

                var options = '<option value=""></option>';

                for ( var i = 0; i < data.length ; i++ )
                    options += '<option value="' + data[i].zone_id + '">' + data[i].zone_name + '</option>';

                $wwpp_shipping_zones.html(options);

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , 'Failed To Retrieve Table Rate Shipping Zones' , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( 'Failed To Retrieve Table Rate Shipping Zones' );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function () {

                $wwpp_shipping_zones
                    .removeAttr( "disabled" )
                    .trigger( "chosen:updated" );

            } );

        $wwpp_shipping_zones.chosen( { allow_single_deselect : true } );
        $wwpp_shipping_zone_methods.chosen( { allow_single_deselect : true } );

    } else if ( $table_rate_sipping_type.val() == 'code_canyon' ) {

        $cc_shipping_zones.attr( "disabled" , "disabled" );
        $cc_shipping_zone_table_rates.attr( "disabled" , "disabled" );

        $cc_shipping_zones.chosen( { allow_single_deselect : true } );
        $cc_shipping_zone_table_rates.chosen( { allow_single_deselect : true } );

    } else if ( $table_rate_sipping_type.val() == 'mango_hour' ) {

        $mh_shipping_zones.attr( "disabled" , "disabled" );
        $mh_shipping_services.attr( "disabled" , "disabled" );

        $mh_shipping_zones.chosen();
        $mh_shipping_services.chosen();

    }

    $wwpp_wholesale_roles.chosen( { allow_single_deselect : true } );
    $wwpp_shipping_methods.chosen( { allow_single_deselect : true } );

} );