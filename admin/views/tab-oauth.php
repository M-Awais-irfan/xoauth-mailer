<?php
defined( 'ABSPATH' ) || exit;
// Prefixed: this file is included inside a method, but it reads like global
// scope to code sniffers (and reviewers), so the variables follow the plugin prefix.
$dih_smtp_connected    = DIH_SMTP_OAuth::is_connected();
$dih_smtp_token        = DIH_SMTP_OAuth::get_token();
$dih_smtp_has_creds    = ! empty( $s['oauth_client_id'] ) && ! empty( $s['oauth_client_secret'] );
$dih_smtp_redirect_uri = DIH_SMTP_OAuth::get_redirect_uri();
?>

<!-- Redirect URI box — always visible for easy copy/paste -->
<div class="dih-smtp-card dih-smtp-card-blue">
	<h2><?php esc_html_e( 'Your Redirect URI — Add This to Google Cloud Console', 'dih-google-smtp' ); ?></h2>
	<p><?php esc_html_e( 'Copy this exact URL into Google Cloud → OAuth Client → Authorized redirect URIs. One character difference causes Error 400.', 'dih-google-smtp' ); ?></p>
	<div class="dih-smtp-copy-row">
		<input type="text" id="dih-smtp-redirect-uri" value="<?php echo esc_attr( $dih_smtp_redirect_uri ); ?>" readonly class="large-text">
		<button type="button" class="button button-secondary" data-copy="dih-smtp-redirect-uri">
			<?php esc_html_e( 'Copy', 'dih-google-smtp' ); ?>
		</button>
	</div>
</div>

<!-- Connection status -->
<div class="dih-smtp-card">
	<h2><?php esc_html_e( 'Connection Status', 'dih-google-smtp' ); ?></h2>
	<?php if ( $dih_smtp_connected ) : ?>
		<p><?php esc_html_e( 'Status:', 'dih-google-smtp' ); ?> <span class="dih-smtp-badge dih-smtp-badge-green"><?php esc_html_e( 'Connected', 'dih-google-smtp' ); ?></span></p>
		<?php if ( ! empty( $dih_smtp_token['expires_at'] ) ) : ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: token expiry date and time (UTC). */
					esc_html__( 'Token expires: %s (auto-refreshes)', 'dih-google-smtp' ),
					esc_html( gmdate( 'Y-m-d H:i:s', $dih_smtp_token['expires_at'] ) )
				);
				?>
			</p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;">
			<?php wp_nonce_field( 'dih_smtp_disconnect_oauth' ); ?>
			<input type="hidden" name="action" value="dih_smtp_disconnect_oauth">
			<p>
				<label>
					<input type="checkbox" name="revoke" value="1">
					<?php esc_html_e( 'Also revoke access at Google (this also disconnects any other site using the same Client ID and Google account)', 'dih-google-smtp' ); ?>
				</label>
			</p>
			<button type="submit" class="button button-secondary"
			        data-confirm="<?php esc_attr_e( 'Disconnect Google account?', 'dih-google-smtp' ); ?>">
				<?php esc_html_e( 'Disconnect', 'dih-google-smtp' ); ?>
			</button>
		</form>
	<?php else : ?>
		<p><?php esc_html_e( 'Status:', 'dih-google-smtp' ); ?> <span class="dih-smtp-badge dih-smtp-badge-red"><?php esc_html_e( 'Not Connected', 'dih-google-smtp' ); ?></span></p>
	<?php endif; ?>
</div>

<!-- Setup guide -->
<div class="dih-smtp-card">
	<h2><?php esc_html_e( 'Google Cloud Setup — Step by Step', 'dih-google-smtp' ); ?></h2>
	<ol class="dih-smtp-steps">
		<li>
			<?php
			printf(
				/* translators: %s: link to the Google Cloud Console. */
				esc_html__( 'Go to %s → select your project.', 'dih-google-smtp' ),
				'<a href="https://console.cloud.google.com/" target="_blank">console.cloud.google.com</a>'
			);
			?>
		</li>
		<li><?php esc_html_e( 'Enable the Gmail API under APIs & Services → Library.', 'dih-google-smtp' ); ?></li>
		<li><?php esc_html_e( 'Go to Google Auth Platform → Clients → Create OAuth 2.0 Client ID.', 'dih-google-smtp' ); ?></li>
		<li><?php esc_html_e( 'Application type: Web application.', 'dih-google-smtp' ); ?></li>
		<li><?php esc_html_e( 'Under Authorized redirect URIs paste the URL from the blue box above.', 'dih-google-smtp' ); ?></li>
		<li><?php esc_html_e( 'Copy Client ID and Client Secret into the Settings tab → Save.', 'dih-google-smtp' ); ?></li>
		<?php // Must come before connecting: Google rejects non-test users while the app is in Testing mode. ?>
		<li><?php esc_html_e( 'Under Audience → Add your email as a test user if app is in testing mode.', 'dih-google-smtp' ); ?></li>
		<li><?php esc_html_e( 'Return here and click Connect Google Account below.', 'dih-google-smtp' ); ?></li>
	</ol>
</div>

<!-- Connect button -->
<?php if ( $dih_smtp_has_creds && ! $dih_smtp_connected ) : ?>
	<div class="dih-smtp-card">
		<h2><?php esc_html_e( 'Connect Your Google Account', 'dih-google-smtp' ); ?></h2>
		<p><?php esc_html_e( 'Credentials saved. Click below to authorize.', 'dih-google-smtp' ); ?></p>
		<?php // Neutral WordPress icon — Google's "G" logo is only allowed in buttons that follow Google's sign-in branding rules. ?>
		<a href="<?php echo esc_url( DIH_SMTP_OAuth::get_auth_url() ); ?>" class="button button-primary dih-smtp-connect-btn">
			<span class="dashicons dashicons-lock" aria-hidden="true"></span>
			<?php esc_html_e( 'Connect Google Account', 'dih-google-smtp' ); ?>
		</a>
	</div>
<?php elseif ( ! $dih_smtp_has_creds ) : ?>
	<div class="dih-smtp-card">
		<p class="description">
			<?php
			printf(
				/* translators: %s: link to the Settings tab. */
				esc_html__( '⚠️ Enter your OAuth2 Client ID and Client Secret in the %s first, then return here.', 'dih-google-smtp' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=dih-google-smtp&tab=settings' ) ) . '">' . esc_html__( 'Settings tab', 'dih-google-smtp' ) . '</a>'
			);
			?>
		</p>
	</div>
<?php endif; ?>
