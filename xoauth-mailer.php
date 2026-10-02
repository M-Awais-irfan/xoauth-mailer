<?php
/**
 * Plugin Name:       XOAuth Mailer for Google Workspace
 * Plugin URI:        https://github.com/M-Awais-irfan/xoauth-mailer
 * Description:       Send WordPress email through Google Workspace using Google's XOAUTH2 SMTP mechanism (or an App Password), built on WordPress's bundled PHPMailer with no extra libraries.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Awais Irfan
 * Author URI:        https://awaisirfan.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       xoauth-mailer
 *
 * @package XOAuth_Mailer
 */

defined( 'ABSPATH' ) || exit;

// Constants
define( 'XOAM_VERSION',    '1.0.0' );
define( 'XOAM_FILE',       __FILE__ );
define( 'XOAM_DIR',        plugin_dir_path( __FILE__ ) );
define( 'XOAM_URL',        plugin_dir_url( __FILE__ ) );
define( 'XOAM_OPTION_KEY', 'xoam_settings' );
define( 'XOAM_LOG_KEY',    'xoam_debug_log' );
define( 'XOAM_TOKEN_KEY',  'xoam_oauth_token' );

// Autoloader
spl_autoload_register( function ( string $class ): void {
	$map = [
		'XOAM_Core'     => 'includes/class-xoam-core.php',
		'XOAM_Settings' => 'includes/class-xoam-settings.php',
		'XOAM_Logger'   => 'includes/class-xoam-logger.php',
		'XOAM_Mailer'   => 'includes/class-xoam-mailer.php',
		'XOAM_OAuth'    => 'includes/class-xoam-oauth.php',
		'XOAM_Admin'    => 'admin/class-xoam-admin.php',
		'XOAM_SMTP'     => 'includes/class-xoam-smtp.php',
	];

	if ( isset( $map[ $class ] ) ) {
		$file = XOAM_DIR . $map[ $class ];
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
} );

// Activation / Deactivation
register_activation_hook( __FILE__, [ 'XOAM_Core', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'XOAM_Core', 'deactivate' ] );

// Boot
add_action( 'plugins_loaded', [ 'XOAM_Core', 'init' ] );
