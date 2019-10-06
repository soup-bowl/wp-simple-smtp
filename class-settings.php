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
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ &$this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ &$this, 'settings_init' ] );
	}

	/**
	 * Registers the 'Mail' setting underneath 'Settings' in the admin GUI.
	 */
	public function add_admin_menu() {
		add_options_page(
			'Mail',
			'Mail',
			'manage_options',
			'wpsimplesmtp',
			[ &$this, 'options_page' ]
		);
	}

	/**
	 * Initialises the settings implementation.
	 */
	public function settings_init() {
		register_setting( 'wpsimplesmtp_smtp', 'wpssmtp_smtp' );

		add_settings_section(
			'wpsimplesmtp_smtp_section',
			__( 'SMTP Configuration', 'wpsimplesmtp' ),
			function () {
				esc_html_e( 'Fill out this section to allow WordPress to dispatch emails.', 'wpsimplesmtp' );
			},
			'wpsimplesmtp_smtp'
		);

		$options = get_option( 'wpssmtp_smtp' );

		$this->settings_field_generator( 'host', 'Host', $options['host'], 'text', 'smtp.example.com' );
		$this->settings_field_generator( 'username', 'Username', $options['username'], 'text', 'foobar@example.com' );
		$this->settings_field_generator( 'password', 'Password', $options['password'], 'password', '' );
		$this->settings_field_generator( 'port', 'Port', $options['port'], 'number', '587' );
	}

	/**
	 * Generates an generic input box.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Name shown to user.
	 * @param string $val         Existing value of the relevant input.
	 * @param string $type        Input element type. Normally 'text'.
	 * @param string $example     Text shown as a placeholder.
	 */
	public function settings_field_generator( $name, $name_pretty, $val, $type, $example ) {
		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $val, $type, $example ) {
				$opt_val = ( isset( $val ) ) ? $val : '';
				?>
				<input type='<?php echo esc_attr( $type ); ?>' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' value='<?php echo esc_attr( $opt_val ); ?>' placeholder='<?php echo esc_attr( $example ); ?>'>
				<?php
			},
			'wpsimplesmtp_smtp',
			'wpsimplesmtp_smtp_section'
		);
	}

	/**
	 * Intialises the options page.
	 */
	public function options_page() {
		?>
			<form action='options.php' method='post'>
			<h2>Mail Settings</h2>
			<?php
			settings_fields( 'wpsimplesmtp_smtp' );
			do_settings_sections( 'wpsimplesmtp_smtp' );
			submit_button();
			?>
			</form>
			<?php

	}
}
