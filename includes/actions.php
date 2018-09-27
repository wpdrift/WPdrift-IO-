<?php
/**
 * WPdrift Worker actions
 *
 * @author  Support HQ <support@upnrunn.com>
 * @package WPdrift Worker
 */

// hide all error on api response
error_reporting( 0 );

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
