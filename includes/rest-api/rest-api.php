<?php
add_action( 'rest_api_init', 'wh_user_meta_fields' );
function wh_user_meta_fields() {
    register_rest_field( 'user', 'last_login', array(
           'get_callback'    => 'wh_get_user_last_login',
           'schema'          => null,
        )
    );

    register_rest_field( 'user', 'has_avatar', array(
           'get_callback'    => 'wh_get_check_user_avatar',
           'schema'          => null,
        )
    );
}

function wh_get_user_last_login( $user ) {
    $session_tokens = get_user_meta( $user['id'], 'session_tokens', true );
    $sessions = array();

    if ( ! empty( $session_tokens ) ) {
        foreach ($session_tokens as $key => $session) {
            $session['token'] = $key;
            $session['login_diff'] = human_time_diff( $session['login'], current_time('timestamp') ) . ' ago';
            $sessions[] = $session;
        }
    }

    return $sessions[0];
}

function wh_get_check_user_avatar( $user ) {
    return wh_has_gravatar( $user['email'] );
}

function wh_has_gravatar( $email_address ) {
	$url = 'http://www.gravatar.com/avatar/' . md5( strtolower( trim ( $email_address ) ) ) . '?d=404';
	$headers = @get_headers( $url );
	return preg_match( '|200|', $headers[0] ) ? true : false;
}
