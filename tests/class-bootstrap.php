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

/**
 * Mocks the WordPress add_action function.
 *
 * @param mixed $a Not used.
 * @param mixed $b Not used.
 * @return null
 */
function add_action( $a, $b = '' ) {
	return null;
}

/**
 * Mocks the WordPress get_option function.
 *
 * @param mixed $a Not used.
 * @return string[]
 */
function get_option( $a ) {
	$env_loc = __DIR__ . '/../.env';
	if ( file_exists( $env_loc ) ) {
		$dotenv = Dotenv::createImmutable( __DIR__ . '/../' );
		$dotenv->load( $env_loc );
	}

	return [
		'host'     => $_ENV['SMTP_HOST'],
		'port'     => $_ENV['SMTP_PORT'],
		'username' => $_ENV['SMTP_USER'],
		'password' => $_ENV['SMTP_PASS'],
		'auth'     => $_ENV['SMTP_AUTH'],
	];
}
