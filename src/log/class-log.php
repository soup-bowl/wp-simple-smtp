<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\LogAttachment;

/**
 * Log object.
 */
class Log {
	/**
	 * WordPress post ID.
	 *
	 * @var integer
	 */
	protected $ID;

	/**
	 * Email subject line.
	 *
	 * @var string
	 */
	protected $subject;

	/**
	 * Email contents.
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Recipients.
	 *
	 * @var array
	 */
	protected $recipients;

	/**
	 * Endpoint headers.
	 *
	 * @var string[]
	 */
	protected $headers;

	/**
	 * Endpoint headers - not split.
	 *
	 * @var string
	 */
	protected $headers_unified;

	/**
	 * Attachments.
	 *
	 * @var LogAttachment|null
	 */
	protected $attachments;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * Timestamp.
	 *
	 * @var string
	 */
	protected $timestamp;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->timestamp = current_time( 'mysql' );
	}

	/**
	 * Gets the post ID.
	 *
	 * @return integer
	 */
	public function get_id() {
		return $this->ID;
	}

	/**
	 * Gets the subject line.
	 *
	 * @return string
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * Gets the body content.
	 *
	 * @return string
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Gets an array of 'To' recipients.
	 *
	 * @return array
	 */
	public function get_recipients() {
		return $this->recipients;
	}

	/**
	 * Gets the from details.
	 *
	 * @return string|null
	 */
	public function get_from() {
		return $this->find_in_headers( 'from' );
	}

	/**
	 * Gets the cc recipients.
	 *
	 * @return string|null
	 */
	public function get_cc() {
		return $this->find_in_headers( 'cc' );
	}

	/**
	 * Gets the bcc recipients.
	 *
	 * @return string|null
	 */
	public function get_bcc() {
		return $this->find_in_headers( 'bcc' );
	}

	/**
	 * Gets the server dispatch headers.
	 *
	 * @return string[]
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Same as get_headers, but the header strings are split.
	 *
	 * @param bool $exclude_recipients Remove CC from the list.
	 * @return array[]
	 */
	public function get_headers_as_array( $exclude_recipients = true ) {
		$collection = [];
		if ( ! empty( $this->get_headers() ) ) {
			foreach ( $this->get_headers() as $header ) {
				$expd = explode( ':', $header );
				if ( $exclude_recipients && in_array( strtolower( $expd[0] ), [ 'cc', 'bcc', 'from' ], true ) ) {
					continue;
				} else {
					$collection[] = $expd;
				}
			}
		}

		return $collection;
	}

	/**
	 * The dispatch headers, unsplit.
	 *
	 * @return string
	 */
	public function get_headers_unified() {
		return $this->headers_unified;
	}

	/**
	 * Gets the attachment references.
	 *
	 * @return LogAttachment[]|null
	 */
	public function get_attachments() {
		return $this->attachments;
	}

	/**
	 * Gets the error message, if applicable.
	 *
	 * @return string
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Gets the timestamp.
	 *
	 * @return string
	 */
	public function get_timestamp() {
		return $this->timestamp;
	}

	/**
	 * Sets the post ID.
	 *
	 * @param integer $id ID.
	 * @return self
	 */
	public function set_id( $id ) {
		$this->ID = $id;

		return $this;
	}

	/**
	 * Sets the subject line.
	 *
	 * @param string $subject Subject.
	 * @return self
	 */
	public function set_subject( $subject ) {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Sets the body content.
	 *
	 * @param string $body Body, either html or plaintext.
	 * @return self
	 */
	public function set_body( $body ) {
		$this->body = $body;

		return $this;
	}

	/**
	 * Sets an array of 'To' recipients.
	 *
	 * @param array $recipients Recipient list.
	 * @return self
	 */
	public function set_recipients( $recipients ) {
		$this->recipients = $recipients;

		return $this;
	}

	/**
	 * Sets the server dispatch headers.
	 *
	 * @param string[] $headers Headers array.
	 * @return self
	 */
	public function set_headers( $headers ) {
		$this->headers = $headers;

		return $this;
	}

	/**
	 * The dispatch headers, unsplit.
	 *
	 * @param string $headers_unified Headers unified.
	 * @return self
	 */
	public function set_headers_unified( $headers_unified ) {
		$this->headers_unified = $headers_unified;

		return $this;
	}

	/**
	 * Sets the attachment references.
	 *
	 * @param LogAttachment[] $attachments Attachment objects.
	 * @return self
	 */
	public function set_attachments( $attachments ) {
		$this->attachments = $attachments;

		return $this;
	}

	/**
	 * Sets the error message, if applicable.
	 *
	 * @param string $error Error.
	 * @return self
	 */
	public function set_error( $error ) {
		$this->error = $error;

		return $this;
	}

	/**
	 * Sets the timestamp.
	 *
	 * @param string $timestamp TS.
	 * @return self
	 */
	public function set_timestamp( $timestamp ) {
		$this->timestamp = $timestamp;

		return $this;
	}

	/**
	 * Searches the header array for a particular header.
	 *
	 * @param string $needle Header to look for.
	 * @return string[]
	 */
	private function find_in_headers( $needle ) {
		$collection = [];
		foreach ( $this->get_headers_as_array( false ) as $header ) {
			if ( strtolower( $header[0] ) === strtolower( $needle ) ) {
				$collection[] = $header[1];
			}
		}

		return $collection;
	}

	/**
	 * Extracts the email from angled brackets, if the syntax is so.
	 *
	 * @param string $input The subject to be inspected.
	 * @return string Either the extracted email address, or the input is returned untouched.
	 */
	private function strip_email( $input ) {
		$stripped = '';
		$rc       = preg_match( '/(?<=\<).+?(?=\>)/', $input, $stripped );

		if ( 1 === $rc ) {
			return $stripped;
		} else {
			return $input;
		}
	}
}
