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
 * Object representation of an email system attachment.
 */
class LogAttachment {
	/**
	 * File location on the filesystem.
	 *
	 * @var string
	 */
	protected $location;

	/**
	 * Whether or not the supplied filepath actually exists.
	 *
	 * @var boolean
	 */
	protected $exists;

	/**
	 * File name.
	 *
	 * @var string
	 */
	protected $filename;

	public function __construct( $location ) {
		if ( file_exists( $location ) ) {
			$this->exists = true;
		} else {
			$this->exists = false;
		}
	}

	public function exists() {
		return $this->exists;
	}
}
