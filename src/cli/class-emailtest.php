<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp\cli;

use wpsimplesmtp\Mailtest;
use WP_CLI;
use WP_CLI\Utils;

/**
 * Adds operations to WP-CLI environments.
 */
class EmailTest {
	/**
	 * Tests the site email functionality.
	 *
	 * <email>
	 * : Email address to send the test to.
	 *
	 * [--html]
	 * : Sends a HTML-based email instead of plaintext.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args       Command-line arguments.
	 * @param array $assoc_args Associated arguments.
	 */
	public function test_email( $args, $assoc_args ) {
		$is_html = Utils\get_flag_value( $assoc_args, 'html' );

		if ( is_email( $args[0] ) ) {
			$email     = Mailtest::generate_test_email( ( $is_html ) ? true : false );
			$recipient = sanitize_email( $args[0] );

			$is_sent = wp_mail( $recipient, $email['subject'], $email['message'], $email['headers'] );

			if ( $is_sent ) {
				WP_CLI::success( __( 'Test email sent successfully.', 'simple-smtp' ) );
			} else {
				WP_CLI::error( __( 'Test email failed. Please check your configuration and try again.', 'simple-smtp' ) );
			}
		} else {
			WP_CLI::error( __( 'Email address provided is invalid.', 'simple-smtp' ) );
		}
	}
}
