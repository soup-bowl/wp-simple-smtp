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
 * Handles the visibility and setup with the WordPress Settings API.
 */
class Settings {
	/**
	 * For settings generator - Multisite modifier.
	 *
	 * @var boolean
	 */
	protected $ms;

	/**
	 * For settings generator - Page assignation.
	 *
	 * @var string
	 */
	protected $page;

	/**
	 * For settings generator - Section assignation.
	 *
	 * @var string
	 */
	protected $section;

	/**
	 * String that represents a dummy password on the front-end.
	 *
	 * @var string
	 */
	protected $dummy_password;

	/**
	 * Constructor.
	 *
	 * @param boolean $ms      For settings generator - Multisite modifier.
	 * @param string  $page    For settings generator - Page assignation.
	 * @param string  $section For settings generator - Section assignation.
	 */
	public function __construct( $ms = false, $page = 'wpsimplesmtp_smtp', $section = 'wpsimplesmtp_smtp_section' ) {
		$this->ms      = $ms;
		$this->page    = $page;
		$this->section = $section;

		$this->dummy_password = '******';
	}

	/**
	 * Returns an array of acceptable security codes and their translated labels.
	 *
	 * @return string[]
	 */
	public function acceptable_security_types() {
		return [
			'def' => __( 'Default', 'simple-smtp' ),
			'ssl' => __( 'SSL', 'simple-smtp' ),
			'tls' => __( 'TLS', 'simple-smtp' ),
			'off' => __( 'None', 'simple-smtp' ),
		];
	}

	/**
	 * Generates an generic input box.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Left-side column name shown to user.
	 * @param string $type        Override text input element with number, password, email, etc.
	 * @param string $example     Content to be shown as a placeholder.
	 * @param string $description Text displayed underneath the input box.
	 */
	public function generate_generic_field( $name, $name_pretty, $type = 'text', $example = '', $description = '' ) {
		$value = $this->options->get( $name, true, $this->ms );

		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $value, $type, $example, $description ) {
				$description = ( ! empty( $description ) ) ? "<p class='description'>{$description}</p>" : '';
				$prv_input   = ( 'password' === $type && ! empty( $value->value ) ) ? $this->dummy_password : $value->value;
				$has_env     = '';
				if ( ! $this->ms && 'CONFIG' !== $value->source ) {
					$has_env = 'disabled';
				}

				?>
				<input id='wpss_<?php echo esc_attr( $name ); ?>' class='regular-text ltr' type='<?php echo esc_attr( $type ); ?>' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' value='<?php echo esc_attr( $prv_input ); ?>' placeholder='<?php echo esc_attr( $example ); ?>' <?php echo esc_attr( $has_env ); ?>>
				<?php

				if ( ! $this->ms && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					echo wp_kses( "<span class='wpsmtp-badge info'>{$value->source}</span>", [ 'span' => [ 'class' => [] ] ] );
				}

				if ( ! empty( $description ) ) {
					echo wp_kses( $description, [ 'p' => [ 'class' => [] ] ] );
				}
			},
			$this->page,
			$this->section,
			[
				'label_for' => 'wpss_' . esc_attr( $name ),
			]
		);
	}

	/**
	 * Generates a singular checkbox field.
	 *
	 * @param string $name           Code name of input.
	 * @param string $name_pretty    Left-side column name shown to user.
	 * @param string $alongside_text Appears alongside the checkbox.
	 * @param string $description    Text displayed underneath the input box.
	 */
	public function generate_unique_checkbox( $name, $name_pretty, $alongside_text = '', $description = '' ) {
		$value = $this->options->get( $name, true, $this->ms );

		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $alongside_text, $value, $description ) {
				$description = ( ! empty( $description ) ) ? "<p class='description'>{$description}</p>" : '';
				$has_env     = '';
				if ( ! $this->ms && 'CONFIG' !== $value->source ) {
					$has_env = 'disabled';
				}

				?>
				<label for='wpss_<?php echo esc_attr( $name ); ?>'>
					<input id='wpss_<?php echo esc_attr( $name ); ?>' type='checkbox' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' <?php checked( $value->value, 1 ); ?> value='1' <?php echo esc_attr( $has_env ); ?>>
					<?php echo esc_html( $alongside_text ); ?>
				</label>
				<?php

				if ( ! $this->ms && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					echo wp_kses( "<span class='wpsmtp-badge info'>{$value->source}</span>", [ 'span' => [ 'class' => [] ] ] );
				}

				if ( ! empty( $description ) ) {
					echo wp_kses( $description, [ 'p' => [ 'class' => [] ] ] );
				}
			},
			$this->page,
			$this->section,
			[
				'label_for' => 'wpss_' . esc_attr( $name ),
			]
		);
	}

	/**
	 * Generates a settings area for multiple checkbox placements.
	 *
	 * @param string   $name        Code name of input.
	 * @param string   $name_pretty Left-side column name shown to user.
	 * @param callback $callback    Function is called within the fieldset.
	 */
	public function generate_checkbox_area( $name, $name_pretty, $callback ) {
		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function() use ( &$callback ) {
				?>
				<fieldset>
					<?php call_user_func( $callback ); ?>
				</fieldset>
				<?php
			},
			$this->page,
			$this->section
		);
	}

	/**
	 * Generates a checkbox without WordPress settings API for use within generate_checkbox_area callback.
	 *
	 * @param string $name           Code name of input.
	 * @param string $alongside_text Appears alongside the checkbox. Required to help identify the field in the collective.
	 * @param string $description    Text displayed underneath the input box.
	 */
	public function generate_checkbox( $name, $alongside_text, $description = '' ) {
		$value       = $this->options->get( $name, true, $this->ms );
		$description = ( ! empty( $description ) ) ? "<p class='description'>{$description}</p>" : '';
		$has_env     = '';
		if ( ! $this->ms && 'CONFIG' !== $value->source ) {
			$has_env = 'disabled';
		}

		$debuginfo = '';
		if ( ! $this->ms && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$debuginfo = "<span class='wpsmtp-badge info'>{$value->source}</span>";
		}

		?>
		<label for='wpss_<?php echo esc_attr( $name ); ?>'>
			<input id='wpss_<?php echo esc_attr( $name ); ?>' type='checkbox' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' <?php checked( $value->value, 1 ); ?> value='1' <?php echo esc_attr( $has_env ); ?>>
			<?php echo esc_html( $alongside_text ); ?> <?php echo wp_kses( $debuginfo, [ 'span' => [ 'class' => [] ] ] ); ?>
			<?php echo wp_kses( $description, [ 'p' => [ 'class' => [] ] ] ); ?>
		</label><br>
		<?php
	}

	/**
	 * Generates an generic input multi-select.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Left-side column name shown to user.
	 * @param array  $options     Array of possible selections, with the index used as a key.
	 * @param string $description Text displayed underneath the input box.
	 */
	public function generate_selection( $name, $name_pretty, $options, $description = '' ) {
		$value = $this->options->get( $name, true, $this->ms );

		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $value, $options, $description ) {
				$description = ( ! empty( $description ) ) ? "<p class='description'>{$description}</p>" : '';
				$has_env     = '';
				if ( ! $this->ms && 'CONFIG' !== $value->source ) {
					$has_env = 'disabled';
				}

				?>
				<select id='wpss_<?php echo esc_attr( $name ); ?>' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' <?php echo esc_attr( $has_env ); ?>>
					<?php foreach ( $options as $key => $option ) : ?>
					<option value='<?php echo esc_attr( $key ); ?>' <?php echo esc_attr( isset( $value ) && (string) $key === (string) $value->value ) ? 'selected' : ''; ?>><?php echo esc_attr( $option ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php
				echo wp_kses( $description, [ 'p' => [ 'class' => [] ] ] );
			},
			$this->page,
			$this->section,
			[
				'label_for' => 'wpss_' . esc_attr( $name ),
			]
		);
	}

	/**
	 * Checks the encryption key is valid, if exists.
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
