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
		add_action( 'admin_init', [ &$this, 'settings_test_init' ] );
		add_action( 'admin_post_ss_test_email', [ &$this, 'test_email_handler' ] );
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
		<form action='admin-post.php' method='post'>
		<input type="hidden" name="action" value="ss_test_email">
		<?php
		wp_nonce_field( 'simple-smtp-test-email' );
		do_settings_sections( 'wpsimplesmtp_smtp_test' );
		submit_button( 'Send', 'secondary' );
		?>
		</form>
		<?php

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

		$this->settings_field_generator( 'host', 'Host', $options['host'], 'text', 'SMTP_HOST', 'smtp.example.com' );
		$this->settings_field_generator( 'username', 'Username', $options['username'], 'text', 'SMTP_USER', 'foobar@example.com' );
		$this->settings_field_generator( 'password', 'Password', $options['password'], 'password', 'SMTP_PASS', '' );
		$this->settings_field_generator( 'port', 'Port', $options['port'], 'number', 'SMTP_PORT', '587' );

		add_settings_field(
			'wpssmtp_smtp_auth',
			'Authenticate',
			function () use ( $options ) {
				$opt_val = ( ! empty( $options['auth'] ) ) ? $options['auth'] : 0;
				$has_env = '';
				if ( ! empty( $_ENV['SMTP_AUTH'] ) ) {
					$opt_val = $_ENV['SMTP_AUTH'];
					$has_env = 'disabled';
				}
				?>
				<input type='checkbox' name='wpssmtp_smtp[auth]' <?php checked( $opt_val, 1 ); ?> value='1' <?php echo esc_attr( $has_env ); ?>>
				<?php
			},
			'wpsimplesmtp_smtp',
			'wpsimplesmtp_smtp_section'
		);
	}

	/**
	 * Generates an generic input box.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Name shown to user.
	 * @param string $val         Existing value of the relevant input.
	 * @param string $type        Input element type. Normally 'text'.
	 * @param string $envvar      Environment label for this entity.
	 * @param string $example     Text shown as a placeholder.
	 */
	public function settings_field_generator( $name, $name_pretty, $val, $type, $envvar, $example ) {
		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $val, $type, $example, $envvar ) {
				$opt_val = ( isset( $val ) ) ? $val : '';
				$has_env = '';
				if ( ! empty( $_ENV[ $envvar ] ) ) {
					$opt_val = $_ENV[ $envvar ];
					$has_env = 'disabled';
				}
				?>
				<input type='<?php echo esc_attr( $type ); ?>' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' value='<?php echo esc_attr( $opt_val ); ?>' placeholder='<?php echo esc_attr( $example ); ?>' <?php echo esc_attr( $has_env ); ?>>
				<?php
			},
			'wpsimplesmtp_smtp',
			'wpsimplesmtp_smtp_section'
		);
	}

	/**
	 * Settings fields for the email test module.
	 */
	public function settings_test_init() {
		add_settings_section(
			'wpsimplesmtp_test_email',
			__( 'Test Email', 'wpsimplesmtp' ),
			function () {
				esc_html_e( 'Sends a simple test email to check your settings.', 'wpsimplesmtp' );
			},
			'wpsimplesmtp_smtp_test'
		);

		add_settings_field(
			'wpssmtp_smtp_email_test',
			'Email recipient',
			function () {
				?>
				<input type='email' name='wpssmtp_test_email_recipient' value='<?php echo esc_attr( wp_get_current_user()->user_email ); ?>'>
				<?php
			},
			'wpsimplesmtp_smtp_test',
			'wpsimplesmtp_test_email'
		);
	}

	/**
	 * Custom admin endpoint to dispatch a test email.
	 */
	public function test_email_handler() {
		if ( isset( $_REQUEST['_wpnonce'], $_REQUEST['_wp_http_referer'], $_REQUEST['wpssmtp_test_email_recipient'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'simple-smtp-test-email' ) ) {
			wp_mail(
				sanitize_email( wp_unslash( $_REQUEST['wpssmtp_test_email_recipient'] ) ),
				'Test email from ' . get_bloginfo( 'name' ),
				'This email proves that your settings are correct.' . PHP_EOL . get_bloginfo( 'url' )
			);

			wp_safe_redirect( urldecode( sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ) ) );
			exit;
		} else {
			wp_die( 'You are not permitted to send a test email.' );
		}
	}
}
