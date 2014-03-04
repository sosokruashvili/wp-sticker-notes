<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

delete_option( "wpst_allow_unauthorized" );

// delete custom tables
global $wpdb;
$wpdb->query("DROP TABLE wp_sticker_notes");
?>