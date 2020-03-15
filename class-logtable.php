<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\Log;

/**
 * Handles the creation and display of the email log table.
 */
class LogTable {
	/**
	 * SMTP logging.
	 *
	 * @var Log
	 */
	protected $log;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->log = new Log();
	}

	/**
	 * Displays the log table.
	 *
	 * @param integer $page             The page to be displayed.
	 * @param integer $maximum_per_page Limits the table display.
	 */
	public function display( $page, $maximum_per_page = 5 ) {
		$page    = ( $page < 0 ) ? 0 : $page;
		$entries = $this->log->get_log_entries( $page, $maximum_per_page );
		$pages   = $this->log->get_log_entry_pages( $maximum_per_page );

		$labels = [
			__( 'Recipient(s)', 'wpsimplesmtp' ),
			__( 'Subject', 'wpsimplesmtp' ),
			__( 'Date', 'wpsimplesmtp' ),
			__( 'Error', 'wpsimplesmtp' ),
		];

		echo wp_kses(
			"<table class=\"wp-list-table widefat fixed striped\">
			<thead>
			<th scope=\"col\" class=\"manage-column column-primary\">{$labels[0]}</th>
			<th scope=\"col\" class=\"manage-column\">{$labels[1]}</th>
			<th scope=\"col\" class=\"manage-column\">{$labels[2]}</th>
			<th scope=\"col\" class=\"manage-column\">{$labels[3]}</th>
			</thead>
			<tbody>",
			$this->allowed_table_html()
		);

		if ( ! empty( $entries ) ) {
			foreach ( $entries as $entry ) {
				$recipients  = implode( ', ', json_decode( $entry->recipient ) );
				$view_url    = esc_html( add_query_arg( 'eid', $entry->log_id, menu_page_url( 'wpsimplesmtp', false ) ) );
				$resend_url  = $view_url . '&resend';
				$row_actions = "<div class=\"row-actions\"><span class=\"view\"><a href=\"{$view_url}\" aria-label=\"View\">View</a> | <span class=\"view\"><a href=\"{$resend_url}\" aria-label=\"View\">Resend</a></div>";

				$date = date( get_option( 'time_format' ) . ', ' . get_option( 'date_format' ), strtotime( $entry->timestamp ) );
				echo wp_kses(
					"<tr>
					<td class=\"has-row-actions\">{$recipients}{$row_actions}</td>
					<td>{$entry->subject}</td>
					<td><abbr title=\"{$entry->timestamp}\">{$date}</abbr></td>
					<td>{$entry->error}</td>
					</tr>",
					$this->allowed_table_html()
				);
			}
		} else {
			echo wp_kses(
				sprintf(
					'<tr><td colspan="3">%s</td></tr>',
					__( 'Nothing to display.', 'wpsimplesmtp' )
				),
				$this->allowed_table_html()
			);
		}

		echo wp_kses(
			"</tbody>
			<tfoot>
			<th scope=\"col\" class=\"manage-column\">{$labels[0]}</th>
			<th scope=\"col\" class=\"manage-column\">{$labels[1]}</th>
			<th scope=\"col\" class=\"manage-column\">{$labels[2]}</th>
			<th scope=\"col\" class=\"manage-column\">{$labels[3]}</th>
			</tfoot>
			</table>",
			$this->allowed_table_html()
		);

		$page_cu = ( $page + 1 );
		$page_co = ( $pages + 1 );
		// translators: %1$s refers to the current page, %2$s is the amount of pages the table has.
		$message     = sprintf( __( 'Showing page %1$s of %2$s.', 'wpsimplesmtp' ), $page_cu, $page_co );
		$nav_buttons = $this->generate_table_buttons( $page, $pages );
		echo wp_kses(
			"<p><i>{$message}</i> {$nav_buttons->back} {$nav_buttons->next}</p>",
			[
				'p' => [],
				'i' => [],
				'a' => [
					'href'     => [],
					'class'    => [],
					'disabled' => [],
				],
			]
		);
	}

	/**
	 * Displays the email data for one email. Output is permission checked before return.
	 *
	 * @param integer $log_id The DB log ID of the email to display.
	 */
	public function display_email( $log_id ) {
		$email = $this->log->get_log_entry_by_id( $log_id );

		if ( current_user_can( 'administrator' ) && isset( $email ) ) {
			$ksa = $this->allowed_email_disp();

			echo wp_kses( "<h2>{$email->subject}</h2>", $ksa );

			$recipients = implode( ', ', json_decode( $email->recipient ) );
			$date       = date( get_option( 'time_format' ) . ', ' . get_option( 'date_format' ), strtotime( $email->timestamp ) );
			echo wp_kses( '<p><strong>' . __( 'Recipient(s)', 'wpsimplesmtp' ) . ": </strong>{$recipients}</p>", $ksa );
			echo wp_kses( '<p><strong>' . __( 'Sent date', 'wpsimplesmtp' ) . ": </strong>{$date}</p>", $ksa );


			if ( isset( $email->headers ) && false !== strpos( $email->headers, 'Content-Type: text\/html' ) ) {
				echo wp_kses_post( $email->body );
			} else {
				echo wp_kses_post( '<pre>' . $email->body . '</pre>' );
			}
		} else {
			echo 'No email found.';
		}
	}

	/**
	 * Postback navigations for the table.
	 *
	 * @param integer $current_page The current page (system, not pretty).
	 * @param integer $max_pages    How many pages the table has to show.
	 * @return stdClass 'next' and 'back', HTML buttons.
	 */
	private function generate_table_buttons( $current_page, $max_pages ) {
		$next_label = __( 'Next', 'wpsimplesmtp' );
		$back_label = __( 'Previous', 'wpsimplesmtp' );
		$current    = admin_url( 'options-general.php?page=wpsimplesmtp' );
		$next_url   = add_query_arg( 'wpss_page', ( $current_page + 1 ), $current );
		$back_url   = add_query_arg( 'wpss_page', ( $current_page - 1 ), $current );
		$next_allow = ( $current_page >= $max_pages ) ? 'disabled' : '';
		$back_allow = ( $current_page <= 0 ) ? 'disabled' : '';

		return (object) [
			'next' => "<a href='{$next_url}' class='button' {$next_allow}>{$next_label}</a>",
			'back' => "<a href='{$back_url}' class='button' {$back_allow}>{$back_label}</a>",
		];
	}

	/**
	 * Array for kses that allows table-related HTML only.
	 *
	 * @return array
	 */
	private function allowed_table_html() {
		return [
			'table' => [
				'class' => [],
			],
			'thead' => [],
			'tfoot' => [],
			'tbody' => [],
			'th'    => [
				'scope' => [],
				'class' => [],
			],
			'tr'    => [],
			'td'    => [
				'class'   => [],
				'colspan' => [],
			],
			'div'   => [
				'class' => [],
			],
			'span'  => [
				'class' => [],
			],
			'a'     => [
				'href' => [],
			],
			'abbr'  => [
				'title' => [],
			],
		];
	}

	/**
	 * Allowed HTML from displaying additional details.
	 *
	 * @return array
	 */
	private function allowed_email_disp() {
		return [
			'p'      => [],
			'h2'     => [],
			'strong' => [],
		];
	}
}
