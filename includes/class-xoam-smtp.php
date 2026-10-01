<?php
/**
 * XOAUTH2-capable PHPMailer SMTP subclass.
 *
 * Overrides authenticate() to send AUTH XOAUTH2 <base64token>
 * directly to Google's SMTP server — no external OAuth library needed.
 *
 * Signature MUST match the parent PHPMailer\PHPMailer\SMTP::authenticate()
 * exactly (no type hints) to avoid PHP fatal declaration errors.
 *
 * @package XOAuth_Mailer
 */

defined( 'ABSPATH' ) || exit;

// PHPMailer\PHPMailer\SMTP must be loaded before this file is included.
// XOAM_Core::load_dependencies() handles that.

class XOAM_SMTP extends PHPMailer\PHPMailer\SMTP {

	/**
	 * The base64-encoded XOAUTH2 credential string.
	 * Format: base64("user=<email>\x01auth=Bearer <token>\x01\x01")
	 *
	 * Set by XOAM_Mailer before sending.
	 *
	 * @var string
	 */
	public $xoauth2_token = '';

	/**
	 * Override authenticate() to use AUTH XOAUTH2 when token is present.
	 *
	 * NO type hints — must exactly match parent method signature.
	 *
	 * @param string      $username  SMTP username (email address).
	 * @param string      $password  SMTP password (unused for XOAUTH2).
	 * @param string|null $authtype  Auth type (overridden to XOAUTH2).
	 * @param mixed       $OAuth     OAuth object (unused — no external lib).
	 * @return bool True on success.
	 */
	public function authenticate( $username, $password, $authtype = null, $OAuth = null ) {
		if ( ! empty( $this->xoauth2_token ) ) {
			XOAM_Logger::log( 'Sending AUTH XOAUTH2 command to SMTP server.' );
			// Send AUTH XOAUTH2 <base64string> as a single SMTP command.
			// Google responds with 235 on success.
			return $this->sendCommand( 'AUTH', 'AUTH XOAUTH2 ' . $this->xoauth2_token, [ 235 ] );
		}

		// No XOAUTH2 token — fall back to standard PHPMailer auth (LOGIN/PLAIN)
		XOAM_Logger::log( 'No XOAUTH2 token — falling back to standard auth.' );
		return parent::authenticate( $username, $password, $authtype, $OAuth );
	}
}
