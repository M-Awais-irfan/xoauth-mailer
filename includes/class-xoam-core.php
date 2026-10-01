<?php
/**
 * Core bootstrap class.
 *
 * Registers all hooks, initialises sub-systems, and handles
 * activation / deactivation lifecycle events.
 *
 * @package XOAuth_Mailer
 */

defined( 'ABSPATH' ) || exit;

class XOAM_Core {

	/** @var XOAM_Core|null Singleton instance */
	private static ?XOAM_Core $instance = null;

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function init(): void {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
	}

	private function __construct() {
		$this->load_dependencies();
		self::maybe_upgrade();
		$this->register_hooks();
	}

	// ── Upgrades ──────────────────────────────────────────────────────────────

	/**
	 * Run upgrade() once whenever the plugin version changes.
	 *
	 * The activation hook does NOT fire when a plugin is updated in place,
	 * so sites that update (rather than reactivate) would otherwise never
	 * get upgrade tasks. Costs one autoloaded option read per request.
	 */
	private static function maybe_upgrade(): void {
		if ( XOAM_VERSION !== get_option( 'xoam_db_version' ) ) {
			self::upgrade();
		}
	}

	/**
	 * One-time data tasks for the current version. Safe to run more than once.
	 */
	private static function upgrade(): void {
		self::migrate_legacy_options();

		// Secrets live in these options — keep them out of the autoloaded set so
		// they're only read when needed. add_option() is a no-op if it exists.
		add_option( XOAM_OPTION_KEY, [], '', false );
		if ( function_exists( 'wp_set_options_autoload' ) ) { // WP 6.4+
			wp_set_options_autoload( [ XOAM_OPTION_KEY, XOAM_TOKEN_KEY ], false );
		}

		// Autoloaded on purpose: maybe_upgrade() reads it on every request, and a
		// non-autoloaded option would cost an extra database query each time.
		update_option( 'xoam_db_version', XOAM_VERSION, true );

		XOAM_Logger::log( 'Upgrade tasks completed for version ' . XOAM_VERSION . '.' );
	}

	/**
	 * Move data saved by versions up to 2.1.x, which used a "dih" prefix,
	 * to the current option names — so existing sites keep their settings
	 * and Google connection after the rename.
	 */
	private static function migrate_legacy_options(): void {
		$map = [
			'dih_google_smtp_settings'    => XOAM_OPTION_KEY,
			'dih_google_smtp_oauth_token' => XOAM_TOKEN_KEY,
			'dih_google_smtp_debug_log'   => XOAM_LOG_KEY,
		];

		foreach ( $map as $old => $new ) {
			$value = get_option( $old, null );
			if ( null === $value ) {
				continue;
			}
			// Never overwrite data already saved under the new name.
			if ( false === get_option( $new ) ) {
				add_option( $new, $value, '', false );
			}
			delete_option( $old );
		}

		delete_option( 'dih_smtp_activated_at' );
		delete_option( 'dih_smtp_db_version' );
	}

	// ── Dependencies ──────────────────────────────────────────────────────────

	private function load_dependencies(): void {
		// Load PHPMailer SMTP base class early so XOAM_SMTP can extend it.
		// We load only SMTP.php and PHPMailer.php — NOT OAuth.php, which has its
		// own broken dependency chain in some WP versions.
		$phpmailer_dir = ABSPATH . WPINC . '/PHPMailer/';

		foreach ( [ 'Exception.php', 'SMTP.php', 'PHPMailer.php' ] as $file ) {
			if ( file_exists( $phpmailer_dir . $file ) ) {
				require_once $phpmailer_dir . $file;
			}
		}

		// Now safe to load our SMTP subclass
		require_once XOAM_DIR . 'includes/class-xoam-smtp.php';
	}

	private function register_hooks(): void {
		// Mailer — intercepts wp_mail
		XOAM_Mailer::register();

		// OAuth — REST API callback endpoint (conflict-free solution)
		XOAM_OAuth::register();

		// Admin UI
		if ( is_admin() ) {
			XOAM_Admin::register();
		}

		// Log wp_mail failures
		add_action( 'wp_mail_failed', [ 'XOAM_Logger', 'log_mail_failure' ] );
	}

	// ── Activation ────────────────────────────────────────────────────────────

	/**
	 * Runs on plugin activation.
	 * No rewrite flush needed: REST routes are registered on every request
	 * and served through core's existing /wp-json/ rewrite rule.
	 */
	public static function activate(): void {
		// Store activation timestamp
		update_option( 'xoam_activated_at', time() );

		// Same tasks as an in-place update — one code path for both
		self::upgrade();

		XOAM_Logger::log( 'Plugin activated. Version: ' . XOAM_VERSION );
	}

	// ── Deactivation ──────────────────────────────────────────────────────────

	/**
	 * Runs on plugin deactivation.
	 * Does NOT delete settings or tokens — use uninstall.php for that.
	 */
	public static function deactivate(): void {
		XOAM_Logger::log( 'Plugin deactivated.' );
	}
}
