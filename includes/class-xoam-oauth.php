<?php
/**
 * OAuth2 flow management.
 *
 * Uses a WordPress REST API endpoint (/wp-json/xoauth-mailer/v1/oauth-callback)
 * as the redirect URI, so plugins that look for an OAuth ?code= parameter
 * on admin_init or admin_post don't pick up this callback.
 *
 * @package XOAuth_Mailer
 */

defined( 'ABSPATH' ) || exit;

class XOAM_OAuth {

	private const REST_NAMESPACE = 'xoauth-mailer/v1';
	private const REST_ROUTE     = '/oauth-callback';

	// Registration

	public static function register(): void {
		add_action( 'rest_api_init', [ __CLASS__, 'register_rest_route' ] );
	}

	/**
	 * Register REST API callback endpoint.
	 *
	 * Redirect URI: https://yoursite.com/wp-json/xoauth-mailer/v1/oauth-callback
	 *
	 * A REST route is used instead of admin_init / admin_post because some
	 * OAuth plugins check every admin request for ?code= and can intercept
	 * the callback. The REST namespace is unique to this plugin.
	 */
	public static function register_rest_route(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			[
				'methods'             => 'GET',
				'callback'            => [ __CLASS__, 'handle_callback' ],
				// No WP authentication required at REST level.
				// Security is handled inside handle_callback() via state transient.
				// This is necessary because Google's redirect does not carry WP session cookies.
				'permission_callback' => '__return_true',
				// Let the REST layer type-check and sanitize the query params.
				'args'                => [
					'code'  => [
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'state' => [
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	// Redirect URI

	/**
	 * Get the OAuth2 redirect URI.
	 * This is what you add to Google Cloud Console > Authorized redirect URIs.
	 */
	public static function get_redirect_uri(): string {
		return rest_url( self::REST_NAMESPACE . self::REST_ROUTE );
	}

	// Authorization URL

	/**
	 * Build the Google OAuth2 authorization URL.
	 * Stores the initiating user ID in a transient keyed by the state value,
	 * so the REST callback can verify the flow without an active session.
	 */
	public static function get_auth_url(): string {
		$s = XOAM_Settings::get();

		$state = wp_generate_password( 32, false );

		// One transient per flow, keyed by the state itself, so two admins
		// (or two browser tabs) never overwrite each other's pending flow.
		set_transient( 'xoam_oauth_state_' . $state, get_current_user_id(), 10 * MINUTE_IN_SECONDS );

		$params = [
			'client_id'     => $s['oauth_client_id'],
			'redirect_uri'  => self::get_redirect_uri(),
			'response_type' => 'code',
			'scope'         => 'https://mail.google.com/',
			'access_type'   => 'offline',
			'prompt'        => 'consent',
			'state'         => $state,
		];

		return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params );
	}

	// Callback Handler

	/**
	 * Handle the Google OAuth2 callback via REST API.
	 *
	 * Security relies on the state transient, not on a WordPress session:
	 * the redirect from Google carries no REST nonce, so WordPress treats
	 * the request as logged out.
	 *
	 * The state transient is:
	 *  - Cryptographically random (32 chars)
	 *  - Single-use (deleted immediately after validation)
	 *  - Short-lived (10 minutes)
	 *  - Tied to the initiating user ID (stored inside the transient)
	 *
	 * @param WP_REST_Request $request REST request object.
	 */
	public static function handle_callback( WP_REST_Request $request ): void {
		$admin_url = admin_url( 'admin.php?page=xoauth-mailer&tab=oauth' );

		// Already sanitized by the route's 'args' schema
		$code  = (string) $request->get_param( 'code' );
		$state = (string) $request->get_param( 'state' );

		// Validate the state value. This is the main security check; it stands in for a nonce.
		// Only accept the 32-char alphanumeric shape get_auth_url() generates before using
		// it in a transient name. \z is used instead of $ because, in PCRE, $ also matches
		// before a trailing newline.
		$user_id = preg_match( '/^[A-Za-z0-9]{32}\z/', $state )
			? (int) get_transient( 'xoam_oauth_state_' . $state )
			: 0;

		if ( ! $user_id ) {
			// WARN, not ERROR: this endpoint is public, so anyone can reach this
			// branch. ERROR level would let them flood the always-on log.
			XOAM_Logger::log( 'OAuth callback: state missing, expired or invalid.', 'WARN' );
			wp_safe_redirect( $admin_url . '&xoam_notice=oauth_error' );
			exit;
		}

		// Single use: delete immediately.
		delete_transient( 'xoam_oauth_state_' . $state );

		// The initiating admin may have lost the capability during the 10-minute window
		if ( ! user_can( $user_id, 'manage_options' ) ) {
			XOAM_Logger::log( 'OAuth callback: initiating user no longer has manage_options.', 'ERROR' );
			wp_safe_redirect( $admin_url . '&xoam_notice=oauth_error' );
			exit;
		}

		if ( empty( $code ) ) {
			XOAM_Logger::log( 'OAuth callback: no authorization code received.', 'ERROR' );
			wp_safe_redirect( $admin_url . '&xoam_notice=oauth_error' );
			exit;
		}

		XOAM_Logger::log( 'OAuth callback validated. Exchanging code for token.' );
		XOAM_Logger::log( 'Initiated by user ID: ' . $user_id );

		// Exchange code for token
		$s        = XOAM_Settings::get();
		$response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
			'timeout' => 15,
			'body'    => [
				'code'          => $code,
				'client_id'     => $s['oauth_client_id'],
				'client_secret' => $s['oauth_client_secret'],
				'redirect_uri'  => self::get_redirect_uri(),
				'grant_type'    => 'authorization_code',
			],
		] );

		if ( is_wp_error( $response ) ) {
			XOAM_Logger::log( 'Token exchange WP error: ' . $response->get_error_message(), 'ERROR' );
			wp_safe_redirect( $admin_url . '&xoam_notice=oauth_error' );
			exit;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = json_decode( wp_remote_retrieve_body( $response ), true );

		XOAM_Logger::log( 'Token exchange HTTP status: ' . $http_code );

		if ( ! empty( $body['access_token'] ) ) {
			$token = [
				'access_token'  => $body['access_token'],
				'refresh_token' => $body['refresh_token'] ?? '',
				'expires_at'    => time() + (int) ( $body['expires_in'] ?? 3600 ),
			];
			update_option( XOAM_TOKEN_KEY, $token, false ); // false = don't autoload
			XOAM_Logger::log( 'OAuth2 token stored. Has refresh_token: ' . ( ! empty( $token['refresh_token'] ) ? 'YES' : 'NO' ) );
			wp_safe_redirect( $admin_url . '&xoam_notice=oauth_success' );
			exit;
		}

		$error = ( $body['error'] ?? 'unknown' ) . ': ' . ( $body['error_description'] ?? '' );
		XOAM_Logger::log( 'Token exchange failed: ' . $error, 'ERROR' );
		wp_safe_redirect( $admin_url . '&xoam_notice=oauth_error' );
		exit;
	}

	// Token Refresh

	/**
	 * Refresh an expired OAuth2 access token using the refresh token.
	 *
	 * @param array $token    Existing token data.
	 * @param array $settings Plugin settings.
	 * @return array Updated token data, or empty array on failure.
	 */
	public static function refresh_token( array $token, array $settings ): array {
		if ( empty( $token['refresh_token'] ) ) {
			XOAM_Logger::log( 'No refresh token available, cannot refresh.', 'ERROR' );
			return [];
		}

		$response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
			'timeout' => 15,
			'body'    => [
				'client_id'     => $settings['oauth_client_id'],
				'client_secret' => $settings['oauth_client_secret'],
				'refresh_token' => $token['refresh_token'],
				'grant_type'    => 'refresh_token',
			],
		] );

		if ( is_wp_error( $response ) ) {
			XOAM_Logger::log( 'Token refresh error: ' . $response->get_error_message(), 'ERROR' );
			return [];
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! empty( $body['access_token'] ) ) {
			$new_token = [
				'access_token'  => $body['access_token'],
				'refresh_token' => $token['refresh_token'],
				'expires_at'    => time() + (int) ( $body['expires_in'] ?? 3600 ),
			];
			update_option( XOAM_TOKEN_KEY, $new_token, false ); // false = don't autoload
			XOAM_Logger::log( 'Token refreshed successfully.' );
			return $new_token;
		}

		// invalid_grant: the refresh token was revoked or has expired and will never
		// work again. Drop it so the OAuth2 tab shows "Not Connected".
		if ( 'invalid_grant' === ( $body['error'] ?? '' ) ) {
			delete_option( XOAM_TOKEN_KEY );
			XOAM_Logger::log( 'Google rejected the refresh token (revoked or expired). Reconnect in the OAuth2 tab.', 'ERROR' );
			return [];
		}

		XOAM_Logger::log( 'Token refresh failed: ' . wp_json_encode( $body ), 'ERROR' );
		return [];
	}

	// Disconnect

	/**
	 * Delete the stored token, optionally revoking it at Google first.
	 *
	 * Deleting locally leaves the refresh token valid at Google. Revoking kills
	 * the whole grant for this Client ID + account, which also disconnects any
	 * other site sharing them, so it is opt-in.
	 *
	 * @param bool $revoke Also revoke the grant at Google.
	 */
	public static function disconnect( bool $revoke = false ): void {
		$token = self::get_token();
		$value = ! empty( $token['refresh_token'] ) ? $token['refresh_token'] : ( $token['access_token'] ?? '' );

		if ( $revoke && '' !== $value ) {
			$response = wp_remote_post( 'https://oauth2.googleapis.com/revoke', [
				'timeout' => 15,
				'body'    => [ 'token' => $value ],
			] );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				XOAM_Logger::log( 'Token revoke at Google failed. Remove access manually at myaccount.google.com/permissions.', 'ERROR' );
			} else {
				XOAM_Logger::log( 'OAuth2 grant revoked at Google.' );
			}
		}

		delete_option( XOAM_TOKEN_KEY );
		XOAM_Logger::log( 'OAuth2 token disconnected by admin.' );
	}

	// Status

	/**
	 * Whether the plugin can get a working access token.
	 *
	 * With a refresh token a new access token can always be requested.
	 * Without one, the stored access token only works until it expires.
	 */
	public static function is_connected(): bool {
		$token = self::get_token();

		if ( ! empty( $token['refresh_token'] ) ) {
			return true;
		}

		return ! empty( $token['access_token'] ) && time() < (int) ( $token['expires_at'] ?? 0 );
	}

	public static function get_token(): array {
		return get_option( XOAM_TOKEN_KEY, [] );
	}
}
