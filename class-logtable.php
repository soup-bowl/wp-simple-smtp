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

		echo wp_kses(
			'<table class="wp-list-table widefat fixed striped">
			<thead>
			<th scope="col" class="manage-column column-primary">Recipient</th>
			<th scope="col" class="manage-column">Body</th>
			<th scope="col" class="manage-column">Date</th>
			</thead>
			<tbody>',
			$this->allowed_table_html()
		);

		if ( ! empty( $entries ) ) {
			foreach ( $entries as $entry ) {
				echo wp_kses(
					"<tr>
					<td>{$entry->recipient}</td>
					<td>{$entry->body}</td>
					<td>{$entry->timestamp}</td>
					</tr>",
					$this->allowed_table_html()
				);
			}
		} else {
			echo wp_kses(
				"<tr>
				<td colspan='3'>Nothing to display.</td>
				</tr>",
				$this->allowed_table_html()
			);
		}

		echo wp_kses(
			'</tbody>
			<tfoot>
			<th scope="col" class="manage-column">Recipient</th>
			<th scope="col" class="manage-column">Body</th>
			<th scope="col" class="manage-column">Date</th>
			</tfoot>
			</table>',
			$this->allowed_table_html()
		);

		$page_cu = ( $page + 1 );
		$page_co = ( $pages + 1 );
		echo wp_kses(
			"<p><i>Showing page {$page_cu} of {$page_co}.</i></p>",
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
