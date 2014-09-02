<?php
/*
Plugin Name: WP Sticky Notes
Plugin URI: http://sticker-notes.com/
Description: Add sticky note for any page to any position 
Version: 2.1.5
Author: Kruashvili
Author URI: http://sticker-notes.com/
License: GPL 2
*/

/*  
Copyright 2014  Kruashvili  (email : soso@kruashvili.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wpst_db_version;
$wpst_db_version = "1.5";

register_activation_hook( __FILE__, '__wp_sticker_plugin_install' );

add_action( 'plugins_loaded', 'wpst_update_db_check' );
add_action( 'admin_menu', '__wp_sticker_menu' );
add_action( 'wp_enqueue_scripts', 'wpst_load_front_files' );

// Plugin information vars
$WPST_PLUGIN['name'] = "WP Sticky Notes";
$WPST_PLUGIN['folder'] = basename( dirname( __FILE__ ) );

/* Set cookie variable to identify unauthorized users */
if( !$_COOKIE["wpst_id"] ) {
	setcookie( "wpst_id", uniqid(), time()+60*60*24*300, "/" );
}
/* Add custom caps to administrator by default */
$role = get_role( "administrator" ); 
$role->add_cap( "wpst_read" );
$role->add_cap( "wpst_edit" );
$role->add_cap( "wpst_create" );

// Update wordpress capabilities
if( @$_POST['permissions_submit'] == 1 ) {
	$role_to_change = $_POST["wpst_group"];
	if( $role_to_change != "everyone" ) {
		$role = get_role( $role_to_change ); 
		$role->remove_cap( "wpst_read" );
		$role->remove_cap( "wpst_edit" );
		$role->remove_cap( "wpst_create" );
		foreach( $_POST[ $role_to_change ] as $cap ) {
			$role->add_cap( $cap );
		}
		update_option( "wpst_allowable_post_type", $_POST['wpst_allowable_post_type'] );
	}
	else {
		update_option( "wpst_allow_unauthorized", $_POST['everyone'] );
		update_option( "wpst_allowable_post_type", $_POST['wpst_allowable_post_type'] );
	}
}

function __wp_sticker_menu() {
	global $WPST_PLUGIN;
	add_menu_page( 'WP Sticky Notes', 'WP Sticky Notes', 'edit_pages', 'wpst_main', '__wp_sticker_get_page', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/images/Notes-icon.png" );
}

function __wp_sticker_get_page() {
	global $WPSticker;
	global $WPST_PLUGIN;
	wp_enqueue_style( 'wpst-main-style', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/admin-style.css", false, "1.2.9" );
	require_once( __DIR__ . "/pages/admin_main.php" );
}

function wpst_is_unauth_and_can( $cap ) {
	if( is_user_logged_in() ) return false;
	$unauth_caps = get_option( "wpst_allow_unauthorized" );
	if( @in_array( $cap, $unauth_caps ) ) return true;
}

function wpst_load_front_files() {
	global $WPST_PLUGIN, $post;
	$post_types = get_option('wpst_allowable_post_type');

	if (in_array($post->post_type, $post_types)){
		wp_enqueue_style( 'wpst-main-style', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/wpst_style.css", false, "1.6.5" );	
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-draggable', "", array("jquery"), "", true );
		wp_enqueue_script( 'jquery-ui-resizable', "", array("jquery"), "", true );
		wp_enqueue_script( 'wpst-main-script', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/wpst_script.js", array("jquery"), "1.6.5", true );
		wp_enqueue_script( 'wpst-autolinker-tool', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/parsers/Autolinker.min.js", array(), "1.0", true );
		
		// Send data to client
		wpst_send_client_data();
	}
}

function wpst_send_client_data() {
	global $WPST_PLUGIN;
	$user_data = get_userdata( get_current_user_id() );
	
	$wpst_current_caps = array( "wpst_read" => wpst_user_can( "wpst_read" ),
								"wpst_create" => wpst_user_can( "wpst_create" ),
								"wpst_edit" => wpst_user_can( "wpst_edit" ) );
	$wpst_current_caps = array_filter( $wpst_current_caps );
	// Send CDATA for JS
	wp_localize_script( 'wpst-main-script', 'wpst_data', array(
						'userid' => get_current_user_id(),
						'home_url' => home_url(),
						'plugin_dir' => plugins_url() . "/" . $WPST_PLUGIN['folder'],
						'stickers' => get_stickers_json(),
						'wpst_current_caps' => $wpst_current_caps
					  ));
}

function wpst_user_can( $subject ) {
	switch( $subject ) {
		case "wpst_read":
			if( current_user_can( "wpst_read" ) || wpst_is_unauth_and_can( "wpst_read" ) )
				return true;
		break;
		
		case "wpst_create":
			if( current_user_can( "wpst_create" ) || wpst_is_unauth_and_can( "wpst_create" ) )
				return true;
		break;
		
		case "wpst_edit":
			if( current_user_can( "wpst_edit" ) || wpst_is_unauth_and_can( "wpst_edit" ) )
				return true;
		break;
		
		default:
			return false;
	}
}

function get_stickers_json() {
	global $wpdb;
	$current_url = "http" . (( $_SERVER['SERVER_PORT'] == 443 ) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$current_user_id = ( get_current_user_id() ) ? get_current_user_id() : "NAN";
	if( wpst_user_can( "wpst_read" ) ) {
		$results[] = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "sticker_notes WHERE url = '{$current_url}' AND target_users = '' ");
	}
	$results[] = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "sticker_notes WHERE url = '{$current_url}' AND author = " . $current_user_id );
	$results[] = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "sticker_notes WHERE url = '{$current_url}' AND cookie_user_id = '" . $_COOKIE["wpst_id"] . "'" );
	$results[] = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "sticker_notes WHERE url = '{$current_url}' AND target_users LIKE '%" . $current_user_id . "|%' ");
	foreach( $results as $res ) {
		if( !empty( $res ) ) {
			foreach( $res as $sticker_objects ) {
				$result[$sticker_objects->sticker_id] = $sticker_objects;
			}
		}
	}
	return json_encode( $result );
}

add_action( 'wp_ajax_nopriv_wpst_save_sticker', 'wpst_save_sticker' );
add_action( 'wp_ajax_wpst_save_sticker', 'wpst_save_sticker' );
function wpst_save_sticker() {
	global $wpdb;
	$sticker_id = $_POST["sticker_id"];
	$sticker_props = stripslashes( $_POST['properties'] );
	$sticker_url = $_POST['url'];
	$sticker_note = stripslashes( $_POST['note'] );
	$t_users = json_decode( $_POST['users'] );

	foreach( $t_users as $t_user ) {
		$target_users .= $t_user . "|";
	}

	if( $sticker_id ) {
		if( !$wpdb->get_results( "SELECT sticker_id FROM  " . $wpdb->prefix ."sticker_notes WHERE sticker_id = '{$sticker_id}'" ) ) {
			if( !wpst_user_can( "wpst_create" ) ) {
				exit( __("Yout cannot create sticky note") );
			}
			$res = $wpdb->insert( $wpdb->prefix ."sticker_notes", array( "sticker_id" => $sticker_id,
																		 "url" => $sticker_url,
																		 "properties" => $sticker_props,
																		 "note" => $sticker_note,
																		 "cr_date" => date("Y-m-d"),
																		 "author" => get_current_user_id(),
																		 "target_users" => $target_users,
																		 "cookie_user_id" => $_COOKIE["wpst_id"]
																		 ) );
			if( !$res )
				exit( __("Something wrong with query") );
			else
				wpst_send_notification( $t_users ); // Send notification mail to target users
		}
		else {
			$where["sticker_id"] = $sticker_id;
			if( !wpst_user_can( "wpst_edit" ) ) {
				$where["author"] = get_current_user_id();
				if( !is_user_logged_in() ) {
					$where["cookie_user_id"] = $_COOKIE["wpst_id"];
				}
			}
			$check = $wpdb->get_results( "SELECT target_users FROM  " . $wpdb->prefix ."sticker_notes WHERE sticker_id = '{$sticker_id}'", "ARRAY_A" );
			$old_target_users = array_filter( explode( "|", $check[0]['target_users'] ) );
			$notification_users = array_diff( $t_users, $old_target_users );
			$res1 = $wpdb->update( $wpdb->prefix ."sticker_notes", array( "sticker_id" => $sticker_id, 
																		"url" => $sticker_url, 
																		"properties" => $sticker_props, 
																		"target_users" => $target_users,
																		"note" => $sticker_note ), 
																	  $where );
			if( !$res1 )
				exit( __("You cannot edit this sticky note or is already deleted") );
			else
				wpst_send_notification( $notification_users ); // Send notification mail to only new target users
			
		}
	}
	unset( $where );
	exit("OK");
}

add_action( 'wp_ajax_nopriv_wpst_delete_sticker', 'wpst_delete_sticker' );
add_action( 'wp_ajax_wpst_delete_sticker', 'wpst_delete_sticker' );
function wpst_delete_sticker() {
	global $wpdb;
	$sticker_id = $_POST["sticker_id"];
	if( $sticker_id ) {
		if( !wpst_user_can( "wpst_edit" ) ) {
			$where["sticker_id"] = $sticker_id;
			$where["author"] = get_current_user_id();
			if( !is_user_logged_in() )
				$where["cookie_user_id"] = $_COOKIE['wpst_id'];
			
			$res2 = $wpdb->delete( $wpdb->prefix ."sticker_notes", $where );
			if( !$res2 )
				exit( __("You cannot delete this sticky note or is already deleted") );
		}
		else {
			$res2 = $wpdb->delete( $wpdb->prefix ."sticker_notes", array( "sticker_id" => $sticker_id ) );
			if( !$res2 )
				exit( __("Something wrong with query") );
		}
	}
	unset( $where );
	exit( "OK" );
}
/* 
Ajax get users list by search keyword
*/
add_action( 'wp_ajax_nopriv_wpst_get_users', 'wpst_get_users' );
add_action( 'wp_ajax_wpst_get_users', 'wpst_get_users' );
function wpst_get_users() {
	$search = strip_tags( $_POST['letters'] );
	$args = array (
		'search'  => '*' . $search .'*',
		'fields'  => array( 'ID', 'display_name' ),
		'search_columns' => array(
			'user_login',
			'user_nicename',
			'user_email',
			'user_url',
		),
		'number'  => 10
	);
	$user_query = new WP_User_Query( $args );
	
	echo json_encode( $user_query->results );
	exit();
}

function wpst_send_notification( $notification_users ) {
	foreach( $notification_users as $userid ) {
		$tempObj = get_userdata( $userid );
		$notification_emails[] = $tempObj->user_email;
	}
	$stc_id = $_POST["sticker_id"];
	$note_url = $_POST['url'];
	$website_url = get_site_url( $blog_id, $path, "http" ); 
	$current_user_info = get_userdata( get_current_user_id() );
	$current_user_display_name = $current_user_info->data->display_name;
	$headers = 'From: ' . $_SERVER['HTTP_HOST'] . ' <' . get_option( "admin_email") . ">\r\n";
	
	add_filter( 'wp_mail_content_type', 'set_html_content_type' );
	wp_mail( $notification_emails, "New sticky note ", "User: {$current_user_display_name} has tagged you on a note with text: <br><blockquote style='color:#7f7f7f'>" .  strip_tags( stripslashes( $_POST['note'] ) ) . "</blockquote><a href='{$note_url}#{$stc_id}'> See the note</a><br/><br/><span style='color:#7f7f7f'>Note: You may not see the note if you wont authorize</span>", $headers );
	// Reset content-type to avoid conflicts
	remove_filter( 'wp_mail_content_type', 'set_html_content_type' );	
}
function set_html_content_type() {
	return 'text/html';
}
function wpst_pr( $ar ) {
	echo "<pre>";
	print_r( $ar );
	echo "</pre>";
}

/* 
Create and display user meta field on user edit page, this field is for 
storing permission value 
*/
add_action( 'show_user_profile', 'wpst_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'wpst_extra_user_profile_fields' );
add_action( 'personal_options_update', 'wpst_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'wpst_save_extra_user_profile_fields' );
 
function wpst_save_extra_user_profile_fields( $user_id ) {
	$user = new WP_User( $user_id );
	$user->remove_cap( "wpst_read" );
	$user->remove_cap( "wpst_edit" );
	$user->remove_cap( "wpst_create" );
	foreach( $_POST['wpst_caps'] as $cap ) {
		$user->add_cap( $cap );
	}
}

function wpst_extra_user_profile_fields( $user ) {  ?> 
    <h3><?php echo __("Sticky Notes Permissions"); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="wpst_caps"><?php echo __("Sticky Notes caps") ?></label></th>
            <td>
            <select name="wpst_caps[]" id="wpst-user-profile-caps-select" multiple style="width:200px; height:100px;">
            	<option <?php echo ( user_can( $user, "wpst_read" ) ) ? "selected" : ""; ?> value="wpst_read"><?php echo __("Read")?></option>
                <option <?php echo ( user_can( $user, "wpst_create" ) ) ? "selected" : ""; ?> value="wpst_create"><?php echo __("Create")?></option>
                <option <?php echo ( user_can( $user, "wpst_edit" ) ) ? "selected" : ""; ?> value="wpst_edit"><?php echo __("Edit")?></option>
            </select>
            <div style="clear:both;"></div>
            <span class="description"><?php echo __("Please choose sticky notes capabilities for this user") ?></span>
            </td>
        </tr>
    </table>
<?php 
} 

function wpst_update_db_check() {
    global $wpst_db_version;
    if ( get_site_option( 'wpst_db_version' ) != $wpst_db_version ) {
        __wp_sticker_plugin_install();
    }
}

function __wp_sticker_plugin_install() {
	global $wpdb;
	global $wpst_db_version;
	$main_table = "CREATE TABLE " . $wpdb->prefix . "sticker_notes (
	id INT NOT NULL AUTO_INCREMENT,
	  sticker_id varchar(200) NOT NULL,
	  url varchar(200) NOT NULL,
	  properties varchar(200) NOT NULL,
	  note text NOT NULL,
	  color varchar(100) NOT NULL,
	  cr_date varchar(200) NOT NULL,
	  author INT NOT NULL,
	  cookie_user_id varchar(100) NOT NULL,
	  target_users varchar(200) NOT NULL,
	  UNIQUE KEY id (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $main_table );
	update_option( "wpst_db_version", $wpst_db_version );
}
?>
