<?php
/*
  Plugin Name: Chapter 3 - Multi-Level Menu V2
  Plugin URI:
  Description: Companion to recipe 'Adding menu items leading to external pages'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

add_action( 'admin_menu', 'ch3mlm_admin_menu' );

function ch3mlm_admin_menu() {
	// Create top-level menu item
	add_menu_page( 'My Complex Plugin Configuration Page',
		'My Complex Plugin', 'manage_options',
		'ch3mlm-main-menu', 'ch3mlm_my_complex_main',
		plugins_url( 'myplugin.png', __FILE__ ) );

	// Create a sub-menu under the top-level menu
	add_submenu_page( 'ch3mlm-main-menu',
		'My Complex Menu Sub-Config Page', 'Sub-Config Page',
		'manage_options', 'ch3mlm-sub-menu',
		'ch3mlm_my_complex_submenu' );
		
	global $submenu;
	$url = 'https://www.packtpub.com/books/info/packt/faq';
	$submenu['ch3mlm-main-menu'][] = array( 'FAQ', 'manage_options', $url );
}