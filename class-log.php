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
 * Handles the processing and display of the email log.
 */
class Log {
	/**
	 * Creates the initial table.
	 */
	public function create_log_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpss_email_log (
		log_id mediumint(9) NOT NULL AUTO_INCREMENT,
		recipient tinytext NOT NULL,
		body text NOT NULL,
		timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (log_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Creates a new log entry.
	 *
	 * @param string $recipient The person who recieved the email.
	 * @param string $content   Whatever was inside the dispatched email.
	 * @param string $timestamp The time the email was sent.
	 */
	public function new_log_entry( $recipient, $content, $timestamp ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'wpss_email_log',
			[
				'recipient' => $recipient,
				'body'      => $content,
				'timestamp' => $timestamp,
			]
		);
	}

	/**
	 * Gets the log entries stored. Pagination can be optionally specified.
	 *
	 * @param integer $offset What page to show. Automatically calculated with limit.
	 * @param integer $limit  How many to retrieve in this call.
	 * @return array
	 */
	public function get_log_entries( $offset = 0, $limit = 0 ) {
		global $wpdb;

		$query = "SELECT log_id, recipient, body, timestamp FROM {$wpdb->prefix}wpss_email_log ORDER BY log_id DESC";
		if ( $limit > 0 ) {
			$offset_calc = $offset * $limit;
			$query      .= " LIMIT {$offset_calc}, {$limit}";
		}

		$response = $wpdb->get_results( $query );

		if ( ! empty( $response ) ) {
			return $response;
		} else {
			$this->create_log_table();
			return null;
		}
	}

	/**
	 * Gets the log pagination.
	 *
	 * @param integer $limit How many were retrieved in the call.
	 * @return integer
	 */
	public function get_log_entry_pages( $limit ) {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpss_email_log" );
		if ( $count === 0 || $limit === 0 ) {
			return 1;
		} else {
			return floor( $count / $limit );
		}
	}
}
