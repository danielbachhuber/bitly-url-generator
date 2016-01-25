<?php

namespace Bitly_URL_Generator;

use WP_CLI;

/**
 * Manage Bitly URLs for WordPress
 */
class CLI_Command extends \WP_CLI_Command {

	/**
	 * Backfill Bitly URLs on all post types that support them
	 *
	 * [--dry-run]
	 * : Execute the command without performing any database operations.
	 *
	 * [--force]
	 * : Forcefully update the value in the database with the response from Bitly
	 */
	public function backfill( $args, $assoc_args ) {
		global $wpdb;
		foreach( new WP_CLI\Iterators\Query( "SELECT ID,post_title,post_type FROM {$wpdb->posts} WHERE post_status='publish'" ) as $row ) {
			if ( ! Controller::post_type_supports_bitly( $row->post_type ) ) {
				continue;
			}
			$short_url = Controller::get_short_url( $row->ID );
			if ( $short_url && ! \WP_CLI\Utils\get_flag_value( $assoc_args, 'force' ) ) {
				WP_CLI::log( sprintf( 'Skipping - %s %d already has short url: %s', $row->post_type, $row->ID, $short_url ) );
				continue;
			}
			$short_url = Controller::generate_short_url( $row->ID );
			if ( is_wp_error( $short_url ) ) {
				WP_CLI::warning( sprintf( "Couldn't generate short url for %s %d because: %s", $row->post_type, $row->ID, $short_url->get_error_message() ) );
			}
			if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run' ) ) {
				WP_CLI::log( sprintf( 'Dry run - %s %d now has short url: %s', $row->post_type, $row->ID, $short_url ) );
			} else {
				WP_CLI::log( sprintf( 'Updating - %s %d now has short url: %s', $row->post_type, $row->ID, $short_url ) );
				Controller::set_short_url( $row->ID, $short_url );
			}
		}
	}

}

WP_CLI::add_command( 'bitly', '\Bitly_URL_Generator\CLI_Command' );
