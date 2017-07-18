<?php
/*
  Plugin Name: Chapter 11 - Hello World V2
  Plugin URI: 
  Description: Companion to recipe 'Making admin page code ready for translation'
  Author: ylefebvre
  Version: 1.0
  Author URI: http://ylefebvre.ca/
 */

register_activation_hook( __FILE__, 'ch11hw_set_default_options_array' );

function ch11hw_set_default_options_array() {
	if ( false === get_option( 'ch11hw_options' ) ) {
		$new_options = array();
		$new_options['default_text'] = __( 'Hello World', 'ch11hw_hello_world' );
		add_option( 'ch11hw_options', $new_options );
	}
}

add_action( 'admin_menu', 'ch11hw_settings_menu' );

function ch11hw_settings_menu() {
	add_options_page(
		__( 'Hello World Configuration', 'ch11hw_hello_world' ),
		__( 'Hello World', 'ch11hw_hello_world' ),
		'manage_options',
		'ch11hw-hello-world', 'ch11hw_config_page' );
}

function ch11hw_config_page() {
	// Retrieve plugin configuration options from database
	$options = get_option( 'ch11hw_options' );
	?>

	<div id="ch11hw-general" class="wrap">
	<h2><?php _e( 'Hello World', 'ch11hw_hello_world' ); ?></h2>

	<form method="post" action="admin-post.php">

	 <input type="hidden" name="action"
		value="save_ch11hw_options" />

	 <!-- Adding security through hidden referrer field -->
	 <?php wp_nonce_field( 'ch11hw' ); ?>

	<?php _e( 'Default Text', 'ch11hw_hello_world' ); ?>:
	<input type="text" name="default_text" value="<?php echo esc_html( $options['default_text'] ); ?>"/><br />
	<input type="submit" value="<?php _e( 'Submit', 'ch11hw_hello_world' ); ?>" class="button-primary"/>
	</form>
	</div>
<?php }

add_action( 'admin_init', 'ch11hw_admin_init' );

function ch11hw_admin_init() {
	add_action( 'admin_post_save_ch11hw_options',
		 'process_ch11hw_options' );
}

function process_ch11hw_options() {
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed' );
	}

	check_admin_referer( 'ch11hw' );

	$options = get_option( 'ch11hw_options' );

	$options['default_text'] = $_POST['default_text'];

	update_option( 'ch11hw_options', $options );
	wp_redirect( add_query_arg( 'page', 'ch11hw-hello-world' , admin_url( 'options-general.php' ) ) );
	exit;
}
