<?php

defined( 'ABSPATH' ) || exit;

/**
 * Autoload the OM4\WooCommerceZapier namespaced classes.
 * Helps keep code simple and memory consumption down.
 * Idea from: http://container.thephpleague.com/3.x/#going-solo.
 */
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'OM4\\WooCommerceZapier\\';
		$base_dir = __DIR__ . '/src/';
		$len      = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}
		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);
