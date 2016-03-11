var wwppWholesaleRolesFormActions = function(){

    var $wholesaleRolesForm = jQuery("#wwpp-wholesale-roles-page").find("#wholesale-form"),
        initialForm = function(){

            $wholesaleRolesForm.find("#role-name").val('');
            $wholesaleRolesForm.find("#role-key").removeAttr('disabled').val('');
            $wholesaleRolesForm.find("#role-desc").val('');
            //$wholesaleRolesForm.find("#role-shipping-class").val('').trigger("chosen:updated");

            $wholesaleRolesForm.find(".submit.add-controls").css("display","block");
            $wholesaleRolesForm.find(".submit.edit-controls").css("display","none");

        },
        setFormToEditMode = function($role){

            console.log($role);

            $wholesaleRolesForm.find("#role-name").val($role['roleName']);
            $wholesaleRolesForm.find("#role-key").attr('disabled','disabled').val($role['roleKey']);
            $wholesaleRolesForm.find("#role-desc").val($role['roleDesc']);
            //$wholesaleRolesForm.find("#role-shipping-class").val($role['roleShippingClassTermId']).trigger("chosen:updated");

            $wholesaleRolesForm.find(".submit.add-controls").css("display","none");
            $wholesaleRolesForm.find(".submit.edit-controls").css("display","block");

        },
        setSubmitButtonToNormalState = function(){

            $wholesaleRolesForm
                .find(".button")
                    .removeAttr("disabled")
                .siblings(".spinner")
                    .css("display","none")
                    .css( "visibility" , "hidden" );

        },
        setSubmitButtonToProcessingState = function(){

            $wholesaleRolesForm
                .find(".button")
                    .attr("disabled","disabled")
                .siblings(".spinner")
                    .css("display","inline-block")
                    .css( "visibility" , "visible" );

        };

    return {
        initialForm                         :   initialForm,
        setFormToEditMode                   :   setFormToEditMode,
        setSubmitButtonToNormalState        :   setSubmitButtonToNormalState,
        setSubmitButtonToProcessingState    :   setSubmitButtonToProcessingState
    };

}();