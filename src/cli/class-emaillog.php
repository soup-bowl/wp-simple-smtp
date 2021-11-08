<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp\cli;

use wpsimplesmtp\Log;
use wpsimplesmtp\LogService;
use WP_CLI;
use WP_CLI\Utils;

/**
 * View email log via CLI.
 */
class EmailLog {
	/**
	 * SMTP logging.
	 *
	 * @var LogService
	 */
	protected $log_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->log_service = new LogService();
	}

	/**
	 * Grabs a log of emails dispatched from the site.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args       Command-line arguments.
	 * @param array $assoc_args Associated arguments.
	 */
	public function load_log( $args, $assoc_args ) {
		$entries = $this->log_service->get_log_entries( 1, 50 );

		$this->list( $entries );
	}

	/**
	 * Generates a CLI output list of entries derrived from the input.
	 *
	 * @param Log[] $entries Log entry collection.
	 * @return void Prints the log to the page.
	 */
	public function list( $entries ) {
		$list_format = [];
		foreach ( $entries as $entry ) {
			$list_format[] = [
				'ID'       => $entry->get_id(),
				'Subject'  => $entry->get_subject(),
				'Date'     => $entry->get_timestamp(),
				'Response' => '',
			];
		}

		Utils\format_items( 'table', $list_format, [ 'ID', 'Subject', 'Date', 'Response' ] );
	}
}
