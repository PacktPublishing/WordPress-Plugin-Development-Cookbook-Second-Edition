<?php

/*
  Plugin Name: Chapter 6 - Book Review User Submission v1
  Plugin URI:
  Description: Companion to recipe 'Creating a client-side content submission form'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

// Declare shortcode and specify function to be called when found
add_shortcode( 'submit-book-review', 'ch6_brus_book_review_form' );

// Function to replace shortcode with content when found
function ch6_brus_book_review_form() { 

	// make sure user is logged in
	if ( !is_user_logged_in() ) {
		echo '<p>You need to be a site member to be able to submit book reviews. Sign up to gain access!</p>';
		return;
	}
	?>

	<form method="post" id="add_book_review" action="">
		<!-- Nonce fields to verify visitor provenance -->
		<?php wp_nonce_field( 'add_review_form', 'br_user_form' ); ?>	
		
	    <!-- Post variable to indicate user-submitted items -->
		<input type="hidden" name="ch6_brus_user_book_review" value="1" />
		
		<table>
			<tr>
				<td>Book Title</td>
				<td><input type="text" name="book_title" /></td>
			</tr>
			<tr>
				<td>Book Author</td>
				<td><input type="text" name="book_author" /></td>
			</tr>
			<tr>
				<td>Review</td>
				<td><textarea name="book_review_text"></textarea></td>
			</tr>
			<tr>
				<td>Rating</td>
				<td>
					<select name="book_review_rating">
					<?php
					// Generate all rating items in drop-down list
					for ( $rating = 5; $rating >= 1; $rating-- ) { ?>
						<option value="<?php echo $rating; ?>"><?php echo $rating; ?> stars
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Book Type</td>
				<td>
					<?php 

					// Retrieve array of all book types in system
					$book_types = get_terms( 'book_reviews_book_type', array( 'orderby' => 'name', 'hide_empty' => 0 ) );

					// Check if book types were found
					if ( !is_wp_error( $book_types ) && !empty( $book_types ) ) {
						echo '<select name="book_review_book_type">';

						// Display all book types
						foreach ( $book_types as $book_type ) {				
							echo '<option value="' . $book_type->term_id . '">' . $book_type->name . '</option>';
						}		
						echo '</select>';
					} ?>
				</td>
			</tr>
		</table>

		<input type="submit" name="submit" value="Submit Review" />
	</form>

<?php }