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
 * Log object.
 */
class Log {
	protected $ID;
	protected $subject;
	protected $body;
	protected $recipients;
	protected $headers;
	protected $headers_unified;
	protected $attachments;
	protected $error;
	protected $timestamp;

	public function __construct() {
		$this->timestamp = current_time( 'mysql' );
	}

	public function get_id() {
		return $this->ID;
	}

	public function get_subject() {
		return $this->subject;
	}

	public function get_body() {
		return $this->body;
	}

	public function get_recipients() {
		return $this->recipients;
	}

	public function get_headers() {
		return $this->headers;
	}

	public function get_headers_unified() {
		return $this->headers_unified;
	}

	public function get_attachments() {
		return $this->attachments;
	}

	public function get_error() {
		return $this->error;
	}

	public function get_timestamp() {
		return $this->timestamp;
	}

	public function set_id( $id ) {
		$this->ID = $id;

		return $this;
	}

	public function set_subject( $subject ) {
		$this->subject = $subject;

		return $this;
	}

	public function set_body( $body ) {
		$this->body = $body;

		return $this;
	}

	public function set_recipients( $recipients ) {
		$this->recipients = $recipients;

		return $this;
	}

	public function set_headers( $headers ) {
		$this->headers = $headers;

		return $this;
	}

	public function set_headers_unified( $headers_unified ) {
		$this->headers_unified = $headers_unified;

		return $this;
	}

	public function set_attachments( $attachments ) {
		$this->attachments = $attachments;

		return $this;
	}

	public function set_error( $error ) {
		$this->error = $error;

		return $this;
	}

	public function set_timestamp( $timestamp ) {
		$this->timestamp = $timestamp;

		return $this;
	}
}
