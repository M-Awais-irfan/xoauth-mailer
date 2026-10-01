<?php defined( 'ABSPATH' ) || exit; ?>
<div class="xoam-card">
	<h2><?php esc_html_e( 'Which Auth Method Should I Use?', 'xoauth-mailer' ); ?></h2>
	<table class="widefat" style="margin-top:10px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Method', 'xoauth-mailer' ); ?></th>
				<th><?php esc_html_e( 'Best For', 'xoauth-mailer' ); ?></th>
				<th><?php esc_html_e( 'Requires', 'xoauth-mailer' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><strong><?php esc_html_e( 'App Password', 'xoauth-mailer' ); ?></strong></td>
				<td><?php esc_html_e( 'Quick setup, small sites', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( '2FA enabled + App Password generated', 'xoauth-mailer' ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'OAuth2', 'xoauth-mailer' ); ?></strong></td>
				<td><?php esc_html_e( 'Production sites, your Google password is never stored', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( 'Google Cloud project + OAuth credentials', 'xoauth-mailer' ); ?></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="xoam-card">
	<h2><?php esc_html_e( 'App Password Setup', 'xoauth-mailer' ); ?></h2>
	<ol class="xoam-steps">
		<li><?php esc_html_e( 'Sign in to your Google Workspace account.', 'xoauth-mailer' ); ?></li>
		<li>
			<?php
			printf(
				/* translators: %s: link to the Google Account security page. */
				esc_html__( 'Go to %s.', 'xoauth-mailer' ),
				'<a href="https://myaccount.google.com/security" target="_blank">myaccount.google.com/security</a>'
			);
			?>
		</li>
		<li><?php esc_html_e( 'Enable 2-Step Verification.', 'xoauth-mailer' ); ?></li>
		<li><?php esc_html_e( 'Search for App Passwords → Create new → name it "WordPress".', 'xoauth-mailer' ); ?></li>
		<li><?php esc_html_e( 'Copy the 16-character password into Settings tab.', 'xoauth-mailer' ); ?></li>
	</ol>
</div>

<div class="xoam-card">
	<h2><?php esc_html_e( 'Common Issues', 'xoauth-mailer' ); ?></h2>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Problem', 'xoauth-mailer' ); ?></th>
				<th><?php esc_html_e( 'Fix', 'xoauth-mailer' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'Authentication failed', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( 'Check username and app password. Ensure 2FA is on.', 'xoauth-mailer' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Connection timeout', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( 'Use port 587 + TLS. Your host may block port 25.', 'xoauth-mailer' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Emails go to spam', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( 'Set up SPF, DKIM, DMARC records in Google Workspace Admin.', 'xoauth-mailer' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'OAuth Error 400: redirect_uri_mismatch', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( 'The Redirect URI in Google Cloud must exactly match the URL shown in the OAuth2 tab.', 'xoauth-mailer' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'OAuth connects but status shows Not Connected', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( 'Another plugin may be intercepting the callback. This plugin uses REST API endpoint which prevents all such conflicts.', 'xoauth-mailer' ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'From email rejected', 'xoauth-mailer' ); ?></td>
				<td><?php esc_html_e( 'From email must match or be an alias of the authenticated Google account.', 'xoauth-mailer' ); ?></td>
			</tr>
		</tbody>
	</table>
</div>
