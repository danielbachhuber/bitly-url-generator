<?php
/**
 * Plugin Name: Bitly URL Generator
 * Version: 0.1-alpha
 * Description: Generates Bitly short URLs for posts
 * Author: Daniel Bachhuber
 * Author URI: https://handbuilt.co
 * Plugin URI: https://handbuilt.co
 * Text Domain: bitly-url-generator
 * Domain Path: /languages
 * @package Bitly URL Generator
 */

/**
 * Initializes the Bitly URL Generator plugin
 */
function bitly_url_generator_load() {

	require_once dirname( __FILE__ ) . '/inc/class-controller.php';
	Bitly_URL_Generator\Controller::get_instance();

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once dirname( __FILE__ ) . '/inc/class-cli-command.php';
	}

}

add_action( 'after_setup_theme', 'bitly_url_generator_load' );
