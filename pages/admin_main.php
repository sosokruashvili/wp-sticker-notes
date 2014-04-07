<!-- Plugin Admin Main Page Template  -->
<h1 class="wpst-admin-head"><?php echo __( "WP Sticky Notes Settings: " )?></h1>
<div class="paypal-donate">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="EP5NWENYAPGTC">
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</div>
<br><br><br>
<h2><?php echo __( "Permission Management" )?></h2><hr>
<?php
global $wpdb;
global $role_to_change; 
// Get user roles array
$USER_P = get_option(  $wpdb->prefix.'user_roles' );
$everyone = get_option( "wpst_allow_unauthorized" );
?>

<form name="group-permissions" action="" method="post">
<div style="float:left;">
    <label><?php echo __( "Groups" )?></label><div class="clear h10"></div>
    <select id="wpst_perm_groups" name="wpst_group">
        <?php foreach( $USER_P as $slug => $user_group ): if( $slug == "administrator" ) continue; ?>
            <option <?php echo ( $role_to_change == $slug ) ? "selected" : ""; ?> value="<?php echo $slug?>"><?php echo $user_group["name"]?></option>
        <?php endforeach?>
            <option <?php echo ( $role_to_change == 'everyone' ) ? "selected" : ""; ?> value="everyone"><?php echo __("Unauthorized users"); ?></option>
    </select>
    <input type="hidden" name="permissions_submit" value="1">
</div>
<div style="float:left; margin-left:40px;">
    <label><?php echo __( "Capabilities" )?></label><div class="clear h10"></div>
    <?php foreach( $USER_P as $slug => $user_group ):  if( $slug == "administrator" ) continue; ?>
    <?php 
		$temp_role = get_role( $slug );
	?>
    <select id="<?php echo $slug?>_caps" name="<?php echo $slug?>[]" multiple class="wpst-caps-list hide" >
      <option <?php echo ( @array_key_exists( "wpst_read", $temp_role->capabilities ) ) ? "selected" : ""; ?> value="wpst_read"><?php echo __("Read")?></option>
      <option <?php echo ( @array_key_exists( "wpst_create", $temp_role->capabilities ) ) ? "selected" : ""; ?> value="wpst_create"><?php echo __("Create")?></option>
      <option <?php echo ( @array_key_exists( "wpst_edit", $temp_role->capabilities ) ) ? "selected" : ""; ?> value="wpst_edit"><?php echo __("Edit / Delete")?></option>
    </select>
    <?php endforeach?>
    
     <select id="everyone_caps" name="everyone[]" multiple class="wpst-caps-list hide" >
      <option <?php echo ( @in_array( "wpst_read", $everyone ) ) ? "selected" : ""; ?> value="wpst_read"><?php echo __("Read")?></option>
      <option <?php echo ( @in_array( "wpst_create", $everyone ) ) ? "selected" : ""; ?> value="wpst_create"><?php echo __("Create")?></option>
      <option <?php echo ( @in_array( "wpst_edit", $everyone ) ) ? "selected" : ""; ?> value="wpst_edit"><?php echo __("Edit / Delete")?></option>
    </select>
</div>
<div class="clear h10"></div>
<span class="description"><?php echo __( "You can also edit permissions individually for users on <a href='/wp-admin/users.php'>user edit page</a>" )?></span>
<div class="clear h10"></div>
    <button class="button action"><?php echo __( "Save" )?></button>
</form>
<div class="clear h30"></div>

<script>
( function( $ ) {
	$("#wpst_perm_groups").change(function(e) {
        $('.wpst-caps-list').hide();
		$("#"+$('#wpst_perm_groups').val()+"_caps").show();
    });
	$("#wpst_perm_groups").change();
} )( jQuery );
</script>