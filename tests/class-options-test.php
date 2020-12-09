<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

use wpsimplesmtp\Options;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

/**
 * Tests the option functionality.
 */
class OptionsTest extends TestCase {
	protected $options;

	public function setUp():void {
		define( 'SECURE_AUTH_KEY', 's7r0237r897d89s69r83289' );

		$this->options = new Options();
	}

	public function test_password_encryption() {
		$string = 'ab123@*';

		$e_str = $this->options->encrypt( 'teststr', $string );
		$e_col = [
			'password'   => $e_str['string'],
			'password_d' => $e_str['d'],
		];

		$d_str = $this->options->maybe_decrypt( $e_col, 'password' );

		$this->assertStringContainsString( $string, $d_str, 'Decrypted string matches initially encrypted string.' );
	}
}
