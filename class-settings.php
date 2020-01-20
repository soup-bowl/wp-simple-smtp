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
use wpsimplesmtp\LogTable;

/**
 * Handles the visibility and setup with the WordPress Settings API.
 */
class Settings {
	/**
	 * SMTP mailer options.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Controls the display of the log table.
	 *
	 * @var LogTable
	 */
	protected $log_table;

	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ &$this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ &$this, 'settings_init' ] );
		add_action( 'admin_init', [ &$this, 'settings_test_init' ] );
		add_action( 'admin_post_ss_test_email', [ &$this, 'test_email_handler' ] );

		$this->options   = new Options();
		$this->log_table = new LogTable();
	}

	/**
	 * Intialises the options page.
	 */
	public function options_page() {
		?>
		<div class="wrap">
			<h1>Mail Settings</h1>
			<form action='options.php' method='post'>
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

			$log_status = $this->options->get( 'log' );
			if ( ! empty( $log_status ) && '1' === $log_status->value ) {
				$page = 0;
				// Felt this wasn't necessary for such a field. Feel free to raise an issue if you disagree.
				// phpcs:disable WordPress.Security.NonceVerification.Recommended
				if ( isset( $_REQUEST, $_REQUEST['wpss_page'] ) && is_numeric( $_REQUEST['wpss_page'] ) ) {
					$page = intval( wp_unslash( $_REQUEST['wpss_page'] ) );
				}
				// phpcs:enable

				echo wp_kses( '<h2>' . __( 'Email Log', 'wpsimplesmtp' ) . '</h2>', [ 'h2' => [] ] );
				$this->log_table->display( $page );
			}
			?>
			</form>
		</div>
		<?php

	}

	/**
	 * Registers the 'Mail' setting underneath 'Settings' in the admin GUI.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Mail', 'wpsimplesmtp' ),
			__( 'Mail', 'wpsimplesmtp' ),
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

		$this->settings_field_generator( 'host', __( 'Host', 'wpsimplesmtp' ), 'text', 'smtp.example.com' );
		$this->settings_field_generator( 'port', __( 'Port', 'wpsimplesmtp' ), 'number', '587' );
		$this->settings_field_generator( 'auth', __( 'Authenticate', 'wpsimplesmtp' ), 'checkbox', '' );
		$this->settings_field_generator( 'user', __( 'Username', 'wpsimplesmtp' ), 'text', 'foobar@example.com' );
		$this->settings_field_generator( 'pass', __( 'Password', 'wpsimplesmtp' ), 'password', '' );
		$this->settings_field_generator( 'from', __( 'Force from', 'wpsimplesmtp' ), 'email', 'do-not-reply@example.com' );
		$this->settings_field_generator( 'fromname', __( 'Force from name', 'wpsimplesmtp' ), 'text', 'WordPress System' );
		$this->settings_field_generator( 'log', __( 'Logging', 'wpsimplesmtp' ), 'checkbox', '' );
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
			__( 'Email recipient', 'wpsimplesmtp' ),
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
				// translators: %s is the website name.
				sprintf( __( 'Test email from %s', 'wpsimplesmtp' ), get_bloginfo( 'name' ) ),
				__( 'This email proves that your settings are correct.', 'wpsimplesmtp' ) . PHP_EOL . get_bloginfo( 'url' )
			);

			wp_safe_redirect( urldecode( sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ) ) );
			exit;
		} else {
			wp_die( esc_attr_e( 'You are not permitted to send a test email.', 'wpsimplesmtp' ) );
		}
	}

	/**
	 * Generates an generic input box.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Name shown to user.
	 * @param string $type        Input element type. Normally 'text'.
	 * @param string $example     Text shown as a placeholder.
	 */
	private function settings_field_generator( $name, $name_pretty, $type, $example ) {
		$value = $this->options->get( $name );

		add_settings_field(
			'wpssmtp_smtp_' . $name,
			$name_pretty,
			function () use ( $name, $value, $type, $example ) {
				switch ( $type ) {
					case 'checkbox':
						$has_env = '';
						if ( 'CONFIG' !== $value->source ) {
							$has_env = 'disabled';
						}
						?>
						<input type='checkbox' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' <?php checked( $value->value, 1 ); ?> value='1' <?php echo esc_attr( $has_env ); ?>>
						<?php
						break;
					default:
						$has_env = '';
						if ( 'CONFIG' !== $value->source ) {
							$has_env = 'disabled';
						}
						?>
						<input type='<?php echo esc_attr( $type ); ?>' name='wpssmtp_smtp[<?php echo esc_attr( $name ); ?>]' value='<?php echo esc_attr( $value->value ); ?>' placeholder='<?php echo esc_attr( $example ); ?>' <?php echo esc_attr( $has_env ); ?>>
						<?php
						break;
				}
			},
			'wpsimplesmtp_smtp',
			'wpsimplesmtp_smtp_section'
		);
	}
}
