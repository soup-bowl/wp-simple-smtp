<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\Options;
use wpsimplesmtp\LogService;
use wpsimplesmtp\LogTable;
use wpsimplesmtp\Mailtest;
use wpsimplesmtp\MailView;

/**
 * Handles the visibility and setup with the WordPress Settings API.
 */
class Settings {
	/**
	 * Generates an generic input box.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Name shown to user.
	 * @param string $type        Input element type. Normally 'text'.
	 * @param string $example     Text shown as a placeholder.
	 * @param string $subtext     Text displayed underneath input box.
	 */
	public function settings_field_generator( $name, $name_pretty, $type, $example = '', $subtext = '' ) {
		$value = $this->options->get( $name );

		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $value, $type, $example, $subtext ) {
				$subtext = ( ! empty( $subtext ) ) ? "<p class='description'>{$subtext}</p>" : '';
				$has_env = '';
				if ( 'CONFIG' !== $value->source ) {
					$has_env = 'disabled';
				}

				switch ( $type ) {
					case 'checkbox':
						?>
						<input id='wpss_<?php echo esc_attr( $name ); ?>' type='checkbox' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' <?php checked( $value->value, 1 ); ?> value='1' <?php echo esc_attr( $has_env ); ?>>
						<?php
						break;
					default:
						?>
						<input id='wpss_<?php echo esc_attr( $name ); ?>' class='regular-text ltr' type='<?php echo esc_attr( $type ); ?>' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' value='<?php echo esc_attr( $value->value ); ?>' placeholder='<?php echo esc_attr( $example ); ?>' <?php echo esc_attr( $has_env ); ?>>
						<?php
						break;
				}
				echo wp_kses( $subtext, [ 'p' => [ 'class' => [] ] ] );
			},
			'wpsimplesmtp_smtp',
			'wpsimplesmtp_smtp_section'
		);
	}

	/**
	 * Generates an generic input multi-select.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Name shown to user.
	 * @param array  $options     Array of possible selections, with the index used as a key.
	 * @param string $type        Input element type. Normally 'text'.
	 * @param string $example     Text shown as a placeholder.
	 * @param string $subtext     Text displayed underneath input box.
	 */
	public function settings_field_generator_multiple( $name, $name_pretty, $options, $type, $example = '', $subtext = '' ) {
		$value = $this->options->get( $name );

		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $value, $options, $type, $example, $subtext ) {
				$subtext = ( ! empty( $subtext ) ) ? "<p class='description'>{$subtext}</p>" : '';
				$has_env = '';
				if ( 'CONFIG' !== $value->source ) {
					$has_env = 'disabled';
				}

				switch ( $type ) {
					case 'dropdown':
					default:
						?>
						<select id='wpss_<?php echo esc_attr( $name ); ?>' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' <?php echo esc_attr( $has_env ); ?>>
							<?php foreach ( $options as $key => $option ) : ?>
							<option value='<?php echo esc_attr( $key ); ?>' <?php echo esc_attr( isset( $value ) && (string) $key === (string) $value->value ) ? 'selected' : ''; ?>><?php echo esc_attr( $option ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php
						break;
				}
				echo wp_kses( $subtext, [ 'p' => [ 'class' => [] ] ] );
			},
			'wpsimplesmtp_smtp',
			'wpsimplesmtp_smtp_section'
		);
	}

	/**
	 * Checks the encrytion key is valid, if exists.
	 */
	public function encryption_keycheck() {
		if ( ! empty( get_option( 'wpssmtp_echk' ) ) && ! $this->options->check_encryption_key() ) {
			add_option( 'wpssmtp_keycheck_fail', true );
		}
	}

	/**
	 * Resets the encryption warning, if it has been triggered.
	 */
	public function reset_encryption_keycheck() {
		if ( ! empty( get_option( 'wpssmtp_keycheck_fail' ) ) ) {
			$this->options->set_encryption_test();
			delete_option( 'wpssmtp_keycheck_fail' );
		}
	}

	/**
	 * Checks the specified setting against multisite configuration to see if access is granted (always true on non-multisite installs).
	 *
	 * @param string $setting The site/network setting name.
	 * @return boolean Returns access grant status.
	 */
	public function can_edit_settings( $setting ) {
		if ( ! is_multisite() ) {
			return true;
		} else {
			if ( is_super_admin() ) {
				return true;
			}

			if ( '0' === get_network_option( get_current_blog_id(), $setting, 0 ) ) {
				return true;
			} else {
				return false;
			}
		}
	}
}
