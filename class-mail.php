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
			$phpmailer->Host     = $config['host'];
			$phpmailer->Port     = $config['port'];
			$phpmailer->Username = $config['username'];
			$phpmailer->Password = $config['password'];
			$phpmailer->SMTPAuth = (bool) $config['auth'];

			$phpmailer->IsSMTP();
		}
		
		return $phpmailer;
	}
}
