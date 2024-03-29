<?php

namespace MonoclePocRestApi;

class Init {

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @param array $args
	 */
  public static function load( $args = [] ) {
		// Load plugin REST API
		Rest_Api::register();
  }
}