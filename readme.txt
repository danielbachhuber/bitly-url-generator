=== Bitly URL Generator ===
Contributors: danielbachhuber
Tags: bitly, shortlinks
Requires at least: 4.0
Tested up to: 4.4.1
Stable tag: 0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate Bitly short URLs for posts

== Description ==

Generates Bitly short URLs for posts, saves the short URL to a `bitly_url` meta field, and filters `wp_get_shortlink()` to use the Bitly short URL when present.

Define your API login and key with `BITLY_URL_GENERATOR_API_LOGIN` and `BITLY_URL_GENERATOR_API_KEY` constants.

By default, the plugin works with posts and pages. Add Bitly support to your custom post type with `add_post_type_support( 'cpt', 'bitly' );`.

Use the `wp bitly backfill` WP-CLI command to generate Bitly short URLs for already published posts.

== Installation ==

Installation is just a few steps:

1. Download, install, and activate the plugin through your preferred means.
2. Define the authentication credentials with `BITLY_URL_GENERATOR_API_LOGIN` and `BITLY_URL_GENERATOR_API_KEY` constants.
3. (Optionally) enable for your custom post type with `add_post_type_support( 'cpt', 'bitly' );`
4. (Optionally) generate Bitly short URLs for already published posts with `wp bitly backfill`

== Changelog ==

= 0.1 (???? ??, ????) =
* Initial release.

