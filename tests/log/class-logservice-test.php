<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

use wpsimplesmtp\LogService;
use wpsimplesmtp\Log;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

/**
 * Mock the WP_Query.
 * https://developer.wordpress.org/reference/classes/wp_query/
 */
class WP_Query {
	/**
	 * Mocks the query function.
	 * https://developer.wordpress.org/reference/classes/wp_query/query/
	 *
	 * @param array $a Unused.
	 * @return null
	 */
	public function query( $a ) {
		return null;
	}

	/**
	 * Mocks the get_posts function.
	 * https://developer.wordpress.org/reference/classes/wp_query/get_postss/
	 *
	 * @return WP_Post[]
	 */
	public function get_posts() {
		return [
			new WP_Post(),
		];
	}
}

/**
 * Mocks WP_Post.
 * https://developer.wordpress.org/reference/classes/wp_post/
 */
class WP_Post {
	/**
	 * ID.
	 *
	 * @var integer
	 */
	public $ID = 1;

	/**
	 * Subject.
	 *
	 * @var string
	 */
	public $post_title = 'Example Post';

	/**
	 * Body.
	 *
	 * @var string
	 */
	public $post_content = 'Example Content';

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	public $post_type = 'sbss_email_log';
}

/**
 * Tests the logging services functionality.
 */
class LogServiceTest extends TestCase {
	/**
	 * Testing class.
	 *
	 * @var LogService
	 */
	protected $log_service;

	/**
	 * Per-test constructor.
	 */
	public function setUp():void {
		$this->log_service = new LogService();

		/**
		 * Mock get_post_meta function.
		 * https://developer.wordpress.org/reference/functions/get_post_meta/
		 *
		 * @param integer $post_id Post ID.
		 * @param string  $key Key.
		 * @param boolean $single Single or Array return.
		 * @return mixed
		 */
		function get_post_meta( $post_id, $key = '', $single = false ) {
			switch ( $key ) {
				case 'attachments':
					return null;
				default:
					return 'test';
			}
		}
	}

	/**
	 * Tests the log entry retrieval system returns the Log type.
	 */
	public function test_get_log_entries() {
		$reply = $this->log_service->get_log_entries();
		$this->assertInstanceOf( Log::class, $reply[0] );
		$this->assertEquals( 1, $reply[0]->get_id() );
		$this->assertEquals( 'Example Post', $reply[0]->get_subject() );
	}
}
