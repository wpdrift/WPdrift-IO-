<?php

/**
 * [add_filter description]
 * @var [type]
 */
add_filter( 'rest_user_collection_params', 'wpdrift_worker_user_collection_params' );
function wpdrift_worker_user_collection_params( $query_params ) {
	$query_params['after'] = array(
		'description' => __( 'Limit response to users registered after a given ISO8601 compliant date.' ),
		'type'        => 'string',
		'format'      => 'date-time',
	);

	$query_params['before'] = array(
		'description' => __( 'Limit response to users registered before a given ISO8601 compliant date.' ),
		'type'        => 'string',
		'format'      => 'date-time',
	);

	return $query_params;
}

/**
 * [add_filter description]
 * @var [type]
 */
add_filter( 'rest_user_query', 'wpdrift_worker_user_query', 10, 2 );
function wpdrift_worker_user_query( $prepared_args, $request ) {
	$prepared_args['date_query'] = array();

	if ( isset( $request['before'] ) ) {
		$prepared_args['date_query'][0]['before'] = $request['before'];
	}

	if ( isset( $request['after'] ) ) {
		$prepared_args['date_query'][0]['after'] = $request['after'];
	}

	return $prepared_args;
}
/**
 * [add_action description]
 * @var [type]
 */
add_action( 'rest_api_init', 'wpdrift_worker_user_meta_fields' );
function wpdrift_worker_user_meta_fields() {
	register_rest_field(
		'user',
		'ip_data',
		array(
			'get_callback' => 'wpdrift_worker_get_user_ip_location_data',
			'schema'       => null,
		)
	);

	register_rest_field(
		'user',
		'last_login',
		array(
			'get_callback' => 'wpdrift_worker_get_user_last_login',
			'schema'       => null,
		)
	);

	register_rest_field(
		'user',
		'has_avatar',
		array(
			'get_callback' => 'wpdrift_worker_get_check_user_avatar',
			'schema'       => null,
		)
	);

	// user registered date formatted
	register_rest_field(
		'user',
		'joined_date',
		array(
			'get_callback' => 'wpdrift_worker_get_user_joined_date',
			'schema'       => null,
		)
	);
	// user total comments, posts, pages count
	register_rest_field(
		'user',
		'posted_content_count',
		array(
			'get_callback' => 'wpdrift_worker_get_user_posted_content_count',
			'schema'       => null,
		)
	);
}

/**
 * [wpdrift_worker_get_user_last_login description]
 * @param  [type] $user [description]
 * @return [type]       [description]
 */
function wpdrift_worker_get_user_last_login( $user ) {
	$login_activity = get_user_meta( $user['id'], 'last_login', true );
	if ( $login_activity ) {
		$login_activity['time_diff'] = human_time_diff( $login_activity['login'], current_time( 'timestamp' ) ) . ' ago';
		return $login_activity;
	}

	return array();
}

/**
 * [wpdrift_worker_get_user_ip_location_data description]
 * @param  [type] $user [description]
 * @return [type]       [description]
 */
function wpdrift_worker_get_user_ip_location_data( $user ) {
	return get_user_meta( $user['id'], 'ip_data', true );
}

/**
 * [wpdrift_worker_get_check_user_avatar description]
 * @param  [type] $user [description]
 * @return [type]       [description]
 */
function wpdrift_worker_get_check_user_avatar( $user ) {
	return wpdrift_worker_has_gravatar( $user['email'] );
}

/**
 * [wpdrift_worker_has_gravatar description]
 * @param  [type]  $email_address [description]
 * @return boolean                [description]
 */
function wpdrift_worker_has_gravatar( $email_address ) {
	$url     = 'http://www.gravatar.com/avatar/' . md5( strtolower( trim( $email_address ) ) ) . '?d=404';
	$headers = @get_headers( $url );
	return preg_match( '|200|', $headers[0] ) ? true : false;
}

/**
 * [wpdrift_worker_get_user_joined_date description]
 * @param  [type] $user [description]
 * @return [type]       [description]
 */
function wpdrift_worker_get_user_joined_date( $user ) {
	$udata      = get_userdata( $user['id'] );
	$registered = $udata->data->user_registered;
	return date( 'M d, Y', strtotime( $registered ) );
}

/**
 * [wpdrift_worker_get_user_posted_content_count description]
 * @param  [type] $user [description]
 * @return [type]       [description]
 */
function wpdrift_worker_get_user_posted_content_count( $user ) {
	global $wpdb;
	$posted_content                   = array();
	$comments_count                   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) AS total FROM $wpdb->comments WHERE comment_approved = 1 AND user_id = %s", $user['id'] ) );
	$posts_count                      = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) AS total FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND post_author = %s", $user['id'] ) );
	$pages_count                      = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) AS total FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND post_author = %s", $user['id'] ) );
	$posted_content['comments_count'] = $comments_count;
	$posted_content['posts_count']    = $posts_count;
	$posted_content['pages_count']    = $pages_count;
	return $posted_content;
}
