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
class Singular extends Settings {
	/**
	 * SMTP mailer options.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Stores and retrieves the emails stored in the log.
	 *
	 * @var LogService
	 */
	protected $log_service;

	/**
	 * Controls the display of the log table.
	 *
	 * @var LogTable
	 */
	protected $log_table;

	/**
	 * Provides testing functions for checking the mail functionality.
	 *
	 * @var Mailtest
	 */
	protected $mail_test;

	/**
	 * Settings aspects relating to the log view.
	 *
	 * @var MailView
	 */
	protected $mail_view;

	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_menu', [ &$this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ &$this, 'settings_init' ] );
		add_action( 'admin_init', [ &$this, 'settings_test_init' ] );
		add_filter( 'pre_update_option_wpssmtp_smtp', [ &$this, 'post_processing' ] );

		$this->options     = new Options();
		$this->log_service = new LogService();
		$this->log_table   = new LogTable();
		$this->mail_test   = new Mailtest();
		$this->mail_view   = new MailView();
	}

	/**
	 * Intialises the options page.
	 */
	public function options_page() {
		if ( isset( $_REQUEST['ssnonce'], $_REQUEST['delete_all'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['ssnonce'] ), 'wpss_purgelog' ) ) {
			$this->log_service->delete_all_logs();

			wp_die( esc_attr_e( 'The log has been cleared.', 'simple-smtp' ) );
		}

		$return = false;
		if ( isset( $_REQUEST['ssnonce'], $_REQUEST['eid'], $_REQUEST['resend'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['ssnonce'] ), 'wpss_action' ) ) {
			$return = true;
			$resp   = $this->mail_test->resend_email( intval( $_REQUEST['eid'] ) );
			if ( $resp ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Email resend request received.', 'simple-smtp' ); ?></p>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'Something went wrong processing your request.', 'simple-smtp' ); ?></p>
				</div>
				<?php
			}
		}

		if ( isset( $_REQUEST['ssnonce'], $_REQUEST['eid'], $_REQUEST['delete'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['ssnonce'] ), 'wpss_action' ) ) {
			$return = true;
			$resp   = $this->log_service->delete_log_entry( intval( $_REQUEST['eid'] ) );
			if ( $resp ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Log entry deleted.', 'simple-smtp' ); ?></p>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'Something went wrong processing your request.', 'simple-smtp' ); ?></p>
				</div>
				<?php
			}
		}

		if ( isset( $_REQUEST['eid'] ) && ! $return ) {
			$this->mail_view->render_email_view( intval( $_REQUEST['eid'] ) );
		} else {
			$this->render_settings();
		}

	}

	/**
	 * Registers the 'Mail' setting underneath 'Settings' in the admin GUI.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Mail', 'simple-smtp' ),
			__( 'Mail', 'simple-smtp' ),
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
			__( 'SMTP Configuration', 'simple-smtp' ),
			function () {
				esc_html_e( 'Fill out this section to allow WordPress to dispatch emails.', 'simple-smtp' );
			},
			'wpsimplesmtp_smtp'
		);

		$this->generate_generic_field( 'host', __( 'Host', 'simple-smtp' ), 'text', 'smtp.example.com' );
		$this->generate_generic_field( 'port', __( 'Port', 'simple-smtp' ), 'number', '587' );
		$this->generate_unique_checkbox( 'auth', __( 'Authenticate', 'simple-smtp' ), __( 'Authenticate connection with username and password', 'simple-smtp' ) );
		$this->generate_generic_field( 'user', __( 'Username', 'simple-smtp' ), 'text', 'foobar@example.com' );
		$this->generate_generic_field( 'pass', __( 'Password', 'simple-smtp' ), 'password', '' );
		$this->generate_generic_field( 'from', __( 'Force from e-mail address', 'simple-smtp' ), 'email', 'do-not-reply@example.com' );
		$this->generate_generic_field( 'fromname', __( 'Force from e-mail sender name', 'simple-smtp' ), 'text', _x( 'WordPress System', 'Force from e-mail sender name', 'simple-smtp' ), '', true );
		$this->generate_selection( 'sec', __( 'Security', 'simple-smtp' ), $this->acceptable_security_types(), __( 'Disabling this may risk email security.', 'simple-smtp' ) );
		$this->generate_checkbox_area(
			'adt',
			__( 'Options', 'simple-smtp' ),
			function() {
				$this->generate_checkbox( 'disable', __( 'Disable email services', 'simple-smtp' ), __( 'When marked, no emails will be sent from this site.', 'simple-smtp' ) );
				$this->generate_checkbox( 'log', __( 'Log all sent emails to the database', 'simple-smtp' ), __( 'Works with the WordPress privacy features.', 'simple-smtp' ) );
				$this->generate_checkbox( 'noverifyssl', __( 'Disable SSL Verification (advanced)', 'simple-smtp' ), __( 'Do not disable this unless you know what you\'re doing.', 'simple-smtp' ) );
			}
		);
	}

	/**
	 * Settings fields for the email test module.
	 */
	public function settings_test_init() {
		add_settings_section(
			'wpsimplesmtp_test_email',
			__( 'Test Email', 'simple-smtp' ),
			function () {
				esc_html_e( 'Sends a simple test email to check your settings.', 'simple-smtp' );
			},
			'wpsimplesmtp_smtp_test'
		);

		add_settings_field(
			'wpssmtp_smtp_email_test',
			__( 'Email recipient', 'simple-smtp' ),
			function () {
				?>
				<input id='wpss_test_recipient' class='regular-text ltr' type='text' name='wpssmtp_test_email_recipient' value='<?php echo esc_attr( wp_get_current_user()->user_email ); ?>'>
				<p class='description'><?php esc_html_e( 'Separate multiple emails with a semi-colon (;).', 'simple-smtp' ); ?></p>
				<?php
			},
			'wpsimplesmtp_smtp_test',
			'wpsimplesmtp_test_email',
			[
				'label_for' => 'wpss_test_recipient',
			]
		);

		add_settings_field(
			'wpssmtp_smtp_email_test_type',
			__( 'HTML Mode', 'simple-smtp' ),
			function () {
				?>
				<label for='wpss_test_html'>
					<input id='wpss_test_html' type='checkbox' name='wpssmtp_test_email_is_html' value='1'>
					<?php esc_html_e( 'Send the test email with HTML content instead of plain text', 'simple-smtp' ); ?>
				</label>
				<?php
			},
			'wpsimplesmtp_smtp_test',
			'wpsimplesmtp_test_email',
			[
				'label_for' => 'wpss_test_html',
			]
		);
	}

	/**
	 * Runs post-save setting processes.
	 *
	 * @param array $options Options array.
	 * @return array Parameter #1 with possible changes.
	 */
	public function post_processing( $options ) {
		// Skip condition check if the password received is a dummy (indicating to not replace the current stored one).
		if ( ! empty( $options['pass'] ) && $this->dummy_password === $options['pass'] ) {
			$current_options   = get_option( 'wpssmtp_smtp' );
			$options['pass']   = ( ! empty( $current_options['pass'] ) ) ? $current_options['pass'] : null;
			$options['pass_d'] = ( ! empty( $current_options['pass_d'] ) ) ? $current_options['pass_d'] : null;
		} elseif ( extension_loaded( 'openssl' ) && ! empty( $options['pass'] ) && $this->dummy_password !== $options['pass'] ) {
			$pass_opt = $this->options->encrypt( 'pass', $options['pass'] );

			$options['pass']   = $pass_opt['string'];
			$options['pass_d'] = $pass_opt['d'];
		}

		$this->reset_encryption_keycheck();

		return $options;
	}

	/**
	 * Shows the configuration pane on the current page.
	 */
	private function render_settings() {
		$this->encryption_keycheck();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Mail Settings', 'simple-smtp' ); ?></h1>
			<?php if ( $this->can_edit_settings( 'wpssmtp_disable_settings' ) ) : ?>
				<form id='wpss-conf' action='options.php' method='post'>
					<?php
					if ( ! empty( $_REQUEST['status'] ) ) {
						$has_pass     = ( 'pass' === $_REQUEST['status'] ) ? true : false;
						$notice_level = ( $has_pass ) ? 'notice-success' : 'notice-error';
						$notice       = ( $has_pass ) ? __( 'Test email sent successfully.', 'simple-smtp' ) : __( 'Test email failed. Please check your configuration and try again.', 'simple-smtp' );

						echo wp_kses(
							"<div class='notice is-dismissible {$notice_level}'><p><strong>{$notice}</strong></p></div>",
							[
								'div'    => [
									'class' => [],
								],
								'p'      => [],
								'strong' => [],
							]
						);
					}

					settings_fields( 'wpsimplesmtp_smtp' );
					do_settings_sections( 'wpsimplesmtp_smtp' );
					submit_button();
					?>
				</form>
			<?php endif; ?>
			<form action='admin-post.php' method='post'>
				<input type="hidden" name="action" value="ss_test_email">
				<?php
				wp_nonce_field( 'simple-smtp-test-email' );
				do_settings_sections( 'wpsimplesmtp_smtp_test' );
				submit_button( __( 'Send', 'simple-smtp' ), 'secondary' );

				if ( $this->can_edit_settings( 'wpssmtp_disable_logging' ) ) {
					$log_status = $this->options->get( 'log' );
					if ( ! empty( $log_status ) && true === filter_var( $log_status->value, FILTER_VALIDATE_BOOLEAN ) ) {
						$page = 0;
						if ( isset( $_REQUEST, $_REQUEST['ssnonce'], $_REQUEST['wpss_page'] )
						&& wp_verify_nonce( sanitize_key( $_REQUEST['ssnonce'] ), 'wpss_logtable' )
						&& is_numeric( $_REQUEST['wpss_page'] ) ) {
							$page = intval( wp_unslash( $_REQUEST['wpss_page'] ) );
						}

						echo wp_kses( '<h2 id="log">' . __( 'Email Log', 'simple-smtp' ) . '</h2>', [ 'h2' => [ 'id' => [] ] ] );
						$this->log_table->display( $page );
					}
				}
				?>
			</form>
		</div>
		<?php
	}
}
