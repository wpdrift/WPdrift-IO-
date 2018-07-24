<?php
/**
 * WP OAuth Server Actions
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
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
function wo_password_reset_action($user, $new_pass)
{
    global $wpdb;
    $wpdb->delete("{$wpdb->prefix}oauth_access_tokens", array( "user_id" => $user->ID ));
    $wpdb->delete("{$wpdb->prefix}oauth_refresh_tokens", array( "user_id" => $user->ID ));
}

add_action('password_reset', 'wo_password_reset_action', 10, 2);

/**
 * [wo_profile_update_action description]
 *
 * @param  int $user_id WP User ID
 *
 * @return Void
 */
function wo_profile_update_action($user_id)
{
    if (! isset($_POST['pass1']) || '' == $_POST['pass1']) {
        return;
    }
    global $wpdb;
    $wpdb->delete("{$wpdb->prefix}oauth_access_tokens", array( "user_id" => $user_id ));
    $wpdb->delete("{$wpdb->prefix}oauth_refresh_tokens", array( "user_id" => $user_id ));
}

add_action('profile_update', 'wo_profile_update_action');

/**
 * Only allow 1 acces_token at a time
 *
 * @param  [type] $results [description]
 *
 * @return [type]          [description]
 */
function wo_only_allow_one_access_token($object)
{
    if (is_null($object)) {
        return;
    }

    // Define the user ID
    $user_id = $object['user_id'];

    // Remove all other access tokens and refresh tokens from the system
    global $wpdb;
    $wpdb->delete("{$wpdb->prefix}oauth_access_tokens", array( "user_id" => $user_id ));
    $wpdb->delete("{$wpdb->prefix}oauth_refresh_tokens", array( "user_id" => $user_id ));

    return;
}

/**
 * Restrict users to only have a single access token
 * @since 3.2.7
 */
$wo_restrict_single_access_token = apply_filters('wo_restrict_single_access_token', false);
if ($wo_restrict_single_access_token) {
    add_action('wo_set_access_token', 'wo_only_allow_one_access_token');
}

// validating requester
add_filter('rest_pre_dispatch', 'validate_wpdrift_request', 10, 3);
function validate_wpdrift_request($result, $server, $request)
{
    $route = $request->get_route();
    if ($route == "/wpdriftsupporter/v1/validate-n-save-host") {
        $host = $_SERVER['REMOTE_ADDR'];
        if ('167.99.167.87' !== $host) {
            // Referer is set to something that we don't allow.
            return 'Invalid Host';
        } else {
            // setup the credentials and
            // Go Ahead!
            $params = $request->get_params('POST');
            $client_id     = wo_gen_key();
            $client_secret = wo_gen_key();
            // Add host
            $grant_types = array(
                'authorization_code',
                'implicit',
                'password',
                'client_credentials',
                'refresh_token'
            );
            $client_data = array(
                'post_title'     => $params['store_name'],
                'post_status'    => 'publish',
                'post_author'    => '1',
                'post_type'      => 'wo_client',
                'comment_status' => 'closed',
                'meta_input'     => array(
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'grant_types'   => $grant_types,
                    'redirect_uri'  => $params['return_url'],
                    'user_id'       => '-1'
                )
            );

            wp_insert_post($client_data);

            $client_data['store_id'] = $params['sid'];

            return $client_data;
        }
        return $result;
    }
    // Otherwise we are good - return original result and let WordPress handle as usual.
    return $result;
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

add_action('rest_api_init', function () {
    register_rest_route('wpdriftsupporter/v1', '/validate-n-save-host/', array(
        'methods' => 'POST',
        'callback' => 'call_for_validating_client_hosts',
    ));

    register_rest_route('wpdriftsupporter/v1', '/validate-token/', array(
        'methods' => 'POST',
        'callback' => 'wpdrit_validate_token',
    ));

    // New edn point to check that plugin is installed or not
    register_rest_route('wpdriftsupporter/v1', '/check-plugin/', array(
        'methods' => 'GET',
        'callback' => 'wpdrit_check_provide_plgn_ver',
    ));

    require_once(dirname( WPDRIFT_HELPER_FILE ) . '/includes/rest-api/class-wpdrift-dashboard-endpoint.php');
    $dashboard_controller = new WD_Dashboard_Endpoint();
    $dashboard_controller->register_routes();

    // Register new recent events end points
    require_once(dirname( WPDRIFT_HELPER_FILE ) . '/includes/rest-api/class-wpdrift-recentevents-endpoint.php');
    $recent_event_controller = new WD_RecentEvents_Endpoint();
    $recent_event_controller->register_routes();
});

function call_for_validating_client_hosts()
{
}

function wpdrit_validate_token($data)
{
    global $wpdb;
    $host = $_SERVER['REMOTE_ADDR'];
    $access_token = $data['access_token'];

    if ('167.99.167.87' !== $host) {
        return 'Invalid Host';
    } else {
        return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}oauth_access_tokens WHERE access_token = '{$access_token}'", OBJECT);
    }
}

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
        $plugin_version = WPDRIFT_HELPER_VERSION;
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active($plugin_directory)) {
            $plugin_active = true;
        }
        return array('plugin_version' => $plugin_version, 'plugin_active' => $plugin_active);
    }
}
