<?php
/*
Plugin Name: WP Sticker Notes
Plugin URI: http://webintelligence.de/
Description: Add sticker note for any page to any position 
Version: 0.7
Author: webintelligence.de
Author URI: http://webintelligence.de/
Update Server: http://webintelligence.de/
Min WP Version: 3.5
Max WP Version: 3.5.+
*/

register_activation_hook(__FILE__, '__wp_sticker_plugin_install');
add_action( 'admin_menu', '__wp_sticker_menu' );
add_action( 'wp_enqueue_scripts', 'wpst_load_front_files' );

// Plugin information vars
$WPST_PLUGIN['name'] = "WP Sticker Notes";
$WPST_PLUGIN['folder'] = basename(dirname(__FILE__));

function __wp_sticker_menu() {
	global $WPST_PLUGIN;
	add_menu_page( 'WP Sticker Notes', 'WP Sticker Notes', 'edit_pages', 'wpst_main', '__wp_sticker_get_page', plugins_url()."/".$WPST_PLUGIN['folder']."/scripts/images/Notes-icon.png" );
}

function __wp_sticker_get_page()
{
	global $WPSticker;
	global $WPST_PLUGIN;
	
	wp_enqueue_style( 'wpst-main-style', plugins_url()."/".$WPST_PLUGIN['folder']."/style.css" );
	require_once(__DIR__."/pages/admin_main.php");
}

function wpst_load_front_files()
{
	global $WPST_PLUGIN;
	wp_enqueue_style( 'wpst-main-style', plugins_url()."/".$WPST_PLUGIN['folder']."/scripts/wpst_style.css", false, "1.0.0" );
	wp_enqueue_style( 'wpst-fontello', plugins_url()."/".$WPST_PLUGIN['folder']."/scripts/fontello/css/fontello.css", false, "1.0.0" );
	wp_enqueue_style( 'jQueryUI', plugins_url()."/".$WPST_PLUGIN['folder']."/scripts/jqueryUI/css/ui-lightness/jquery-ui-1.10.4.custom.min.css", false, "1.0.0" );
	wp_enqueue_script( 'jquery', plugins_url()."/".$WPST_PLUGIN['folder']."/scripts/jquery-1.11.0.min.js", false, "1.0.0" );
	wp_enqueue_script( 'wpst-main-script', plugins_url()."/".$WPST_PLUGIN['folder']."/scripts/wpst_script.js", array("jquery"), "1.0.0", true );
	wp_enqueue_script( 'jQueryUI', plugins_url()."/".$WPST_PLUGIN['folder']."/scripts/jqueryUI/js/jquery-ui-1.10.4.custom.js", array("jquery"), "1.0.0", true );
	
	// Send data to client
	wpst_send_client_data();
}

function wpst_send_client_data()
{
	global $WPST_PLUGIN;
	wp_localize_script('wpst-main-script', 'wpst_data', array(
	  'userid' => get_current_user_id(),
	  'home_url' => home_url(),
	  'plugin_dir' => plugins_url()."/".$WPST_PLUGIN['folder'],
	  'stickers' => get_stickers_json()
	));
}

function get_stickers_json()
{
	global $wpdb;
	$current_url = "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$results = $wpdb->get_results("SELECT * FROM wp_sticker_notes WHERE url = '{$current_url}'");
	return json_encode( $results );
}

add_action( 'wp_ajax_nopriv_wpst_save_sticker', 'wpst_save_sticker' );
add_action( 'wp_ajax_wpst_save_sticker', 'wpst_save_sticker' );
function wpst_save_sticker()
{
	global $wpdb;
	$sticker_id = $_POST["sticker_id"];
	$sticker_props = stripslashes($_POST['properties']);
	$sticker_url = $_POST['url'];
	$sticker_note = $_POST['note'];
	if($sticker_id)
	{
		if( !$wpdb->get_results("SELECT sticker_id FROM  wp_sticker_notes WHERE sticker_id = '{$sticker_id}'") )
			$res = $wpdb->query("INSERT INTO wp_sticker_notes (sticker_id, url, properties, note, cr_date) VALUES ('{$sticker_id}', '{$sticker_url}', '{$sticker_props}', '{$sticker_note}', '".date("Y-m-d")."')");
		else
			$res1 = $wpdb->query("UPDATE wp_sticker_notes SET sticker_id = '{$sticker_id}', url = '{$sticker_url}', properties = '{$sticker_props}', note = '{$sticker_note}' WHERE  sticker_id = '{$sticker_id}'");	
	}
	
	echo "OK";
	exit();
}

add_action( 'wp_ajax_nopriv_wpst_delete_sticker', 'wpst_delete_sticker' );
add_action( 'wp_ajax_wpst_delete_sticker', 'wpst_delete_sticker' );
function wpst_delete_sticker()
{
	global $wpdb;
	$sticker_id = $_POST["sticker_id"];
	if($sticker_id)
		$res2 = $wpdb->query( "DELETE FROM wp_sticker_notes WHERE  sticker_id = '{$sticker_id}'" );
	if($res2)
		echo "OK";
	exit();
}

function __wp_sticker_plugin_install() 
{
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$main_table = "CREATE TABLE wp_sticker_notes (
	id INT NOT NULL AUTO_INCREMENT,
	  sticker_id varchar(200) NOT NULL,
	  url varchar(200) NOT NULL,
	  properties varchar(200) NOT NULL,
	  note text NOT NULL,
	  color varchar(100) NOT NULL,
	  cr_date varchar(200) NOT NULL,
	  UNIQUE KEY id (id)
	);";
	$settings_table = "CREATE TABLE wp_sticker_notes_settings (
	id INT NOT NULL AUTO_INCREMENT,
	  parameter varchar(200) NOT NULL,
	  value varchar(200) NOT NULL,
	  UNIQUE KEY id (id)
	);";
	dbDelta($main_table);
	dbDelta($settings_table);
}