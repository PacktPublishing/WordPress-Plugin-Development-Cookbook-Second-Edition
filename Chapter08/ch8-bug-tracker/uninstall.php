<?php

// Check that file was called from WordPress admin
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Get access to global WordPress wpdb class
global $wpdb;

// Check if site is configured for network installation
if ( is_multisite() ) {
	if ( !empty( $_GET['networkwide'] ) ) {
		$start_blog = $wpdb->blogid;

		// Get blog list and cycle through all blogs under network
		$blog_list = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
		foreach ( $blog_list as $blog ) {
			switch_to_blog( $blog );

			// Call function to delete bug table with prefix
			ch8bt_drop_table( $wpdb->get_blog_prefix() );
		}
		switch_to_blog( $start_blog );
		return;
	}	
}

ch8bt_drop_table( $wpdb->prefix );

function ch8bt_drop_table( $prefix ) {
	global $wpdb;
	$wpdb->query( 'DROP TABLE ' . $prefix . 'ch8_bug_data' );
}
