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
		$entries = $this->log->get_log_entries( $page, $maximum_per_page );
		$pages   = $this->log->get_log_entry_pages( $maximum_per_page );

		$labels = [
			__( 'Recipient(s)', 'wpsimplesmtp' ),
			__( 'Subject', 'wpsimplesmtp' ),
			__( 'Body', 'wpsimplesmtp' ),
			__( 'Date', 'wpsimplesmtp' ),
			__( 'Message', 'wpsimplesmtp' ),
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
				$recipients = implode( ', ', unserialize( $entry->recipient ) );
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
		$message = sprintf( __( 'Showing page %1$s of %2$s.', 'wpsimplesmtp' ), $page_cu, $page_co );
		echo wp_kses(
			"<p><i>{$message}</i></p>",
			[
				'p' => [],
				'i' => [],
			]
		);
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
