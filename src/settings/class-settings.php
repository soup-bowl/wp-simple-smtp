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
	protected $log;

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
					<p><?php esc_html_e( 'Email resend request recieved.', 'simple-smtp' ); ?></p>
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

		$sec = [
			'def' => __( 'Default', 'simple-smtp' ),
			'ssl' => __( 'SSL', 'simple-smtp' ),
			'tls' => __( 'TLS', 'simple-smtp' ),
		];

		$this->settings_field_generator( 'host', __( 'Host', 'simple-smtp' ), 'text', 'smtp.example.com' );
		$this->settings_field_generator( 'port', __( 'Port', 'simple-smtp' ), 'number', '587' );
		$this->settings_field_generator( 'auth', __( 'Authenticate', 'simple-smtp' ), 'checkbox', '' );
		$this->settings_field_generator( 'user', __( 'Username', 'simple-smtp' ), 'text', 'foobar@example.com' );
		$this->settings_field_generator( 'pass', __( 'Password', 'simple-smtp' ), 'password', '' );
		$this->settings_field_generator( 'from', __( 'Force from', 'simple-smtp' ), 'email', 'do-not-reply@example.com' );
		$this->settings_field_generator( 'fromname', __( 'Force from name', 'simple-smtp' ), 'text', 'WordPress System' );
		$this->settings_field_generator_multiple( 'sec', __( 'Security', 'simple-smtp' ), $sec, 'dropdown' );
		$this->settings_field_generator( 'noverifyssl', __( 'Disable SSL Verification', 'simple-smtp' ), 'checkbox', '', __( 'Do not disable this unless you know what you\'re doing.', 'simple-smtp' ) );
		$this->settings_field_generator( 'log', __( 'Logging', 'simple-smtp' ), 'checkbox', '' );
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
				<input class='regular-text ltr' type='text' name='wpssmtp_test_email_recipient' value='<?php echo esc_attr( wp_get_current_user()->user_email ); ?>'>
				<p class='description'><?php esc_html_e( 'Seperate multiple emails with a semi-colon (;).', 'simple-smtp' ); ?></p>
				<?php
			},
			'wpsimplesmtp_smtp_test',
			'wpsimplesmtp_test_email'
		);

		add_settings_field(
			'wpssmtp_smtp_email_test_type',
			__( 'HTML Mode', 'simple-smtp' ),
			function () {
				?>
				<input type='checkbox' name='wpssmtp_test_email_is_html' value='1'>
				<?php
			},
			'wpsimplesmtp_smtp_test',
			'wpsimplesmtp_test_email'
		);
	}

	/**
	 * Runs post-save setting processes.
	 *
	 * @param array $options Options array.
	 * @return array Parameter #1 with possible changes.
	 */
	public function post_processing( $options ) {
		if ( extension_loaded( 'openssl' ) && '' !== $options['pass'] ) {
			$pass_opt = $this->options->encrypt( 'pass', $options['pass'] );

			$options['pass']   = $pass_opt['string'];
			$options['pass_d'] = $pass_opt['d'];
		}

		$this->reset_encryption_keycheck();

		return $options;
	}

	/**
	 * Generates an generic input box.
	 *
	 * @param string $name        Code name of input.
	 * @param string $name_pretty Name shown to user.
	 * @param string $type        Input element type. Normally 'text'.
	 * @param string $example     Text shown as a placeholder.
	 * @param string $subtext     Text displayed underneath input box.
	 */
	private function settings_field_generator( $name, $name_pretty, $type, $example = '', $subtext = '' ) {
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
	private function settings_field_generator_multiple( $name, $name_pretty, $options, $type, $example = '', $subtext = '' ) {
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
	 * Shows the configuration pane on the current page.
	 */
	private function render_settings() {
		$this->encryption_keycheck();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Mail Settings', 'simple-smtp' ); ?></h1>
			<form id='wpss-conf' action='options.php' method='post'>
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
			submit_button( __( 'Send', 'simple-smtp' ), 'secondary' );

			$log_status = $this->options->get( 'log' );
			if ( ! empty( $log_status ) && true === filter_var( $log_status->value, FILTER_VALIDATE_BOOLEAN ) ) {
				$page = 0;
				if ( isset( $_REQUEST, $_REQUEST['ssnonce'], $_REQUEST['wpss_page'] )
				&& wp_verify_nonce( sanitize_key( $_REQUEST['ssnonce'] ), 'wpss_logtable' )
				&& is_numeric( $_REQUEST['wpss_page'] ) ) {
					$page = intval( wp_unslash( $_REQUEST['wpss_page'] ) );
				}

				echo wp_kses( '<h2>' . __( 'Email Log', 'simple-smtp' ) . '</h2>', [ 'h2' => [] ] );
				$this->log_table->display( $page );
			}
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Checks the encrytion key is valid, if exists.
	 */
	private function encryption_keycheck() {
		if ( ! empty( get_option( 'wpssmtp_echk' ) ) && ! $this->options->check_encryption_key() ) {
			add_option( 'wpssmtp_keycheck_fail', true );
		}
	}

	/**
	 * Resets the encryption warning, if it has been triggered.
	 */
	private function reset_encryption_keycheck() {
		if ( ! empty( get_option( 'wpssmtp_keycheck_fail' ) ) ) {
			$this->options->set_encryption_test();
			delete_option( 'wpssmtp_keycheck_fail' );
		}
	}
}
