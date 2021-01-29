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
use wpsimplesmtp\Log;
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
	 * Stores and retrieves the emails stored in the log.
	 *
	 * @var Log
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
		add_action( 'admin_post_ss_test_email', [ &$this, 'test_email_handler' ] );
		add_filter( 'pre_update_option_wpssmtp_smtp', [ &$this, 'post_processing' ] );

		$this->options   = new Options();
		$this->log       = new Log();
		$this->log_table = new LogTable();
	}

	/**
	 * Intialises the options page.
	 */
	public function options_page() {
		if ( isset( $_REQUEST['ssnonce'], $_REQUEST['delete_all'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['ssnonce'] ), 'wpss_purgelog' ) ) {
			$this->log->delete_all_logs();

			wp_die( esc_attr_e( 'The log has been cleared.', 'simple-smtp' ) );
		}

		$return = false;
		if ( isset( $_REQUEST['ssnonce'], $_REQUEST['eid'], $_REQUEST['resend'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['ssnonce'] ), 'wpss_action' ) ) {
			$return = true;
			$resp   = $this->resend_email( intval( $_REQUEST['eid'] ) );
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
			$resp   = $this->log->delete_log_entry( intval( $_REQUEST['eid'] ) );
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
			$this->render_email_view( intval( $_REQUEST['eid'] ) );
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
	 * Custom admin endpoint to dispatch a test email.
	 */
	public function test_email_handler() {
		if ( isset( $_REQUEST['_wpnonce'], $_REQUEST['_wp_http_referer'], $_REQUEST['wpssmtp_test_email_recipient'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'simple-smtp-test-email' ) ) {
			$is_html      = ( isset( $_REQUEST['wpssmtp_test_email_is_html'] ) ) ? true : false;
			$content_type = ( $is_html ) ? 'Content-Type: text/html' : 'Content-Type: text/plain';
			$content      = __( 'This email proves that your settings are correct.', 'simple-smtp' ) . PHP_EOL . get_bloginfo( 'url' );

			if ( $is_html ) {
				$html_email  = '<body>';
				$html_email .= '<div style="text-align: center;margin-top: 5%;font-size: 4em;">' . __( '&#9989;', 'simple-smtp' ) . '</div>';
				$html_email .= '<h1 style="font-family: sans-serif;text-align: center;font-size: 4em;">' . __( 'This is a test email', 'simple-smtp' ) . '</h1>';
				$html_email .= '<p style="font-family: sans-serif;text-align: center;font-size: 1em;">' . $content . '</p>';
				$html_email .= '</body>';

				$content = wp_kses_post( $html_email );
			}

			// Sanitize rule disabled here as it doesn't detect the later sanitize call. Feel free to refactor.
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$recipients = explode( ';', wp_unslash( $_REQUEST['wpssmtp_test_email_recipient'] ) );
			$recp_count = count( $recipients );
			// phpcs:enable
			for ( $i = 0; $i < $recp_count; $i++ ) {
				$recipients[ $i ] = sanitize_email( trim( $recipients[ $i ] ) );
			}

			wp_mail(
				$recipients,
				// translators: %s is the website name.
				sprintf( __( 'Test email from %s', 'simple-smtp' ), get_bloginfo( 'name' ) ),
				$content,
				[ 'x-test: WP SMTP', $content_type ]
			);

			wp_safe_redirect( admin_url( 'options-general.php?page=wpsimplesmtp' ) );
			exit;
		} else {
			wp_die( esc_attr_e( 'You are not permitted to send a test email.', 'simple-smtp' ) );
		}
	}

	/**
	 * Resends an email.
	 *
	 * @param integer $email_id Email/log ID to resend.
	 * @return boolean
	 */
	public function resend_email( $email_id ) {
		$email      = $this->log->get_log_entry_by_id( $email_id );
		$recipients = implode( ', ', json_decode( get_post_meta( $email->ID, 'recipients', true ) ) );
		$headers    = json_decode( get_post_meta( $email->ID, 'headers', true ) );
		$opts       = get_option( 'wpss_resent', [] );

		if ( isset( $email ) && ! in_array( $email_id, $opts, true ) ) {
			$opts[] = $email_id;
			update_option( 'wpss_resent', $opts );

			wp_mail(
				$recipients,
				$email->post_title,
				$email->post_content,
				$headers
			);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Runs post-save setting processes.
	 *
	 * @param array $options Options array.
	 * @return array Parameter #1 with possible changes.
	 */
	public function post_processing( $options ) {
		if ( extension_loaded( 'openssl' ) ) {
			$pass_opt = $this->options->encrypt( 'pass', $options['pass'] );

			$options['pass']   = $pass_opt['string'];
			$options['pass_d'] = $pass_opt['d'];
		}

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
			submit_button( __('Send', 'simple-smtp' ), 'secondary' );

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
	 * Render the email with useful information.
	 *
	 * @param integer $id Email log ID.
	 * @return void Prints to page.
	 */
	private function render_email_view( $id ) {
		$log        = $this->log->get_log_entry_by_id( $id );
		$recset     = ( in_array( (int) $id, get_option( 'wpss_resent', [] ), true ) ) ? ' disabled' : '';
		$resend_url = add_query_arg(
			[
				'eid'     => $id,
				'ssnonce' => wp_create_nonce( 'wpss_action' ),
			],
			menu_page_url( 'wpsimplesmtp', false )
		) . '&resend';

		if ( current_user_can( 'administrator' ) && isset( $log ) ) {
			$recipients = implode( ', ', json_decode( get_post_meta( $log->ID, 'recipients', true ) ) );
			$date       = gmdate( get_option( 'time_format' ) . ', ' . get_option( 'date_format' ), strtotime( get_post_meta( $log->ID, 'timestamp', true ) ) );

			$content = '';
			if ( isset( $log->headers ) && false !== strpos( $log->headers, 'Content-Type: text\/html' ) ) {
				$content = wp_kses_post( $log->post_content );
			} else {
				$content = wp_kses_post( '<pre>' . $log->post_content . '</pre>' );
			}
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'View Email', 'simple-smtp' ); ?></h1>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">
							<div class="postbox">
								<h2 class="hndle"><?php echo esc_html( $log->post_title ); ?></h2>			
								<div class="inside">
									<?php echo wp_kses_post( $content ); ?>
								</div>	
							</div>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="stuffbox">
								<h2 class="hndle"><?php esc_html_e( 'Information', 'simple-smtp' ); ?></h2>
								<div class="inside">
									<div id="minor-publishing">
										<div id="misc-publishing-actions">
											<div class="misc-pub-section"><?php esc_html_e( 'Recipient(s)', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $recipients ); ?></strong></div>
											<div class="misc-pub-section"><?php esc_html_e( 'Date sent', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $date ); ?></strong></div>
										</div>
										<div class="clear"></div>
									</div>
									<div id="major-publishing-actions">
										<div id="publishing-action">
											<a href="<?php echo esc_html( $resend_url ); ?>" class="button button-primary button-large <?php echo esc_attr( $recset ); ?>"><?php esc_html_e( 'Resend', 'simple-smtp' ); ?></a>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
			<?php
		} else {
			wp_die( 'No email found.' );
		}
	}
}
