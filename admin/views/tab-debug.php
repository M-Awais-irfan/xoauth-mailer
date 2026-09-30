<?php
defined( 'ABSPATH' ) || exit;
// Prefixed: this file is included inside a method, but it reads like global
// scope to code sniffers (and reviewers), so the variables follow the plugin prefix.
$dih_smtp_log     = DIH_SMTP_Logger::get_entries();
$dih_smtp_enabled = DIH_SMTP_Settings::get_one( 'debug_enabled' ) === '1';
?>
<div class="dih-smtp-card">
	<h2>
		<?php esc_html_e( 'Debug Log', 'dih-google-smtp' ); ?>
		<span style="font-size:12px;font-weight:400;margin-left:10px;">
			<?php if ( $dih_smtp_enabled ) : ?>
				<span class="dih-smtp-badge dih-smtp-badge-green"><?php esc_html_e( 'Logging Enabled', 'dih-google-smtp' ); ?></span>
			<?php else : ?>
				<span class="dih-smtp-badge dih-smtp-badge-gray"><?php esc_html_e( 'Logging Disabled', 'dih-google-smtp' ); ?></span>
			<?php endif; ?>
		</span>
	</h2>

	<?php if ( ! $dih_smtp_enabled ) : ?>
		<p><?php esc_html_e( '⚠️ Enable debug logging in the Settings tab to capture SMTP activity.', 'dih-google-smtp' ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $dih_smtp_log ) ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:12px;">
			<?php wp_nonce_field( 'dih_smtp_clear_log' ); ?>
			<input type="hidden" name="action" value="dih_smtp_clear_log">
			<button type="submit" class="button button-secondary"
			        data-confirm="<?php esc_attr_e( 'Clear all log entries?', 'dih-google-smtp' ); ?>">
				<?php esc_html_e( 'Clear Log', 'dih-google-smtp' ); ?>
			</button>
			<span class="description" style="margin-left:10px;">
				<?php
				printf(
					/* translators: %s: number of log entries. */
					esc_html( _n( '%s entry (last 100 kept)', '%s entries (last 100 kept)', count( $dih_smtp_log ), 'dih-google-smtp' ) ),
					esc_html( number_format_i18n( count( $dih_smtp_log ) ) )
				);
				?>
			</span>
		</form>

		<div style="overflow-x:auto;">
			<table class="dih-smtp-log-table widefat">
				<thead>
					<tr>
						<th style="width:160px;"><?php esc_html_e( 'Time', 'dih-google-smtp' ); ?></th>
						<th style="width:70px;"><?php esc_html_e( 'Level', 'dih-google-smtp' ); ?></th>
						<th><?php esc_html_e( 'Message', 'dih-google-smtp' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $dih_smtp_log as $dih_smtp_entry ) : ?>
						<tr>
							<td class="description" style="white-space:nowrap;"><?php echo esc_html( $dih_smtp_entry['time'] ); ?></td>
							<td class="dih-smtp-log-level-<?php echo esc_attr( $dih_smtp_entry['level'] ); ?>"><?php echo esc_html( $dih_smtp_entry['level'] ); ?></td>
							<td><code style="background:none;font-size:12px;"><?php echo esc_html( $dih_smtp_entry['message'] ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<p class="description"><?php esc_html_e( 'No log entries yet. Send a test email to generate logs.', 'dih-google-smtp' ); ?></p>
	<?php endif; ?>
</div>
