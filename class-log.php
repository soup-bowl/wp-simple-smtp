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
	public function create_log_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}wpss_email_log (
		log_id mediumint(9) NOT NULL AUTO_INCREMENT,
		recipient tinytext NOT NULL,
		body text NOT NULL,
		timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (log_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

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
}
