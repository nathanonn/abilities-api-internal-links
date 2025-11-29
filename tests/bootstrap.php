<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package InternalLinksAPI\Tests
 */

// Load Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Initialize Brain\Monkey.
Brain\Monkey\setUp();

// Define WordPress constants that may be needed.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}
