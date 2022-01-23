<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\LogService;

/**
 * Provides testing functions for checking the mail functionality.
 */
class Mailtest {
	/**
	 * Stores and retrieves the emails stored in the log.
	 *
	 * @var LogService
	 */
	protected $log_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->log_service = new LogService();
	}

	/**
	 * Hooks the class actions into the WordPress ecosystem.
	 */
	public function hooks() {
		add_action( 'admin_post_ss_test_email', [ &$this, 'test_email_handler' ] );
	}

	/**
	 * Resends an email.
	 *
	 * @param integer $email_id Email/log ID to resend.
	 * @return boolean
	 */
	public function resend_email( $email_id ) {
		$email      = $this->log_service->get_log_entry_by_id( $email_id );
		$recipients = implode( ', ', $email->get_recipients() );
		$opts       = get_option( 'wpss_resent', [] );

		$attachpaths = [];
		if ( ! empty( $email->get_attachments() ) ) {
			foreach ( $email->get_attachments() as $attachment ) {
				if ( $attachment->exists() ) {
					$attachpaths[] = $attachment->file_path();
				}
			}
		}

		if ( isset( $email ) && ! in_array( $email_id, $opts, true ) ) {
			$opts[] = $email_id;
			update_option( 'wpss_resent', $opts );

			wp_mail(
				$recipients,
				$email->get_subject(),
				$email->get_body(),
				$email->get_headers(),
				$attachpaths
			);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Custom admin endpoint to dispatch a test email.
	 */
	public static function test_email_handler() {
		if ( isset( $_REQUEST['_wpnonce'], $_REQUEST['_wp_http_referer'], $_REQUEST['wpssmtp_test_email_recipient'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'simple-smtp-test-email' ) ) {
			$email = self::generate_test_email( ( isset( $_REQUEST['wpssmtp_test_email_is_html'] ) ) ? true : false );

			// Sanitize rule disabled here as it doesn't detect the later sanitize call. Feel free to refactor.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$recipients = explode( ';', wp_unslash( $_REQUEST['wpssmtp_test_email_recipient'] ) );
			$recp_count = count( $recipients );
			// phpcs:enable
			for ( $i = 0; $i < $recp_count; $i++ ) {
				$recipients[ $i ] = sanitize_email( trim( $recipients[ $i ] ) );
			}

			$success = wp_mail( $recipients, $email['subject'], $email['message'], $email['headers'] );

			wp_safe_redirect(
				add_query_arg(
					[
						'status' => ( $success ) ? 'pass' : 'fail',
					],
					admin_url( 'options-general.php?page=wpsimplesmtp' )
				)
			);
			exit;
		} else {
			wp_die( esc_attr_e( 'You are not permitted to send a test email.', 'simple-smtp' ) );
		}
	}

	/**
	 * Generates the subject, content and headers of a test email.
	 *
	 * @param boolean $is_html Whether to setup a plaintext or HTML-based email.
	 * @return string
	 */
	public static function generate_test_email( $is_html = false ) {
		$content_type = ( $is_html ) ? 'Content-Type: text/html' : 'Content-Type: text/plain';
		$content      = __( 'This email proves that your settings are correct.', 'simple-smtp' ) . PHP_EOL . get_bloginfo( 'url' );

		if ( $is_html ) {
			$html_email  = '<body>';
			$html_email .= '<div style="text-align: center;margin-top: 5%;font-size: 4em;">' . __( '&#9989;', 'simple-smtp' ) . '</div>';
			$html_email .= '<h1 style="font-family: sans-serif;text-align: center;font-size: 4em;">' . __( 'This is a test email', 'simple-smtp' ) . '</h1>';
			$html_email .= '<p style="font-family: sans-serif;text-align: center;font-size: 1em;">' . $content . '</p>';
			$html_email .= '</body>';

			$content = wp_kses_post( $html_email );
		}

		// translators: %s is the website name.
		$subject = sprintf( __( 'Test email from %s', 'simple-smtp' ), get_bloginfo( 'name' ) );

		return [
			'subject' => $subject,
			'message' => $content,
			'headers' => [ 'x-test: WP SMTP', $content_type ],
		];
	}
}
