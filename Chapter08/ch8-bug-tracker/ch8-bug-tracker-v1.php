<?php
/*
  Plugin Name: Chapter 8 - Bug Tracker v1
  Plugin URI:
  Description: Companion to recipe 'Creating new database tables'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

// Register function to be called when plugin is activated
register_activation_hook( __FILE__, 'ch8bt_activation' );

// Activation Callback
function ch8bt_activation() {
	// Get access to global database access class
	global $wpdb;

	// Create table on main blog in network mode or single blog
	ch8bt_create_table( $wpdb->get_blog_prefix() );
}

// Function to create new database table
function ch8bt_create_table( $prefix ) {
	// Prepare SQL query to create database table
	// using received table prefix
	$creation_query =
		'CREATE TABLE IF NOT EXISTS ' . $prefix . 'ch8_bug_data (
			`bug_id` int(20) NOT NULL AUTO_INCREMENT,
			`bug_description` text,
			`bug_version` varchar(10) DEFAULT NULL,
			`bug_report_date` date DEFAULT NULL,
			`bug_status` int(3) NOT NULL DEFAULT 0,
			PRIMARY KEY (`bug_id`)
			);';

	global $wpdb;
	$wpdb->query( $creation_query );
}