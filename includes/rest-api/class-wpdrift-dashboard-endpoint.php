<?php
/**
 * WD_Dashboard_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * Dashboard endpoints.
 *
 * @since 1.0.0
 */
class WD_Dashboard_Endpoint extends WP_REST_Controller
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->namespace = 'wpdriftsupporter/v1';
        $this->rest_base = 'dashboard';
    }

    /**
     * Register the component routes.
     *
     * @since 1.0.0
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'                => array(

                ),
            )
        ));
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items($request)
    {
        $items = array();
        $items['users'] = count_users();
        $items['week_users'] = $this->get_weekly_users();
        $items['posts'] = wp_count_posts();
        $items['week_posts'] = $this->get_weekly_posts();
        $items['pages'] = wp_count_posts('page');
        $items['week_pages'] = $this->get_weekly_posts('page');
        $items['comments'] = wp_count_comments();
        $items['week_comments'] = $this->get_weekly_comments();
        $items['last_five_users'] = $this->retrieve_recent_five_signup_users();
        $data = array();
        foreach ($items as $key => $item) {
            $itemdata = $this->prepare_item_for_response($item, $request);
            $data[$key] = $this->prepare_response_for_collection($itemdata);
        }

        return rest_ensure_response($data);
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response($item, $request)
    {
        return $item;
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_items_permissions_check($request)
    {
        return current_user_can('list_users');
    }

    /**
     * Retrive weekly posts
     *
     * @since 1.0.0
     */
    public function get_weekly_posts($type = 'post')
    {
        $args = array(
            'post_type' => $type,
            'post_status' => 'publish',
            'date_query' => array(
                array(
                    'after' => '1 week ago'
                )
            )
        );

        $posts = get_posts($args);
        return count($posts);
    }

    /**
     * Retrive weekly comments
     *
     * @since 1.0.0
     */
    public function get_weekly_comments()
    {
        $args = array(
            'date_query' => array(
                array(
                    'after' => '1 week ago'
                )
            )
        );

        $comments = get_comments($args);
        return count($comments);
    }

    /**
     * Retrive weekly users
     *
     * @since 1.0.0
     */
    public function get_weekly_users()
    {
        $args = array(
            'date_query' => array(
                array(
                    'after' => '1 week ago'
                )
            )
        );

        $users = get_users($args);
        return count($users);
    }

    /**
    * Retrieve Recent Sigup Users
    *
    * @since 1.0.0
    */
    public function retrieve_recent_five_signup_users()
    {
        global $wpdb;
        $last_ten_uids = $wpdb->get_results("SELECT ID FROM $wpdb->users ORDER BY ID DESC LIMIT 5");
        $users_ids = array();
        foreach ($last_ten_uids as $last_ten_uid) {
            $users_ids[] = $last_ten_uid->ID;
        }
        $args = array(
          'include' => $users_ids
        );

        $users = get_users($args);
        foreach ($users as $user) {
            if (wh_has_gravatar($user->data->user_email)) {
                $user->avatar = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($user->data->user_email))) . '?s=96&d=404';
                ;
            } else {
                $user->avatar = "";
            }
        }
        return $users;
    }
}
