<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\Options;

/**
 * Displays useful information in the dashboard widget `At a Glance`.
 */
class Glance {
	/**
	 * Registers the relevant WordPress hooks upon creation.
	 */
	public function hooks() {
		$log_enabled = ( new Options() )->get( 'log' );
		if ( ! empty( $log_enabled ) && true === filter_var( $log_enabled->value, FILTER_VALIDATE_BOOLEAN ) ) {
			add_filter( 'dashboard_glance_items', [ &$this, 'at_a_glance_items' ], 10, 1 );
		}
	}

	/**
	 * Adds post-type info to `At a Glance`-dashboard widget.
	 *
	 * @since 1.3.1
	 *
	 * @param array $items The items to display in the `At a Glance`-dashboard.
	 * @return array $items All existing plus the new items.
	 */
	public function at_a_glance_items( $items = [] ) {
		$post_types = [ 'sbss_email_log' ];

		foreach ( $post_types as $type ) {

			if ( ! post_type_exists( $type ) ) {
				continue;
			}
			$num_posts = wp_count_posts( $type );

			if ( $num_posts ) {

				$published = intval( $num_posts->publish );
				$post_type = get_post_type_object( $type );
				/* translators: %s: counter of how many email log entries. */
				$text      = _n( '%s e-mail log entry', '%s e-mail log entries', $published, 'simple-smtp' );
				$text      = sprintf( $text, number_format_i18n( $published ) );
				$edit_link = admin_url( 'options-general.php?page=wpsimplesmtp#log' );

				// Echo list element is a hack so we can add classes to the list element.
				if ( current_user_can( $post_type->cap->edit_posts ) ) {
					echo sprintf(
						'<li class="email-log-count %1$s-count"><a href="%3$s">%2$s</a></li>',
						esc_attr( $type ),
						esc_html( $text ),
						esc_url( $edit_link )
					) . "\n";
				} else {
					echo sprintf(
						'<li class="email-log-count %1$s-count">%2$s</li>',
						esc_attr( $type ),
						esc_html( $text ),
					) . "\n";
				}
			}
		}
		return $items;
	}
}
