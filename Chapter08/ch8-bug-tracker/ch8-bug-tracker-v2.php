<?php
/*
  Plugin Name: Chapter 8 - Bug Tracker v2
  Plugin URI:
  Description: Companion to recipe 'Updating custom table structure on plugin upgrade'
  Author: ylefebvre
  Version: 2.0
  Author URI: http://ylefebvre.ca/
 */

// Register function to be called when plugin is activated
register_activation_hook( __FILE__, 'ch8bt_activation' );

// Activation Callback
function ch8bt_activation() {
	// Get access to global database access class
	global $wpdb;

	// Check to see if WordPress installation is a network
	if ( is_multisite() ) {		
		// If it is, cycle through all blogs, switch to them
		// and call function to create plugin table
		if ( !empty( $_GET['networkwide'] ) ) {
			$start_blog = $wpdb->blogid;

			$blog_list = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
			foreach ( $blog_list as $blog ) {
				switch_to_blog( $blog );

				// Send blog table prefix to table creation function
				ch8bt_create_table( $wpdb->get_blog_prefix() );
			}
			switch_to_blog( $start_blog );
			return;
		}	
	}

	// Create table on main blog in network mode or single blog
	ch8bt_create_table( $wpdb->get_blog_prefix() );
}

// Register function to be called when new blogs are added
// to a network site
add_action( 'wpmu_new_blog', 'ch8bt_new_network_site' );

function ch8bt_new_network_site( $blog_id ) {
	global $wpdb;

	// Check if this plugin is active when new blog is created
	// Include plugin functions if it is    
	if ( !function_exists( 'is_plugin_active_for_network' ) ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	// Select current blog, create new table and switch back to
	// main blog
	if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
		$start_blog = $wpdb->blogid;
		switch_to_blog( $blog_id );

		// Send blog table prefix to table creation function
		ch8bt_create_table( $wpdb->get_blog_prefix() );

		switch_to_blog( $start_blog );
	}
}

// Function to create new database table
function ch8bt_create_table( $prefix ) {
	// Prepare SQL query to create database table
	// using received table prefix
	$creation_query =
		'CREATE TABLE ' . $prefix . 'ch8_bug_data (
			`bug_id` int(20) NOT NULL AUTO_INCREMENT,
			`bug_description` text,
			`bug_version` varchar(10) DEFAULT NULL,
			`bug_report_date` date DEFAULT NULL,
			`bug_status` int(3) NOT NULL DEFAULT 0,
			`bug_title` VARCHAR( 128 ) NULL,
			PRIMARY KEY (`bug_id`)
			);';

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $creation_query );
}