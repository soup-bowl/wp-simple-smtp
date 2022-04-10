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
 * Add a custom dashboard widget on index.php.
 */
class Dashboard {
	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function hooks() {
		add_action( 'wp_dashboard_setup', [ &$this, 'add_dashboard_widget' ], 20 );
}

	/**
	 * Add custom Dashboard widget.
	 */
	public function add_dashboard_widget() {
		if ( current_user_can( 'delete_published_posts' ) ) { // Author.
			wp_add_dashboard_widget(
				'smtp_widget_high',
				_x( 'E-mails', 'dashboard widget title', 'simple-smtp' ),
				[ &$this, 'content_dashboard_widget_high' ]
			);
		} else { // Read or below.
			wp_add_dashboard_widget(
				'smtp_widget_low',
				_x( 'E-mails', 'dashboard widget title', 'simple-smtp' ),
				[ &$this, 'content_dashboard_widget_low' ]
			);
		}
	}

	/**
	 * The custom Dashboard widget content.
	 */
	public function content_dashboard_widget_high() {
		$log_enabled = ( new Options() )->get( 'log' );
		$host = ( new Options() )->get( 'host' );
		$port = ( new Options() )->get( 'host' );

		$string  = '<div>';
		$string .= '<h3>' . __( 'Logging', 'simple-smtp' ) . '</h3>';
		$string .= '<ul>';
		if ( ! empty( $log_enabled ) && true === filter_var( $log_enabled->value, FILTER_VALIDATE_BOOLEAN ) ) {
			$string .= '<li>' . __( 'Logging is enabled.', 'simple-smtp' ) . '</li>';
			$string .= '<li>' . __( 'Logs are kept for 30 days.', 'simple-smtp' ) . '</li>';
		} else {
			$string .= '<li>' . __( 'Logging is not enabled.', 'simple-smtp' ) . '</li>';
		}
		$string .= '</ul>';
		$string .= '</div>';
		$string .= '<div>';
		$string .= '<h3>' . __( 'Configuration', 'simple-smtp' ) . '</h3>';
		$string .= '<ul>';
		$string .= '<li>' . __( 'Custom e-mail settings are used.', 'simple-smtp' ) . '</li>';
		$string .= '<li>' . 'sender (name and email address)' . '</li>';
		$string .= '<li>' . $host->value . '</li>';
		$string .= '</ul>';
		$string .= '</div>';

		$string .= '<div class="wpsmtp-dashboard-footer">';
		$string .= '<p>' . '<a href="#">' . __( 'Settings', 'simple-smtp' ) . '</a> | <a href="#">' . __( 'Log', 'simple-smtp' ) . '</a>  | <a href="#">' . __( 'Development', 'simple-smtp' ) . '</a>' . '</p>';
		// $string .= '<p class="wpsmtp-credit">' . __( 'WordPress Simple SMTP - v 1.3.1', 'simple-smtp' ) . '</p>';
		$string .= '</div>';
		echo wp_kses_post( $string );
	}

	/**
	 * The custom Dashboard widget content.
	 */
	public function content_dashboard_widget_low() {
		$string  = '<h2>Low</h2>';
		$string .= '<p>Integer ullamcorper fames placerat quam tortor eleifend faucibus nullam velit tellus consectetur libero luctus nascetur adipiscing vulputate aptent tincidunt dapibus efficitur quis fusce conubia dolor</p>';
		echo wp_kses_post( $string );
	}
}
