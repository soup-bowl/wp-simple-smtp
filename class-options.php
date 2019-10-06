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
					'value'  => $options[ $name ],
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
}
