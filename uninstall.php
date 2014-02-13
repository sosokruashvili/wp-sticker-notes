<?
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

// delete custom tables
global $wpdb;
$wpdb->query("DROP TABLE wp_sticker_notes");
//$wpdb->query("DROP TABLE IF EXISTS wp_sticker_notes");
?>