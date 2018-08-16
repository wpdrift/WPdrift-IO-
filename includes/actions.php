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
 * @since 1.0.0
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
 * @since 1.0.0
 */
$wpdrift_worker_restrict_single_access_token = apply_filters( 'wpdrift_worker_restrict_single_access_token', false );
if ( $wpdrift_worker_restrict_single_access_token ) {
	add_action( 'wpdrift_worker_set_access_token', 'wpdrift_worker_only_allow_one_access_token' );
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
