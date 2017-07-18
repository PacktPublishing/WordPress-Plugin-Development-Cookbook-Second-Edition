<?php

/*
  Plugin Name: Chapter 5 - Post Source Link v2
  Plugin URI:
  Description: Companion to recipe 'Displaying custom post data via filter functions'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

// Register function to be called when meta boxes are being registered
// for each post editor
add_action( 'add_meta_boxes', 'ch5_psl_register_meta_box' );

// Create meta box to hold post source information
function ch5_psl_register_meta_box() {
	add_meta_box( 'ch5_psl_source_meta_box', 'Post/Page Source',
		'ch5_psl_source_meta_box', 'post', 'normal' );

	add_meta_box( 'ch5_psl_source_meta_box', 'Post/Page Source',
		'ch5_psl_source_meta_box', 'page', 'normal' );
}

// Display meta box contents
function ch5_psl_source_meta_box( $post ) { 
	// Retrieve current source name and address based on post ID
	$post_source_name = esc_html( get_post_meta( $post->ID, 'post_source_name', true ) );
	$post_source_address = esc_html( get_post_meta( $post->ID, 'post_source_address', true ) );
	?>

	<!-- Display fields to enter and edit source name and source address -->
	<table>
		<tr>
			<td style="width: 100px">Source Name</td>
			<td>
				<input type='text' size="40" name='post_source_name' value='<?php echo $post_source_name; ?>' />
			</td>
		</tr>
		<tr>
			<td>Source Address</td>
			<td>
				<input type='text' size="40" name='post_source_address' value='<?php echo $post_source_address; ?>' />
			</td>
		</tr>
	</table>
<?php }

// Register function to be called when post is being saved
add_action( 'save_post', 'ch5_psl_save_source_data', 10, 2 );

function ch5_psl_save_source_data( $post_id = false, $post = false ) {
	// Check post type for posts or pages
	if ( 'post' == $post->post_type || 'page' == $post->post_type ) {
		// Store data in post meta table if present in post data
		if ( isset( $_POST['post_source_name'] ) ) {
			update_post_meta( $post_id, 'post_source_name', sanitize_text_field( $_POST['post_source_name'] ) );
		}

		if ( isset( $_POST['post_source_address'] ) ) {
			update_post_meta( $post_id, 'post_source_address', sanitize_url( $_POST['post_source_address'] ) );
		}
	}
}

/************************************************************************
 Code from recipe 'Displaying custom post data via filter functions'
 ************************************************************************/

add_filter( 'the_content', 'ch5_psl_display_source_link' );

function ch5_psl_display_source_link ( $content ) {  
    $post_id = get_the_ID();
  
    if ( !empty( $post_id ) ) {
		if ( 'post' == get_post_type( $post_id ) ||
		     'page' == get_post_type( $post_id ) ) {
			
			// Retrieve current source name and address based on post ID 
			$post_source_name =  
				get_post_meta( $post_id, 'post_source_name', true ); 
			$post_source_address = 
				get_post_meta( $post_id, 'post_source_address', true ); 
			 
			// Output information to browser 
			if ( !empty( $post_source_name ) &&  
				 !empty( $post_source_address ) ) { 
				$content .= '<div class="source_link">Source: '; 
				$content .= '<a href="' . esc_url ( $post_source_address ); 
				$content .= '">' . esc_html( $post_source_name ) . '</a></div>';
			} 
		}
	}
	
	return $content;
} 