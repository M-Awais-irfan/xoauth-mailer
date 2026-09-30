<?php defined( 'ABSPATH' ) || exit; ?>
<div class="dih-smtp-card">
	<h2><?php esc_html_e( 'Which Auth Method Should I Use?', 'dih-google-smtp' ); ?></h2>
	<table class="widefat" style="margin-top:10px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Method', 'dih-google-smtp' ); ?></th>
				<th><?php esc_html_e( 'Best For', 'dih-google-smtp' ); ?></th>
				<th><?php esc_html_e( 'Requires', 'dih-google-smtp' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><strong><?php esc_html_e( 'App Password', 'dih-google-smtp' ); ?></strong></td>
				<td><?php esc_html_e( 'Quick setup, small sites', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( '2FA enabled + App Password generated', 'dih-google-smtp' ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'OAuth2', 'dih-google-smtp' ); ?></strong></td>
				<td><?php esc_html_e( 'Production sites, your Google password is never stored', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( 'Google Cloud project + OAuth credentials', 'dih-google-smtp' ); ?></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="dih-smtp-card">
	<h2><?php esc_html_e( 'App Password Setup', 'dih-google-smtp' ); ?></h2>
	<ol class="dih-smtp-steps">
		<li><?php esc_html_e( 'Sign in to your Google Workspace account.', 'dih-google-smtp' ); ?></li>
		<li>
			<?php
			printf(
				/* translators: %s: link to the Google Account security page. */
				esc_html__( 'Go to %s.', 'dih-google-smtp' ),
				'<a href="https://myaccount.google.com/security" target="_blank">myaccount.google.com/security</a>'
			);
			?>
		</li>
		<li><?php esc_html_e( 'Enable 2-Step Verification.', 'dih-google-smtp' ); ?></li>
		<li><?php esc_html_e( 'Search for App Passwords → Create new → name it "WordPress".', 'dih-google-smtp' ); ?></li>
		<li><?php esc_html_e( 'Copy the 16-character password into Settings tab.', 'dih-google-smtp' ); ?></li>
	</ol>
</div>

<div class="dih-smtp-card">
	<h2><?php esc_html_e( 'Common Issues', 'dih-google-smtp' ); ?></h2>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Problem', 'dih-google-smtp' ); ?></th>
				<th><?php esc_html_e( 'Fix', 'dih-google-smtp' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Authentication failed', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( 'Check username and app password. Ensure 2FA is on.', 'dih-google-smtp' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Connection timeout', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( 'Use port 587 + TLS. Your host may block port 25.', 'dih-google-smtp' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Emails go to spam', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( 'Set up SPF, DKIM, DMARC records in Google Workspace Admin.', 'dih-google-smtp' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'OAuth Error 400: redirect_uri_mismatch', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( 'The Redirect URI in Google Cloud must exactly match the URL shown in the OAuth2 tab.', 'dih-google-smtp' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'OAuth connects but status shows Not Connected', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( 'Another plugin may be intercepting the callback. This plugin uses REST API endpoint which prevents all such conflicts.', 'dih-google-smtp' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'From email rejected', 'dih-google-smtp' ); ?></td>
				<td><?php esc_html_e( 'From email must match or be an alias of the authenticated Google account.', 'dih-google-smtp' ); ?></td>
			</tr>
		</tbody>
	</table>
</div>
