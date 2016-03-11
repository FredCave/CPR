<script>
    jQuery(document).ready(function($){

        $("#wholesale-visibility-select").chosen();

    });
</script>
<div id="wholesale-visiblity" class="misc-pub-section">
    <strong><?php _e('Restrict To Wholesale Roles:','woocommerce-wholesale-prices-premium'); ?></strong>
    <p><em><?php _e('Set this product to be visible only to specified wholesale user role/s only','woocommerce-wholesale-prices-premium'); ?></em></p>
    <div id="wholesale-visibility-select-container">

        <select style="width: 100%;" data-placeholder="Choose wholesale users..." name="wholesale-visibility-select[]" id="wholesale-visibility-select" multiple>
        <?php
        foreach($allRegisteredWholesaleRoles as $roleKey => $role){
            ?>
            <option value="<?php echo $roleKey ?>" <?php if(in_array($roleKey,$currProductWholesaleFilter)){ echo "selected"; } ?>><?php echo $role['roleName']; ?></option>
            <?php
        }
        ?>
        </select><!--#wholesale-visibility-select-->

    </div><!--#wholesale-visibility-select-->

</div><!--#wholesale-visiblity-filter-->