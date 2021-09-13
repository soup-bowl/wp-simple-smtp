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
 * Handles the visibility and setup with the WordPress Settings API.
 */
class Privacy {
	/**
	 * SMTP mailer options.
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Stores and retrieves the emails stored in the log.
	 *
	 * @var LogService
	 */
	protected $log_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->options     = new Options();
		$this->log_service = new LogService();
	}
	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function hooks() {
		$log_status = $this->options->get( 'log' );
		if ( ! empty( $log_status ) && true === filter_var( $log_status->value, FILTER_VALIDATE_BOOLEAN ) ) {
			add_filter(
				'wp_privacy_personal_data_erasers',
				function( $erasers ) {
					$erasers['wp-simple-smtp'] = array(
						'eraser_friendly_name' => __( 'Remove user from SMTP log', 'simple-smtp' ),
						'callback'             => [ &$this, 'remove_data' ],
					);

					return $erasers;
				}
			);
		}
	}

	/**
	 * Searches for the requested email address in the mail log.
	 *
	 * @param string  $email_address Email address to lookup.
	 * @param integer $page          Pagination indicator (TODO).
	 * @return array
	 */
	public function remove_data( $email_address, $page = 1 ) {
		$count = $this->log_service->delete_all_logs_to_email( $email_address );

		return [
			'items_removed'  => $count,
			'items_retained' => false,
			'messages'       => [],
			'done'           => true,
		];
	}
}
