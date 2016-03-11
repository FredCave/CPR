/**
 * A function implementing the revealing module pattern to house all ajax request. It implements the ajax promise methodology
 * @return {Ajax Promise} promise it returns a promise, I promise that #lamejoke
 *
 * Info:
 * Ajax is a variable injected by the server inside this js file. It has an attribute named ajaxurl which points
 * to admin ajax url for ajax call purposes
 */
var wwlcFrontEndAjaxServices = function(){

    var createUser =   function( userData , wwlc_register_user_nonce_field ){
        return jQuery.ajax({
            url         :   Ajax.ajaxurl,
            type        :   "POST",
            data        :   { action : "wwlc_createUser" , userData : userData , wwlc_register_user_nonce_field : wwlc_register_user_nonce_field },
            dataType    :   "json"
        });
    },
    getStates =   function( cc ){
        return jQuery.ajax({
            url         :   Ajax.ajaxurl,
            type        :   "POST",
            data        :   { action : "wwlc_getStates" , cc : cc },
            dataType    :   "json"
        });
    };

    return {
        createUser  :   createUser,
        getStates   :   getStates
    }

}();