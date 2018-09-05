<?php
/**
 * EDD_GetUsers_Metas_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * EDD Get Users Meta endpoints.
 *
 * @since 1.0.0
 */
class EDD_GetUsers_Metas_Endpoint extends WP_REST_Controller
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->namespace = 'wpdriftio/v1';
        $this->rest_base = 'getusers-metas';
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
        $parameters = $request->get_params();
        $items = array();
        $items['edd_users_metas'] = $this->retrieve_edd_users_metas($parameters);
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
    * Retrieve EDD Users Meta
    *
    * @since 1.0.0
    */
    public function retrieve_edd_users_metas($parameters)
    {
        global $wpdb;
        $posts_per_page = trim($parameters['per_page']) != "" ? trim($parameters['per_page']) : 1;
        $offset = trim($parameters['offset']) != "" ? trim($parameters['offset']) : 0;
        $task = trim($parameters['task']) != "" ? trim($parameters['task']) : "";
        $post_id = trim($parameters['id']) != "" ? trim($parameters['id']) : "";

        if($task == "get_totals") {
            $found_posts = $wpdb->get_var( "SELECT count(`umeta_id`) FROM ".$wpdb->prefix."usermeta" );
            $users_metas['found_posts'] = $found_posts;
            $max_num_pages = ceil($found_posts / $posts_per_page);
            $users_metas['max_num_pages'] = $max_num_pages;
        } else if($task == "get_single") {
            $users_metas = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."usermeta WHERE user_id = %d", $post_id ));
        } else {
            $users_metas = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."usermeta LIMIT %d,%d", $offset, $posts_per_page ));
        }
        return $users_metas;
    }
}
