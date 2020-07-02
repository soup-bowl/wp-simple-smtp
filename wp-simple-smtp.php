<?php
/**
 * Adds mail configuration to WordPress in a simple, standardised plugin.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 *
 * @wordpress-plugin
 * Plugin Name:       Simple SMTP
 * Description:       Adds mail configuration to WordPress in a simple, standardised plugin.
 * Plugin URI:        https://github.com/soup-bowl/simple-smtp
 * Version:           0.3.3-dev
 * Author:            soup-bowl
 * Author URI:        https://www.soupbowl.io
 * License:           MIT
 * Text Domain:       wpsimplesmtp
 */

use wpsimplesmtp\Log;
use wpsimplesmtp\Settings;
use wpsimplesmtp\Mail;

/**
 * Autoloader.
 */
require_once __DIR__ . '/vendor/autoload.php';

if ( is_admin() ) {
	new Settings();
}

new Mail();

add_action(
	'wpss_clear_resent',
	function() {
		delete_option( 'wpss_resent' );
	}
);

/**
 * Actions to be executed on plugin activation.
 */
function wpsmtp_activation() {
	if ( ! wp_next_scheduled( 'wpss_clear_resent' ) ) {
		wp_schedule_event( time(), 'hourly', 'wpss_clear_resent' );
	}
}

/**
 * Actions to be executed on deactivation.
 */
function wpsmtp_deactivation() {
	wp_unschedule_event(
		wp_next_scheduled( 'wpss_clear_resent' ),
		'wpss_clear_resent'
	);
}

/**
 * Actions to be executed on multi-site deletion.
 */
add_action(
	'wp_delete_site',
	function( $old_site ) {
		( new Log() )->delete_log_table( $old_site->blog_id );
	}
);

/**
 * Create CPT for storing logs.
 */
add_action(
	'init',
	function() {
		( new Log() )->register_log_storage();
	}
);

register_activation_hook( __FILE__, 'wpsmtp_activation' );
register_deactivation_hook( __FILE__, 'wpsmtp_deactivation' );
