/**
 * A function implementing the revealing module pattern to house all ajax request. It implements the ajax promise methodology
 * @return {Ajax Promise} promise it returns a promise, I promise that #lamejoke
 *
 * Info:
 * Ajax is a variable injected by the server inside this js file. It has an attribute named ajaxurl which points
 * to admin ajax url for ajax call purposes
 */
var wwofFrontEndAjaxServices    =   function(){

    var displayProductListing   =   function( paged , search , catFilter ) {

            return jQuery.ajax({
                url         :   Ajax.ajaxurl,
                type        :   "POST",
                data        :   { action : "wwof_displayProductListing" , "paged" : paged , "search" : search , "catFilter" : catFilter },
                dataType    :   "html"
            });

        },
        addProductToCart        =   function( productType , productID , variationID , quantity ) {

            return jQuery.ajax({
                url         :   Ajax.ajaxurl,
                type        :   "POST",
                data        :   { action : "wwof_addProductToCart" , "productType" : productType , "productID" : productID , "variationID" : variationID , "quantity" : quantity },
                dataType    :   "json"
            });

        },
        addProductsToCart       =   function ( products ) {

            return jQuery.ajax({
                url         :   Ajax.ajaxurl,
                type        :   "POST",
                data        :   { action : "wwof_addProductsToCart" , "products" : products },
                dataType    :   "json"
            });

        };

    return {
        displayProductListing   :   displayProductListing,
        addProductToCart        :   addProductToCart,
        addProductsToCart       :   addProductsToCart
    }

}();