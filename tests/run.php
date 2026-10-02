<?php
/**
 * Runs every tests/*Test.php file in its own PHP process and reports the result.
 *
 * Usage: composer test (or php tests/run.php)
 */

$files  = glob( __DIR__ . '/*Test.php' );
$failed = 0;

foreach ( $files as $file ) {
	echo '== ' . basename( $file ) . "\n";
	passthru( escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $file ), $code );
	if ( 0 !== $code ) {
		++$failed;
	}
	echo "\n";
}

echo $failed ? "$failed test file(s) failed.\n" : count( $files ) . " test file(s) passed.\n";
exit( $failed ? 1 : 0 );
