<!-- Plugin Admin Main Page Template  -->
<h1><?=__("WP Sticker Notes Settings: ")?></h1> 
<br><br><br>
<h2><?=__("Permission Management")?></h2><hr>
<?
global $wpdb;

// Get user roles array to use user group list
$USER_P = get_option(  $wpdb->prefix.'user_roles', $default );
$groups = get_option( "wpst_allow_user_groups" );

?>
<form name="user-group-choose" action="" method="post">
	<label><?=__("Select user group which can create and see stickers")?></label><div style="clear:both; margin-top:20px;"></div>
	<select name="group[]" multiple style="width:200px; height:250px;">
    	<? foreach( $USER_P as $slug => $user_group ):  if( $slug == 'administrator' ) continue;  ?>
    		<option <?=(in_array($slug, $groups)) ? "selected" : "";?> value="<?=$slug?>"><?=$user_group["name"]?></option>
        <? endforeach?>
    </select>
    <div class="clear h20"></div>
    <button class="button action"><?=__("Save")?></button>
</form>


