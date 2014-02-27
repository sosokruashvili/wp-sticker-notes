<!-- Plugin Admin Main Page Template  -->
<h1><?php echo __( "WP Sticky Notes Settings: " )?></h1> 
<br><br><br>
<h2><?php echo __( "Permission Management" )?></h2><hr>
<?php
global $wpdb;
// Get user roles array
$USER_P = get_option(  $wpdb->prefix.'user_roles' );
$groups = get_option( "wpst_allow_user_groups" );
?>
<form name="user-group-choose" action="" method="post">
	<label><?php echo __( "Select user group which can create and see stickers" )?></label><div class="clear h10"></div>
	<select name="group[]" multiple >
    	<?php foreach( $USER_P as $slug => $user_group ):  if( $slug == 'administrator' ) continue;  ?>
    		<option <?php echo ( @in_array( $slug, $groups ) ) ? "selected" : ""; ?> value="<?php echo $slug?>"><?php echo $user_group["name"]?></option>
        <?php endforeach?>
        	<option value="everyone" <?php echo ( @in_array( "everyone", $groups ) ) ? "selected" : ""; ?> ><?php echo __("Everyone ( Includes unauthorized users )"); ?></option>
    </select>
    <div class="clear h10"></div>
    <button class="button action"><?php echo __( "Save" )?></button>
</form>
<div class="clear h30"></div>