jQuery(document).ready(function($){

    // Variable Declaration And Selector Caching
    var $wwlc_approve = $(".wwlc_approve"),
        $wwlc_reject = $(".wwlc_reject"),
        $wwlc_activate = $(".wwlc_activate"),
        $wwlc_deactivate = $(".wwlc_deactivate"),
        errorNoticeDuration = "8000",
        successNoticeDuration = "5000";

    // Events
    $wwlc_approve.click(function(){

        var $this = $(this);

        $this.attr( 'disabled' , 'disabled' );

        var userID = $.trim( $this.attr( "data-userID" ) );

        wwlcBackEndAjaxServices.approveUser( userID )
            .done(function( data , textStatus , jqXHR ){

                if ( data.status == 'success' )
                    window.location = data.redirect_url;

            })
            .fail(function( jqXHR , textStatus , errorThrown ){

                toastr.error( jqXHR.responseText , UserListingVars.approving_failed_message , { "closeButton" : true , "showDuration" : errorNoticeDuration } );

                console.log( UserListingVars.approving_failed_message );
                console.log( jqXHR );
                console.log( '----------' );

                $this.removeAttr( 'disabled' );

            });

        return false;

    });

    $wwlc_reject.click(function(){

        var $this = $(this);

        $this.attr( 'disabled' , 'disabled' );

        var userID = $.trim( $this.attr( "data-userID" ) );

        wwlcBackEndAjaxServices.rejectUser( userID )
            .done(function( data , textStatus , jqXHR ){

                if ( data.status == 'success' )
                    window.location = data.redirect_url;

            })
            .fail(function( jqXHR , textStatus , errorThrown ){

                toastr.error( jqXHR.responseText , UserListingVars.rejecting_failed_message , { "closeButton" : true , "showDuration" : errorNoticeDuration } );

                console.log( UserListingVars.rejecting_failed_message );
                console.log( jqXHR );
                console.log( '----------' );

                $this.removeAttr( 'disabled' );

            });

        return false;

    });

    $wwlc_activate.click(function(){

        var $this = $(this);

        $this.attr( 'disabled' , 'disabled' );

        var userID = $.trim( $this.attr( "data-userID" ) );

        wwlcBackEndAjaxServices.activateUser( userID )
            .done(function( data , textStatus , jqXHR ){

                if ( data.status == 'success' )
                    window.location = data.redirect_url;

            })
            .fail(function( jqXHR , textStatus , errorThrown ){

                toastr.error( jqXHR.responseText , UserListingVars.activating_failed_message , { "closeButton" : true , "showDuration" : errorNoticeDuration } );

                console.log( UserListingVars.activating_failed_message );
                console.log( jqXHR );
                console.log( '----------' );

                $this.removeAttr( 'disabled' );

            });

        return false;

    });

    $wwlc_deactivate.click(function(){

        var $this = $(this);

        $this.attr( 'disabled' , 'disabled' );

        var userID = $.trim( $this.attr( "data-userID" ) );

        wwlcBackEndAjaxServices.deactivateUser( userID )
            .done(function( data , textStatus , jqXHR ){

                if ( data.status == 'success' )
                    window.location = data.redirect_url;

            })
            .fail(function( jqXHR , textStatus , errorThrown ){

                toastr.error( jqXHR.responseText , UserListingVars.deactivating_failed_message , { "closeButton" : true , "showDuration" : errorNoticeDuration } );

                console.log( UserListingVars.deactivating_failed_message );
                console.log( jqXHR );
                console.log( '----------' );

                $this.removeAttr( 'disabled' );

            });

        return false;

    });

    // On Load
    $( ".wwlc_user_row_action" ).removeAttr( 'disabled' , 'disabled' );
    $( ".wwlc_user_row_action.hidden" ).closest( "span" ).css( "display" , "none" );

});