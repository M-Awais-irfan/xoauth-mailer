<?php defined( 'ABSPATH' ) || exit; ?>
<div class="dih-smtp-card">
	<h2><?php esc_html_e( 'Send a Test Email', 'dih-google-smtp' ); ?></h2>
	<p><?php esc_html_e( 'Sends a real email through your configured Google Workspace SMTP.', 'dih-google-smtp' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'dih_smtp_test_email' ); ?>
		<input type="hidden" name="action" value="dih_smtp_send_test">
		<table class="form-table">
			<tr>
				<th><label for="test_email_to"><?php esc_html_e( 'Send To', 'dih-google-smtp' ); ?></label></th>
				<td>
					<input type="email" id="test_email_to" name="test_email_to"
					       value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
					       class="regular-text" required>
					<p class="description"><?php esc_html_e( 'Enter any email address to receive the test.', 'dih-google-smtp' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Send Test Email', 'dih-google-smtp' ), 'primary', 'submit', false ); ?>
	</form>
</div>
