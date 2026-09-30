<?php
/**
 * Settings management.
 *
 * Single source of truth for all plugin settings.
 * Add defaults here when adding new mailer providers.
 *
 * @package DIH_Google_SMTP
 */

defined( 'ABSPATH' ) || exit;

class DIH_SMTP_Settings {

	/**
	 * Default settings.
	 * To add a new provider in future, add its defaults here.
	 */
	private static array $defaults = [
		// Sender identity
		'from_email'          => '',
		'from_name'           => '',

		// SMTP server
		'smtp_host'           => 'smtp.gmail.com',
		'smtp_port'           => '587',
		'smtp_encryption'     => 'tls',

		// Auth
		'auth_method'         => 'app_password', // 'app_password' | 'oauth2'
		'username'            => '',
		'app_password'        => '',

		// OAuth2
		'oauth_client_id'     => '',
		'oauth_client_secret' => '',

		// Debug
		'debug_enabled'       => '0',
	];

	/**
	 * Secret settings, mapped to the wp-config.php constant that can override them.
	 * A constant keeps the secret out of the database (and out of DB backups).
	 */
	private const SECRETS = [
		'app_password'        => 'DIH_SMTP_APP_PASSWORD',
		'oauth_client_secret' => 'DIH_SMTP_CLIENT_SECRET',
	];

	/**
	 * Get all settings merged with defaults.
	 */
	public static function get(): array {
		$saved = get_option( DIH_SMTP_OPTION_KEY, [] );
		$merged = wp_parse_args( $saved, self::$defaults );

		// Set dynamic default for from_name if not saved yet
		if ( empty( $merged['from_name'] ) ) {
			$merged['from_name'] = get_bloginfo( 'name' );
		}

		// wp-config.php constants win over saved values
		foreach ( self::SECRETS as $key => $constant ) {
			if ( defined( $constant ) ) {
				$merged[ $key ] = (string) constant( $constant );
			}
		}

		return $merged;
	}

	/**
	 * Whether a secret setting is defined as a constant in wp-config.php.
	 */
	public static function is_constant( string $key ): bool {
		return isset( self::SECRETS[ $key ] ) && defined( self::SECRETS[ $key ] );
	}

	/**
	 * Get a single setting value.
	 */
	public static function get_one( string $key, $fallback = '' ) {
		$settings = self::get();
		return $settings[ $key ] ?? $fallback;
	}

	/**
	 * Sanitize and save settings from $_POST.
	 * Called by WordPress settings API via register_setting().
	 */
	public static function sanitize( $input ): array {
		// sanitize_option runs on every update_option() call, not just our form,
		// so a non-array value (e.g. from WP-CLI) must not cause a TypeError.
		if ( ! is_array( $input ) ) {
			$input = [];
		}

		$clean = [];

		$clean['from_email']      = sanitize_email( $input['from_email'] ?? '' );
		$clean['from_name']       = sanitize_text_field( $input['from_name'] ?? '' );
		$clean['smtp_host']       = sanitize_text_field( $input['smtp_host'] ?? 'smtp.gmail.com' );
		$clean['smtp_encryption'] = in_array( $input['smtp_encryption'] ?? '', [ 'tls', 'ssl', 'none' ], true )
			? $input['smtp_encryption']
			: 'tls';
		$clean['smtp_port']       = in_array( (string) ( $input['smtp_port'] ?? '' ), [ '587', '465', '25' ], true )
			? (string) $input['smtp_port']
			: '587';
		$clean['auth_method']     = in_array( $input['auth_method'] ?? '', [ 'app_password', 'oauth2' ], true )
			? $input['auth_method']
			: 'app_password';
		$clean['username']            = sanitize_email( $input['username'] ?? '' );
		$clean['oauth_client_id']     = sanitize_text_field( $input['oauth_client_id'] ?? '' );
		$clean['debug_enabled']       = ! empty( $input['debug_enabled'] ) ? '1' : '0';

		// Secrets are never echoed back into the form (see tab-settings.php),
		// so a blank submission means "keep what's saved", not "erase it".
		$saved = (array) get_option( DIH_SMTP_OPTION_KEY, [] );
		foreach ( array_keys( self::SECRETS ) as $key ) {
			$value         = sanitize_text_field( $input[ $key ] ?? '' );
			$clean[ $key ] = '' !== $value ? $value : ( $saved[ $key ] ?? '' );
		}

		DIH_SMTP_Logger::log( 'Settings saved by user.' );

		return $clean;
	}
}
