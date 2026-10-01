<?php
defined( 'ABSPATH' ) || exit;
$xoam_connected    = XOAM_OAuth::is_connected();
$xoam_token        = XOAM_OAuth::get_token();
$xoam_has_creds    = ! empty( $s['oauth_client_id'] ) && ! empty( $s['oauth_client_secret'] );
$xoam_redirect_uri = XOAM_OAuth::get_redirect_uri();
?>

<!-- Redirect URI box -->
<div class="xoam-card xoam-card-blue">
	<h2><?php esc_html_e( 'Your Redirect URI: add this to Google Cloud Console', 'xoauth-mailer' ); ?></h2>
	<p><?php esc_html_e( 'Copy this exact URL into Google Cloud > OAuth Client > Authorized redirect URIs. One character difference causes Error 400.', 'xoauth-mailer' ); ?></p>
	<div class="xoam-copy-row">
		<input type="text" id="xoam-redirect-uri" value="<?php echo esc_attr( $xoam_redirect_uri ); ?>" readonly class="large-text">
		<button type="button" class="button button-secondary" data-copy="xoam-redirect-uri">
			<?php esc_html_e( 'Copy', 'xoauth-mailer' ); ?>
		</button>
	</div>
</div>

<!-- Connection status -->
<div class="xoam-card">
	<h2><?php esc_html_e( 'Connection Status', 'xoauth-mailer' ); ?></h2>
	<?php if ( $xoam_connected ) : ?>
		<p><?php esc_html_e( 'Status:', 'xoauth-mailer' ); ?> <span class="xoam-badge xoam-badge-green"><?php esc_html_e( 'Connected', 'xoauth-mailer' ); ?></span></p>
		<?php if ( ! empty( $xoam_token['expires_at'] ) ) : ?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: token expiry date and time (UTC). */
					esc_html__( 'Token expires: %s (auto-refreshes)', 'xoauth-mailer' ),
					esc_html( gmdate( 'Y-m-d H:i:s', $xoam_token['expires_at'] ) )
				);
				?>
			</p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;">
			<?php wp_nonce_field( 'xoam_disconnect_oauth' ); ?>
			<input type="hidden" name="action" value="xoam_disconnect_oauth">
			<p>
				<label>
					<input type="checkbox" name="revoke" value="1">
					<?php esc_html_e( 'Also revoke access at Google (this also disconnects any other site using the same Client ID and Google account)', 'xoauth-mailer' ); ?>
				</label>
			</p>
			<button type="submit" class="button button-secondary"
			        data-confirm="<?php esc_attr_e( 'Disconnect Google account?', 'xoauth-mailer' ); ?>">
				<?php esc_html_e( 'Disconnect', 'xoauth-mailer' ); ?>
			</button>
		</form>
	<?php else : ?>
		<p><?php esc_html_e( 'Status:', 'xoauth-mailer' ); ?> <span class="xoam-badge xoam-badge-red"><?php esc_html_e( 'Not Connected', 'xoauth-mailer' ); ?></span></p>
	<?php endif; ?>
</div>

<!-- Setup guide -->
<div class="xoam-card">
	<h2><?php esc_html_e( 'Google Cloud Setup, Step by Step', 'xoauth-mailer' ); ?></h2>
	<ol class="xoam-steps">
		<li>
			<?php
			printf(
				/* translators: %s: link to the Google Cloud Console. */
				esc_html__( 'Go to %s > select your project.', 'xoauth-mailer' ),
				'<a href="https://console.cloud.google.com/" target="_blank">console.cloud.google.com</a>'
			);
			?>
		</li>
		<li><?php esc_html_e( 'Enable the Gmail API under APIs & Services > Library.', 'xoauth-mailer' ); ?></li>
		<li><?php esc_html_e( 'Go to Google Auth Platform > Clients > Create OAuth 2.0 Client ID.', 'xoauth-mailer' ); ?></li>
		<li><?php esc_html_e( 'Application type: Web application.', 'xoauth-mailer' ); ?></li>
		<li><?php esc_html_e( 'Under Authorized redirect URIs paste the URL from the blue box above.', 'xoauth-mailer' ); ?></li>
		<li><?php esc_html_e( 'Copy Client ID and Client Secret into the Settings tab > Save.', 'xoauth-mailer' ); ?></li>
		<?php // Must come before connecting: Google rejects non-test users while the app is in Testing mode. ?>
		<li><?php esc_html_e( 'Under Audience > Add your email as a test user if app is in testing mode.', 'xoauth-mailer' ); ?></li>
		<li><?php esc_html_e( 'Return here and click Connect Google Account below.', 'xoauth-mailer' ); ?></li>
	</ol>
</div>

<!-- Connect button -->
<?php if ( $xoam_has_creds && ! $xoam_connected ) : ?>
	<div class="xoam-card">
		<h2><?php esc_html_e( 'Connect Your Google Account', 'xoauth-mailer' ); ?></h2>
		<p><?php esc_html_e( 'Credentials saved. Click below to authorize.', 'xoauth-mailer' ); ?></p>
		<a href="<?php echo esc_url( XOAM_OAuth::get_auth_url() ); ?>" class="button button-primary xoam-connect-btn">
			<span class="dashicons dashicons-lock" aria-hidden="true"></span>
			<?php esc_html_e( 'Connect Google Account', 'xoauth-mailer' ); ?>
		</a>
	</div>
<?php elseif ( ! $xoam_has_creds ) : ?>
	<div class="xoam-card">
		<p class="description">
			<?php
			printf(
				/* translators: %s: link to the Settings tab. */
				esc_html__( 'Enter your OAuth2 Client ID and Client Secret in the %s first, then return here.', 'xoauth-mailer' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=xoauth-mailer&tab=settings' ) ) . '">' . esc_html__( 'Settings tab', 'xoauth-mailer' ) . '</a>'
			);
			?>
		</p>
	</div>
<?php endif; ?>
