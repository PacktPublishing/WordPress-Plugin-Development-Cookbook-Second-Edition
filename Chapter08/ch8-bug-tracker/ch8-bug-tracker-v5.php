<?php
/*
  Plugin Name: Chapter 8 - Bug Tracker v5
  Plugin URI:
  Description: Companion to recipe 'Deleting records from custom tables'
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

// Register function to be called when admin menu is constructed
add_action( 'admin_menu', 'ch8bt_settings_menu' );

// Add new menu item under Settings menu for Bug Tracker
function ch8bt_settings_menu() {
	add_options_page( 'Bug Tracker Data Management',
		'Bug Tracker', 'manage_options',
		'ch8bt-bug-tracker',
		'ch8bt_config_page' );
}

// Function to render plugin admin page
function ch8bt_config_page() {
	global $wpdb;
	?>
	<!-- Top-level menu -->
	<div id="ch8bt-general" class="wrap">
	<h2>Bug Tracker
		<a class="add-new-h2" 
			href="<?php echo add_query_arg( array ( 'page' => 'ch8bt-bug-tracker', 'id' => 'new' ), admin_url( 'options-general.php' ) ); ?>">Add New Bug</a></h2>
		
	<!-- Display bug list if no parameter sent in URL -->
	<?php if ( empty( $_GET['id'] ) ) { 
		$bug_query = 'select * from ' . $wpdb->get_blog_prefix();
		$bug_query .= 'ch8_bug_data ORDER by bug_report_date DESC';
		$bug_items = $wpdb->get_results( $bug_query, ARRAY_A );
	?>

	<h3>Manage Bug Entries</h3>
	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
	<input type="hidden" name="action" value="delete_ch8bt_bug" />

	<!-- Adding security through hidden referrer field -->
	<?php wp_nonce_field( 'ch8bt_deletion' ); ?>
	
	<table class="wp-list-table widefat fixed" >
	<thead><tr><th style="width: 50px"></th><th style="width: 80px">ID</th>
	<th style=width: 300px>Title</th><th>Version</th></tr></thead>

	<?php 
		// Display bugs if query returned results
		if ( $bug_items ) {
			foreach ( $bug_items as $bug_item ) {
				echo '<tr style="background: #FFF">';
				echo '<td><input type="checkbox" name="bugs[]" value="';
				echo esc_attr( $bug_item['bug_id'] ) . '" /></td>';
				echo '<td>' . $bug_item['bug_id'] . '</td>';
				echo '<td><a href="' . add_query_arg( array( 'page' => 'ch8bt-bug-tracker', 'id' => $bug_item['bug_id'] ), admin_url( 'options-general.php' ) );
				echo '">' . $bug_item['bug_title'] . '</a></td>';
				echo '<td>' . $bug_item['bug_version'] . '</td></tr>';
			}
		} else {
			echo '<tr style="background: #FFF">';
			echo '<td colspan="4">No Bug Found</td></tr>';
		}
	?>
	</table><br />
	
	<input type="submit" value="Delete Selected" class="button-primary"/>
	</form>

	<?php } elseif ( isset( $_GET['id'] ) && ( 'new' == $_GET['id'] || is_numeric( $_GET['id'] ) ) ) {

	// Display bug creation and editing form if bug is new
	// or numeric id was sen       
	$bug_id = intval( $_GET['id'] );
	$mode = 'new';

	// Query database if numeric id is present
	if ( $bug_id > 0 ) {
		$bug_query = 'select * from ' . $wpdb->get_blog_prefix();
		$bug_query .= 'ch8_bug_data where bug_id = %d';

		$bug_data = $wpdb->get_row( $wpdb->prepare( $bug_query, $bug_id ), ARRAY_A );

		if ( $bug_data ) {
			$mode = 'edit';
		}
	}
	
	if ( 'new' == $mode ) {
        $bug_data = array(
            'bug_title' => '',
            'bug_description' => '',
            'bug_version' => '',
            'bug_status' => ''
        ); 
    }

	// Display title based on current mode
	if ( 'new' == $mode ) {
		echo '<h3>Add New Bug</h3>';
	} elseif ( 'edit' == $mode ) {
		echo '<h3>Edit Bug #' . $bug_data['bug_id'] . ' - ';
		echo $bug_data['bug_title'] . '</h3>';
	}
	?>

	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
	<input type="hidden" name="action" value="save_ch8bt_bug" />
	<input type="hidden" name="bug_id" value="<?php echo $bug_id; ?>" />

	<!-- Adding security through hidden referrer field -->
	<?php wp_nonce_field( 'ch8bt_add_edit' ); ?>

	<!-- Display bug editing form, with previous values if available -->
	<table>
		<tr>
			<td style="width: 150px">Title</td>
			<td><input type="text" name="bug_title" size="60" value="<?php echo esc_html( $bug_data['bug_title'] ); ?>"/></td>
		</tr>
		<tr>
			<td>Description</td>
			<td><textarea name="bug_description" cols="60"><?php echo esc_textarea( $bug_data['bug_description'] ); ?></textarea></td>
		</tr>
		<tr>
			<td>Version</td>
			<td><input type="text" name="bug_version" value="<?php echo esc_html( $bug_data['bug_version'] ); ?>" /></td>
		</tr>
		<tr>
			<td>Status</td>
			<td>
				<select name="bug_status">
				<?php
					$bug_statuses = array( 0 => 'Open', 1 => 'Closed', 2 => 'Not-a-Bug' );
					foreach ( $bug_statuses as $status_id => $status ) {
						echo '<option value="' . $status_id . '" ';
						selected( $bug_data['bug_status'], $status_id );
						echo '>' . $status;
					}
				?>
				</select>
			</td>
		</tr>
	</table>
	<input type="submit" value="Submit" class="button-primary"/>
	</form>

	<?php } ?>
	</div>
<?php }

// Register function to be called when administration pages init takes place
add_action( 'admin_init', 'ch8bt_admin_init' );

// Register functions to be called when bugs are saved
function ch8bt_admin_init() {
	add_action('admin_post_save_ch8bt_bug',
		'process_ch8bt_bug');

	add_action('admin_post_delete_ch8bt_bug',
		'delete_ch8bt_bug');
}

// Function to be called when new bugs are created or existing bugs
// are saved
function process_ch8bt_bug() {
	// Check if user has proper security level
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed' );
	}

	// Check if nonce field is present for security
	check_admin_referer( 'ch8bt_add_edit' );
	global $wpdb;

	// Place all user submitted values in an array
	$bug_data = array();
	$bug_data['bug_title'] = ( isset( $_POST['bug_title'] ) ? sanitize_text_field( $_POST['bug_title'] ) : '' );
	$bug_data['bug_description'] = ( isset( $_POST['bug_description'] ) ? sanitize_text_field( $_POST['bug_description'] ) : '' );
	$bug_data['bug_version'] = ( isset( $_POST['bug_version'] ) ? sanitize_text_field( $_POST['bug_version'] ) : '' );

	// Set bug report date as current date
	$bug_data['bug_report_date'] = date( 'Y-m-d' );

	// Set status of all new bugs to 0 (Open)
	$bug_data['bug_status'] = ( isset( $_POST['bug_status'] ) ? intval( $_POST['bug_status'] ) : 0 );

	// Call the wpdb insert or update method based on value
	// of hidden bug_id field
	if ( isset( $_POST['bug_id'] ) && 0 == $_POST['bug_id'] ) {
		$wpdb->insert($wpdb->get_blog_prefix() . 'ch8_bug_data', $bug_data );
	} elseif ( isset( $_POST['bug_id'] ) && $_POST['bug_id'] > 0 ) {
		$wpdb->update( $wpdb->get_blog_prefix() . 'ch8_bug_data', $bug_data, array( 'bug_id' => $_POST['bug_id'] ) );
	}

	// Redirect the page to the admin form
	wp_redirect( add_query_arg( 'page', 'ch8bt-bug-tracker', admin_url( 'options-general.php' ) ) );
	exit;
}

// Function to be called when deleting bugs
function delete_ch8bt_bug() {
	// Check that user has proper security level
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed' );
	}

	// Check if nonce field is present
	check_admin_referer( 'ch8bt_deletion' );

	// If bugs are present, cycle through array and call SQL
	// command to delete entries one by one
	if ( !empty( $_POST['bugs'] ) ) {
		// Retrieve array of bugs IDs to be deleted
		$bugs_to_delete = $_POST['bugs'];
		global $wpdb;

		foreach ( $bugs_to_delete as $bug_to_delete ) {
			$query = 'DELETE from ' . $wpdb->get_blog_prefix() . 'ch8_bug_data ';
			$query .= 'WHERE bug_id = %d';
			$wpdb->query( $wpdb->prepare( $query, intval( $bug_to_delete ) ) );
		}
	}

	// Redirect the page to the admin form
	wp_redirect( add_query_arg( 'page', 'ch8bt-bug-tracker', admin_url( 'options-general.php' ) ) );
	exit;
}