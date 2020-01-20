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
	 * @var Log
	 */
	protected $log;

	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		$this->options = new Options();
		$this->log     = new Log();

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
		if ( ! empty( $log_status ) && '1' === $log_status->value ) {
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

		if ( ! empty( $config ) ) {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName
			$phpmailer->Host     = $this->options->get( 'host' )->value;
			$phpmailer->Port     = $this->options->get( 'port' )->value;
			$phpmailer->Username = $this->options->get( 'user' )->value;
			$phpmailer->Password = $this->options->get( 'pass' )->value;
			$phpmailer->SMTPAuth = $this->options->get( 'auth' )->value;

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

		$this->log->log_entry_error( $wpss_mail_id, $error->get_error_message( 'wp_mail_failed' ) );
	}

	/**
	 * Run housekeeping before an email is dispatched.
	 *
	 * @param array $parameters The parameters currently held.
	 * @return array The same paramter array, with some additions from this function.
	 */
	public function preprocess_mail( $parameters ) {
		global $wpss_mail_id;

		if ( $this->options->get( 'log' )->value === '1' ) {
			$recipient_array = ( is_array( $parameters['to'] ) ) ? $parameters['to'] : [ $parameters['to'] ];

			$this->log->create_log_table();

			$wpss_mail_id = $this->log->new_log_entry(
				wp_json_encode( $recipient_array ),
				$parameters['subject'],
				$parameters['message'],
				current_time( 'mysql' )
			);
		}

		return $parameters;
	}
}
