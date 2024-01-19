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
	/**
	 * Options.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Constructor.
	 */
	public function setUp(): void {
		if ( ! defined( 'SECURE_AUTH_KEY' ) ) {
			define( 'SECURE_AUTH_KEY', 's7r0237r897d89s69r83289' );
		}

		$this->options = new Options();
	}

	/**
	 * Test to check the encryption validation routine is successfully verifying the test key.
	 */
	public function test_encryption_validator() {
		$keyphrase = $this->options->set_encryption_test();
		$success   = $this->options->check_encryption_key();

		$this->assertTrue( $success );
	}

	/**
	 * Runs the encrytion/decryption routine to validate a successful encrypted value store.
	 */
	public function test_password_encryption() {
		$string = 'ab123@*';

		$e_str = $this->options->encrypt( 'teststr', $string );
		$e_col = array(
			'password'   => $e_str['string'],
			'password_d' => $e_str['d'],
		);

		$d_str = $this->options->maybe_decrypt( $e_col, 'password' );

		$this->assertStringContainsString( $string, $d_str, 'Decrypted string matches initially encrypted string.' );
	}
}
