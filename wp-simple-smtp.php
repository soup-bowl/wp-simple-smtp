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
 * Version:           0.3
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

function wpsmtp_activation() {
	$log = new Log();
	$log->create_log_table();
}

function wpsmtp_deactivation() {
	$log = new Log();
	$log->delete_log_table();
}

register_activation_hook( __FILE__, 'wpsmtp_activation' );
register_deactivation_hook( __FILE__, 'wpsmtp_deactivation' );
