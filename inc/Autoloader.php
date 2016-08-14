<?php
namespace SHinse\ExportEMEventsToCSV\inc;

/**
 * Class Autoloader
 *
 * @package SHinse\ExportEMEventsToCSV\inc
 *          A very simple Autoloader class.
 *          Usage: Put the file in your root directory, include it and run Autoloader::register();
 */
class Autoloader {

	public static function register() {

		spl_autoload_register( function ( $class ) {

			if ( stripos( $class, __NAMESPACE__ ) === 0 ) {
				$file = ( __DIR__ . DIRECTORY_SEPARATOR . ( substr( $class, strlen( __NAMESPACE__ ) + 1 ) ) . '.php' );
				include( $file );
			}

		}
		);
	}
}