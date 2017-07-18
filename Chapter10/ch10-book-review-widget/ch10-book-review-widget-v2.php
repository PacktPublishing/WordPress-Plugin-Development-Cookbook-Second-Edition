<?php
/*
  Plugin Name: Chapter 10 - Book Review Widget V2
  Plugin URI:
  Description: Companion to recipe 'Displaying configuration options'
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

	// Code to render options form
	function form( $instance ) {
		// Retrieve previous values from instance
		// or set default values if not present
		$render_widget = ( !empty( $instance['render_widget'] ) ?
						   $instance['render_widget'] : 'true' );
		$nb_book_reviews = ( !empty( $instance['nb_book_reviews'] ) ?
								$instance['nb_book_reviews'] : 5 );
		$widget_title = ( !empty( $instance['widget_title'] ) ? 
							esc_attr( $instance['widget_title'] ) :
							'Book Reviews' );
		?>

		<!-- Display fields to specify title and item count -->
		<p>
			<label for="<?php echo 
						$this->get_field_id( 'render_widget' ); ?>">
			<?php echo 'Display Widget'; ?>			
			<select id="<?php echo $this->get_field_id( 'render_widget' ); ?>"
					name="<?php echo $this->get_field_name( 'render_widget' ); ?>">
				<option value="true"
					<?php selected( $render_widget, 'true' ); ?>>
					Yes</option>
				<option value="false"
					<?php selected( $render_widget, 'false' ); ?>>
					No</option>
			</select>
			</label>
		</p>
		<p>
			<label for="<?php echo 
						$this->get_field_id( 'widget_title' ); ?>">
			<?php echo 'Widget Title:'; ?>			
			<input type="text" 
					id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
					name="<?php echo $this->get_field_name( 'widget_title' ); ?>"
					value="<?php echo $widget_title; ?>" />			
			</label>
		</p> 
		<p>
			<label for="<?php echo 
						$this->get_field_id( 'nb_book_reviews' ); ?>">
			<?php echo 'Number of reviews to display:'; ?>			
			<input type="text" 
					id="<?php echo $this->get_field_id( 'nb_book_reviews' ); ?>"
					name="<?php echo $this->get_field_name( 'nb_book_reviews' ); ?>"
					value="<?php echo $nb_book_reviews; ?>" />			
			</label>
		</p>

		<?php wp_reset_query();
	}
}