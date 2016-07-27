<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

if ( current_user_can( 'delete_plugins' ) ) {

	// delete options
	global $wpdb;
	$table_name = $wpdb->prefix . "tlc_streamer_list";
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
	delete_option("tlc_nb_stream");
	delete_option("tlc_show_offline");
	delete_option("tlc_page_allstream");
	delete_option("tlc_redirection_stream");
	 // delete_option("tlc_streamlist_settings");
	  
	   
	 
	

}