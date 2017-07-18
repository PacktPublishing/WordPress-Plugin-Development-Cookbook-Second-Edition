<?php
/*
  Plugin Name: Chapter 3 - Hide Menu Item Separate Admin
  Plugin URI:
  Description: Companion to recipe 'Splitting admin code from the main plugin file to optimize site performance'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

// Check if user is visiting administration pages and load external code file if that is the case
if ( is_admin() ) {
	include plugin_dir_path( __FILE__ ) . 'ch3-hide-menu-item-admin-functions.php';
}