<?php
/**
 * EDD_GetLogs_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * EDD Logs endpoints.
 *
 * @since 1.0.0
 */
class EDD_GetLogs_Endpoint extends WP_REST_Controller
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->namespace = 'wpdriftio/v1';
        $this->rest_base = 'geteddlogs';
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
        $items['edd_logs'] = $this->retrieve_edd_logs($parameters);
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
    * Retrieve EDD Logs
    *
    * @since 1.0.0
    */
    public function retrieve_edd_logs($parameters)
    {
        global $wpdb;
        $posts_per_page = trim($parameters['per_page']) != "" ? trim($parameters['per_page']) : 1;
        $offset = trim($parameters['offset']) != "" ? trim($parameters['offset']) : 0;
        $task = trim($parameters['task']) != "" ? trim($parameters['task']) : "";
        $args = array(
            'post_type'              => 'edd_log',
            'post_status'            => 'any',
            'posts_per_page'         => $posts_per_page,
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            );

        if($task == "get_totals") {
            $logs = new WP_Query( $args );
            $edd_logs['found_posts'] = $logs->found_posts;
            $edd_logs['max_num_pages'] = $logs->max_num_pages;
        } else {
            $args['offset'] = $offset;
            $edd_logs = get_posts( $args );
        }

        return $edd_logs;
    }
}
