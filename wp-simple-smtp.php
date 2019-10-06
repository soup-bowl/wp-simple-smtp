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
 * Plugin URI:        https://gitlab.com/soup-bowl/simple-smtp
 * Version:           trunk
 * Author:            soup-bowl
 * Author URI:        https://soupbowl.io
 * License:           MIT
 * Text Domain:       wpsimplesmtp
 */

/**
 * Autoloader.
 */
require_once __DIR__ . '/vendor/autoload.php';

$env_loc = __DIR__ . '/.env';
if ( file_exists( $env_loc ) ) {
	$dotenv = new \Symfony\Component\Dotenv\Dotenv();
	$dotenv->load( $env_loc );
}

if ( is_admin() ) {
	new wpsimplesmtp\Settings();
}

new wpsimplesmtp\Mail();
