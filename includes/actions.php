<?php
/**
 * WPdrift Worker actions
 *
 * @author  WPdrift <kishore@upnrunn.com>
 * @package WPdrift Worker
 */
// hide all error on api response
error_reporting(0);
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

	/**
	 * Detect EDD plugin. Then add edd all api end points
	 */
	if ( in_array( 'easy-digital-downloads/easy-digital-downloads.php', (array) get_option( 'active_plugins', array() ) ) ) {
		require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/edd/edd-end-points.php' );
	}

}

add_action( 'rest_api_init', 'wpdriftio_register_rest_routes' );
