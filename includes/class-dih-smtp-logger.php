<?php
/**
 * Debug logger.
 *
 * Stores log entries in the database (wp_options).
 * Keeps last 100 entries. Only writes when debug is enabled.
 *
 * @package DIH_Google_SMTP
 */

defined( 'ABSPATH' ) || exit;

class DIH_SMTP_Logger {

	private const MAX_ENTRIES = 100;

	/**
	 * Write a log entry.
	 *
	 * @param string $message Log message.
	 * @param string $level   INFO | WARN | ERROR | DEBUG
	 */
	public static function log( string $message, string $level = 'INFO' ): void {
		// Always log ERROR level regardless of debug toggle
		if ( $level !== 'ERROR' ) {
			$debug = DIH_SMTP_Settings::get_one( 'debug_enabled' );
			if ( $debug !== '1' ) {
				return;
			}
		}

		$log   = get_option( DIH_SMTP_LOG_KEY, [] );
		$log[] = [
			'time'    => current_time( 'mysql' ),
			'level'   => strtoupper( $level ),
			'message' => sanitize_text_field( $message ),
		];

		// Keep only last N entries
		if ( count( $log ) > self::MAX_ENTRIES ) {
			$log = array_slice( $log, -self::MAX_ENTRIES );
		}

		update_option( DIH_SMTP_LOG_KEY, $log, false );
	}

	/**
	 * Retrieve all log entries (newest first).
	 */
	public static function get_entries(): array {
		return array_reverse( get_option( DIH_SMTP_LOG_KEY, [] ) );
	}

	/**
	 * Clear all log entries.
	 */
	public static function clear(): void {
		delete_option( DIH_SMTP_LOG_KEY );
	}

	/**
	 * Hook callback for wp_mail_failed action.
	 */
	public static function log_mail_failure( \WP_Error $error ): void {
		self::log( 'wp_mail() failed: ' . $error->get_error_message(), 'ERROR' );
	}
}
