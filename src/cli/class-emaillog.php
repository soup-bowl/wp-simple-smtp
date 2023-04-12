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
	 * [<page>]
	 * : Page to load up. Defaults to showing the latest.
	 *
	 * [<limit>]
	 * : Define the amount to show up on the page. This will override the 'simple_smtp_log_table_max_per_page' filter.
	 *
	 * [--all]
	 * : Loads the entire log. This can be a slow process on large systems.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args       Command-line arguments.
	 * @param array $assoc_args Associated arguments.
	 */
	public function load_log( $args, $assoc_args ) {
		$page     = ( isset( $args[0] ) && is_numeric( $args[0] ) ) ? (int) $args[0] : 1;
		$load_all = Utils\get_flag_value( $assoc_args, 'all' );

		if ( $load_all ) {
			$maximum_per_page = PHP_INT_MAX;
		} else {
			/**
			 * Overrides the default log limit to show a custom account of entries in the log viewer.
			 *
			 * @param int $maximum_per_page The amount of entries to be shown in the log.
			 */
			$maximum_per_page = (int) apply_filters( 'simple_smtp_log_table_max_per_page', 20 );
			$maximum_per_page = ( isset( $args[1] ) && is_numeric( $args[1] ) ) ? (int) $args[1] : $maximum_per_page;
			$maximum_per_page = ( $maximum_per_page < 1 ) ? 1 : $maximum_per_page;
		}

		$pages = ( $this->log_service->get_log_entry_pages( $maximum_per_page ) + 1 );
		$page  = ( $page < 1 ) ? 1 : $page;
		// translators: %1$s refers to the current page, %2$s is the amount of pages the table has.
		$message = sprintf( __( 'Showing page %1$s of %2$s.', 'simple-smtp' ), $page, $pages );

		$entries = $this->log_service->get_log_entries( $page, $maximum_per_page );

		$this->list_log_entities( $entries );
		if ( empty( $load_all ) ) {
			WP_CLI::line( $message );
		}
	}

	/**
	 * Displays the contents of an email.
	 *
	 * <ID>
	 * : ID of the email you wish to load up.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args       Command-line arguments.
	 * @param array $assoc_args Associated arguments.
	 */
	public function view_email( $args, $assoc_args ) {
		$email = $this->log_service->get_log_entry_by_id( (int) $args[0] );
		if ( ! empty( $email ) ) {
			WP_CLI::line( 'Recipient(s): ' . implode( ', ', $email->get_recipients() ) );
			WP_CLI::line( 'Subject: ' . $email->get_subject() );
			WP_CLI::line( 'Headers: ' . implode( ', ', $email->get_headers() ) );
			WP_CLI::line( 'Contents:' );
			WP_CLI::line( $email->get_body() );
		} else {
			WP_CLI::error( __( 'Email not found.', 'simple-smtp' ) );
		}
	}

	/**
	 * Generates a CLI output list of entries derived from the input.
	 *
	 * @param Log[] $entries Log entry collection.
	 * @return void Prints the log to the page.
	 */
	private function list_log_entities( $entries ) {
		$list_format = [];
		foreach ( $entries as $entry ) {
			$list_format[] = [
				__( 'Mail ID', 'simple-smtp' ) => $entry->get_id(),
				__( 'Subject', 'simple-smtp' ) => $entry->get_subject(),
				__( 'Date', 'simple-smtp' )    => $entry->get_timestamp(),
				__( 'Error', 'simple-smtp' )   => $entry->get_error(),
			];
		}

		Utils\format_items(
			'table',
			$list_format,
			[
				__( 'Mail ID', 'simple-smtp' ),
				__( 'Subject', 'simple-smtp' ),
				__( 'Date', 'simple-smtp' ),
				__( 'Error', 'simple-smtp' ),
			]
		);
	}
}
