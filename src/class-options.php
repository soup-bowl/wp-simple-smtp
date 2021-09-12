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
 * Handles the retrieval of system variables.
 */
class Options {
	/**
	 * Test value constant for checking encryption functionality.
	 *
	 * @var string
	 */
	protected $test_value = 'helloworld';

	/**
	 * Gets the setting value. This checks the following in chronological order:
	 * - Environmental variables (including .env).
	 * - Constants (e.g. wp-config.php).
	 * - Values stored in via settings GUI.
	 *
	 * @param string  $name               Desired variable name ('value' will check for 'SMTP_VALUE').
	 * @param boolean $blank_obj_on_empty If true, system pretends the CONFIG value was empty if null.
	 * @param boolean $ms_only            Check MS settings and return empty if no network settings were found.
	 * @return stdClass 'value' and 'source'.
	 */
	public function get( $name, $blank_obj_on_empty = true, $ms_only = false ) {
		$sysname = 'SMTP_' . strtoupper( $name );

		if ( ! $ms_only && ! empty( $_ENV[ $sysname ] ) ) {
			return (object) [
				'value'  => $_ENV[ $sysname ],
				'source' => 'ENV',
			];
		} elseif ( ! $ms_only && defined( $sysname ) ) {
			return (object) [
				'value'  => constant( $sysname ),
				'source' => 'CONST',
			];
		} else {
			if ( is_multisite() ) {
				$options = get_site_option( 'wpssmtp_smtp_ms', null );
				if ( ! empty( $options ) && array_key_exists( $name, $options ) ) {
					return (object) [
						'value'  => $this->maybe_decrypt( $options, $name ),
						'source' => 'MULTISITE',
					];
				}
			}

			if ( ! $ms_only ) {
				$options = get_option( 'wpssmtp_smtp' );
				if ( ! empty( $options ) && array_key_exists( $name, $options ) ) {
					return (object) [
						'value'  => $this->maybe_decrypt( $options, $name ),
						'source' => 'CONFIG',
					];
				} else {
					if ( $blank_obj_on_empty ) {
						return (object) [
							'value'  => '',
							'source' => 'CONFIG',
						];
					} else {
						return null;
					}
				}
			} else {
				if ( $blank_obj_on_empty ) {
					return (object) [
						'value'  => '',
						'source' => 'MULTISITE',
					];
				} else {
					return null;
				}
			}
		}
	}

	/**
	 * Encrypts the given entities.
	 *
	 * @param string $name  Option name.
	 * @param string $value Option value.
	 */
	public function encrypt( $name, $value ) {
		$pl = [
			'string' => $value,
			'd'      => 0,
		];

		if ( extension_loaded( 'openssl' ) ) {
			$this->set_encryption_test();

			$pl['string'] = openssl_encrypt( $value, 'AES-128-ECB', $this->encryption_key() );
			$pl['d']      = 1;
		}

		return $pl;
	}

	/**
	 * Checks if the string is encrypted, and if so decrypts it.
	 *
	 * @param array  $options Collection where the setting (and decryptor) are located.
	 * @param string $name    Setting name.
	 * @return string Decrypted (if it was) contents.
	 */
	public function maybe_decrypt( $options, $name ) {
		if ( extension_loaded( 'openssl' ) ) {
			$encrypt_id = ( ! empty( $options[ $name . '_d' ] ) ) ? (int) $options[ $name . '_d' ] : 0;

			switch ( $encrypt_id ) {
				case 1:
					return openssl_decrypt( $options[ $name ], 'AES-128-ECB', $this->encryption_key() );
				case 0:
				default:
					return $options[ $name ];
			}
		} else {
			return $options[ $name ];
		}
	}

	/**
	 * Checks the encryption passphrase has not changed.
	 *
	 * @return boolean Represents whether the test value decryption was a success or not.
	 */
	public function check_encryption_key() {
		$codeword = openssl_decrypt( get_option( 'wpssmtp_echk' ), 'AES-128-ECB', $this->encryption_key() );

		if ( $this->test_value === $codeword ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sets a test string for testing encryption purposes.
	 */
	public function set_encryption_test() {
		update_option(
			'wpssmtp_echk',
			openssl_encrypt( $this->test_value, 'AES-128-ECB', $this->encryption_key() )
		);
	}

	/**
	 * Encryption key used by this plugin.
	 *
	 * @return string Encryption key.
	 */
	private function encryption_key() {
		return SECURE_AUTH_KEY;
	}
}
