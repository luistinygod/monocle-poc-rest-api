<?php

namespace MonoclePocRestApi;

/**
 * Autoloads plugin classes using PSR-4.
 *
 * @author Luis Godinho <luismgod@gmail.com>
 */
class Autoloader {
	/**
	 * Handles autoloading of plugin classes.
	 *
	 * @param String $class
	 * @return void
	 */
	public static function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
				return;
		}

		$class = substr( $class, strlen( __NAMESPACE__ ) );
		$class = strtolower( $class );
		$class = str_ireplace( '_', '-', $class );
		$file_parts = explode( '\\', $class );
		$len = count( $file_parts );
		// $file_parts[ $len - 1 ] = $file_parts[ $len - 1 ];
		$file = dirname(__FILE__) .  implode('/',  $file_parts ) . '.php';

		if ( is_file( $file ) ) {
			require $file;
		}
	}

	/**
	 * Registers as an SPL autoloader.
	 *
	 * @param bool $prepend
	 */
	public static function register( $prepend = false ) {
		spl_autoload_register( [ new self(), 'autoload' ], true, $prepend );
	}
}