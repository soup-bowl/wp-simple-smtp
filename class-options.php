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
	 * Gets the setting value. This checks the following in chronological order:
	 * - Environmental variables (including .env).
	 * - Constants (e.g. wp-config.php).
	 * - Values stored in via settings GUI.
	 *
	 * @param string  $name                Desired variable name ('value' will check for 'SMTP_VALUE').
	 * @param boolean $blank_obj_on_empty If true, system pretends the CONFIG value was empty if null.
	 * @return stdClass 'value' and 'source'.
	 */
	public function get( $name, $blank_obj_on_empty = true ) {
		$sysname = 'SMTP_' . strtoupper( $name );

		if ( ! empty( $_ENV[ $sysname ] ) ) {
			return (object) [
				'value'  => $_ENV[ $sysname ],
				'source' => 'ENV',
			];
		} elseif ( defined( $sysname ) ) {
			return (object) [
				'value'  => constant( $sysname ),
				'source' => 'CONST',
			];
		} else {
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
		}
	}

	public function encrypt( $name, $value ) {
		$pl = [
			'string' => $value,
			'd'      => 0,
		];
		
		if ( extension_loaded( 'openssl' ) ) {
			$pl['string'] = openssl_encrypt( $value, 'AES-128-ECB', $this->encryption_key() );
			$pl['d']      = 1;
		}

		return $pl;
	}

	private function maybe_decrypt( $options, $name ) {
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

	private function encryption_key () {
		return 'test';
	}
}
