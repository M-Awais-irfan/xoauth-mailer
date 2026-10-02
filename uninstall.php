<?php
/**
 * Uninstall script.
 *
 * Runs when the plugin is deleted from the WordPress plugins screen.
 * Removes all plugin data from the database.
 *
 * Deactivation keeps all data; only uninstall removes it.
 *
 * @package XOAuth_Mailer
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'xoam_settings' );
delete_option( 'xoam_debug_log' );
delete_option( 'xoam_oauth_token' );
delete_option( 'xoam_db_version' );

// Legacy data from versions up to 2.1.x (former "dih" prefix), in case the
// plugin is deleted before the migration in XOAM_Core ever ran.
foreach ( [ 'dih_google_smtp_settings', 'dih_google_smtp_debug_log', 'dih_google_smtp_oauth_token', 'dih_smtp_activated_at', 'dih_smtp_db_version' ] as $xoam_legacy_option ) {
	delete_option( $xoam_legacy_option );
}
delete_transient( 'dih_smtp_oauth_state' );

// No raw SQL needed: the XOAUTH2 credential is no longer stored in transients,
// and per-flow state transients (xoam_oauth_state_*) expire after
// 10 minutes and are purged by core's daily delete_expired_transients event.
