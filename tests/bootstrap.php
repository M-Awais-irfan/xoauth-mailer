<?php
/**
 * Test bootstrap: minimal WordPress function stubs, PHPMailer from Composer,
 * and the real plugin file (constants and autoloader).
 *
 * Options live in $GLOBALS['opts']. wp_remote_post() returns $GLOBALS['http']
 * as a JSON body and counts calls in $GLOBALS['posts'].
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'MINUTE_IN_SECONDS', 60 );

$GLOBALS['opts']  = [];
$GLOBALS['http']  = null;
$GLOBALS['posts'] = 0;

function get_option( $key, $default = false ) {
	return array_key_exists( $key, $GLOBALS['opts'] ) ? $GLOBALS['opts'][ $key ] : $default;
}
function add_option( $key, $value = '', $deprecated = '', $autoload = null ) {
	if ( array_key_exists( $key, $GLOBALS['opts'] ) ) {
		return false;
	}
	$GLOBALS['opts'][ $key ] = $value;
	return true;
}
function update_option( $key, $value, $autoload = null ) {
	$GLOBALS['opts'][ $key ] = $value;
	return true;
}
function delete_option( $key ) {
	unset( $GLOBALS['opts'][ $key ] );
	return true;
}
function wp_parse_args( $args, $defaults ) {
	return array_merge( $defaults, (array) $args );
}
function sanitize_text_field( $value ) {
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}
function sanitize_email( $value ) {
	return trim( (string) $value );
}
function get_bloginfo( $show ) {
	return 'Example Site';
}
function current_time( $type ) {
	return '2026-01-01 00:00:00';
}
function wp_json_encode( $data ) {
	return json_encode( $data );
}
function wp_remote_post( $url, $args ) {
	++$GLOBALS['posts'];
	return [ 'body' => wp_json_encode( $GLOBALS['http'] ) ];
}
function wp_remote_retrieve_body( $response ) {
	return $response['body'];
}
function wp_remote_retrieve_response_code( $response ) {
	return 200;
}
function is_wp_error( $thing ) {
	return false;
}
function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}
function plugin_dir_url( $file ) {
	return 'https://example.com/wp-content/plugins/xoauth-mailer/';
}
function add_action() {}
function register_activation_hook() {}
function register_deactivation_hook() {}

require dirname( __DIR__ ) . '/vendor/autoload.php'; // PHPMailer
require dirname( __DIR__ ) . '/xoauth-mailer.php';    // Constants and autoloader

$GLOBALS['failures'] = 0;

/**
 * Print PASS/FAIL for one check.
 */
function xoam_test( bool $passed, string $label ): void {
	echo ( $passed ? 'PASS ' : 'FAIL ' ) . $label . "\n";
	if ( ! $passed ) {
		++$GLOBALS['failures'];
	}
}

register_shutdown_function(
	static function () {
		exit( $GLOBALS['failures'] > 0 ? 1 : 0 );
	}
);
