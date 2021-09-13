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
 * Version:           1.2
 * Author:            soup-bowl
 * Author URI:        https://www.soupbowl.io
 * License:           MIT
 */

use wpsimplesmtp\LogService;
use wpsimplesmtp\Singular as Settings;
use wpsimplesmtp\Multisite as SettingsMultisite;
use wpsimplesmtp\Privacy;
use wpsimplesmtp\Mail;
use wpsimplesmtp\MailDisable;
use wpsimplesmtp\Mailtest;
use wpsimplesmtp\Options;

/**
 * Autoloader.
 */
require_once __DIR__ . '/vendor/autoload.php';

// Override and disable emails if the user has disabled them.
$disabled = ( new Options() )->get( 'disable' );
if ( ! empty( $disabled ) && true === filter_var( $disabled->value, FILTER_VALIDATE_BOOLEAN ) ) {
	add_action(
		'plugins_loaded',
		function() {
			global $phpmailer;
			$phpmailer = new MailDisable();
		}
	);
}

if ( is_admin() ) {
	new Settings();
	( new Privacy() )->hooks();
	if ( is_multisite() ) {
		new SettingsMultisite();
	}
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
			wp_enqueue_style( 'wpss_admin_css', plugin_dir_url( __FILE__ ) . 'assets/smtp-config.css', [], '1.1' );
			wp_enqueue_script( 'wpss_config', plugin_dir_url( __FILE__ ) . 'assets/smtp-config.js', [ 'jquery', 'wp-i18n' ], '1.3', true );
			wp_set_script_translations( 'wpss_config', 'simple-smtp' );
		}
	}
);

( new Mailtest() )->hooks();

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
		( new LogService() )->register_log_storage();
	}
);

/**
 * Displays plugin errors on admin screen if error criteria is met.
 */
function wpsmtp_has_error() {
	$kses_standard = [
		'div' => [
			'class' => [],
		],
		'p'   => [],
	];

	if ( ! empty( get_option( 'wpssmtp_keycheck_fail' ) ) ) {
		$notice  = '<div class="error fade"><p>';
		$notice .= __( 'Encryption keys have changed - Please update the SMTP password to avoid email disruption.', 'simple-smtp' );
		$notice .= '</p></div>';
		echo wp_kses( $notice, $kses_standard );
	}

	$disabled = ( new Options() )->get( 'disable' );
	if ( ! empty( $disabled ) && true === filter_var( $disabled->value, FILTER_VALIDATE_BOOLEAN ) ) {
		$notice  = '<div class="error fade"><p>';
		$notice .= __( 'Emails have been disabled. Please visit the Mail settings if you wish to re-enable them.', 'simple-smtp' );
		$notice .= '</p></div>';
		echo wp_kses( $notice, $kses_standard );
	}
}
add_action( 'admin_notices', 'wpsmtp_has_error' );

register_activation_hook( __FILE__, 'wpsmtp_activation' );
register_deactivation_hook( __FILE__, 'wpsmtp_deactivation' );
