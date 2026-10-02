<?php
/**
 * XOAM_Settings: sanitizing, secret handling and wp-config.php overrides.
 */

require __DIR__ . '/bootstrap.php';

// Encryption follows the port.
xoam_test( 'ssl' === XOAM_Settings::sanitize( [ 'smtp_port' => '465', 'smtp_encryption' => 'tls' ] )['smtp_encryption'], 'port 465 uses SSL even if TLS is submitted' );
xoam_test( 'tls' === XOAM_Settings::sanitize( [ 'smtp_port' => '587', 'smtp_encryption' => 'ssl' ] )['smtp_encryption'], 'port 587 uses STARTTLS' );
xoam_test( '587' === XOAM_Settings::sanitize( [ 'smtp_port' => '2525' ] )['smtp_port'], 'an unknown port falls back to 587' );

// Secrets are kept when the field is left blank.
$GLOBALS['opts'][ XOAM_OPTION_KEY ] = XOAM_Settings::sanitize( [ 'app_password' => 'abcd efgh', 'oauth_client_secret' => 'sec' ] );
xoam_test( 'abcd efgh' === get_option( XOAM_OPTION_KEY )['app_password'], 'a new secret is saved' );

$GLOBALS['opts'][ XOAM_OPTION_KEY ] = XOAM_Settings::sanitize( [ 'app_password' => '', 'from_name' => 'X' ] );
xoam_test( 'abcd efgh' === get_option( XOAM_OPTION_KEY )['app_password'], 'a blank App Password field keeps the saved one' );
xoam_test( 'sec' === get_option( XOAM_OPTION_KEY )['oauth_client_secret'], 'a missing Client Secret field keeps the saved one' );

$GLOBALS['opts'][ XOAM_OPTION_KEY ] = XOAM_Settings::sanitize( [ 'app_password' => 'new' ] );
xoam_test( 'new' === get_option( XOAM_OPTION_KEY )['app_password'], 'a new value replaces the saved secret' );

// Non-array input (for example from WP-CLI) must not cause a fatal error.
try {
	XOAM_Settings::sanitize( '' );
	xoam_test( true, 'non-array input is handled' );
} catch ( TypeError $e ) {
	xoam_test( false, 'non-array input is handled: ' . $e->getMessage() );
}

// Auth method allowlist.
xoam_test( 'app_password' === XOAM_Settings::sanitize( [ 'auth_method' => 'something-else' ] )['auth_method'], 'an unknown auth method falls back to App Password' );

// wp-config.php constants override saved secrets and are never stored.
xoam_test( ! XOAM_Settings::is_constant( 'app_password' ), 'is_constant() is false before the constant is defined' );
define( 'XOAM_APP_PASSWORD', 'from-config' );
xoam_test( XOAM_Settings::is_constant( 'app_password' ), 'is_constant() is true after the constant is defined' );
xoam_test( 'from-config' === XOAM_Settings::get()['app_password'], 'the constant wins over the saved value' );
xoam_test( 'new' === get_option( XOAM_OPTION_KEY )['app_password'], 'the constant is never written to the database' );
xoam_test( ! XOAM_Settings::is_constant( 'username' ), 'is_constant() is false for settings that are not secrets' );
