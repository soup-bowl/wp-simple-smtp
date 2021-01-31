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
	 * File name including extension.
	 *
	 * @var string
	 */
	protected $basename;

	/**
	 * File name.
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * File extension.
	 *
	 * @var string
	 */
	protected $extension;

	public function new( $location ) {
		$this->location = $location;

		if ( file_exists( $this->location ) ) {
			$this->exists = true;

			$file = pathinfo( $this->location );
			$this->basename  = $file['basename'];
			$this->filename  = $file['filename'];
			$this->extension = ( isset( $file['extension'] ) ) ? $file['extension'] : '';
		} else {
			$this->exists    = false;
			$this->basename  = '';
			$this->filename  = '';
			$this->extension = '';
		}

		return $this;
	}

	public function file_path() {
		return $this->location;
	}

	public function exists() {
		return $this->exists;
	}

	public function filename() {
		return $this->filename;
	}

	public function basename() {
		return $this->basename;
	}

	public function extension() {
		return $this->extension;
	}

	public function unpack( $input ) {
		$input           = json_decode( $input );
		$this->location  = $input->location;
		$this->basename  = $input->basename;
		$this->filename  = $input->filename;
		$this->extension = $input->extension;

		if ( file_exists( $this->location ) ) {
			$this->exists = true;
		} else {
			$this->exists = false;
		}

		return $this;
	}

	public function to_string() {
		return json_encode([
			'location'  => $this->location,
			'basename'  => $this->basename,
			'filename'  => $this->filename,
			'extension' => $this->extension,
		]);
	}
}
