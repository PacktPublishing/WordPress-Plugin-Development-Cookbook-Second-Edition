<?php
/*
  Plugin Name: Chapter 3 - Hide Menu Item
  Plugin URI:
  Description: Companion to recipe 'Hiding items which users should not access from the default menu'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

// Register function to be called when admin menu is constructed
add_action( 'admin_menu', 'ch3hmi_hide_menu_item' );

// Implement function to hide comments menu item and permalink menu item
function ch3hmi_hide_menu_item() {
	remove_menu_page( 'edit-comments.php' );

	remove_submenu_page( 'options-general.php', 'options-permalink.php' );
}