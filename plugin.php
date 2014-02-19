<?php
/*
Plugin Name: WP Sticker Notes
Plugin URI: http://sticker-notes.com/
Description: Add sticker note for any page to any position 
Version: 1.0.0
Author: Kruashvili
Author URI: http://sticker-notes.com/
License: GPL2
*/

/*  
Copyright 2014  SosoKruashvili  (email : yruashvili@gmail.com)

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

register_activation_hook( __FILE__, '__wp_sticker_plugin_install' );

add_action( 'admin_menu', '__wp_sticker_menu' );
add_action( 'wp_enqueue_scripts', 'wpst_load_front_files' );

// Plugin information vars
$WPST_PLUGIN['name'] = "WP Sticker Notes";
$WPST_PLUGIN['folder'] = basename( dirname( __FILE__ ) );

// Register and update plugin options to wordpress options mechanism 
if( $_POST['group'] ) {
	add_option( "wpst_allow_user_groups", $_POST['group'] );
	update_option( "wpst_allow_user_groups", $_POST['group'] );
}

function __wp_sticker_menu() {
	global $WPST_PLUGIN;
	add_menu_page( 'WP Sticker Notes', 'WP Sticker Notes', 'edit_pages', 'wpst_main', '__wp_sticker_get_page', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/images/Notes-icon.png" );
}

function __wp_sticker_get_page() {
	global $WPSticker;
	global $WPST_PLUGIN;
	
	wp_enqueue_style( 'wpst-main-style', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/admin-style.css" );
	require_once( __DIR__ . "/pages/admin_main.php" );
}

function wpst_load_front_files() {
	// Check user permissions
	if( ! wpst_check_permissions() ) return;
	global $WPST_PLUGIN;
	
	wp_enqueue_style( 'wpst-main-style', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/wpst_style.css", false, "1.0.3" );	
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-draggable', "", array("jquery"), "", true );
	wp_enqueue_script( 'jquery-ui-resizable', "", array("jquery"), "", true );
	wp_enqueue_script( 'wpst-main-script', plugins_url() . "/" . $WPST_PLUGIN['folder'] . "/scripts/wpst_script.js", array("jquery"), "1.0.3", true );
	
	// Send data to client
	wpst_send_client_data();
}

function wpst_send_client_data() {
	global $WPST_PLUGIN;
	// Send CDATA for JS
	wp_localize_script( 'wpst-main-script', 'wpst_data', array(
						'userid' => get_current_user_id(),
						'home_url' => home_url(),
						'plugin_dir' => plugins_url() . "/" . $WPST_PLUGIN['folder'],
						'stickers' => get_stickers_json()
					  ));
}

function get_stickers_json() {
	global $wpdb;
	$current_url = "http" . (( $_SERVER['SERVER_PORT'] == 443 ) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$results = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix ."sticker_notes WHERE url = '{$current_url}'" );
	return json_encode( $results );
}

function wpst_check_permissions() {
	// Get Current User Data Object
	$curent_user_data = get_userdata( get_current_user_id() );
	$current_user_groups = $curent_user_data->roles;
	// Get allows groups to use this plugin front
	$allowed_groups = get_option( "wpst_allow_user_groups" );
	// Return true if user is administrator
	if( @in_array( "administrator", $current_user_groups ) ) {
		return true;
	}
	
	if( ! $current_user_groups ) return false;
	
	foreach( @$current_user_groups as $group ) { 
		if( in_array( $group, $allowed_groups ) )
			return true;
	}
	return false;
}


add_action( 'wp_ajax_nopriv_wpst_save_sticker', 'wpst_save_sticker' );
add_action( 'wp_ajax_wpst_save_sticker', 'wpst_save_sticker' );
function wpst_save_sticker() {
	global $wpdb;
	$sticker_id = $_POST["sticker_id"];
	$sticker_props = stripslashes($_POST['properties']);
	$sticker_url = $_POST['url'];
	$sticker_note = $_POST['note'];
	if( $sticker_id ) {
		if( ! $wpdb->get_results( "SELECT sticker_id FROM  " . $wpdb->prefix ."sticker_notes WHERE sticker_id = '{$sticker_id}'" ) )
			$res = $wpdb->query( "INSERT INTO " . $wpdb->prefix ."sticker_notes (sticker_id, url, properties, note, cr_date) VALUES ('{$sticker_id}', '{$sticker_url}', '{$sticker_props}', '{$sticker_note}', '".date("Y-m-d")."')" );
		else
			$res1 = $wpdb->query( "UPDATE " . $wpdb->prefix ."sticker_notes SET sticker_id = '{$sticker_id}', url = '{$sticker_url}', properties = '{$sticker_props}', note = '{$sticker_note}' WHERE  sticker_id = '{$sticker_id}'" );	
	}
	echo "OK";
	exit();
}

add_action( 'wp_ajax_nopriv_wpst_delete_sticker', 'wpst_delete_sticker' );
add_action( 'wp_ajax_wpst_delete_sticker', 'wpst_delete_sticker' );
function wpst_delete_sticker() {
	global $wpdb;
	$sticker_id = $_POST["sticker_id"];
	if( $sticker_id )
		$res2 = $wpdb->query( "DELETE FROM " . $wpdb->prefix ."sticker_notes WHERE  sticker_id = '{$sticker_id}'" );
	if( $res2 )
		echo "OK";
	exit(); 
}

function __wp_sticker_plugin_install() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$main_table = "CREATE TABLE " . $wpdb->prefix . "sticker_notes (
	id INT NOT NULL AUTO_INCREMENT,
	  sticker_id varchar(200) NOT NULL,
	  url varchar(200) NOT NULL,
	  properties varchar(200) NOT NULL,
	  note text NOT NULL,
	  color varchar(100) NOT NULL,
	  cr_date varchar(200) NOT NULL,
	  UNIQUE KEY id (id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	dbDelta( $main_table );
}
?>