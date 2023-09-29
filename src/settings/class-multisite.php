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

/**
 * Display and control settings for WordPress Multisite instances.
 */
class Multisite extends Settings {
	/**
	 * SMTP mailer options.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		parent::__construct( true, 'wpsimplesmtp_smtp_ms', 'wpsimplesmtp_ms_adminaccess_section' );

		add_action( 'network_admin_menu', [ &$this, 'add_network_menu' ] );
		add_action( 'admin_init', [ &$this, 'network_settings_init' ] );
		add_action( 'network_admin_edit_wpsimplesmtpms', [ &$this, 'update_network_settings' ] );

		$this->options = new Options();
	}

	/**
	 * Registers the 'Network Mail' setting underneath 'Settings' in the multisite administration GUI.
	 */
	public function add_network_menu() {
		add_submenu_page(
			'settings.php',
			__( 'Network Mail', 'simple-smtp' ),
			__( 'Network Mail', 'simple-smtp' ),
			'manage_network_options',
			'wpsimplesmtpms',
			[ &$this, 'options_page' ]
		);
	}

	/**
	 * Intialises the options page.
	 */
	public function options_page() {
		$this->render_settings();
	}

	/**
	 * Initialises the settings implementation.
	 */
	public function network_settings_init() {
		register_setting( 'wpsimplesmtp_smtp_ms', 'wpssmtp_smtp_ms' );

		add_settings_section(
			'wpsimplesmtp_ms_adminaccess_section',
			__( 'Global Administration Settings', 'simple-smtp' ),
			function () {
				esc_html_e( 'Configurations set here will overwrite any local site settings.', 'simple-smtp' );
			},
			'wpsimplesmtp_smtp_ms'
		);

		$this->generate_generic_field( 'host', __( 'Host', 'simple-smtp' ), 'text', 'smtp.example.com' );
		$this->generate_generic_field( 'port', __( 'Port', 'simple-smtp' ), 'number', '587' );
		$this->generate_unique_checkbox( 'auth', __( 'Authenticate', 'simple-smtp' ), __( 'Authenticate connection with username and password', 'simple-smtp' ) );
		$this->generate_generic_field( 'user', __( 'Username', 'simple-smtp' ), 'text', 'foobar@example.com' );
		$this->generate_generic_field( 'pass', __( 'Password', 'simple-smtp' ), 'password' );
		$this->generate_generic_field( 'from', __( 'Force from e-mail address', 'simple-smtp' ), 'email', 'do-not-reply@example.com' );
		$this->generate_generic_field( 'fromname', __( 'Force from e-mail sender name', 'simple-smtp' ), 'text', _x( 'WordPress System', 'Force from e-mail sender name', 'simple-smtp' ) );
		$this->generate_selection( 'sec', __( 'Security', 'simple-smtp' ), $this->acceptable_security_types(), __( 'Disabling this may risk email security.', 'simple-smtp' ) );
		$this->generate_checkbox_area(
			'adt',
			__( 'Options', 'simple-smtp' ),
			function() {
				$this->generate_checkbox( 'disable', __( 'Disable email services', 'simple-smtp' ), __( 'When marked, all multisite email services will be disabled.', 'simple-smtp' ) );
				$this->generate_checkbox( 'log', __( 'Log all sent emails to the database', 'simple-smtp' ), __( 'Works with the WordPress privacy features.', 'simple-smtp' ) );
				$this->generate_checkbox( 'noverifyssl', __( 'Disable SSL Verification (advanced)', 'simple-smtp' ), __( 'Do not disable this unless you know what you\'re doing.', 'simple-smtp' ) );
			}
		);

		add_settings_field(
			'wpssmtp_smtp_siteselection',
			__( 'Site Administration Control', 'simple-smtp' ),
			function () {
				$collection = [];
				$sites      = get_sites();

				foreach ( $sites as $site ) {
					$site_details = get_blog_details( array( 'blog_id' => $site->blog_id ) );
					$url          = $site_details->siteurl;
					$name         = $site_details->blogname;
					$string       = \sprintf(
						// translators: Tooltip to clarify to the user clicking the link will take them to the child SMTP settings.
						_x( 'Go to settings for %s', 'Sub site name', 'simple-smtp' ),
						$name
					);
					$collection[] = [
						'id'       => $site->blog_id,
						'url'      => $url,
						'string'   => $string,
						'name'     => $name,
						'settings' => add_query_arg( [ 'page' => 'wpsimplesmtp' ], get_admin_url( $site->blog_id ) . 'options-general.php' ),
						'no_set'   => get_network_option( $site->blog_id, 'wpssmtp_disable_settings', 0 ),
						'no_log'   => get_network_option( $site->blog_id, 'wpssmtp_disable_logging', 0 ),
					];
				}

				?>
				<table class="wp-list-table widefat striped wpsmtp-multisite-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Site', 'simple-smtp' ); ?></th>
							<th><?php esc_html_e( 'Disable Settings', 'simple-smtp' ); ?></th>
							<th><?php esc_html_e( 'Disable Logging', 'simple-smtp' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $collection as $site ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $site['settings'] ); ?>" title="<?php echo esc_attr( $site['string'] ); ?>"><?php echo esc_attr( $site['name'] ); ?></a><br /><?php echo esc_url( $site['url'] ); ?></td>
								<td><input type='checkbox' name='wpssmtp_perm_set_s<?php echo (int) $site['id']; ?>' <?php checked( $site['no_set'], 1 ); ?> value='1'></td>
								<td><input type='checkbox' name='wpssmtp_perm_log_s<?php echo (int) $site['id']; ?>' <?php checked( $site['no_log'], 1 ); ?> value='1'></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="description"><?php esc_html_e( 'Hide aspects from local site administrators. Super administrators will still be able to see settings.', 'simple-smtp' ); ?></p>
				<?php
			},
			'wpsimplesmtp_smtp_ms',
			'wpsimplesmtp_ms_adminaccess_section'
		);
	}

	/**
	 * Shows the configuration pane on the current page.
	 */
	private function render_settings() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Network Mail', 'simple-smtp' ); ?></h1>
			<form action='edit.php?action=wpsimplesmtpms' method='post'>	
				<?php
				wp_nonce_field( 'simple-smtp-ms' );
				do_settings_sections( 'wpsimplesmtp_smtp_ms' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Retrieves the settings page when the administrator has sent changed settings.
	 */
	public function update_network_settings() {
		if ( isset( $_REQUEST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'simple-smtp-ms' ) ) {
			wp_die( esc_attr_e( 'Your nonce key has expired.', 'simple-smtp' ) );
		}

		if ( ! is_multisite() && ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_attr_e( 'You do not have permission to use this endpoint.', 'simple-smtp' ) );
		}

		// Save per-site configurations for access.
		$sites = get_sites();
		foreach ( $sites as $site ) {
			$id      = $site->blog_id;
			$set_val = ( isset( $_REQUEST[ "wpssmtp_perm_set_s{$id}" ] ) && '1' === $_REQUEST[ "wpssmtp_perm_set_s{$id}" ] ) ? 1 : 0;
			$log_val = ( isset( $_REQUEST[ "wpssmtp_perm_log_s{$id}" ] ) && '1' === $_REQUEST[ "wpssmtp_perm_log_s{$id}" ] ) ? 1 : 0;

			update_network_option( $site->blog_id, 'wpssmtp_disable_settings', $set_val );
			update_network_option( $site->blog_id, 'wpssmtp_disable_logging', $log_val );
		}

		// Save over-ruling SMTP configurations.
		if ( isset( $_REQUEST['wpssmtp_smtp'] ) ) {
			$settings = [];
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['host'] ) ) {
				$settings['host'] = sanitize_text_field( wp_unslash( $_REQUEST['wpssmtp_smtp']['host'] ) ); }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['port'] ) ) {
				$settings['port'] = (int) $_REQUEST['wpssmtp_smtp']['port']; }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['auth'] ) ) {
				$settings['auth'] = (int) $_REQUEST['wpssmtp_smtp']['auth']; }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['user'] ) ) {
				$settings['user'] = sanitize_text_field( wp_unslash( $_REQUEST['wpssmtp_smtp']['user'] ) ); }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['pass'] ) ) {
				if ( $this->dummy_password === $_REQUEST['wpssmtp_smtp']['pass'] ) {
					$settings['pass'] = get_site_option( 'wpssmtp_smtp_ms', [ 'pass' => null ] )['pass'];
				} else {
					$settings['pass'] = sanitize_text_field( wp_unslash( $_REQUEST['wpssmtp_smtp']['pass'] ) );
				}
			}
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['from'] ) ) {
				$settings['from'] = sanitize_email( wp_unslash( $_REQUEST['wpssmtp_smtp']['from'] ) ); }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['fromname'] ) ) {
				$settings['fromname'] = sanitize_text_field( wp_unslash( $_REQUEST['wpssmtp_smtp']['fromname'] ) ); }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['sec'] ) && 'def' !== $_REQUEST['wpssmtp_smtp']['sec'] ) {
				$settings['sec'] = sanitize_text_field( wp_unslash( $_REQUEST['wpssmtp_smtp']['sec'] ) ); }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['noverifyssl'] ) ) {
				$settings['noverifyssl'] = (int) $_REQUEST['wpssmtp_smtp']['noverifyssl']; }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['disable'] ) ) {
				$settings['disable'] = (int) $_REQUEST['wpssmtp_smtp']['disable']; }
			if ( ! empty( $_REQUEST['wpssmtp_smtp']['log'] ) ) {
				$settings['log'] = (int) $_REQUEST['wpssmtp_smtp']['log']; }

			update_site_option( 'wpssmtp_smtp_ms', $settings );
		}

		// Done with saving - send them back.
		wp_safe_redirect( network_admin_url( 'settings.php?page=wpsimplesmtpms' ) );
		exit;
	}
}
