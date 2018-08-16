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
