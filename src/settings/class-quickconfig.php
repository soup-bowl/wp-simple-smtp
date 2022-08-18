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
 * Configuration applicable to the quick config segment.
 */
class QuickConfig {
	/**
	 * Returns an array of possible SMTP configuration options.
	 *
	 * @return array
	 */
	public static function settings() {
		return [
			[
				'name'           => 'Gmail',
				'server'         => 'smtp.gmail.com',
				'port'           => '587',
				'authentication' => true,
				'encryption'     => 'tls',
			],
			[
				'name'           => 'Microsoft Exchange',
				'server'         => 'smtp.office365.com',
				'port'           => '587',
				'authentication' => true,
				'encryption'     => 'tls',
			],
			[
				'name'           => 'SendGrid',
				'server'         => 'smtp.sendgrid.net',
				'port'           => '587',
				'authentication' => true,
				'user'           => 'apikey',
				'encryption'     => 'tls',
			],
			[
				'name'           => 'Pepipost',
				'server'         => 'smtp.pepipost.com',
				'port'           => '587',
				'authentication' => true,
			],
			[
				'name'           => 'SendinBlue',
				'server'         => 'smtp-relay.sendinblue.com',
				'port'           => '587',
				'authentication' => true,
			],
			[
				'name'           => 'Amazon SES',
				'server'         => 'email-smtp.<CHANGE>.amazonaws.com',
				'port'           => '465',
				'authentication' => true,
				'encryption'     => 'tls',
			],
			[
				'name'           => 'One.com',
				'server'         => 'send.one.com',
				'port'           => '465',
				'authentication' => true,
				'encryption'     => 'ssl',
			],
		];
	}
}
