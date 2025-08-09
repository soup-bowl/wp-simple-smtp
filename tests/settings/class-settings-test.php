<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

use wpsimplesmtp\Singular as Settings;
use wpsimplesmtp\Multisite as SettingsMultisite;
use wpsimplesmtp\Options;

use PHPUnit\Framework\TestCase;

/**
 * Tests the settings functionality.
 */
class SettingsTest extends TestCase {
	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Multisite settings.
	 *
	 * @var SettingsMultisite
	 */
	protected $multisite_settings;

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
		$this->settings           = new Settings();
		$this->multisite_settings = new SettingsMultisite();
		$this->options            = new Options();
	}

	/**
	 * Checks that the system is correctly identifying the dummy password and handles it as no change to the system.
	 */
	public function test_dummy_passwords() {
		$smtp_password = ( getenv( 'SMTP_PASS' ) !== false ) ? getenv( 'SMTP_PASS' ) : '';
		$options_good  = [ 'pass' => 'abc' ];
		$options_dummy = [ 'pass' => '******' ];

		// Chuck an SMTP setting at it. It should return either the password, or the encrypted variant.
		$response_good = $this->settings->post_processing( $options_good );
		if ( extension_loaded( 'openssl' ) ) {
			$this->assertEquals( 1, $response_good['pass_d'], '(OpenSSL on) The received password did not match the setting we sent.' );
		} else {
			$this->assertEquals( $smtp_password, $response_good['pass'], '(OpenSSL off) The received password did not match the setting we sent.' );
		}

		// Chuck the dummy trigger password. This should return the existing password.
		$response_dummy = $this->settings->post_processing( $options_dummy );
		$this->assertEquals( $smtp_password, $response_dummy['pass'], 'Passed dummy password, and did not receive the previous setting response in return.' );
	}
}
