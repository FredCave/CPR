var wwppWholesaleRolesListingActions = function(){

    var $wholesaleRolesTable = jQuery("#wwpp-wholesale-roles-page").find(".wp-list-table"),
        $wholesaleRolesList = $wholesaleRolesTable.find("#the-list"),
        refreshRowClasses = function(){

            $wholesaleRolesList.find("tr").each(function(index){

                if((index + 1) % 2 != 0){

                    jQuery(this).addClass('alternate');

                }else{

                    jQuery(this).removeClass('alternate');

                }

            });

        },
        removeNewlyAddedRowClasses = function(){

            setTimeout(function(){
                $wholesaleRolesList
                    .find('.newlyAdded')
                    .removeClass('newlyAdded');
            },3000);

        },
        setRowToEditMode = function($row){

            $row.addClass("editing");

        },
        setRowsToNormalMode = function(){

            $wholesaleRolesList.find("tr").removeClass("editing");

        },
        incrementRolesCount = function(){

            $wholesaleRolesTable.siblings(".tablenav").find(".wholesale-roles-count").each(function(){

                jQuery(this).text(parseInt(jQuery(this).text(),10) + 1);

            });

        },
        decrementRolesCount = function(){

            $wholesaleRolesTable.siblings(".tablenav").find(".wholesale-roles-count").each(function(){

                if(parseInt(jQuery(this).text(),10) > 0)
                    jQuery(this).text(parseInt(jQuery(this).text(),10) - 1);

            });

        },
        addRole = function(newRole){

            var newRow =    '<tr class="newlyAdded">' +
                                '<th class="check-column" scope="row">' +
                                    '<input type="checkbox">' +
                                '</th>' +
                                '<td class="role-name column-role-name">' +
                                    '<strong><a>'+newRole['roleName']+'</a></strong><br>' +
                                    '<div class="row-actions">' +
                                        '<span class="edit"><a class="edit-role" href="#">Edit</a> | </span>' +
                                        '<span class="delete"><a class="delete-role" href="#">Delete</a></span>' +
                                    '</div>' +
                                '</td>' +
                                '<td class="role-key column-role-key">'+newRole['roleKey']+'</td>' +
                                '<td class="role-desc column-role-desc">'+newRole['roleDesc']+'</td>' +
                                //'<td class="role-shipping-class column-role-shipping-class">'+
                                //    '<span class="shipping-class-name">'+newRole['roleShippingClassName']+'</span>'+
                                //    '<span class="shipping-class-term-id" style="display:none !important;">'+newRole['roleShippingClassTermId']+'</span>'+
                                //'</td>' +
                            '</tr>';

            $wholesaleRolesList.append(newRow);

            incrementRolesCount();

            refreshRowClasses();

            removeNewlyAddedRowClasses();

        },
        editRole = function(role){

            var currentRow = $wholesaleRolesList.find('.column-role-key').filter(function(){

                return jQuery(this).text() === role['roleKey'];

            }).closest('tr');

            currentRow.find('.column-role-name').find('strong').find('a').text(role['roleName']);
            currentRow.find('.column-role-desc').text(role['roleDesc']);
            //currentRow.find('.column-role-shipping-class').find('.shipping-class-name').text(role['roleShippingClassName']);
            //currentRow.find('.column-role-shipping-class').find('.shipping-class-term-id').text(role['roleShippingClassTermId']);
            currentRow.addClass('newlyAdded');

            removeNewlyAddedRowClasses();

        },
        deleteRole = function(roleKey){

            $wholesaleRolesList.find('.column-role-key').filter(function(){

                return jQuery(this).text() === roleKey;

            }).closest('tr').remove();

            decrementRolesCount();
            refreshRowClasses();

        };

    return {
        addRole             :   addRole,
        editRole            :   editRole,
        deleteRole          :   deleteRole,
        setRowToEditMode    :   setRowToEditMode,
        setRowsToNormalMode :   setRowsToNormalMode
    };

}();