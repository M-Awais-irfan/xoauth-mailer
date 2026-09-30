<?php
/**
 * Uninstall script.
 *
 * Runs when the plugin is deleted from the WordPress plugins screen.
 * Removes ALL plugin data from the database.
 *
 * Note: Deactivation does NOT delete data — only uninstall does.
 *
 * @package DIH_Google_SMTP
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'dih_google_smtp_settings' );
delete_option( 'dih_google_smtp_debug_log' );
delete_option( 'dih_google_smtp_oauth_token' );
delete_option( 'dih_smtp_activated_at' );
delete_option( 'dih_smtp_db_version' );

// Legacy single-key state transient from earlier versions
delete_transient( 'dih_smtp_oauth_state' );

// No raw SQL needed: the XOAUTH2 credential is no longer stored in transients,
// and per-flow state transients (dih_smtp_oauth_state_*) expire after
// 10 minutes and are purged by core's daily delete_expired_transients event.
