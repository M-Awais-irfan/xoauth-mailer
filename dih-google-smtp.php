<?php
/**
 * Plugin Name:       DIH SMTP for Google Workspace
 * Plugin URI:        https://github.com/M-Awais-irfan/xoauth-mailer
 * Description:       Send all WordPress emails via Google Workspace using OAuth2 or App Password.
 * Version:           2.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Awais Irfan
 * Author URI:        https://awaisirfan.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dih-google-smtp
 *
 * @package DIH_Google_SMTP
 */

defined( 'ABSPATH' ) || exit;

// ── Constants ─────────────────────────────────────────────────────────────────
define( 'DIH_SMTP_VERSION',    '2.1.0' );
define( 'DIH_SMTP_FILE',       __FILE__ );
define( 'DIH_SMTP_DIR',        plugin_dir_path( __FILE__ ) );
define( 'DIH_SMTP_URL',        plugin_dir_url( __FILE__ ) );
define( 'DIH_SMTP_OPTION_KEY', 'dih_google_smtp_settings' );
define( 'DIH_SMTP_LOG_KEY',    'dih_google_smtp_debug_log' );
define( 'DIH_SMTP_TOKEN_KEY',  'dih_google_smtp_oauth_token' );

// ── Autoloader ────────────────────────────────────────────────────────────────
spl_autoload_register( function ( string $class ): void {
	$map = [
		'DIH_SMTP_Core'     => 'includes/class-dih-smtp-core.php',
		'DIH_SMTP_Settings' => 'includes/class-dih-smtp-settings.php',
		'DIH_SMTP_Logger'   => 'includes/class-dih-smtp-logger.php',
		'DIH_SMTP_Mailer'   => 'includes/class-dih-smtp-mailer.php',
		'DIH_SMTP_OAuth'    => 'includes/class-dih-smtp-oauth.php',
		'DIH_SMTP_Admin'    => 'admin/class-dih-smtp-admin.php',
		'DIH_SMTP_XOAUTH2'  => 'includes/class-dih-smtp-xoauth2.php',
	];

	if ( isset( $map[ $class ] ) ) {
		$file = DIH_SMTP_DIR . $map[ $class ];
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
} );

// ── Activation / Deactivation ─────────────────────────────────────────────────
register_activation_hook( __FILE__, [ 'DIH_SMTP_Core', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'DIH_SMTP_Core', 'deactivate' ] );

// ── Boot ──────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', [ 'DIH_SMTP_Core', 'init' ] );
