<?php
/**
 * Simple email configuration within WordPress.
 *
 * @package sb-simple-smtp
 * @author soup-bowl <code@soupbowl.io>
 * @license MIT
 */

namespace wpsimplesmtp;

use wpsimplesmtp\LogService;

/**
 * Settings aspects relating to the log view.
 */
class MailView {
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
		$this->log_service = new LogService();
	}

	/**
	 * Render the email with useful information.
	 *
	 * @param integer $id Email log ID.
	 * @return void Prints to page.
	 */
	public function render_email_view( $id ) {
		$log        = $this->log_service->get_log_entry_by_id( $id );
		$recset     = ( in_array( (int) $id, get_option( 'wpss_resent', [] ), true ) ) ? ' disabled' : '';
		$resend_url = add_query_arg(
			[
				'eid'     => $id,
				'ssnonce' => wp_create_nonce( 'wpss_action' ),
			],
			menu_page_url( 'wpsimplesmtp', false )
		) . '&resend';

		if ( current_user_can( 'manage_options' ) && isset( $log ) ) {
			$to   = implode( ', ', $log->get_recipients() );
			$from = implode( ', ', $log->get_from() );
			$cc   = implode( ', ', $log->get_cc() );
			$bcc  = implode( ', ', $log->get_bcc() );
			$date = gmdate( get_option( 'time_format' ) . ', ' . get_option( 'date_format' ), strtotime( $log->get_timestamp() ) );

			$content = '';
			if ( ! empty( $log->get_headers() ) && false !== strpos( $log->get_headers_unified(), 'Content-Type: text\/html' ) ) {
				$content = wp_kses_post( $log->get_body() );
			} else {
				$content = wp_kses_post( '<pre>' . $log->get_body() . '</pre>' );
			}
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'View Email', 'simple-smtp' ); ?></h1>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">
							<div class="postbox">
								<h2 class="hndle"><?php echo esc_html( $log->get_subject() ); ?></h2>			
								<div class="inside">
									<?php echo wp_kses_post( wpautop( $log->get_body() ) ); ?>
								</div>	
							</div>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="stuffbox">
								<h2 class="hndle"><?php esc_html_e( 'Information', 'simple-smtp' ); ?></h2>
								<div class="inside">
									<div id="minor-publishing">
										<div id="misc-publishing-actions">
											<?php if ( ! empty( $to ) ) : ?>
											<div class="misc-pub-section">
												<?php esc_html_e( 'To', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $to ); ?></strong>
											</div>
											<?php endif; ?>

											<?php if ( ! empty( $from ) ) : ?>
											<div class="misc-pub-section">
												<?php esc_html_e( 'From', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $from ); ?></strong>
											</div>
											<?php endif; ?>

											<?php if ( ! empty( $cc ) ) : ?>
											<div class="misc-pub-section">
												<?php esc_html_e( 'CC', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $cc ); ?></strong>
											</div>
											<?php endif; ?>

											<?php if ( ! empty( $bcc ) ) : ?>
											<div class="misc-pub-section">
												<?php esc_html_e( 'BCC', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $bcc ); ?></strong>
											</div>
											<?php endif; ?>

											<div class="misc-pub-section">
												<?php esc_html_e( 'Date sent', 'simple-smtp' ); ?>: <strong><?php echo esc_html( $date ); ?></strong>
											</div>

											<?php if ( ! empty( $log->get_headers() ) ) : ?>
												<div class="misc-pub-section">
													<?php esc_html_e( 'Header(s)', 'simple-smtp' ); ?>:
													<ol>
														<?php foreach ( $log->get_headers_as_array() as $header ) : ?>
															<li>
																<?php if ( isset( $header[1] ) ) : ?>
																	<?php echo esc_html( $header[0] ); ?>: <strong><?php echo esc_html( $header[1] ); ?></strong>
																<?php else : ?>
																	<strong><?php echo esc_html( $header[0] ); ?></strong>
																<?php endif; ?>
															</li>
														<?php endforeach; ?>
													</ol>
												</div>
											<?php endif; ?>

											<?php if ( ! empty( $log->get_attachments() ) ) : ?>
												<div class="misc-pub-section">
													<?php esc_html_e( 'Attachment(s)', 'simple-smtp' ); ?>:
													<ol>
														<?php foreach ( $log->get_attachments() as $attachment ) : ?>
															<li>
																<?php echo esc_html( $attachment->basename() ); ?>
																<?php if ( ! $attachment->exists() ) : ?>
																	<span class="wpsmtp-badge warning"><?php esc_html_e( 'File missing', 'simple-smtp' ); ?></span>
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
			wp_die( esc_html__( 'No email found.', 'simple-smtp' ) );
		}
	}
}
