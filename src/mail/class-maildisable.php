<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

if ( ! class_exists( 'PHPMailer', false ) ) {
	require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
	require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
}

/**
 * To kill off the email functionality, we override the PHPMailer global that WordPress is using with our extended
 * PHPMailer. This allows us to replace the send function with a custom exception, that will still act like everything
 * is normal, right up until the dispatch where a custom exception is thrown. Means the users can re-send their emails
 * once the disabled lock is lifted. Neat!
 */
class MailDisable extends \PHPMailer\PHPMailer\PHPMailer {
	/**
	 * Disables the PHPMailer send functionality, and forces an Exception instead.
	 *
	 * @throws \PHPMailer\PHPMailer\Exception Disabled email message.
	 */
	public function send() {
		throw new \PHPMailer\PHPMailer\Exception( 'WPSS_MAIL_OFF' );
	}
}
