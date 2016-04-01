jQuery( document ).ready( function( $ ) {

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Cache Selector
     |------------------------------------------------------------------------------------------------------------------
     */
    var $wwofProductListingContainer = $( "#wwof_product_listing_container" ),
        $wwofProductListingFilter = $( "#wwof_product_listing_filter" ),
        $bottomListActions = $( ".bottom_list_actions" );



    /*
     |------------------------------------------------------------------------------------------------------------------
     | Functions
     |------------------------------------------------------------------------------------------------------------------
     */
    function disableElement( $element ) {

        $element.attr( 'disabled' , 'disabled' ).addClass( 'disabled' );

    }

    function enableElement( $element ) {

        $element.removeAttr( 'disabled' ).removeClass( 'disabled' );

    }

    function attachErrorStateToElement( $element ) {

        $element.addClass( 'error' );

    }

    function detachErrorStateToElement( $element ) {

        $element.removeClass( 'error' );

    }

    function disableSearchCommandFields() {

        disableElement( $wwofProductListingFilter.find( "#wwof_product_search_form" ) );
        disableElement( $wwofProductListingFilter.find( "#wwof_product_search_category_filter" ) );
        disableElement( $wwofProductListingFilter.find( "#wwof_product_search_btn" ) );
        disableElement( $wwofProductListingFilter.find( "#wwof_product_displayall_btn" ) );

    }

    function disabledPagingLinks() {

        disableElement( $wwofProductListingContainer.find( "#wwof_product_listing_pagination ul li a" ) );

    }

    function enabledSearchCommandFields() {

        enableElement( $wwofProductListingFilter.find( "#wwof_product_search_form" ) );
        enableElement( $wwofProductListingFilter.find( "#wwof_product_search_category_filter" ) );
        enableElement( $wwofProductListingFilter.find( "#wwof_product_search_btn" ) );
        enableElement( $wwofProductListingFilter.find( "#wwof_product_displayall_btn" ) );

    }

    function enablePagingLinks() {

        enableElement( $wwofProductListingContainer.find( "#wwof_product_listing_pagination ul li a" ) );

    }

    function showProcessingOverlay() {

        var $overlay_container;

        if ( $wwofProductListingContainer.find( "#wwof_product_listing_table" ).length > 0 )
            $overlay_container = $wwofProductListingContainer.find( "#wwof_product_listing_table" );
        else
            $overlay_container = $wwofProductListingContainer.find( "#wwof_product_listing_ajax_content" );

        $overlay_container.css( 'min-height' , '200px' );

        var table_width = $overlay_container.width(),
            table_height = $overlay_container.height();

        $overlay_container.append(
            '<div class="processing-overlay" style="position: absolute; width: ' + table_width + 'px; height: ' + table_height + 'px; min-height: 200px; top: 0; left: 0;">' +
            '<div class="loading-icon"></div>' +
            '</div>'
        );

    }

    function removeProcessingOverlay() {

        var $overlay_container;

        if ( $wwofProductListingContainer.find( "#wwof_product_listing_table_container" ).length > 0 )
            $overlay_container = $wwofProductListingContainer.find( "#wwof_product_listing_table_container" );
        else
            $overlay_container = $wwofProductListingContainer.find( "#wwof_product_listing_ajax_content" );

        $overlay_container.find( ".processing-overlay" ).remove();

    }

    function fadeOutElement( $element , delay ) {

        setTimeout( function() {
            $element.fadeOut( 'fast' );
        } , delay );

    }

    function getParameterByName( name , url ) {

        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(url);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));

    }

    function LoadProductListing( paged , search , catFilter ) {

        disableSearchCommandFields();
        disabledPagingLinks();
        showProcessingOverlay();

        wwofFrontEndAjaxServices.displayProductListing( paged , search , catFilter )
            .done( function( data , textStatus , jqXHR ) {

                $wwofProductListingContainer.find( "#wwof_product_listing_ajax_content" ).html( data , search );

                enabledSearchCommandFields();
                // We dont need to re-eanble paging links, a new paging links will be added anyways.
                // Alos this is a bug fix for clicking the paging links multiple times consecutive in the middle of the ajax request process
                removeProcessingOverlay();

            } )
            .fail( function( jqXHR , textStatus , errorThrown ) {

                alert( errorThrown );

                enabledSearchCommandFields();
                enablePagingLinks(); // We re-enable paging links as no new paging links are added when an error occured during ajax request
                removeProcessingOverlay();

            } );




    }

    function blockFragments ( fragments ) {

        if ( fragments ) {

            $.each( fragments, function( key , value ) {
                $( key ).addClass( 'updating' );
            } );

        }

    }

    function unblockFragments ( fragments ) {

        if ( fragments ) {

            $.each( fragments, function( key , value ) {
                $( key ).removeClass( 'updating' );
            } );

        }

    }

    function replaceFragments ( fragments ) {

        if ( fragments ) {

            $.each( fragments, function( key, value ) {
                $( key ).replaceWith( value );
            } );

        }

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Events
     |------------------------------------------------------------------------------------------------------------------
     |
     | 5. Change the variation price accordingly depending on the selected variation.
     |
     */

    // 1
    $wwofProductListingContainer.delegate( '#wwof_product_listing_pagination ul li a' , 'click' , function() {

        var $this = $( this );

        if ( !$this.hasClass( 'disabled' ) ) {

            var url         =   $this.attr( 'href' ),
                paged       =   getParameterByName( 'paged' , url ),
                search      =   getParameterByName( 'search' , url ),
                catFilter   =   getParameterByName( 'cat_filter' , url );

            LoadProductListing( paged , search , catFilter );

        }

        return false;

    });

    // 2
    $wwofProductListingFilter.find( "#wwof_product_search_btn" ).click( function() {

        var search  =   $.trim( $wwofProductListingFilter.find( "#wwof_product_search_form" ).val() ),
            catFilter = $.trim( $wwofProductListingFilter.find( "#wwof_product_search_category_filter" ).find( "option:selected" ).val() )

        // To eliminate before and after spaces
        $wwofProductListingFilter.find( "#wwof_product_search_form" ).val( search );

        if ( search == "" ) {

            // Display all products
            LoadProductListing( 1 , "" , catFilter );

        } else {

            // Display only specified products
            LoadProductListing( 0 , search , catFilter );

        }

    } );

    // 3
    $wwofProductListingFilter.find( "#wwof_product_displayall_btn" ).click( function() {

        $wwofProductListingFilter.find( "#wwof_product_search_form" ).val( "" );
        $wwofProductListingFilter.find( "#wwof_product_search_category_filter" ).find( "option:first" ).attr( 'selected' , 'selected' );

        LoadProductListing( 1 , "" , "" );

    });

    // 4
    $wwofProductListingFilter.find( "#wwof_product_search_form" ).keyup( function( event ) {

        if ( event.keyCode == 13 ) {
            $( "#wwof_product_search_btn" ).click();
        }

    });

    // 5
    $wwofProductListingContainer.delegate( '.product_variations' , 'change' , function() {

        var $this = $( this ),
            variation_id = $this.val(),
            variation_prices = $this.closest( 'tr' ).find( '.product_price_col' ).find( '.variable_price' ).find( '.price' ),
            variation_sku = $this.closest( 'tr' ).find( '.product_sku_col' ).find( '.variable_sku' ).find( '.sku' ),
            variation_item_stock = $this.closest( 'tr' ).find( '.product_stock_quantity_col' ).find( '.variable_stock_quantity' ).find( '.stock_quantity' ),
            variation_minimum_order_qty = $this.closest( 'tr' ).find( '.product_quantity_col' ).find( '.variable-minimum-order-quantity' ),
            qty_field = $this.closest( 'tr' ).find( '.product_quantity_col' ).find( '.qty' );

        variation_prices
            .css( 'display' , 'none' )
            .each( function ( idx ) {

                if( $( this ).attr( 'data-variation-id' ) == variation_id )
                    $( this ).css( 'display' , 'inline-block' );

            });

        variation_sku
            .css( 'display' , 'none' )
            .each( function ( idx ) {

                if( $( this ).attr( 'data-variation-id' ) == variation_id )
                    $( this ).css( 'display' , 'inline-block' );

            } );

        variation_item_stock
            .css( 'display' , 'none' )
            .each( function ( idx ) {

                if( $( this ).attr( 'data-variation-id' ) == variation_id )
                    $( this ).css( 'display','inline-block' );

            } );

        var moq = variation_minimum_order_qty.find( '.min-order-qty[data-variation-id="' + variation_id + '"]' );

        if ( moq.length ) {

            var moq_val = $.trim( moq.text() );
            if ( !moq_val )
                moq_val = 1;

            qty_field.val( moq_val );

        }

    } );

    // 6
    $wwofProductListingContainer.delegate( '.wwof_add_to_cart_button' , 'click' , function() {

        var $this = $( this ),
            $current_tr = $this.closest( 'tr' );

        $this
            .attr( 'disabled' , 'disabled' )
            .siblings( '.spinner' )
            .removeClass( 'success' )
            .removeClass( 'error' );
            // .css( 'display' , 'inline-block' );

        disableSearchCommandFields();
        disabledPagingLinks();

        var productType = $current_tr.find( ".product_meta_col" ).find( ".product_type" ).text(),
            productID = $current_tr.find( ".product_meta_col" ).find( ".main_product_id" ).text(),
            //variationID = $current_tr.find( ".product_title_col" ).find( ".product_variations" ).find( "option:selected" ).val() || 0,
            variationID = $this.parents(".variation_wrapper").attr("data-variation"),
            //quantity = $current_tr.find( ".product_quantity_col" ).find( ".qty" ).val();
            quantity = $this.siblings( ".quantity" ).find( ".qty" ).val();
            // console.log( 335, productType, productID, variationID, quantity );

        if ( productType == "variable" && variationID == 0 ) {

            alert( Options.no_variation_message );

            enabledSearchCommandFields();
            enablePagingLinks(); // We re-enable paging links as no new paging links are added when an error occur during ajax request

            $this
                .removeAttr( 'disabled' )
                .siblings( '.spinner' )
                .addClass( 'error' );

            fadeOutElement( $this.siblings( '.spinner' ) , 6000 );

            return false;

        }

        wwofFrontEndAjaxServices.addProductToCart( productType , productID , variationID , quantity )
            .done( function( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    enabledSearchCommandFields();
                    enablePagingLinks(); // We re-enable paging links as no new paging links are added when an error occured during ajax request

                    $wwofProductListingContainer
                        .find( ".wwof_cart_sub_total" )
                        .replaceWith( data.cart_subtotal_markup );

                    // CUSTOM UPDATE MAIN CART TALLY
                    // console.log( 374, data );

                    $this
                        .removeAttr( 'disabled' )
                        .siblings( '.spinner' )
                        .addClass( 'success' );

                    fadeOutElement( $this.siblings( '.spinner' ) , 3000 );

                    // Update cart widget
                    var fragments = data.fragments,
                        cart_hash = data.cart_hash;

                    // Block fragments class
                    blockFragments( fragments );

                    // Replace fragments
                    replaceFragments( fragments );

                    // Unblock fragments class
                    unblockFragments( fragments );

                    //Trigger event so themes can refresh other areas
                    $( 'body' ).trigger( 'added_to_cart', [ fragments, cart_hash, $this ] );
                    $( 'body' ).trigger( 'adding_to_cart' );

                } else if ( data.status == 'failed' ) {

                    alert( data.error_message );

                    enabledSearchCommandFields();
                    enablePagingLinks(); // We re-enable paging links as no new paging links are added when an error occured during ajax request

                    $this
                        .removeAttr( 'disabled' )
                        .siblings( '.spinner' )
                        .addClass( 'error' );

                    fadeOutElement( $this.siblings( '.spinner' ) , 6000 );

                }

            } )
            .fail( function( jqXHR , textStatus , errorThrown ) {

                console.log( jqXHR.responseText );

                alert( errorThrown );

                enabledSearchCommandFields();
                enablePagingLinks(); // We re-enable paging links as no new paging links are added when an error occured during ajax request

                $this
                    .removeAttr( 'disabled' )
                    .siblings( '.spinner' )
                    .addClass( 'error' );

                fadeOutElement( $this.siblings( '.spinner' ) , 6000 );

            } );
    } );

    // 7
    $wwofProductListingContainer.delegate( '#wwof_bulk_add_to_cart_button' , 'click' , function () {

        var $this = $( this ),
            products = [];

        $this
            .attr( 'disabled' , 'disabled' )
            .siblings( '.spinner' )
                .css( 'display' , 'inline-block' );

        disableSearchCommandFields();
        disabledPagingLinks();

        $wwofProductListingContainer
            .find( ".wwof_add_to_cart_checkbox" )
            .each( function ( index ) {

                if ( $( this ).is( ":checked" ) ) {

                    var $current_tr = $( this ).closest( 'tr'),
                        productType = $current_tr.find( ".product_meta_col" ).find( ".product_type" ).text(),
                        productID = $current_tr.find( ".product_meta_col" ).find( ".main_product_id" ).text(),
                        variationID = $current_tr.find( ".product_title_col" ).find( ".product_variations" ).find( "option:selected" ).val() || 0,
                        quantity = $current_tr.find( ".product_quantity_col" ).find( ".qty" ).val(),
                        addCurrentItem = true;

                    if ( productType == "variable" && variationID == 0 )
                        addCurrentItem = false;

                    if ( addCurrentItem ) {

                        products.push( {
                            productType :   productType,
                            productID   :   productID,
                            variationID :   variationID,
                            quantity    :   quantity
                        } );

                    }
                }

            } );

        if ( products.length > 0 ) {

            wwofFrontEndAjaxServices.addProductsToCart( products )
                .done ( function( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        $this
                            .siblings( ".products_added" )
                                .css( "display" , "inline-block" )
                            .find( "b" )
                                .text( data.total_added )
                            .end().end()
                            .siblings( ".view_cart" )
                                .css( "display" , "block" );

                        fadeOutElement( $this.siblings( ".products_added" ) , 8000 );
                        //fadeOutElement( $this.siblings( ".view_cart" ) , 10000 );

                        $wwofProductListingContainer
                            .find( ".wwof_cart_sub_total" )
                            .replaceWith( data.cart_subtotal_markup );

                        // Update cart widget
                        var fragments = data.fragments,
                            cart_hash = data.cart_hash;

                        // Block fragments class
                        blockFragments( fragments );

                        // Replace fragments
                        replaceFragments( fragments );

                        // Unblock fragments class
                        unblockFragments( fragments );

                        //Trigger event so themes can refresh other areas
                        $( 'body' ).trigger( 'added_to_cart', [ fragments, cart_hash, $this ] );
                        $( 'body' ).trigger( 'adding_to_cart' );

                    } else if (data.status == 'failed' )
                        alert( data.error_message );

                } )
                .fail ( function( jqXHR , textStatus , errorThrown ) {

                    console.log( jqXHR.responseText );
                    alert( errorThrown );

                } )
                .always ( function () {

                    enabledSearchCommandFields();
                    enablePagingLinks(); // We re-enable paging links as no new paging links are added when an error occured during ajax request

                    $this
                        .removeAttr( 'disabled' )
                        .siblings( '.spinner' )
                        .css( 'display' , 'none' );

                    $wwofProductListingContainer
                        .find( ".wwof_add_to_cart_checkbox" )
                        .removeAttr( 'checked' );

                    $wwofProductListingContainer
                        .find( ".quantity input[type='number']" )
                        .val( 1 );

                } );

        } else {

            enabledSearchCommandFields();
            enablePagingLinks(); // We re-enable paging links as no new paging links are added when an error occured during ajax request

            $this
                .removeAttr( 'disabled' )
                .siblings( '.spinner' )
                    .css( 'display' , 'none' );

        }

    } );




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Exe
     |------------------------------------------------------------------------------------------------------------------
     |
     | 1. Load product listing on load
     | 2. On every product item inserted to product listing, attach fancy box to its product links
     | 3. Set default values to search fields
     |------------------------------------------------------------------------------------------------------------------
     */

    // 1
    LoadProductListing( 1 , "" , "" );

    // 2
    $wwofProductListingContainer.delegate( "#wwof_product_listing_ajax_content" , "DOMNodeInserted" , function( e ) {

        // Get the e.target and wrap it in jquery to make it a jquery object
        var $element = $( e.target );

        // Only attach fancy box if settings does allow it
        if ( Options.display_details_on_popup == 'yes' ) {

            // Attach fancy box feature to product links
            $element.find( '.product_link' ).fancybox( {
                maxWidth    :   600,
                maxHeight   :   600,
                fitToView   :   false,
                width       :   '60%',
                height      :   '60%',
                autoSize    :   false,
                closeClick  :   false,
                openEffect  :   'none',
                closeEffect :   'none',
                type        :   'ajax',
                helpers     :   {
                    overlay :   {
                        locked      :   true, // prevents scrolling on the background
                        opacity     :   0.5
                    }
                }
            } );
        }

        // Trigger product variation select box change event on load
        $element.find( '.product_variations' ).trigger( 'change' );

    } );

    // 3.
    $wwofProductListingFilter.find( "#wwof_product_search_form" ).val( '' );
    $wwofProductListingFilter.find( "#wwof_product_search_category_filter" ).find( "option:first" ).attr( 'selected' , 'selected' );

} );