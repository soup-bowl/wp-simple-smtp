<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

use wpsimplesmtp\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

/**
 * Tests the mail functionality.
 */
class MailTest extends TestCase {
	/**
	 * Mail rep.
	 *
	 * @var Mail
	 */
	protected $mail;

	/**
	 * Constructor.
	 */
	public function setUp():void {
		$this->mail = new Mail();
	}

	/**
	 * Tests using the environment mailer to ensure the plugin is functioning.
	 *
	 * @throws Exception Throws a PHPMailer exception.
	 */
	public function test_smtp_communication() {
		$phpmailer = new PHPMailer( true );
		$phpmailer = $this->mail->process_mail( $phpmailer );

		$phpmailer->addAddress( 'hello@example.com', 'Example User' );
		$phpmailer->SetFrom( 'wordpress@example.com' );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->Subject = 'Simple SMTP Test Unit';
		$phpmailer->Body    = 'This is a test email from the WordPress simple SMTP plugin.';
		// phpcs:enable

		try {
			$phpmailer->send();
		} catch ( Exception $e ) {
			throw $e;
		}

		$this->assertTrue( true, 'Email was sent successfully to the SMTP server.' );
	}
}
