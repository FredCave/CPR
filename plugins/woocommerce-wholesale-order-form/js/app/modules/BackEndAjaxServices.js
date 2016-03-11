/**
 * A function implementing the revealing module pattern to house all ajax request. It implements the ajax promise methodology
 * @return {Ajax Promise} promise it returns a promise, I promise that #lamejoke
 *
 * Info:
 * ajaxurl points to admin ajax url for ajax call purposes. Added by wp when script is wp enqueued
 */
var wwofBackEndAjaxServices = function() {

    var createWholesalePage =   function() {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwof_createWholesalePage" },
                dataType    :   "json"
            });

        },
        saveWWOFLicenseDetails = function( licenseDetails ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwof_saveLicenseDetails" , licenseDetails : licenseDetails },
                dataType    :   "json"
            });

        };

    return {
        createWholesalePage     :   createWholesalePage,
        saveWWOFLicenseDetails  :   saveWWOFLicenseDetails
    }

}();