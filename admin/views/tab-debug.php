<?php
defined( 'ABSPATH' ) || exit;
$xoam_log     = XOAM_Logger::get_entries();
$xoam_enabled = XOAM_Settings::get_one( 'debug_enabled' ) === '1';
?>
<div class="xoam-card">
	<h2>
		<?php esc_html_e( 'Debug Log', 'xoauth-mailer' ); ?>
		<span style="font-size:12px;font-weight:400;margin-left:10px;">
			<?php if ( $xoam_enabled ) : ?>
				<span class="xoam-badge xoam-badge-green"><?php esc_html_e( 'Logging Enabled', 'xoauth-mailer' ); ?></span>
			<?php else : ?>
				<span class="xoam-badge xoam-badge-gray"><?php esc_html_e( 'Logging Disabled', 'xoauth-mailer' ); ?></span>
			<?php endif; ?>
		</span>
	</h2>

	<?php if ( ! $xoam_enabled ) : ?>
		<p><?php esc_html_e( 'Enable debug logging in the Settings tab to capture SMTP activity.', 'xoauth-mailer' ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $xoam_log ) ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:12px;">
			<?php wp_nonce_field( 'xoam_clear_log' ); ?>
			<input type="hidden" name="action" value="xoam_clear_log">
			<button type="submit" class="button button-secondary"
			        data-confirm="<?php esc_attr_e( 'Clear all log entries?', 'xoauth-mailer' ); ?>">
				<?php esc_html_e( 'Clear Log', 'xoauth-mailer' ); ?>
			</button>
			<span class="description" style="margin-left:10px;">
				<?php
				printf(
					/* translators: %s: number of log entries. */
					esc_html( _n( '%s entry (last 100 kept)', '%s entries (last 100 kept)', count( $xoam_log ), 'xoauth-mailer' ) ),
					esc_html( number_format_i18n( count( $xoam_log ) ) )
				);
				?>
			</span>
		</form>

		<div style="overflow-x:auto;">
			<table class="xoam-log-table widefat">
				<thead>
					<tr>
						<th scope="col" style="width:160px;"><?php esc_html_e( 'Time', 'xoauth-mailer' ); ?></th>
						<th scope="col" style="width:70px;"><?php esc_html_e( 'Level', 'xoauth-mailer' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Message', 'xoauth-mailer' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $xoam_log as $xoam_entry ) : ?>
						<tr>
							<td class="description" style="white-space:nowrap;"><?php echo esc_html( $xoam_entry['time'] ); ?></td>
							<td class="xoam-log-level-<?php echo esc_attr( $xoam_entry['level'] ); ?>"><?php echo esc_html( $xoam_entry['level'] ); ?></td>
							<td><code style="background:none;font-size:12px;"><?php echo esc_html( $xoam_entry['message'] ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'No log entries yet. Send a test email to generate logs.', 'xoauth-mailer' ); ?></p>
	<?php endif; ?>
</div>
