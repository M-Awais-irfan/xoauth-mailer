<?php
/**
 * XOAM_Mailer::configure(): From Name handling and debug log redaction.
 */

require __DIR__ . '/bootstrap.php';

use PHPMailer\PHPMailer\PHPMailer;

$GLOBALS['opts'][ XOAM_OPTION_KEY ] = [
	'username'      => 'me@example.com',
	'app_password'  => 'secret',
	'auth_method'   => 'app_password',
	'from_name'     => 'My Site',
	'from_email'    => '',
	'debug_enabled' => '1',
];

$log = static fn() => implode( "\n", array_column( get_option( XOAM_LOG_KEY, [] ), 'message' ) );

// From Name.
$mail           = new PHPMailer();
$mail->FromName = 'WordPress'; // Core's default.
XOAM_Mailer::configure( $mail );
xoam_test( 'My Site' === $mail->FromName, 'core default "WordPress" name is replaced when From Email is empty' );

$mail           = new PHPMailer();
$mail->FromName = 'Contact Form';
XOAM_Mailer::configure( $mail );
xoam_test( 'Contact Form' === $mail->FromName, 'a name set by another plugin is left alone' );

$GLOBALS['opts'][ XOAM_OPTION_KEY ]['from_email'] = 'sender@example.com';
$mail           = new PHPMailer();
$mail->FromName = 'Contact Form';
XOAM_Mailer::configure( $mail );
xoam_test( 'My Site' === $mail->FromName && 'sender@example.com' === $mail->From, 'with From Email set, both address and name are applied' );

// Debug log redaction.
$GLOBALS['opts'][ XOAM_LOG_KEY ] = [];
$mail = new PHPMailer();
XOAM_Mailer::configure( $mail );
$debug = $mail->Debugoutput;
$conversation = [
	[ "CLIENT -> SERVER: AUTH XOAUTH2 dXNlcj1zZWNyZXQ=\r\n", 1 ],
	[ "SERVER -> CLIENT: 235 2.7.0 Accepted\r\n", 2 ],
	[ "CLIENT -> SERVER: DATA\r\n", 1 ],
	[ "SERVER -> CLIENT: 354 Go ahead\r\n", 2 ],
	[ "CLIENT -> SERVER: To: someone@example.com\r\n", 1 ],
	[ "CLIENT -> SERVER: Subject: Private\r\n", 1 ],
	[ "CLIENT -> SERVER: \r\n", 1 ],
	[ "CLIENT -> SERVER: User IP: 203.0.113.9\r\n", 1 ],
	[ "CLIENT -> SERVER: .\r\n", 1 ],
	[ "SERVER -> CLIENT: 250 2.0.0 OK\r\n", 2 ],
	[ "CLIENT -> SERVER: QUIT\r\n", 1 ],
];
foreach ( $conversation as [ $line, $level ] ) {
	$debug( $line, $level );
}
$all = $log();
xoam_test( ! str_contains( $all, 'dXNlcj1zZWNyZXQ=' ), 'the XOAUTH2 credential is not written to the log' );
xoam_test( str_contains( $all, 'AUTH XOAUTH2 [credentials hidden]' ), 'the XOAUTH2 line is replaced with a placeholder' );
xoam_test( ! str_contains( $all, 'someone@example.com' ) && ! str_contains( $all, 'Private' ) && ! str_contains( $all, '203.0.113.9' ), 'message headers and body are not written to the log' );
xoam_test( str_contains( $all, '[message content hidden, 4 lines]' ), 'the message is replaced by one summary line with the right count' );
xoam_test( str_contains( $all, '354 Go ahead' ) && str_contains( $all, '250 2.0.0 OK' ) && str_contains( $all, 'QUIT' ), 'SMTP commands and replies around the message are still logged' );

// Each email gets a new closure, so state does not leak between emails.
$GLOBALS['opts'][ XOAM_LOG_KEY ] = [];
$mail = new PHPMailer();
XOAM_Mailer::configure( $mail );
( $mail->Debugoutput )( "CLIENT -> SERVER: EHLO example.com\r\n", 1 );
xoam_test( str_contains( $log(), 'EHLO example.com' ), 'the next email starts outside the message body' );
