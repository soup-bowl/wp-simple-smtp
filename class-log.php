<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use stdClass;

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
		recipient text NOT NULL,
		subject text NOT NULL,
		body text NOT NULL,
		headers text,
		timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		error text,
		PRIMARY KEY  (log_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Creates a new log entry.
	 *
	 * @param string $recipients The person(s) who recieved the email.
	 * @param string $subject    Subject headline from the email.
	 * @param string $content    Whatever was inside the dispatched email.
	 * @param array  $headers    Email headers served alongside the dispatch.
	 * @param string $timestamp  The time the email was sent.
	 * @param string $error      Any errors encountered during the exchange.
	 * @return integer ID of the newly-inserted entry.
	 */
	public function new_log_entry( $recipients, $subject, $content, $headers, $timestamp, $error = null ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'wpss_email_log',
			[
				'recipient' => $recipients,
				'subject'   => $subject,
				'body'      => $content,
				'headers'   => $headers,
				'timestamp' => $timestamp,
				'error'     => $error,
			]
		);

		return $wpdb->insert_id;
	}

	/**
	 * Updates the provided ID with an error message.
	 *
	 * @param integer $id    ID of the email log entry.
	 * @param string  $error Error message to be stored.
	 * @return boolean Success state.
	 */
	public function log_entry_error( $id, $error ) {
		global $wpdb;

		$upd = $wpdb->update(
			$wpdb->prefix . 'wpss_email_log',
			[
				'error' => $error,
			],
			[
				'log_id' => $id,
			]
		);

		if ( false === $upd ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Gets a single log entry based upon the ID.
	 *
	 * @param integer $id Log ID to retrieve details of.
	 * @return stdClass
	 */
	public function get_log_entry_by_id( $id ) {
		global $wpdb;

		$query = "SELECT log_id, recipient, subject, body, headers, timestamp, error FROM {$wpdb->prefix}wpss_email_log WHERE log_id = {$id}";

		$response = $wpdb->get_results( $query );

		if ( ! empty( $response ) ) {
			return $response[0];
		} else {
			return null;
		}
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

		$query = "SELECT log_id, recipient, subject, body, headers, timestamp, error FROM {$wpdb->prefix}wpss_email_log ORDER BY log_id DESC";
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
		if ( 0 === $count || 0 === $limit ) {
			return 1;
		} else {
			return floor( $count / $limit );
		}
	}
}
