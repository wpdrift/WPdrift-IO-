<?php
/**
 * Plugin Name: WPdrift IO - Worker
 * Plugin URI: http://wpdrift.io/
 * Version: 1.0.0
 * Description: Analytics, automation, tools, and much more for WordPress.
 * Author: WPdrift
 * Author URI: https://wpdrift.com/
 * Text Domain: wpdrift-worker
 *
 * @author  Support HQ <support@upnrunn.com>
 * @package WPdrift Worker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPDRIFT_WORKER_VERSION', '1.0.0' );

/**
 * [if description]
 * @var [type]
 */
if ( ! defined( 'WPDRIFT_WORKER_FILE' ) ) {
	define( 'WPDRIFT_WORKER_FILE', __FILE__ );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpdrift-worker-activator.php
 */
function activate_wpdrift_worker( $network_wide ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpdrift-worker-activator.php';
	WPdrift_IO_Activator::activate( $network_wide );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpdrift-worker-deactivator.php
 */
function deactivate_wpdrift_worker( $network_wide ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpdrift-worker-deactivator.php';
	WPdrift_IO_Deactivator::deactivate( $network_wide );
}

register_activation_hook( __FILE__, 'activate_wpdrift_worker' );
register_deactivation_hook( __FILE__, 'deactivate_wpdrift_worker' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpdrift-worker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpdrift_worker() {
	_wpdw()->run();
}

run_wpdrift_worker();
