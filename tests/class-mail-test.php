<?php

use wpsimplesmtp\Mail;

use PHPUnit\Framework\TestCase;

// Mock the add_action and get_option of the constructor.
function add_action( $a, $b ) {
	return null;
}

function get_option( $a ) {
	$env_loc = __DIR__ . '/../.env';
	if ( file_exists( $env_loc ) ) {
		$dotenv = new Symfony\Component\Dotenv\Dotenv();
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

/**
 * Test entries for Money.
 */
class MailTest extends TestCase {
	protected $mail;
	public function setUp():void {
		$this->mail = new Mail();
	}

	public function test_smtp_communication() {
		$phpmailer = new PHPMailer( true );
		$phpmailer = $this->mail->process_mail( $phpmailer );

		$phpmailer->addAddress( 'hello@example.com', 'Example User' );

		$phpmailer->Subject = 'Simple SMTP Test Unit';
    	$phpmailer->Body    = 'This is a test email from the WordPress simple SMTP plugin.';

		try {
			$phpmailer->send();
		} catch( Exception $e ) {
			throw $e;
		}

		$this->assertTrue( true, 'Email was sent successfully to the SMTP server.' );
	}
}
