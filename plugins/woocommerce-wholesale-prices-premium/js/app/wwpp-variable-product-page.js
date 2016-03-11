jQuery( document ).ready( function ( $ ) {

    var $variations_form = $( ".variations_form" );

    $variations_form.on( "woocommerce_variation_has_changed" , function () {

        var variation_id = $variations_form.find( ".single_variation_wrap .variation_id" ).attr( 'value' ),
            $single_variation = $variations_form.find( ".single_variation" ),
            $qty_field = $variations_form.find( ".variations_button .qty" );

        for ( var i = 0 ; i < WWPPVariableProductPageVars.variations.length ; i++ ) {

            if ( WWPPVariableProductPageVars.variations[ i ][ 'variation_id' ] == variation_id ) {

                // There is this case where if a variable product has variations has the same regular price
                // WooCommerce won't spit out the mark up that shows price for each variation on the front end
                // Which makes sense coz they all have the same price, so they will just refer tot he price printed on the variable level
                // ( Note: I am talking here about single variable product page on the front end )
                // Now the prob with this is, the wholesale price won't be displayed too on the front end, coz we are hooking on the function
                // that displays the price of each variation on the front end but remember that function won't get triggered coz of the
                // condition above right? So we need an alternative way of displaying the wholesale price on the front end then.
                // That's the purpose of the code below. We check if there is no tag inside the .single_variation tag with a class of price
                // This means that no price mark up was printed out
                // If indeed true , we manually output the price html
                // And by price html I mean either the regular price or the regular price crossed out with wholesale price
                // Note, you should prepend, not override the entire html of .single_variation as it may contain other markup

                if ( $single_variation.find( ".price" ).length <= 0 )
                    $single_variation.prepend( WWPPVariableProductPageVars.variations[ i ][ 'price_html' ] );

                // Change the minimum attribute of the quantity field to the minimum order quantity set for this product to avail wholesale pricing
                // if it has a wholesale price and has a minimum order quantity set
                if ( WWPPVariableProductPageVars.variations[ i ][ 'minimum_order' ] > 0 && WWPPVariableProductPageVars.variations[ i ][ 'has_wholesale_price' ] )
                    $qty_field.val( WWPPVariableProductPageVars.variations[ i ][ 'minimum_order' ] );

            }

        }

    } );

} );