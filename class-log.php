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
use WP_Query;

/**
 * Handles the processing and display of the email log.
 */
class Log {
	/**
	 * Name of the custom post type used for storing logs.
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->post_type = 'sbss_email_log';
	}

	/**
	 * Register the log storage CPT within WordPress.
	 */
	public function register_log_storage() {
		register_post_type( $this->post_type );
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
		$post_id = wp_insert_post(
			[
				'post_title'   => $subject,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => $this->post_type,
				'meta_input'   => [
					'recipients' => $recipients,
					'headers'    => $headers,
					'timestamp'  => $timestamp,
					'error'      => $error,
				],
			]
		);

		return $post_id;
	}

	/**
	 * Updates the provided ID with an error message.
	 *
	 * @param integer $id    ID of the email log entry.
	 * @param string  $error Error message to be stored.
	 * @return void
	 */
	public function log_entry_error( $id, $error ) {
		update_post_meta( $id, 'error', $error );
	}

	/**
	 * Gets a single log entry based upon the ID.
	 *
	 * @param integer $id Log ID to retrieve details of.
	 * @return stdClass
	 */
	public function get_log_entry_by_id( $id ) {
		return get_post( $id );
	}

	/**
	 * Gets the log entries stored. Pagination can be optionally specified.
	 *
	 * @param integer $page  What page to show. Automatically calculated with limit.
	 * @param integer $limit How many to retrieve in this call.
	 * @return array
	 */
	public function get_log_entries( $page = 0, $limit = 0 ) {
		$get_posts = new WP_Query();
		$get_posts->query(
			[
				'post_type'      => $this->post_type,
				'posts_per_page' => $limit,
				'paged'          => $page,
			]
		);

		return $get_posts->get_posts();
	}

	/**
	 * Gets the log pagination.
	 *
	 * @param integer $limit How many were retrieved in the call.
	 * @return integer
	 */
	public function get_log_entry_pages( $limit ) {
		$count = (int) wp_count_posts( $this->post_type )->publish;

		if ( false !== $count ) {
			return floor( $count / $limit );
		} else {
			return 1;
		}
	}

	/**
	 * Deletes a log entry.
	 *
	 * @param integer $id WordPress post ID.
	 * @return boolean
	 */
	public function delete_log_entry( $id ) {
		$post = get_post( $id );

		if ( $this->post_type === $post->post_type ) {
			$r = wp_delete_post( $id );
			if ( ! empty( $r ) || false !== $r ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Deletes all log entries.
	 *
	 * @return boolean
	 */
	public function delete_all_logs() {
		$all = get_posts(
			array(
				'post_type'   => $this->post_type,
				'numberposts' => -1,
			)
		);

		foreach ( $all as $log ) {
			wp_delete_post( $log->ID );
		}

		return true;
	}
}
