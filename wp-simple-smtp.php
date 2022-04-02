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
 * Version:           1.3
 * Author:            soup-bowl
 * Author URI:        https://www.soupbowl.io
 * License:           MIT
 */

use wpsimplesmtp\LogService;
use wpsimplesmtp\Singular as Settings;
use wpsimplesmtp\Multisite as SettingsMultisite;
use wpsimplesmtp\QuickConfig;
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

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'email-test', [ new wpsimplesmtp\cli\EmailTest(), 'test_email' ] );
	WP_CLI::add_command( 'email-log', [ new wpsimplesmtp\cli\EmailLog(), 'load_log' ] );
	WP_CLI::add_command( 'email-view', [ new wpsimplesmtp\cli\EmailLog(), 'view_email' ] );
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
	'wpss_clear_logs',
	function() {
		$is_disabled = apply_filters( 'simple_smtp_disable_log_prune', false );
		// 2629800 = 1 Month.
		if ( ! $is_disabled ) {
			( new LogService() )->prune_logs( 2629800 );
		}
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $page ) {
		if ( 'settings_page_wpsimplesmtp' === $page || 'settings_page_wpsimplesmtpms' === $page ) {
			wp_enqueue_style( 'wpss_admin_css', plugin_dir_url( __FILE__ ) . 'assets/smtp-config.css', [], '1.2' );
			wp_enqueue_script( 'wpss_config', plugin_dir_url( __FILE__ ) . 'assets/smtp-config.js', [ 'jquery', 'wp-i18n' ], '1.3', true );
			wp_set_script_translations( 'wpss_config', 'simple-smtp' );

			$smtp_settings = QuickConfig::settings();

			wp_localize_script( 'wpss_config', 'wpss_qc_settings', $smtp_settings );
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

	if ( ! wp_next_scheduled( 'wpss_clear_logs' ) ) {
		wp_schedule_event( time(), 'hourly', 'wpss_clear_logs' );
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

	// Clear out remaining log files upon deactivation.
	( new LogService() )->delete_all_logs();
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

/**
 * Adds post-type info to 'At a Glance'-dashboard widget.
 *
 * @since 1.x.x
 *
 * @param array $items The items to display in the `At a Glance-dashboard`.
 * @return array $items All existing plus the new items.
 */
function at_a_glance_items( $items = array() ) {

	// @soup-bowl - Maybe add a check if logging is enabled or not? Do not the best way to do that, do you=

	$post_types = array( 'sbss_email_log' );

	foreach ( $post_types as $type ) {

		if ( ! post_type_exists( $type ) ) {
			continue;
		}
		$num_posts = wp_count_posts( $type );

		if ( $num_posts ) {

			$published  = intval( $num_posts->publish );
			$post_type  = get_post_type_object( $type );
			/* translators: %s: counter of how many posts. */
			$text      = _n( '%s e-mail sent', '%s e-mails sent', $published, 'simple-smtp' );
			$text      = sprintf( $text, number_format_i18n( $published ) );
			$edit_link = admin_url( 'options-general.php?page=wpsimplesmtp' ); // @soup-bowl - Maybe add ID to log table so user is scrolled to that position?

			if ( current_user_can( $post_type->cap->edit_posts ) ) {
				echo sprintf( '<li class="post-count %1$s-count"><a href="%3$s">%2$s</a></li>', $type, $text, $edit_link ) . "\n";
			} else {
				echo sprintf( '<li class="%1$s-count">%2$s</li>', $type, $text ) . "\n";
			}
		}
	}
	return $items;
}
add_filter( 'dashboard_glance_items', 'at_a_glance_items', 10, 1 );
