/**
 * A function implementing the revealing module pattern to house all ajax request. It implements the ajax promise methodology
 * @return {Ajax Promise} promise it returns a promise, I promise that #lamejoke
 *
 * Info:
 * ajaxurl points to admin ajax url for ajax call purposes. Added by wp when script is wp enqueued
 */
var wwlcBackEndAjaxServices = function(){

    var approveUser = function( userID ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_approveUser" , userID : userID },
                dataType    :   "json"
            });

        },
        rejectUser = function( userID ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_rejectUser" , userID : userID },
                dataType    :   "json"
            });

        },
        activateUser = function( userID ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_activateUser" , userID : userID },
                dataType    :   "json"
            });

        },
        deactivateUser = function( userID ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_deactivateUser" , userID : userID },
                dataType    :   "json"
            });

        },
        createLeadPages = function(){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_createLeadPages" },
                dataType    :   "json"
            });

        },
        saveWWLCLicenseDetails = function( licenseDetails ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_saveLicenseDetails" , licenseDetails : licenseDetails },
                dataType    :   "json"
            });

        },
        addRegistrationFormCustomField = function( customField ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_addRegistrationFormCustomField" , customField : customField },
                dataType    :   "json"
            });

        },
        editRegistrationFormCustomField = function( customField ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_editRegistrationFormCustomField" , customField : customField },
                dataType    :   "json"
            });

        },
        deleteRegistrationFormCustomField = function( field_id ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_deleteRegistrationFormCustomField" , field_id : field_id },
                dataType    :   "json"
            });

        },
        getRegistrationFormCustomFieldByID = function ( field_id ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_getCustomFieldByID" , field_id : field_id },
                dataType    :   "json"
            });

        },
        getStates = function( cc ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_getStates" , cc : cc },
                dataType    :   "json"
            });

        };

    return {
        getStates                           :   getStates,
        approveUser                         :   approveUser,
        rejectUser                          :   rejectUser,
        activateUser                        :   activateUser,
        deactivateUser                      :   deactivateUser,
        createLeadPages                     :   createLeadPages,
        saveWWLCLicenseDetails              :   saveWWLCLicenseDetails,
        addRegistrationFormCustomField      :   addRegistrationFormCustomField,
        editRegistrationFormCustomField     :   editRegistrationFormCustomField,
        deleteRegistrationFormCustomField   :   deleteRegistrationFormCustomField,
        getRegistrationFormCustomFieldByID  :   getRegistrationFormCustomFieldByID

    }

}();