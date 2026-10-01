<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="options.php">
	<?php settings_fields( 'xoam_settings_group' ); ?>

	<div class="xoam-card">
		<h2><?php esc_html_e( 'Sender Identity', 'xoauth-mailer' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="from_name"><?php esc_html_e( 'From Name', 'xoauth-mailer' ); ?></label></th>
				<td>
					<input type="text" id="from_name" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[from_name]"
					       value="<?php echo esc_attr( $s['from_name'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Name recipients will see in their inbox.', 'xoauth-mailer' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="from_email"><?php esc_html_e( 'From Email', 'xoauth-mailer' ); ?></label></th>
				<td>
					<input type="email" id="from_email" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[from_email]"
					       value="<?php echo esc_attr( $s['from_email'] ); ?>" class="regular-text"
					       placeholder="<?php esc_attr_e( 'you@yourdomain.com', 'xoauth-mailer' ); ?>">
					<p class="description"><?php esc_html_e( 'Must match your Google Workspace account email or an alias.', 'xoauth-mailer' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<div class="xoam-card">
		<h2><?php esc_html_e( 'SMTP Server', 'xoauth-mailer' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="smtp_host"><?php esc_html_e( 'SMTP Host', 'xoauth-mailer' ); ?></label></th>
				<td>
					<input type="text" id="smtp_host" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[smtp_host]"
					       value="<?php echo esc_attr( $s['smtp_host'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Google Workspace:', 'xoauth-mailer' ); ?> <code>smtp.gmail.com</code></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smtp_port"><?php esc_html_e( 'SMTP Port', 'xoauth-mailer' ); ?></label></th>
				<td>
					<select id="smtp_port" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[smtp_port]">
						<option value="587" <?php selected( $s['smtp_port'], '587' ); ?>><?php esc_html_e( '587 (TLS, recommended)', 'xoauth-mailer' ); ?></option>
						<option value="465" <?php selected( $s['smtp_port'], '465' ); ?>><?php esc_html_e( '465 (SSL)', 'xoauth-mailer' ); ?></option>
						<option value="25"  <?php selected( $s['smtp_port'], '25' ); ?>><?php esc_html_e( '25 (TLS)', 'xoauth-mailer' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Encryption is set automatically from the port.', 'xoauth-mailer' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<div class="xoam-card">
		<h2><?php esc_html_e( 'Authentication', 'xoauth-mailer' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Auth Method', 'xoauth-mailer' ); ?></th>
				<td>
					<?php // Core's radio-group pattern: one option per line, grouped in a fieldset for screen readers. ?>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Auth Method', 'xoauth-mailer' ); ?></span></legend>
						<label>
							<input type="radio" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[auth_method]"
							       value="app_password" <?php checked( $s['auth_method'], 'app_password' ); ?>>
							<?php esc_html_e( 'App Password', 'xoauth-mailer' ); ?>
							<span class="description"><?php esc_html_e( '(easier: turn on 2-Step Verification, then create an App Password)', 'xoauth-mailer' ); ?></span>
						</label>
						<br>
						<label>
							<input type="radio" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[auth_method]"
							       value="oauth2" <?php checked( $s['auth_method'], 'oauth2' ); ?>>
							<?php esc_html_e( 'OAuth2', 'xoauth-mailer' ); ?>
							<span class="description"><?php esc_html_e( '(recommended: your Google password is never stored)', 'xoauth-mailer' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="username"><?php esc_html_e( 'Google Email', 'xoauth-mailer' ); ?></label></th>
				<td>
					<input type="email" id="username" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[username]"
					       value="<?php echo esc_attr( $s['username'] ); ?>" class="regular-text"
					       placeholder="<?php esc_attr_e( 'you@yourdomain.com', 'xoauth-mailer' ); ?>">
				</td>
			</tr>
			<tr id="xoam-row-app-password">
				<th scope="row"><label for="app_password"><?php esc_html_e( 'App Password', 'xoauth-mailer' ); ?></label></th>
				<td>
					<?php if ( XOAM_Settings::is_constant( 'app_password' ) ) : ?>
						<p><em><?php esc_html_e( 'Defined in wp-config.php.', 'xoauth-mailer' ); ?></em></p>
					<?php else : ?>
						<?php // The saved secret is never printed; a blank field keeps it. ?>
						<input type="password" id="app_password" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[app_password]"
						       value="" class="regular-text" autocomplete="new-password">
						<?php if ( ! empty( $s['app_password'] ) ) : ?>
							<p class="description xoam-saved">
								<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
								<?php esc_html_e( 'An App Password is saved (hidden for security). Leave blank to keep it, or enter a new one to replace it.', 'xoauth-mailer' ); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<p class="description">
						<?php
						printf(
							/* translators: %s: link to Google's App Passwords page. */
							esc_html__( 'Generate at %s.', 'xoauth-mailer' ),
							'<a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer">myaccount.google.com/apppasswords</a>'
						);
						?>
						<?php esc_html_e( 'Requires 2FA enabled.', 'xoauth-mailer' ); ?>
					</p>
				</td>
			</tr>
			<tr id="xoam-row-oauth-id">
				<th scope="row"><label for="oauth_client_id"><?php esc_html_e( 'OAuth2 Client ID', 'xoauth-mailer' ); ?></label></th>
				<td>
					<input type="text" id="oauth_client_id" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[oauth_client_id]"
					       value="<?php echo esc_attr( $s['oauth_client_id'] ); ?>" class="large-text">
				</td>
			</tr>
			<tr id="xoam-row-oauth-secret">
				<th scope="row"><label for="oauth_client_secret"><?php esc_html_e( 'OAuth2 Client Secret', 'xoauth-mailer' ); ?></label></th>
				<td>
					<?php if ( XOAM_Settings::is_constant( 'oauth_client_secret' ) ) : ?>
						<p><em><?php esc_html_e( 'Defined in wp-config.php.', 'xoauth-mailer' ); ?></em></p>
					<?php else : ?>
						<?php // The saved secret is never printed; a blank field keeps it. ?>
						<input type="password" id="oauth_client_secret" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[oauth_client_secret]"
						       value="" class="large-text" autocomplete="new-password">
						<?php if ( ! empty( $s['oauth_client_secret'] ) ) : ?>
							<p class="description xoam-saved">
								<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
								<?php esc_html_e( 'A Client Secret is saved (hidden for security). Leave blank to keep it, or enter a new one to replace it.', 'xoauth-mailer' ); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<p class="description">
						<?php esc_html_e( 'Save settings, then go to the OAuth2 Setup tab to connect your account.', 'xoauth-mailer' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>

	<div class="xoam-card">
		<h2><?php esc_html_e( 'Debugging', 'xoauth-mailer' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Debug Log', 'xoauth-mailer' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( XOAM_OPTION_KEY ); ?>[debug_enabled]"
						       value="1" <?php checked( $s['debug_enabled'], '1' ); ?>>
						<?php esc_html_e( 'Log all SMTP activity (view in Debug Log tab)', 'xoauth-mailer' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Disable on production once everything is working.', 'xoauth-mailer' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<?php submit_button( __( 'Save Settings', 'xoauth-mailer' ) ); ?>
</form>
