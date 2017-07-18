<?php

/*
  Plugin Name: Chapter 4 - Book Reviews V6
  Plugin URI: 
  Description: Companion to recipe 'Adding custom fields to categories'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

/****************************************************************************
 * Code from recipe 'Creating a custom post type'
 ****************************************************************************/

add_action( 'init', 'ch4_br_create_book_post_type' );

function ch4_br_create_book_post_type() {
	register_post_type( 'book_reviews',
		array(
				'labels' => array(
				'name' => 'Book Reviews',
				'singular_name' => 'Book Review',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Book Review',
				'edit' => 'Edit',
				'edit_item' => 'Edit Book Review',
				'new_item' => 'New Book Review',
				'view' => 'View',
				'view_item' => 'View Book Review',
				'search_items' => 'Search Book Reviews',
				'not_found' => 'No Book Reviews found',
				'not_found_in_trash' => 'No Book Reviews found in Trash',
				'parent' => 'Parent Book Review'
			),
		'public' => true,
		'menu_position' => 20,
		'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),
		'taxonomies' => array( '' ),
		'menu_icon' => plugins_url( 'book-reviews.png', __FILE__ ),
		'has_archive' => false,
		'exclude_from_search' => true
		)
	);

	/* Code from recipe 'Adding custom taxonomies for custom post types */    
	register_taxonomy(
		'book_reviews_book_type',
		'book_reviews',
		array(
			'labels' => array(
				'name' => 'Book Type',
				'add_new_item' => 'Add New Book Type',
				'new_item_name' => "New Book Type Name"
			),
			'show_ui' => true,
			'show_tagcloud' => false,
			'hierarchical' => true
		)
	);
}

/****************************************************************************
 * Code from recipe 'Adding a new section to the custom post type editor'
 ****************************************************************************/

// Register function to be called when admin interface is visited
add_action( 'admin_init', 'ch4_br_admin_init' );

// Function to register new meta box for book review post editor
function ch4_br_admin_init() {
	add_meta_box( 'ch4_br_review_details_meta_box', 'Book Review Details', 'ch4_br_display_review_details_meta_box', 'book_reviews', 'normal', 'high' );
}

// Function to display meta box contents
function ch4_br_display_review_details_meta_box( $book_review ) { 
	// Retrieve current author and rating based on book review ID
	$book_author = esc_html( get_post_meta( $book_review->ID, 'book_author', true ) );
	$book_rating = intval( get_post_meta( $book_review->ID, 'book_rating', true ) );
	?>
	<table>
		<tr>
			<td style="width: 150px">Book Author</td>
			<td><input type='text' size='80' name='book_review_author_name' value='<?php echo $book_author; ?>' /></td>
		</tr>
		<tr>
			<td style="width: 150px">Book Rating</td>
			<td>
				<select style="width: 100px" name="book_review_rating">
					<!-- Loop to generate all items in dropdown list -->
					<?php for ( $rating = 5; $rating >= 1; $rating -- ) { ?>
					<option value="<?php echo $rating; ?>" <?php echo selected( $rating, $book_rating ); ?>><?php echo $rating; ?> stars
					<?php } ?>
				</select>
			</td>
		</tr>
	</table>

<?php }

// Register function to be called when posts are saved
// The function will receive 2 arguments
add_action( 'save_post', 'ch4_br_add_book_review_fields', 10, 2 );

function ch4_br_add_book_review_fields( $post_id = false, $post = false ) {
	// Check post type for book reviews
	if ( 'book_reviews' == $post->post_type ) {
		// Store data in post meta table if present in post data
		if ( isset( $_POST['book_review_author_name'] ) ) {
			update_post_meta( $post_id, 'book_author', sanitize_text_field( $_POST['book_review_author_name'] ) );
		}
		
		if ( isset( $_POST['book_review_rating'] ) && !empty( $_POST['book_review_rating'] ) ) {
			update_post_meta( $post_id, 'book_rating', intval( $_POST['book_review_rating'] ) );
		}
	}
}

/************************************************************************************
 * Code from recipe 'Displaying single custom post type items using a custom layout'
 ************************************************************************************/

add_filter( 'template_include', 'ch4_br_template_include', 1 );

function ch4_br_template_include( $template_path ){
	
	if ( 'book_reviews' == get_post_type() ) {
		if ( is_single() ) {
			// checks if the file exists in the theme first,
			// otherwise install content filter
			if ( $theme_file = locate_template( array( 'single-book_reviews.php' ) ) ) {
				$template_path = $theme_file;
			} else {
				add_filter( 'the_content', 'ch4_br_display_single_book_review', 20 );
			}
		}
	}	
	
	return $template_path;
}

function ch4_br_display_single_book_review( $content ) {
    if ( !empty( get_the_ID() ) ) {
        // Display featured image in right-aligned floating div
        $content .= '<div style="float: right; margin: 10px">';
        $content .= get_the_post_thumbnail( get_the_ID(), 'medium' );
        $content .= '</div>';

        // Display Title and Author Name
        $content .= '<strong>Title: </strong>' . get_the_title( get_the_ID() );
        $content .= '<br /><strong>Author: </strong>';
        $content .= esc_html( get_post_meta( get_the_ID(), 'book_author', true ) );
        $content .= '<br />';

        // Display yellow stars based on rating -->
        $content .= '<strong>Rating: </strong>';

        $nb_stars = intval( get_post_meta( get_the_ID(), 'book_rating', true ) );

        for ( $star_counter = 1; $star_counter <= 5; $star_counter++ ) {
            if ( $star_counter <= $nb_stars ) {
                $content .= '<img src="' . plugins_url( 'star-icon.png', __FILE__ ) . '" />';
            } else {
                $content .= '<img src="' .
                    plugins_url( 'star-icon-grey.png', __FILE__ ) . '" />';
            }
        }
		
		$book_types = wp_get_post_terms( get_the_ID(), 
                'book_reviews_book_type' ); 
 
		$content .= '<br /><strong>Type: </strong>';

		if ( $book_types ) { 
			$first_entry = true; 
			for ( $i = 0; $i < count( $book_types ); $i++ ) { 
				if ( !$first_entry ) {
					$content .= ', ';
				}
				$content .= $book_types[$i]->name; 
				$first_entry = false; 
			} 
		} else {
			$content .= 'None Assigned';
		}
		$content .= '<br />'; 

        // Display book review contents
        $content .= '<div class="entry-content">' . get_the_content( get_the_ID() ) . '</div>';

        return $content;
     }
} 

/****************************************************************************
 * Code from recipe 'Displaying custom post type data in shortcodes'
 ****************************************************************************/

add_shortcode( 'book-review-list', 'ch4_br_book_review_list' );

// Implementation of short code function
function ch4_br_book_review_list() {
	// Preparation of query array to retrieve 5 book reviews
	$query_params = array( 'post_type' => 'book_reviews',
                           'post_status' => 'publish',
                           'posts_per_page' => 5 );
	
	// Retrieve page query variable, if present
	$page_num = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

	// If page number is higher than 1, add to query array
	if ( $page_num != 1 ) {
		$query_params['paged'] = $page_num;
	}

	// Execution of post query
	$book_review_query = new WP_Query;
    $book_review_query->query( $query_params );
	
	// Check if any posts were returned by query
	if ( $book_review_query->have_posts() ) {
		// Display posts in table layout
		$output = '<table>';
		$output .= '<tr><th style="width: 350px"><strong>Title</strong></th>';
		$output .= '<th><strong>Author</strong></th></tr>';

		// Cycle through all items retrieved
		while ( $book_review_query->have_posts() ) {
			$book_review_query->the_post();
			$output .= '<tr><td><a href="' . get_permalink() . '">';
			$output .= get_the_title( get_the_ID() ) . '</a></td>';
			$output .= '<td>' . esc_html( get_post_meta( get_the_ID(), 'book_author', true ) );
			$output .= '</td></tr>';
		}

		$output .= '</table>';

		// Display page navigation links
		if ( $book_review_query->max_num_pages > 1 ) {
			$output .= '<nav id="nav-below">';
			$output .= '<div class="nav-previous">';
			$output .= get_next_posts_link( '<span class="meta-nav">&larr;</span> Older reviews', $book_review_query->max_num_pages );
			$output .= '</div>';
			$output .= "<div class='nav-next'>";
			$output .= get_previous_posts_link( 'Newer reviews <span class="meta-nav">&rarr;</span>', $book_review_query->max_num_pages );
			$output .= '</div>';
			$output .= '</nav>';
		}

		// Reset post data query
		wp_reset_postdata();
	}

	return $output;
}

/****************************************************************************
 * Code from recipe 'Adding custom fields to categories'
 ****************************************************************************/

add_action( 'book_reviews_book_type_edit_form_fields', 'ch4_br_book_type_new_fields', 10, 2 );
add_action( 'book_reviews_book_type_add_form_fields', 'ch4_br_book_type_new_fields', 10, 2 );

function ch4_br_book_type_new_fields( $tag ) {
	$mode = 'new';
	
	if ( is_object( $tag ) ) {
		$mode = 'edit';
		$book_cat_color = get_term_meta( $tag->term_id, 'book_type_color', true );
	}
	$book_cat_color = empty( $book_cat_color ) ? '#' : $book_cat_color;

	if ( 'edit' == $mode ) {
		echo '<tr class="form-field">';
		echo '<th scope="row" valign="top">';
	} elseif ( 'new' == $mode ) {
		echo '<div class="form-field">';
	} ?>

	<label for="tag-category-url">Color</label>
	<?php if ( 'edit' == $mode ) {
		echo '</th><td>';
	} ?>

	<input type="text" id="book_type_color" name="book_type_color" value="<?php echo $book_cat_color; ?>" />
	<p class="description">Color associated with book type (e.g. #199C27 or #CCC)</p>

	<?php if ( 'edit' == $mode ) {
		echo '</td></tr>';
	} elseif ( 'new' == $mode ) {
		echo '</div>';
	}
}

add_action( 'edited_book_reviews_book_type', 'ch4_br_save_book_type_new_fields', 10, 2 );
add_action( 'created_book_reviews_book_type', 'ch4_br_save_book_type_new_fields', 10, 2 );

function ch4_br_save_book_type_new_fields( $term_id, $tt_id ) {
	if ( !$term_id ) {
		return;
	}

	if ( isset( $_POST['book_type_color'] ) && ( '#' == $_POST['book_type_color'] || preg_match( '/#([a-f0-9]{3}){1,2}\b/i', $_POST['book_type_color'] ) ) ) {
		$returnvalue = update_term_meta( $term_id, 'book_type_color', $_POST['book_type_color'] );
	}
}
