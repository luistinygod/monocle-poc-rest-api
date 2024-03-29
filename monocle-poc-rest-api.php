<?php
/**
 * Plugin Name:       Monocle PoC REST API
 * Plugin URI:        https://monocle.com/
 * Description:       Test the REST API authentication and how the content is formatted for Monocle.
 * Version:           1.0.0
 * Author:            Poetik
 * Author URI:        https://www.poetik.dev/
 * Text Domain:       m-poc-restapi
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'MON_POC_VERSION', '1.0.0' );
define( 'MON_POC_FILE', __FILE__ );
define( 'MON_POC_PATH', plugin_dir_path( __FILE__ ) );
define( 'MON_POC_URL', plugin_dir_url( __FILE__ ) );
define( 'MON_POC_BASENAME', plugin_basename( __FILE__ ) );
define( 'MON_POC_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets' );
define( 'MON_POC_ASSETS_PATH', plugin_dir_path( __FILE__ ) . 'assets' );
define( 'MON_POC_REST_NAMESPACE', 'moncl-poc/v1' );

// Setup class autoloader (PSR-4)
require_once MON_POC_PATH . '/includes/autoloader.php';
\MonoclePocRestApi\Autoloader::register();

// Fetch plugin definitions from the header
$args = get_file_data( MON_POC_FILE , array( 'text_domain' => 'Text Domain' ) );

// Load plugin
\MonoclePocRestApi\Init::load( $args );
