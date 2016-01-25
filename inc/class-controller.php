<?php

namespace Bitly_URL_Generator;

use WP_Error;

/**
 * Primary controller for generating Bitly URLs on posts, and filtering them
 * to wp_get_shortlink()
 */
class Controller {

	private static $instance;

	const SUPPORTS_KEY = 'bitly';
	const META_KEY = 'bitly_url';

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Controller;
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}
		return self::$instance;
	}

	private function setup_actions() {
		add_action( 'init', array( $this, 'action_init_early' ), 1 ); // after create_initial_post_types
		add_action( 'wp_insert_post', array( $this, 'action_wp_insert_post' ), 10, 2 );
	}

	private function setup_filters() {

	}

	/**
	 * Register Bitly support for default post types
	 */
	public function action_init_early() {
		add_post_type_support( 'post', self::SUPPORTS_KEY );
		add_post_type_support( 'page', self::SUPPORTS_KEY );
	}

	/**
	 * Generate Bitly short URLs when the post is updated
	 */
	public function action_wp_insert_post( $post_id, $post ) {
		if ( 'publish' != $post->post_status
				|| ! self::post_type_supports_bitly( $post->post_type )
				|| self::get_short_url( $post_id ) ) {
				return;
		}
		$short_url = self::generate_short_url( $post_id );
		if ( is_wp_error( $short_url ) ) {
			return;
		}
		self::set_short_url( $post_id, $short_url );
	}

	/**
	 * Whether or not a given post type supports Bitly URL generation
	 *
	 * @param string $post_type
	 * @return bool
	 */
	public static function post_type_supports_bitly( $post_type ) {
		return post_type_supports( $post_type, self::SUPPORTS_KEY );
	}

	/**
	 * Get the Bitly short url for a given post
	 *
	 * @param integer $post_id
	 * @return string
	 */
	public static function get_short_url( $post_id ) {
		return get_post_meta( $post_id, self::META_KEY, true );
	}

	/**
	 * Generate a short url for a given post
	 *
	 * @param integer $post_id
	 * @return string|WP_Error
	 */
	public static function generate_short_url( $post_id ) {
		$permalink = get_permalink( $post_id );

		$options = apply_filters( 'bitly_url_generator_options', array(
			'login'     => @constant( 'BITLY_URL_GENERATOR_API_LOGIN' ),
			'api_key'   => @constant( 'BITLY_URL_GENERATOR_API_KEY' ),
		) );

		if ( empty( $options['login'] ) || empty( $options['api_key'] ) ) {
			return new WP_Error( 'bitly_invalid_auth', __( 'No API key or login specified.', 'bitly-url-generator' ) );
		}

		$args = array(
			'login'   => $options['login'],
			'apiKey'  => $options['api_key'],
			'longUrl' => get_permalink( $post_id ),
			'format'  => 'json',
		);
		$request_url = add_query_arg( $args, 'https://api-ssl.bitly.com/v3/shorten' );
		$response = wp_remote_get( $request_url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 200 !== $body['status_code'] ) {
			return new WP_Error( 'bitly_api_fail', sprintf( __( 'Bitly API returned an error: %s', 'bitly-url-generator' ), sanitize_text_field( $body['message'] ) ) );
		}
		if ( ! empty( $body['data']['url'] ) ) {
			return esc_url_raw( $body['data']['url'] );
		}
		return new WP_Error( 'bitly_api_fail', __( 'Unknown error connecting to the Bitly API.', 'bitly-url-generator' ) );
	}

	/**
	 * Set a short url for a given post
	 *
	 * @param integer $post_id
	 * @param string $short_url
	 */
	public static function set_short_url( $post_id, $short_url ) {
		update_post_meta( $post_id, self::META_KEY, $short_url );
	}

}
