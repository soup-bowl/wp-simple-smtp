<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\Options;
use wpsimplesmtp\Log;
use wpsimplesmtp\LogService;
use wpsimplesmtp\LogAttachment;

/**
 * Configures PHPMailer to use our settings rather than the default.
 */
class Mail {
	/**
	 * SMTP mailer options.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * SMTP logging.
	 *
	 * @var LogService
	 */
	protected $log_service;

	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		$this->options     = new Options();
		$this->log_service = new LogService();

		$from = $this->options->get( 'from', true );
		if ( ! empty( $from->value ) ) {
			add_filter(
				'wp_mail_from',
				function( $email ) use ( $from ) {
					return $from->value;
				}
			);
		}

		$from_name = $this->options->get( 'fromname', true );
		if ( ! empty( $from_name->value ) ) {
			add_filter(
				'wp_mail_from_name',
				function( $email ) use ( $from_name ) {
					return $from_name->value;
				}
			);
		}

		add_action( 'phpmailer_init', [ &$this, 'process_mail' ] );

		$log_status = $this->options->get( 'log' );
		if ( ! empty( $log_status ) && true === filter_var( $log_status->value, FILTER_VALIDATE_BOOLEAN ) ) {
			add_action( 'wp_mail', [ &$this, 'preprocess_mail' ] );
			add_action( 'wp_mail_failed', [ &$this, 'process_error' ] );
		}
	}

	/**
	 * Hooks into the WordPress mail routine to re-configure PHP Mailer.
	 *
	 * @param PHPMailer $phpmailer The configuration object.
	 */
	public function process_mail( $phpmailer ) {
		$config = get_option( 'wpssmtp_smtp' );
		$host   = $this->options->get( 'host' )->value;

		if ( ! empty( $host ) ) {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName
			$phpmailer->Host     = $host;
			$phpmailer->Port     = $this->options->get( 'port' )->value;
			$phpmailer->Username = $this->options->get( 'user' )->value;
			$phpmailer->Password = $this->options->get( 'pass' )->value;
			$phpmailer->SMTPAuth = $this->options->get( 'auth' )->value;

			$sec = $this->options->get( 'sec' );
			if ( ! empty( $sec ) && in_array( (string) $sec->value, [ 'ssl', 'tls' ], true ) ) {
				$phpmailer->SMTPSecure = $sec->value;
			} elseif ( ! empty( $sec ) && in_array( (string) $sec->value, [ 'off' ], true ) ) {
				$phpmailer->SMTPAutoTLS = false;
			}

			$ssl_status = $this->options->get( 'noverifyssl' );
			if ( ! empty( $ssl_status ) && true === filter_var( $ssl_status->value, FILTER_VALIDATE_BOOLEAN ) ) {
				$phpmailer->SMTPOptions = [
					'ssl' => [
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true,
					],
				];
			}

			$phpmailer->IsSMTP();
			// phpcs:enable
		}

		return $phpmailer;
	}

	/**
	 * Handles an error response from the WordPress system.
	 *
	 * @param WP_Error $error The error thrown by the mailer.
	 */
	public function process_error( $error ) {
		global $wpss_mail_id;

		$this->log_service->log_entry_error( $wpss_mail_id, $error->get_error_message( 'wp_mail_failed' ) );
	}

	/**
	 * Run housekeeping before an email is dispatched.
	 *
	 * @param array $parameters The parameters currently held.
	 * @return array The same parameter array, with some additions from this function.
	 */
	public function preprocess_mail( $parameters ) {
		global $wpss_mail_id;

		if ( true === filter_var( $this->options->get( 'log' )->value, FILTER_VALIDATE_BOOLEAN ) ) {
			$recipient_array = ( is_array( $parameters['to'] ) ) ? $parameters['to'] : [ $parameters['to'] ];

			$attachments = [];
			foreach ( $parameters['attachments'] as $attachment ) {
				$attachments[] = ( new LogAttachment() )->new( $attachment )->to_string();
			}

			$log = new Log();
			$log->set_recipients( $recipient_array );
			$log->set_subject( $parameters['subject'] );
			$log->set_body( $parameters['message'] );
			$log->set_headers( $parameters['headers'] );
			$log->set_attachments( $attachments );

			$wpss_mail_id = $this->log_service->new_log_entry( $log );
		}

		return $parameters;
	}
}
