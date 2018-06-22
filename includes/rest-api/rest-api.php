<?php
add_action('rest_api_init', 'wh_user_meta_fields');
function wh_user_meta_fields()
{
    register_rest_field(
        'user',
        'last_login',
        array(
           'get_callback'    => 'wh_get_user_last_login',
           'schema'          => null,
        )
    );

    register_rest_field(
        'user',
        'has_avatar',
        array(
           'get_callback'    => 'wh_get_check_user_avatar',
           'schema'          => null,
        )
    );

    // user registered date formatted
    register_rest_field(
        'user',
        'joined_date',
        array(
           'get_callback'    => 'wh_get_user_joined_date',
           'schema'          => null,
        )
    );
    // user total comments, posts, pages count
    register_rest_field(
        'user',
        'posted_content_count',
        array(
           'get_callback'    => 'wh_get_user_posted_content_count',
           'schema'          => null,
        )
    );
}

function wh_get_user_last_login($user)
{
    $session_tokens = get_user_meta($user['id'], 'session_tokens', true);
    $sessions = array();

    if (! empty($session_tokens)) {
        foreach ($session_tokens as $key => $session) {
            $session['token'] = $key;
            $session['login_diff'] = human_time_diff($session['login'], current_time('timestamp')) . ' ago';
            $sessions[] = $session;
        }
    }

    return $sessions[0];
}

function wh_get_check_user_avatar($user)
{
    return wh_has_gravatar($user['email']);
}

function wh_has_gravatar($email_address)
{
    $url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email_address))) . '?d=404';
    $headers = @get_headers($url);
    return preg_match('|200|', $headers[0]) ? true : false;
}

function wh_get_user_joined_date($user)
{
    $udata = get_userdata($user['id']);
    $registered = $udata->data->user_registered;
    return date("M d, Y", strtotime($registered));
}

function wh_get_user_posted_content_count($user)
{
    global $wpdb;
    $posted_content = array();
    $comments_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) AS total FROM $wpdb->comments WHERE comment_approved = 1 AND user_id = %s", $user['id']));
    $posts_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) AS total FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND post_author = %s", $user['id']));
    $pages_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) AS total FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' AND post_author = %s", $user['id']));
    $posted_content['comments_count'] = $comments_count;
    $posted_content['posts_count'] = $posts_count;
    $posted_content['pages_count'] = $pages_count;
    return $posted_content;
}
