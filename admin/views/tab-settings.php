<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="options.php">
	<?php settings_fields( 'dih_smtp_settings_group' ); ?>

	<div class="dih-smtp-card">
		<h2><?php esc_html_e( 'Sender Identity', 'dih-google-smtp' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="from_name"><?php esc_html_e( 'From Name', 'dih-google-smtp' ); ?></label></th>
				<td>
					<input type="text" id="from_name" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[from_name]"
					       value="<?php echo esc_attr( $s['from_name'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Name recipients will see in their inbox.', 'dih-google-smtp' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="from_email"><?php esc_html_e( 'From Email', 'dih-google-smtp' ); ?></label></th>
				<td>
					<input type="email" id="from_email" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[from_email]"
					       value="<?php echo esc_attr( $s['from_email'] ); ?>" class="regular-text"
					       placeholder="<?php esc_attr_e( 'you@yourdomain.com', 'dih-google-smtp' ); ?>">
					<p class="description"><?php esc_html_e( 'Must match your Google Workspace account email or an alias.', 'dih-google-smtp' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<div class="dih-smtp-card">
		<h2><?php esc_html_e( 'SMTP Server', 'dih-google-smtp' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="smtp_host"><?php esc_html_e( 'SMTP Host', 'dih-google-smtp' ); ?></label></th>
				<td>
					<input type="text" id="smtp_host" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[smtp_host]"
					       value="<?php echo esc_attr( $s['smtp_host'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Google Workspace:', 'dih-google-smtp' ); ?> <code>smtp.gmail.com</code></p>
				</td>
			</tr>
			<tr>
				<th><label for="smtp_port"><?php esc_html_e( 'SMTP Port', 'dih-google-smtp' ); ?></label></th>
				<td>
					<select id="smtp_port" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[smtp_port]">
						<option value="587" <?php selected( $s['smtp_port'], '587' ); ?>><?php esc_html_e( '587 — TLS (Recommended)', 'dih-google-smtp' ); ?></option>
						<option value="465" <?php selected( $s['smtp_port'], '465' ); ?>><?php esc_html_e( '465 — SSL', 'dih-google-smtp' ); ?></option>
						<option value="25"  <?php selected( $s['smtp_port'], '25' ); ?>><?php esc_html_e( '25 — None', 'dih-google-smtp' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="smtp_encryption"><?php esc_html_e( 'Encryption', 'dih-google-smtp' ); ?></label></th>
				<td>
					<select id="smtp_encryption" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[smtp_encryption]">
						<option value="tls"  <?php selected( $s['smtp_encryption'], 'tls' ); ?>><?php esc_html_e( 'TLS (STARTTLS)', 'dih-google-smtp' ); ?></option>
						<option value="ssl"  <?php selected( $s['smtp_encryption'], 'ssl' ); ?>><?php esc_html_e( 'SSL', 'dih-google-smtp' ); ?></option>
						<option value="none" <?php selected( $s['smtp_encryption'], 'none' ); ?>><?php esc_html_e( 'None', 'dih-google-smtp' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
	</div>

	<div class="dih-smtp-card">
		<h2><?php esc_html_e( 'Authentication', 'dih-google-smtp' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Auth Method', 'dih-google-smtp' ); ?></th>
				<td>
					<?php // Core's radio-group pattern: one option per line, grouped in a fieldset for screen readers. ?>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Auth Method', 'dih-google-smtp' ); ?></span></legend>
						<label>
							<input type="radio" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[auth_method]"
							       value="app_password" <?php checked( $s['auth_method'], 'app_password' ); ?>>
							<?php esc_html_e( 'App Password', 'dih-google-smtp' ); ?>
							<span class="description"><?php esc_html_e( '(Easier — enable 2FA then generate an App Password)', 'dih-google-smtp' ); ?></span>
						</label>
						<br>
						<label>
							<input type="radio" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[auth_method]"
							       value="oauth2" <?php checked( $s['auth_method'], 'oauth2' ); ?>>
							<?php esc_html_e( 'OAuth2', 'dih-google-smtp' ); ?>
							<span class="description"><?php esc_html_e( '(Recommended — your Google password is never stored)', 'dih-google-smtp' ); ?></span>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th><label for="username"><?php esc_html_e( 'Google Email', 'dih-google-smtp' ); ?></label></th>
				<td>
					<input type="email" id="username" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[username]"
					       value="<?php echo esc_attr( $s['username'] ); ?>" class="regular-text"
					       placeholder="<?php esc_attr_e( 'you@yourdomain.com', 'dih-google-smtp' ); ?>">
				</td>
			</tr>
			<tr id="row-app-password">
				<th><label for="app_password"><?php esc_html_e( 'App Password', 'dih-google-smtp' ); ?></label></th>
				<td>
					<?php if ( DIH_SMTP_Settings::is_constant( 'app_password' ) ) : ?>
						<p><em><?php esc_html_e( 'Defined in wp-config.php.', 'dih-google-smtp' ); ?></em></p>
					<?php else : ?>
						<?php // Never print the saved secret — leave blank to keep it. ?>
						<input type="password" id="app_password" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[app_password]"
						       value="" class="regular-text" autocomplete="new-password">
						<?php if ( ! empty( $s['app_password'] ) ) : ?>
							<p class="description dih-smtp-saved">
								<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
								<?php esc_html_e( 'An App Password is saved (hidden for security). Leave blank to keep it, or enter a new one to replace it.', 'dih-google-smtp' ); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<p class="description">
						<?php
						printf(
							/* translators: %s: link to Google's App Passwords page. */
							esc_html__( 'Generate at %s.', 'dih-google-smtp' ),
							'<a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a>'
						);
						?>
						<?php esc_html_e( 'Requires 2FA enabled.', 'dih-google-smtp' ); ?>
					</p>
				</td>
			</tr>
			<tr id="row-oauth-id">
				<th><label for="oauth_client_id"><?php esc_html_e( 'OAuth2 Client ID', 'dih-google-smtp' ); ?></label></th>
				<td>
					<input type="text" id="oauth_client_id" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[oauth_client_id]"
					       value="<?php echo esc_attr( $s['oauth_client_id'] ); ?>" class="large-text">
				</td>
			</tr>
			<tr id="row-oauth-secret">
				<th><label for="oauth_client_secret"><?php esc_html_e( 'OAuth2 Client Secret', 'dih-google-smtp' ); ?></label></th>
				<td>
					<?php if ( DIH_SMTP_Settings::is_constant( 'oauth_client_secret' ) ) : ?>
						<p><em><?php esc_html_e( 'Defined in wp-config.php.', 'dih-google-smtp' ); ?></em></p>
					<?php else : ?>
						<?php // Never print the saved secret — leave blank to keep it. ?>
						<input type="password" id="oauth_client_secret" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[oauth_client_secret]"
						       value="" class="large-text" autocomplete="new-password">
						<?php if ( ! empty( $s['oauth_client_secret'] ) ) : ?>
							<p class="description dih-smtp-saved">
								<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
								<?php esc_html_e( 'A Client Secret is saved (hidden for security). Leave blank to keep it, or enter a new one to replace it.', 'dih-google-smtp' ); ?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<p class="description">
						<?php esc_html_e( 'Save settings, then go to the OAuth2 Setup tab to connect your account.', 'dih-google-smtp' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>

	<div class="dih-smtp-card">
		<h2><?php esc_html_e( 'Debugging', 'dih-google-smtp' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Enable Debug Log', 'dih-google-smtp' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( DIH_SMTP_OPTION_KEY ); ?>[debug_enabled]"
						       value="1" <?php checked( $s['debug_enabled'], '1' ); ?>>
						<?php esc_html_e( 'Log all SMTP activity (view in Debug Log tab)', 'dih-google-smtp' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Disable on production once everything is working.', 'dih-google-smtp' ); ?></p>
				</td>
			</tr>
		</table>
	</div>

	<?php submit_button( __( 'Save Settings', 'dih-google-smtp' ) ); ?>
</form>
