<?php
/**
 * Plugin Name: WPdrift IO - Worker
 * Plugin URI: http://wpdrift.io/
 * Version: 2.0.1
 * Description: Analytics, automation, tools, and much more for WordPress.
 * Author: WPdrift
 * Author URI: https://wpdrift.com/
 * Text Domain: wpdrift-worker
 *
 * @author  WPdrift <kishore@upnrunn.com>
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
define( 'WPDRIFT_WORKER_VERSION', '2.0.1' );

/**
 * [if description]
 * @var [type]
 */
if ( ! defined( 'WPDRIFT_WORKER_FILE' ) ) {
	define( 'WPDRIFT_WORKER_FILE', __FILE__ );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpdrift-io-activator.php
 */
function activate_wpdrift_worker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpdrift-io-activator.php';
	WPdrift_IO_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpdrift-io-deactivator.php
 */
function deactivate_wpdrift_worker() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpdrift-io-deactivator.php';
	WPdrift_IO_Deactivator::deactivate();
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
	_WO()->run();
}

run_wpdrift_worker();

/**
 * [require_once description]
 * @var [type]
 */
require_once( dirname( __FILE__ ) . '/includes/functions.php' );
require_once( dirname( __FILE__ ) . '/includes/rest-api/rest-api.php' );
require_once( dirname( __FILE__ ) . '/includes/rest-api/hooks-users.php' );

/**
 * Adds/registers query vars
 *
 * @return void
 */
function wpdrift_worker_server_register_query_vars() {
	wpdrift_worker_server_register_rewrites();

	global $wp;
	$wp->add_query_var( 'oauth' );
}

add_action( 'init', 'wpdrift_worker_server_register_query_vars' );

/**
 * Registers rewrites for OAuth2 Server
 *
 * - authorize
 * - token
 * - .well-known
 * - wpoauthincludes
 *
 * @return void
 */
function wpdrift_worker_server_register_rewrites() {
	add_rewrite_rule( '^oauth/(.+)', 'index.php?oauth=$matches[1]', 'top' );
}

/**
 * [template_redirect_intercept description]
 *
 * @return [type] [description]
 */
function wpdrift_worker_server_template_redirect_intercept( $template ) {
	global $wp_query;

	if ( $wp_query->get( 'oauth' ) || $wp_query->get( 'well-known' ) ) {
		require_once dirname( __FILE__ ) . '/library/class-wo-api.php';
		exit;
	}

	return $template;
}

add_filter( 'template_include', 'wpdrift_worker_server_template_redirect_intercept', 100 );

/**
 * OAuth2 Server Activation
 *
 * @param  [type] $network_wide [description]
 *
 * @return [type]               [description]
 */
function wpdrift_worker_server_activation( $network_wide ) {
	if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
		$mu_blogs = wp_get_sites();
		foreach ( $mu_blogs as $mu_blog ) {
			switch_to_blog( $mu_blog['blog_id'] );
			wpdrift_worker_server_register_rewrites();
			flush_rewrite_rules();
		}
		restore_current_blog();
	} else {
		wpdrift_worker_server_register_rewrites();
		flush_rewrite_rules();
	}
}

register_activation_hook( __FILE__, 'wpdrift_worker_server_activation' );

/**
 * OAuth Server Deactivation
 *
 * @param  [type] $network_wide [description]
 *
 * @return [type]               [description]
 */
function wpdrift_worker_server_deactivation( $network_wide ) {
	if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
		$mu_blogs = wp_get_sites();
		foreach ( $mu_blogs as $mu_blog ) {
			switch_to_blog( $mu_blog['blog_id'] );
			flush_rewrite_rules();
		}
		restore_current_blog();
	} else {
		flush_rewrite_rules();
	}
}

register_deactivation_hook( __FILE__, 'wpdrift_worker_server_deactivation' );
