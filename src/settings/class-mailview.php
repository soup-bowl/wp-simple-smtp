<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\Log;

/**
 * Settings aspects relating to the log view.
 */
class MailView {
	/**
	 * Stores and retrieves the emails stored in the log.
	 *
	 * @var Log
	 */
	protected $log;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->log = new Log();
	}

	/**
	 * Render the email with useful information.
	 *
	 * @param integer $id Email log ID.
	 * @return void Prints to page.
	 */
	public function render_email_view( $id ) {
		$log         = $this->log->get_log_entry_by_id( $id );
		$attachments = $this->log->get_log_entry_attachments( $id );
		$recset      = ( in_array( (int) $id, get_option( 'wpss_resent', [] ), true ) ) ? ' disabled' : '';
		$resend_url  = add_query_arg(
			[
				'eid'     => $id,
				'ssnonce' => wp_create_nonce( 'wpss_action' ),
			],
			menu_page_url( 'wpsimplesmtp', false )
		) . '&resend';

		if ( current_user_can( 'administrator' ) && isset( $log ) ) {
			$recipients = implode( ', ', json_decode( get_post_meta( $log->ID, 'recipients', true ) ) );
			$date       = gmdate( get_option( 'time_format' ) . ', ' . get_option( 'date_format' ), strtotime( get_post_meta( $log->ID, 'timestamp', true ) ) );

			$content = '';
			if ( isset( $log->headers ) && false !== strpos( $log->headers, 'Content-Type: text\/html' ) ) {
				$content = wp_kses_post( $log->post_content );
			} else {
				$content = wp_kses_post( '<pre>' . $log->post_content . '</pre>' );
			}
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'View Email', 'simple-smtp' ); ?></h1>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">
							<div class="postbox">
								<h2 class="hndle"><?php echo esc_html( $log->post_title ); ?></h2>			
								<div class="inside">
									<?php echo wp_kses_post( $content ); ?>
								</div>	
							</div>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="stuffbox">
								<h2 class="hndle"><?php esc_html_e( 'Information', 'simple-smtp' ); ?></h2>
								<div class="inside">
									<div id="minor-publishing">
										<div id="misc-publishing-actions">
											<div class="misc-pub-section"><?php esc_html_e( 'Recipient(s)', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $recipients ); ?></strong></div>
											<div class="misc-pub-section"><?php esc_html_e( 'Date sent', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $date ); ?></strong></div>
											<?php if ( ! empty( $attachments ) ) : ?>
												<div class="misc-pub-section">
													<?php esc_html_e( 'Attachment(s)', 'simple-smtp' ); ?>:
													<ol>
														<?php foreach ( $attachments as $attachment ) : ?>
															<li>
																<?php echo esc_html( $attachment->basename() ); ?>
																<?php if ( ! $attachment->exists() ) : ?>
																	<span class="wpsmtp-badge wpsmtp-badge-warning"><?php esc_html_e( 'File missing', 'simple-smtp' ); ?></span>
																<?php endif; ?>
															</li>
														<?php endforeach; ?>
													</ol>
												</div>
											<?php endif; ?>
										</div>
										<div class="clear"></div>
									</div>
									<div id="major-publishing-actions">
										<div id="publishing-action">
											<a href="<?php echo esc_html( $resend_url ); ?>" class="button button-primary button-large <?php echo esc_attr( $recset ); ?>"><?php esc_html_e( 'Resend', 'simple-smtp' ); ?></a>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
			<?php
		} else {
			wp_die( 'No email found.' );
		}
	}
}
