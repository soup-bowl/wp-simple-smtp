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
			__( 'Body', 'wpsimplesmtp' ),
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
			<th scope=\"col\" class=\"manage-column\">{$labels[4]}</th>
			</thead>
			<tbody>",
			$this->allowed_table_html()
		);

		if ( ! empty( $entries ) ) {
			foreach ( $entries as $entry ) {
				$recipients = implode( ', ', json_decode( $entry->recipient ) );
				echo wp_kses(
					"<tr>
					<td>{$recipients}</td>
					<td>{$entry->subject}</td>
					<td>{$entry->body}</td>
					<td>{$entry->timestamp}</td>
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
			<th scope=\"col\" class=\"manage-column\">{$labels[4]}</th>
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
				'colspan' => [],
			],
		];
	}
}
