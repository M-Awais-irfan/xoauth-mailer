<?php
/**
 * Core bootstrap class.
 *
 * Registers all hooks, initialises sub-systems, and handles
 * activation / deactivation lifecycle events.
 *
 * @package DIH_Google_SMTP
 */

defined( 'ABSPATH' ) || exit;

class DIH_SMTP_Core {

	/** @var DIH_SMTP_Core|null Singleton instance */
	private static ?DIH_SMTP_Core $instance = null;

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
		if ( DIH_SMTP_VERSION !== get_option( 'dih_smtp_db_version' ) ) {
			self::upgrade();
		}
	}

	/**
	 * One-time data tasks for the current version. Safe to run more than once.
	 */
	private static function upgrade(): void {
		// Secrets live in these options — keep them out of the autoloaded set so
		// they're only read when needed. add_option() is a no-op if it exists.
		add_option( DIH_SMTP_OPTION_KEY, [], '', false );
		if ( function_exists( 'wp_set_options_autoload' ) ) { // WP 6.4+
			wp_set_options_autoload( [ DIH_SMTP_OPTION_KEY, DIH_SMTP_TOKEN_KEY ], false );
		}

		// Autoloaded on purpose: maybe_upgrade() reads it on every request, and a
		// non-autoloaded option would cost an extra database query each time.
		update_option( 'dih_smtp_db_version', DIH_SMTP_VERSION, true );

		DIH_SMTP_Logger::log( 'Upgrade tasks completed for version ' . DIH_SMTP_VERSION . '.' );
	}

	// ── Dependencies ──────────────────────────────────────────────────────────

	private function load_dependencies(): void {
		// Load PHPMailer SMTP base class early so DIH_SMTP_XOAUTH2 can extend it.
		// We load only SMTP.php and PHPMailer.php — NOT OAuth.php, which has its
		// own broken dependency chain in some WP versions.
		$phpmailer_dir = ABSPATH . WPINC . '/PHPMailer/';

		foreach ( [ 'Exception.php', 'SMTP.php', 'PHPMailer.php' ] as $file ) {
			if ( file_exists( $phpmailer_dir . $file ) ) {
				require_once $phpmailer_dir . $file;
			}
		}

		// Now safe to load our SMTP subclass
		require_once DIH_SMTP_DIR . 'includes/class-dih-smtp-xoauth2.php';
	}

	private function register_hooks(): void {
		// Mailer — intercepts wp_mail
		DIH_SMTP_Mailer::register();

		// OAuth — REST API callback endpoint (conflict-free solution)
		DIH_SMTP_OAuth::register();

		// Admin UI
		if ( is_admin() ) {
			DIH_SMTP_Admin::register();
		}

		// Log wp_mail failures
		add_action( 'wp_mail_failed', [ 'DIH_SMTP_Logger', 'log_mail_failure' ] );
	}

	// ── Activation ────────────────────────────────────────────────────────────

	/**
	 * Runs on plugin activation.
	 * No rewrite flush needed: REST routes are registered on every request
	 * and served through core's existing /wp-json/ rewrite rule.
	 */
	public static function activate(): void {
		// Store activation timestamp
		update_option( 'dih_smtp_activated_at', time() );

		// Same tasks as an in-place update — one code path for both
		self::upgrade();

		DIH_SMTP_Logger::log( 'Plugin activated. Version: ' . DIH_SMTP_VERSION );
	}

	// ── Deactivation ──────────────────────────────────────────────────────────

	/**
	 * Runs on plugin deactivation.
	 * Does NOT delete settings or tokens — use uninstall.php for that.
	 */
	public static function deactivate(): void {
		DIH_SMTP_Logger::log( 'Plugin deactivated.' );
	}
}
