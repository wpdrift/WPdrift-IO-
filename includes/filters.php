<?php

/**
 * Default Method Filter for the resource server API calls
 *
 * @since  1.0.0 Endpoints now can accept public methods that bypass the token authorization
 */
function wpdrift_worker_default_endpoints() {
	$endpoints = array(
		'me'      => array(
			'func'   => '_wpdrift_worker_method_me',
			'public' => false,
		),
		'destroy' => array(
			'func'   => '_wpdrift_worker_method_destroy',
			'public' => false,
		),
	);

	return $endpoints;
}

add_filter( 'wpdrift_worker_endpoints', 'wpdrift_worker_default_endpoints', 1 );

/**
 * Token Introspection
 * Since spec call for the response to return even with an invalid token, this method
 * will be set to public.
 * @since 1.0.0
 *
 * @param null $token
 */
function _wpdrift_worker_method_introspection( $token = null ) {
	$access_token = &$token['access_token'];

	$request = OAuth2\Request::createFromGlobals();

	if ( strtolower( @$request->server['REQUEST_METHOD'] ) != 'post' ) {
		$response = new OAuth2\Response();
		$response->setError(
			405,
			'invalid_request',
			'The request method must be POST when calling the introspection endpoint.',
			'https://tools.ietf.org/html/rfc7662#section-2.1'
		);
		$response->addHttpHeaders( array( 'Allow' => 'POST' ) );
		$response->send();
	}

	// Check if the token is valid
	$valid = wpdrift_worker_public_get_access_token( $access_token );
	if ( false == $valid ) {
		$response = new OAuth2\Response( array(
			'active' => false,
		) );
		$response->send();
	}

	if ( $valid['user_id'] != 0 || ! is_null( $valid['user_id'] ) ) {
		$user     = get_userdata( $valid['user_id'] );
		$username = $user->user_login;
	}
	$introspection = apply_filters( 'wpdrift_worker_introspection_response', array(
		'active'    => true,
		'scope'     => $valid['scope'],
		'client_id' => $valid['client_id']
	) );
	$response      = new OAuth2\Response( $introspection );
	$response->send();

	exit;
}

/**
 * DEFAULT DESTROY METHOD
 * This method has been added to help secure installs that want to manually destroy sessions (valid access tokens).
 * @since  1.0.0
 *
 * @param null $token
 */
function _wpdrift_worker_method_destroy( $token = null ) {
	$access_token = &$token['access_token'];

	global $wpdb;
	$stmt = $wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'access_token' => $access_token ) );

	/** If there is a refresh token we need to remove it as well. */
	if ( ! empty( $_REQUEST['refresh_token'] ) ) {
		$stmt = $wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'refresh_token' => $_REQUEST['refresh_token'] ) );
	}

	/** Prepare the return */
	$response = new OAuth2\Response( array(
		'status'      => true,
		'description' => __( 'Session destroyed successfully', 'wpdrift-worker' ),
	) );
	$response->send();
	exit;
}

/**
 * DEFAULT ME METHOD - DO NOT REMOVE DIRECTLY
 * This is the default resource call "/oauth/me". Do not edit or remove.
 *
 * @param null $token
 */
function _wpdrift_worker_method_me( $token = null ) {

	if ( ! isset( $token['user_id'] ) || 0 == $token['user_id'] ) {
		$response = new OAuth2\Response();
		$response->setError(
			400,
			'invalid_request',
			'Invalid token',
			'https://tools.ietf.org/html/draft-ietf-oauth-v2-31#section-7.2'
		);
		$response->send();
		exit;
	}

	$user    = get_user_by( 'id', $token['user_id'] );
	$me_data = (array) $user->data;

	unset( $me_data['user_pass'] );
	unset( $me_data['user_activation_key'] );
	unset( $me_data['user_url'] );

	/**
	 * @since  1.0.0
	 * OpenID Connect looks for the field "email".asd
	 * Sooooo. We shall provide it. (at least for Moodle)
	 */
	$me_data['email'] = $me_data['user_email'];

	/**
	 * user information returned by the default me method is filtered
	 * @since 1.0.0
	 * @filter wpdrift_worker_me_resource_return
	 */
	$me_data = apply_filters( 'wpdrift_worker_me_resource_return', $me_data );

	$response = new OAuth2\Response( $me_data );
	$response->send();
	exit;
}
