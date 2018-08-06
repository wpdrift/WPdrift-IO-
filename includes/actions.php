<?php
/**
 * WPdrift Worker actions
 *
 * @author  WPdrift <kishore@upnrunn.com>
 * @package WPdrift Worker
 */

/**
 * Invalidate any token and refresh tokens during password reset
 *
 * @param  object $user WP_User Object
 * @param  String $new_pass New Password
 *
 * @return Void
 *
 * @since 3.1.8
 */
function wpdrift_worker_password_reset_action( $user, $new_pass ) {
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user->ID ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user->ID ) );
}

add_action( 'password_reset', 'wpdrift_worker_password_reset_action', 10, 2 );

/**
 * [wpdrift_worker_profile_update_action description]
 *
 * @param  int $user_id WP User ID
 *
 * @return Void
 */
function wpdrift_worker_profile_update_action( $user_id ) {
	if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
		return;
	}

	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user_id ) );
}

add_action( 'profile_update', 'wpdrift_worker_profile_update_action' );

/**
 * Only allow 1 acces_token at a time
 *
 * @param  [type] $results [description]
 *
 * @return [type]          [description]
 */
function wpdrift_worker_only_allow_one_access_token( $object ) {
	if ( is_null( $object ) ) {
		return;
	}

	// Define the user ID
	$user_id = $object['user_id'];

	// Remove all other access tokens and refresh tokens from the system
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user_id ) );

	return;
}

/**
 * Restrict users to only have a single access token
 * @since 3.2.7
 */
$wpdrift_worker_restrict_single_access_token = apply_filters( 'wpdrift_worker_restrict_single_access_token', false );
if ( $wpdrift_worker_restrict_single_access_token ) {
	add_action( 'wo_set_access_token', 'wpdrift_worker_only_allow_one_access_token' );
}

// Debugging part
if (!function_exists('_custlog')) {
	function _custlog($message)
	{
		if (WP_DEBUG === true) {
			if (is_array($message) || is_object($message)) {
				error_log('<<<<<<<< :: DEBUG Array :: >>>>>>>>');
				error_log(print_r($message, true));
			} else {
				error_log('<<<<<<<< :: DEBUG String :: >>>>>>>>');
				error_log($message);
			}
		}
	}
}

/**
 * Function to register our new routes from the controller.
 * @return [type] [description]
 */
function wpdriftio_register_rest_routes() {
	// New edn point to check that plugin is installed or not
	register_rest_route('wpdriftsupporter/v1', '/check-plugin/', array(
		'methods'  => 'GET',
		'callback' => 'wpdrit_check_provide_plgn_ver',
	));

	/**
	 * [require_once description]
	 * @var [type]
	 */
	require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-site-controller.php' );
	$site_controller = new WPdrift_Site_Controller();
	$site_controller->register_routes();

	/**
	 * [require_once description]
	 * @var [type]
	 */
	require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-dashboard-controller.php' );
	$dashboard_controller = new WPdrift_Dashboard_Controller();
	$dashboard_controller->register_routes();

	/**
	 * [require_once description]
	 * @var [type]
	 */
	require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-clients-controller.php' );
	$clients_controller = new WPdrift_Clients_Controller();
	$clients_controller->register_routes();

	/**
	 * [require_once description]
	 * @var [type]
	 */
	require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-users-controller.php' );
	$users_controller = new WPdrift_Users_Controller();
	$users_controller->register_routes();

	/**
	 * [require_once description]
	 * @var [type]
	 */
	require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-hits-controller.php' );
	$hits_controller = new WPdrift_Hits_Controller();
	$hits_controller->register_routes();

	/**
	 * Register new recent events end points
	 * @var [type]
	 */
	require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-recentevents-controller.php' );
	$events_controller = new WPdrift_RecentEvents_Controller();
	$events_controller->register_routes();
}

add_action( 'rest_api_init', 'wpdriftio_register_rest_routes' );

function wh_save_login_activity($user_login, $user)
{
	$session_tokens = get_user_meta($user->ID, 'session_tokens', true);
	$sessions = array();

	if (! empty($session_tokens)) {
		foreach ($session_tokens as $key => $session) {
			$session['token'] = $key;
			$sessions[] = $session;
		}
	}

	update_user_meta($user->ID, 'last_login', $session);

	$ip_data = wh_get_user_ip_data($session['ip']);
	$ip_data = json_decode($ip_data, true);
	if (! empty($ip_data)  && ('success' == $ip_data['status'])) {
		update_user_meta($user->ID, 'ip_data', $ip_data);
		foreach ($ip_data as $key => $value) {
			update_user_meta($user->ID, 'ip_' . $key, $value);
		}
	}
}
add_action('wp_login', 'wh_save_login_activity', 999, 2);
// check plugin is installed and then add version of plugin
function wpdrit_check_provide_plgn_ver()
{
	global $wpdb;
	$host = $_SERVER['REMOTE_ADDR'];
	if ('167.99.167.87' !== $host) {
		return 'Invalid Host';
	} else {
		$plugin_directory = "WPdrift-IO/wpdrift-io.php";
		$plugin_active = false;
		$plugin_version = WPDRIFT_WORKER_VERSION;
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if (is_plugin_active($plugin_directory)) {
			$plugin_active = true;
		}
		return array('plugin_version' => $plugin_version, 'plugin_active' => $plugin_active);
	}
}
