<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

$GLOBALS['sbss_temp_store'] = [];

/**
 * Mocks the WordPress add_action function.
 * See https://developer.wordpress.org/reference/functions/add_action/ for more information.
 *
 * @param string  $tag             Unused.
 * @param mixed   $function_to_add Unused.
 * @param integer $priority        Unused.
 * @param integer $accepted_args   Unused.
 * @return true
 */
function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	return true;
}

/**
 * Mocks the WordPress add_filter function.
 * See https://developer.wordpress.org/reference/functions/add_filter/ for more information.
 *
 * @param string  $tag             Unused.
 * @param mixed   $function_to_add Unused.
 * @param integer $priority        Unused.
 * @param integer $accepted_args   Unused.
 * @return true
 */
function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	return true;
}

/**
 * Mocks the WordPress get_option function.
 * See https://developer.wordpress.org/reference/functions/get_option/ for more information.
 *
 * @param string $option  Key name.
 * @param mixed  $default Default return.
 * @return mixed
 */
function get_option( $option, $default = false ) {
	global $sbss_temp_store;

	$env_loc = __DIR__ . '/../.env';
	if ( file_exists( $env_loc ) ) {
		$dotenv = Dotenv::createImmutable( __DIR__ . '/../' );
		$dotenv->load( $env_loc );
	}

	switch ( $option ) {
		case 'wpssmtp_smtp':
			return [
				'host'     => $_ENV['SMTP_HOST'],
				'port'     => $_ENV['SMTP_PORT'],
				'username' => $_ENV['SMTP_USER'],
				'password' => $_ENV['SMTP_PASS'],
				'auth'     => $_ENV['SMTP_AUTH'],
				'from'     => $_ENV['SMTP_FROM'],
				'fromname' => $_ENV['SMTP_FROMNAME'],
			];
		default:
			if ( ! empty( $sbss_temp_store[ $option ] ) ) {
				return $sbss_temp_store[ $option ];
			} else {
				return '';
			}
	}
}

/**
 * Mocks the WordPress update_option function.
 * See https://developer.wordpress.org/reference/functions/update_option/ for more information.
 *
 * @param string $name Key name.
 * @param mixed  $value Variable to be stored.
 * @return mixed
 */
function update_option( $name, $value ) {
	global $sbss_temp_store;

	$sbss_temp_store[ $name ] = $value;

	return true;
}
