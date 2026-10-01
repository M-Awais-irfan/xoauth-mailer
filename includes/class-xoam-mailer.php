<?php
/**
 * Mailer configuration.
 *
 * Hooks into phpmailer_init to configure PHPMailer for Google Workspace.
 * Injects XOAM_SMTP subclass when OAuth2 is the selected auth method.
 *
 * To add a new provider in the future (Outlook, SendGrid, etc.):
 *   1. Add its settings defaults in XOAM_Settings::$defaults
 *   2. Add a new configure_*() method in this class
 *   3. Call it from configure() based on a provider setting
 *
 * @package XOAuth_Mailer
 */

defined( 'ABSPATH' ) || exit;

class XOAM_Mailer {

	/**
	 * XOAUTH2 credential built by configure_oauth2() (priority 10) and consumed
	 * by inject_xoauth2() (priority 20). Both run inside the same wp_mail() call,
	 * so a static property is enough — the token never needs to touch the database.
	 *
	 * @var string
	 */
	private static string $xoauth2 = '';

	/**
	 * Register phpmailer_init hooks.
	 */
	public static function register(): void {
		// Priority 10 — main SMTP configuration
		add_action( 'phpmailer_init', [ __CLASS__, 'configure' ], 10 );

		// Priority 20 — inject XOAUTH2 SMTP subclass AFTER main config
		add_action( 'phpmailer_init', [ __CLASS__, 'inject_xoauth2' ], 20 );
	}

	/**
	 * Configure PHPMailer with Google Workspace SMTP settings.
	 *
	 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance.
	 */
	public static function configure( PHPMailer\PHPMailer\PHPMailer $phpmailer ): void {
		$s = XOAM_Settings::get();

		// Clear any credential left over from an earlier wp_mail() call in this request.
		self::$xoauth2 = '';

		if ( empty( $s['username'] ) ) {
			XOAM_Logger::log( 'SMTP username is empty — skipping SMTP override.', 'WARN' );
			return;
		}

		XOAM_Logger::log( 'Configuring PHPMailer for Google Workspace SMTP.' );

		// ── Server settings ────────────────────────────────────────────────
		$phpmailer->isSMTP();
		$phpmailer->Host     = sanitize_text_field( $s['smtp_host'] );
		$phpmailer->Port     = (int) $s['smtp_port'];
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = sanitize_email( $s['username'] );

		$phpmailer->SMTPSecure = ( $s['smtp_encryption'] === 'ssl' )
			? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
			: PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

		// ── Sender identity ────────────────────────────────────────────────
		if ( ! empty( $s['from_email'] ) ) {
			$phpmailer->setFrom(
				sanitize_email( $s['from_email'] ),
				sanitize_text_field( $s['from_name'] )
			);
		} elseif ( 'WordPress' === $phpmailer->FromName ) {
			// No From Email set: keep the sender address, but replace core's default
			// "WordPress" name. Names set by other plugins (e.g. contact forms) are left alone.
			$phpmailer->FromName = sanitize_text_field( $s['from_name'] );
		}

		// ── Auth method ────────────────────────────────────────────────────
		if ( $s['auth_method'] === 'oauth2' ) {
			self::configure_oauth2( $phpmailer, $s );
		} else {
			self::configure_app_password( $phpmailer, $s );
		}

		// ── Debug output ───────────────────────────────────────────────────
		if ( $s['debug_enabled'] === '1' ) {
			$phpmailer->SMTPDebug  = 2;
			// A new closure is created for every email, so its static state starts fresh each time.
			$phpmailer->Debugoutput = static function ( string $str, int $level ): void {
				static $in_data = false; // Between the server's "354" reply and the client's closing "."
				static $hidden  = 0;

				$line = trim( $str );

				// Message headers + body: don't store email content (privacy) — just count the lines.
				if ( $in_data ) {
					if ( 'CLIENT -> SERVER: .' !== $line ) {
						++$hidden;
						return;
					}
					$in_data = false;
					$line    = "CLIENT -> SERVER: [message content hidden — {$hidden} lines]";
					$hidden  = 0;
				}

				// Core's SMTP::client_send() only hides AUTH LOGIN/PLAIN credentials.
				// Our AUTH XOAUTH2 line carries a live access token, so redact it here.
				if ( str_contains( $line, 'AUTH XOAUTH2' ) ) {
					$line = 'CLIENT -> SERVER: AUTH XOAUTH2 [credentials hidden]';
				}

				XOAM_Logger::log( "PHPMailer[$level]: " . $line, 'DEBUG' );

				// "354 Go ahead" means everything the client sends next is the message itself
				if ( str_starts_with( $line, 'SERVER -> CLIENT: 354' ) ) {
					$in_data = true;
				}
			};
		}

		XOAM_Logger::log( 'PHPMailer configuration complete.' );
	}

	/**
	 * Configure App Password authentication.
	 */
	private static function configure_app_password(
		PHPMailer\PHPMailer\PHPMailer $phpmailer,
		array $s
	): void {
		XOAM_Logger::log( 'Auth method: App Password.' );

		if ( empty( $s['app_password'] ) ) {
			XOAM_Logger::log( 'App password is empty — email may fail.', 'ERROR' );
			return;
		}

		$phpmailer->Password = $s['app_password'];
	}

	/**
	 * Configure OAuth2 authentication.
	 * Builds the XOAUTH2 credential and keeps it in self::$xoauth2
	 * so inject_xoauth2() (priority 20) can pick it up.
	 */
	private static function configure_oauth2(
		PHPMailer\PHPMailer\PHPMailer $phpmailer,
		array $s
	): void {
		XOAM_Logger::log( 'Auth method: OAuth2.' );

		$token = get_option( XOAM_TOKEN_KEY, [] );

		if ( empty( $token['access_token'] ) ) {
			XOAM_Logger::log( 'OAuth2 access token missing.', 'ERROR' );
			return;
		}

		// Refresh if expired
		if ( ! empty( $token['expires_at'] ) && time() > (int) $token['expires_at'] ) {
			XOAM_Logger::log( 'OAuth2 token expired — refreshing.' );
			$token = XOAM_OAuth::refresh_token( $token, $s );
		}

		if ( empty( $token['access_token'] ) ) {
			XOAM_Logger::log( 'OAuth2 token refresh failed.', 'ERROR' );
			return;
		}

		// Build XOAUTH2 base64 string
		// Format: base64( "user=<email>\x01auth=Bearer <token>\x01\x01" )
		// Google's XOAUTH2 SASL mechanism requires base64 — this is encoding, not obfuscation.
		$xoauth2 = base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'user=' . sanitize_email( $s['username'] )
			. "\x01auth=Bearer " . $token['access_token']
			. "\x01\x01"
		);

		XOAM_Logger::log( 'XOAUTH2 credential built. Token length: ' . strlen( $token['access_token'] ) );

		// Hand over to inject_xoauth2() — same request, so no database needed
		self::$xoauth2 = $xoauth2;

		// Temporarily disable SMTPAuth so PHPMailer won't run its own auth
		// before inject_xoauth2() swaps in our SMTP subclass
		$phpmailer->SMTPAuth = false;
	}

	/**
	 * Inject our XOAUTH2-capable SMTP subclass into PHPMailer.
	 * Runs at priority 20 — after configure() at priority 10.
	 */
	public static function inject_xoauth2( PHPMailer\PHPMailer\PHPMailer $phpmailer ): void {
		$s = XOAM_Settings::get();

		if ( $s['auth_method'] !== 'oauth2' ) {
			return;
		}

		$xoauth2       = self::$xoauth2;
		self::$xoauth2 = ''; // Single use

		if ( empty( $xoauth2 ) ) {
			XOAM_Logger::log( 'XOAUTH2 credential missing — cannot inject custom SMTP.', 'ERROR' );
			return;
		}

		if ( ! class_exists( 'XOAM_SMTP' ) ) {
			XOAM_Logger::log( 'XOAM_SMTP class not found.', 'ERROR' );
			return;
		}

		$smtp_instance              = new XOAM_SMTP();
		$smtp_instance->xoauth2_token = $xoauth2;

		$phpmailer->setSMTPInstance( $smtp_instance );
		$phpmailer->SMTPAuth = true; // Re-enable so PHPMailer calls authenticate()

		XOAM_Logger::log( 'XOAM_SMTP injected into PHPMailer.' );
	}
}
