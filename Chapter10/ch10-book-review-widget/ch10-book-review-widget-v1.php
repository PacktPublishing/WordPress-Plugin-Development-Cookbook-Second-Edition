<?php
/*
  Plugin Name: Chapter 10 - Book Review Widget V1
  Plugin URI:
  Description: Companion to recipe 'Creating a new widget in WordPress'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

// Register function to be called when widget initialization occurs
add_action( 'widgets_init', 'ch10brw_create_widgets' );

// Create new widget
function ch10brw_create_widgets() {
	register_widget( 'Book_Reviews' );
}

// Widget implementation class
class Book_Reviews extends WP_Widget {
	// Constructor function
	function __construct() {
		// Widget creation function
		parent::__construct( 'book_reviews',
							 'Book Reviews',
							 array( 'description' =>
									'Displays list of recent book reviews' ) );
	}
}