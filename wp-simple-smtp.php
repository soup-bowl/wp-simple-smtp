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
 * Plugin URI:        https://www.soupbowl.io/wp-plugins
 * Version:           1.0.1
 * Author:            soup-bowl
 * Author URI:        https://www.soupbowl.io
 * License:           MIT
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

add_action(
	'admin_enqueue_scripts',
	function ( $page ) {
		if ( 'settings_page_wpsimplesmtp' === $page ) {
			wp_enqueue_script( 'wpss_config', plugin_dir_url( __FILE__ ) . 'smtp-config.js', [ 'jquery', 'wp-i18n' ], '1.1', true );
			wp_set_script_translations( 'wpss_config', 'simple-smtp' );
		}
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
