jQuery(document).ready(function($){

    // Variable Declarations
    var $wholesaleRolesPage = $("#wwpp-wholesale-roles-page"),
        $wholesaleTable = $wholesaleRolesPage.find(".wp-list-table"),
        $wholesaleForm = $wholesaleRolesPage.find("#wholesale-form"),
        errorToastrShowDuration = "12000",
        successToastrShowDuration = "5000";




    // Events ---------

    // Only allow letters, numbers and underscores in rolekey
    $wholesaleForm.find( "#role-key" ).keyup( function() {

        var raw_text =  jQuery(this).val();
        var return_text = raw_text.replace(/[^a-zA-Z0-9_]/g,'');
        jQuery(this).val(return_text);

    } );

    $wholesaleForm.find("#add-wholesale-role-submit").click(function(){

        wwppWholesaleRolesFormActions.setSubmitButtonToProcessingState();

        var roleName = $.trim($wholesaleForm.find("#role-name").val()),
            roleKey = $.trim($wholesaleForm.find("#role-key").val()),
            roleDesc = $.trim($wholesaleForm.find("#role-desc").val()),
            roleShippingClassName = $.trim($wholesaleForm.find("#role-shipping-class").find("option:selected").text()),
            roleShippingClassTermId = $.trim($wholesaleForm.find("#role-shipping-class").find("option:selected").val()),
            checkPoint = true;

        if(roleName == ''){
            toastr.error('Please Enter Role Name','Error in Wholesale Form',{"closeButton": true,"showDuration": errorToastrShowDuration});
            checkPoint = false;
            wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();
        }

        if(roleKey == ''){
            toastr.error('Please Enter Role Key','Error in Wholesale Form',{"closeButton": true,"showDuration": errorToastrShowDuration});
            checkPoint = false;
            wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();
        }

        if(checkPoint){

            var newRole =   {
                'roleKey'                   :   roleKey,
                'roleName'                  :   roleName,
                'roleDesc'                  :   roleDesc,
                'roleShippingClassName'     :   roleShippingClassName,
                'roleShippingClassTermId'   :   roleShippingClassTermId
            };

            wwppBackendAjaxServices.addNewWholesaleRole(newRole)
                .done(function(data, textStatus, jqXHR){

                    if(data.status == 'success'){

                        wwppWholesaleRolesListingActions.addRole(newRole);

                        toastr.success(newRole.roleName+' Wholesale Role Successfully Added','Successfully Added New Role',{"closeButton": true,"showDuration": successToastrShowDuration});

                    }else{

                        toastr.error(data.error_message,'Failed to Add New Wholesale Role',{"closeButton": true,"showDuration": errorToastrShowDuration});
                        console.log(data);

                    }

                    wwppWholesaleRolesFormActions.initialForm();
                    wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();

                })
                .fail(function(jqXHR, textStatus, errorThrown){

                    toastr.error(jqXHR.responseText,'Failed to Add New Wholesale Role',{"closeButton": true,"showDuration": errorToastrShowDuration});

                    console.log('Failed to Add New Wholesale Role');
                    console.log(jqXHR);
                    console.log('----------');

                    wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();

                });

        }

        return false;

    });

    $wholesaleForm.find("#edit-wholesale-role-submit").click(function(){

        wwppWholesaleRolesFormActions.setSubmitButtonToProcessingState();

        var roleName = $.trim($wholesaleForm.find("#role-name").val()),
            roleKey = $.trim($wholesaleForm.find("#role-key").val()),
            roleDesc = $.trim($wholesaleForm.find("#role-desc").val()),
            roleShippingClassName = $.trim($wholesaleForm.find("#role-shipping-class").find("option:selected").text()),
            roleShippingClassTermId = $.trim($wholesaleForm.find("#role-shipping-class").find("option:selected").val()),
            checkPoint = true;

        if(roleName == ''){
            toastr.error('Please Enter Role Name','Error in Wholesale Form',{"closeButton": true,"showDuration": errorToastrShowDuration});
            checkPoint = false;
            wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();
        }

        if(roleKey == ''){
            toastr.error('Please Enter Role Key','Error in Wholesale Form',{"closeButton": true,"showDuration": errorToastrShowDuration});
            checkPoint = false;
            wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();
        }

        if(checkPoint){

            var role =   {
                            'roleKey'                   :   roleKey,
                            'roleName'                  :   roleName,
                            'roleDesc'                  :   roleDesc,
                            'roleShippingClassName'     :   roleShippingClassName,
                            'roleShippingClassTermId'   :   roleShippingClassTermId
                        };

            wwppBackendAjaxServices.editWholesaleRole(role)
                .done(function(data, textStatus, jqXHR){

                    if(data.status == 'success'){

                        wwppWholesaleRolesListingActions.editRole(role);

                        toastr.success(role.roleName+' Wholesale Role Successfully Edited','Successfully Edited Role',{"closeButton": true,"showDuration": successToastrShowDuration});

                    }else{

                        toastr.error(data.error_message,'Failed to Edit Wholesale Role',{"closeButton": true,"showDuration": errorToastrShowDuration});
                        console.log(data);

                    }

                    wwppWholesaleRolesListingActions.setRowsToNormalMode();
                    wwppWholesaleRolesFormActions.initialForm();
                    wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();

                })
                .fail(function(jqXHR, textStatus, errorThrown){

                    toastr.error(jqXHR.responseText,'Failed to Edit Wholesale Role',{"closeButton": true,"showDuration": errorToastrShowDuration});

                    console.log('Failed to Edit New Wholesale Role');
                    console.log(jqXHR);
                    console.log('----------');

                    wwppWholesaleRolesFormActions.setSubmitButtonToNormalState();

                });

        }

        return false;

    });

    $wholesaleForm.find("#cancel-edit-wholesale-role-submit").click(function(){

        wwppWholesaleRolesListingActions.setRowsToNormalMode();
        wwppWholesaleRolesFormActions.initialForm();

    });

    $wholesaleTable.delegate(".edit-role","click",function(){

        wwppWholesaleRolesListingActions.setRowsToNormalMode();

        var $currentRow = $(this).closest("tr"),
            role = {
                'roleName'                  :   $.trim($currentRow.find(".column-role-name > strong > a").text()),
                'roleKey'                   :   $.trim($currentRow.find(".column-role-key").text()),
                'roleDesc'                  :   $.trim($currentRow.find(".column-role-desc").text()),
                'roleShippingClassName'     :   $.trim($currentRow.find(".column-role-shipping-class").find(".shipping-class-name").text()),
                'roleShippingClassTermId'   :   $.trim($currentRow.find(".column-role-shipping-class").find(".shipping-class-term-id").text())
            };

        wwppWholesaleRolesFormActions.setFormToEditMode(role);
        wwppWholesaleRolesListingActions.setRowToEditMode($currentRow);

        return false;

    });

    $wholesaleTable.delegate(".delete-role","click",function(){

        var $currentRow = $(this).closest("tr"),
            roleKey = $.trim($currentRow.find(".column-role-key").text());

        if(confirm('Delete '+roleKey+' Wholesale Role?')){

            wwppBackendAjaxServices.deleteWholesaleRole(roleKey)
                .done(function(data, textStatus, jqXHR){

                    if(data.status == 'success'){

                        wwppWholesaleRolesListingActions.deleteRole(roleKey);

                        toastr.success(roleKey+' Wholesale Role Successfully Deleted','Successfully Deleted Role',{"closeButton": true,"showDuration": successToastrShowDuration});

                    }else{

                        toastr.error(data.error_message,'Failed to Delete Wholesale Role',{"closeButton": true,"showDuration": errorToastrShowDuration});
                        console.log(data);

                    }

                })
                .fail(function(jqXHR, textStatus, errorThrown){

                    toastr.error(jqXHR.responseText,'Failed to Delete Wholesale Role',{"closeButton": true,"showDuration": errorToastrShowDuration});

                    console.log('Failed to Delete Wholesale Role');
                    console.log(jqXHR);
                    console.log('----------');

                });

        }

        return false;

    });




    // Init on load
    wwppWholesaleRolesFormActions.initialForm();
    //$wholesaleForm.find("#role-shipping-class").chosen({allow_single_deselect: true});

});