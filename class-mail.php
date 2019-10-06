<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

/**
 * Configures PHPMailer to use our settings rather than the default.
 */
class Mail {
	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		add_action( 'phpmailer_init', [ &$this, 'process_mail' ] );
	}

	/**
	 * Hooks into the WordPress mail routine to re-configure PHP Mailer.
	 *
	 * @param PHPMailer $phpmailer The configuration object.
	 */
	public function process_mail( $phpmailer ) {
		$config = get_option( 'wpssmtp_smtp' );

		if ( ! empty( $config ) ) {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$phpmailer->Host     = ( empty( $_ENV['SMTP_HOST'] ) ) ? $config['host'] : $_ENV['SMTP_HOST'];
			$phpmailer->Port     = ( empty( $_ENV['SMTP_PORT'] ) ) ? $config['port'] : $_ENV['SMTP_PORT'];
			$phpmailer->Username = ( empty( $_ENV['SMTP_USER'] ) ) ? $config['username'] : $_ENV['SMTP_USER'];
			$phpmailer->Password = ( empty( $_ENV['SMTP_PASS'] ) ) ? $config['password'] : $_ENV['SMTP_PASS'];
			$phpmailer->SMTPAuth = ( empty( $_ENV['SMTP_AUTH'] ) ) ? (bool) $config['auth'] : $_ENV['SMTP_AUTH'];
			// phpcs:enable

			$phpmailer->IsSMTP();
		}

		return $phpmailer;
	}
}
