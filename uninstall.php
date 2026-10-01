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

// OAuth state transients (xoam_oauth_state_*) expire after 10 minutes and are
// purged by core's daily delete_expired_transients event, so no SQL is needed.
