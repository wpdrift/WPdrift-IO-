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
    if ( get_avatar( $user['id'] ) ) {
        return true;
    }

    return false;
}
