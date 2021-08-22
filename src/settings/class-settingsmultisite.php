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
 * Display and control settings for WordPress Multisite instances.
 */
class SettingsMultisite {
	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function __construct() {
		add_action( 'network_admin_menu', [ &$this, 'add_network_menu' ] );
		add_action( 'admin_init', [ &$this, 'network_settings_init' ] );
		add_action( 'network_admin_edit_wpsimplesmtpms', [ &$this, 'update_network_settings' ] );
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
			__( 'Site Administration Control', 'simple-smtp' ),
			function () {
				esc_html_e( 'Decide if mail configuration is accessible only to super administrators.', 'simple-smtp' );
			},
			'wpsimplesmtp_smtp_ms'
		);

		add_settings_field(
			'wpssmtp_smtp_siteselection',
			__( 'Site Administration Control', 'simple-smtp' ),
			function () {
				$collection = [];

				$sites = get_sites();
				foreach ( $sites as $site ) {
					$collection[] = [
						'id'       => $site->blog_id,
						'url'      => $site->domain . $site->path,
						'settings' => add_query_arg( [ 'page' => 'wpsimplesmtp' ], $site->domain . $site->path . 'wp-admin/options-general.php?' ),
						'no_set'   => get_network_option( $site->blog_id, 'wpssmtp_disable_settings', 0 ),
						'no_log'   => get_network_option( $site->blog_id, 'wpssmtp_disable_logging', 0 ),
					];
				}

				?>
				<table class="wp-list-table widefat striped">
					<thead>
						<tr>
							<th>Site</th>
							<th>Disable Settings</th>
							<th>Disable Logging</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $collection as $site ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $site['settings'] ); ?>"><?php echo esc_url( $site['url'] ); ?></a></td>
								<td><input type='checkbox' name='wpssmtp_perm_set_s<?php echo (int) $site['id']; ?>' <?php checked( $site['no_set'], 1 ); ?> value='1'></td>
								<td><input type='checkbox' name='wpssmtp_perm_log_s<?php echo (int) $site['id']; ?>' <?php checked( $site['no_log'], 1 ); ?> value='1'></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
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

		$sites = get_sites();
		foreach ( $sites as $site ) {
			$id      = $site->blog_id;
			$set_val = ( isset( $_REQUEST[ "wpssmtp_perm_set_s{$id}" ] ) && '1' === $_REQUEST[ "wpssmtp_perm_set_s{$id}" ] ) ? 1 : 0;
			$log_val = ( isset( $_REQUEST[ "wpssmtp_perm_log_s{$id}" ] ) && '1' === $_REQUEST[ "wpssmtp_perm_log_s{$id}" ] ) ? 1 : 0;

			update_network_option( $site->blog_id, 'wpssmtp_disable_settings', $set_val );
			update_network_option( $site->blog_id, 'wpssmtp_disable_logging', $log_val );
		}

		wp_safe_redirect( admin_url( 'network/settings.php?page=wpsimplesmtpms' ) );
		exit;
	}
}
