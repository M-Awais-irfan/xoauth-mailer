<?php
/**
 * OAuth2 sending path: XOAUTH2 injection, token refresh timing,
 * connection status and invalid_grant handling.
 */

require __DIR__ . '/bootstrap.php';

use PHPMailer\PHPMailer\PHPMailer;

$client = [ 'oauth_client_id' => 'id', 'oauth_client_secret' => 'secret' ];

$GLOBALS['opts'][ XOAM_OPTION_KEY ] = [
	'username'      => 'me@example.com',
	'auth_method'   => 'oauth2',
	'debug_enabled' => '0',
] + $client;

// XOAUTH2 injection.
$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'ya29.test', 'refresh_token' => 'r', 'expires_at' => time() + 3600 ];

xoam_test( ! class_exists( 'XOAM_SMTP', false ), 'XOAM_SMTP is not loaded before mail is sent' );

$mail = new PHPMailer();
XOAM_Mailer::configure( $mail );      // phpmailer_init, priority 10.
XOAM_Mailer::inject_xoauth2( $mail ); // phpmailer_init, priority 20.

$smtp = $mail->getSMTPInstance();
xoam_test( $smtp instanceof XOAM_SMTP, 'PHPMailer uses XOAM_SMTP for OAuth2' );
xoam_test( true === $mail->SMTPAuth, 'SMTPAuth is re-enabled so authenticate() runs' );
xoam_test(
	base64_encode( "user=me@example.com\x01auth=Bearer ya29.test\x01\x01" ) === $smtp->xoauth2_token,
	'the XOAUTH2 credential is built in the format Google expects'
);

// Refresh timing.
$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'old', 'refresh_token' => 'r', 'expires_at' => time() + 30 ];
$GLOBALS['http']  = [ 'access_token' => 'new', 'expires_in' => 3600 ];
$GLOBALS['posts'] = 0;
XOAM_Mailer::configure( new PHPMailer() );
xoam_test( 1 === $GLOBALS['posts'] && 'new' === get_option( XOAM_TOKEN_KEY )['access_token'], 'a token with 30 seconds left is refreshed before sending' );

$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'fresh', 'refresh_token' => 'r', 'expires_at' => time() + 3000 ];
$GLOBALS['posts'] = 0;
XOAM_Mailer::configure( new PHPMailer() );
xoam_test( 0 === $GLOBALS['posts'], 'a token with 50 minutes left is not refreshed' );

// Connection status.
$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'a', 'refresh_token' => 'r', 'expires_at' => time() - 999 ];
xoam_test( XOAM_OAuth::is_connected(), 'expired access token with a refresh token counts as connected' );

$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'a', 'refresh_token' => '', 'expires_at' => time() - 1 ];
xoam_test( ! XOAM_OAuth::is_connected(), 'expired access token without a refresh token is not connected' );

$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'a', 'refresh_token' => '', 'expires_at' => time() + 600 ];
xoam_test( XOAM_OAuth::is_connected(), 'a valid access token without a refresh token is connected until it expires' );

unset( $GLOBALS['opts'][ XOAM_TOKEN_KEY ] );
xoam_test( ! XOAM_OAuth::is_connected(), 'no token is not connected' );

// invalid_grant drops the dead token; temporary errors keep it.
$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'a', 'refresh_token' => 'revoked', 'expires_at' => 0 ];
$GLOBALS['http'] = [ 'error' => 'invalid_grant', 'error_description' => 'Token has been expired or revoked.' ];
xoam_test( [] === XOAM_OAuth::refresh_token( get_option( XOAM_TOKEN_KEY ), $client ), 'invalid_grant: refresh returns an empty token' );
xoam_test( false === get_option( XOAM_TOKEN_KEY ) && ! XOAM_OAuth::is_connected(), 'invalid_grant: the token is deleted and the tab shows Not Connected' );

$GLOBALS['opts'][ XOAM_TOKEN_KEY ] = [ 'access_token' => 'a', 'refresh_token' => 'r', 'expires_at' => 0 ];
$GLOBALS['http'] = [ 'error' => 'server_error' ];
XOAM_OAuth::refresh_token( get_option( XOAM_TOKEN_KEY ), $client );
xoam_test( false !== get_option( XOAM_TOKEN_KEY ), 'a temporary error keeps the token' );
