<?php
/**
 * EDD_GetTerm_Assigned_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * EDD Term Assigned endpoints.
 *
 * @since 1.0.0
 */
class EDD_GetTerm_Assigned_Endpoint extends WP_REST_Controller
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->namespace = 'wpdriftio/v1';
        $this->rest_base = 'getterm-assigned';
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
        $items['edd_term_assigned'] = $this->retrieve_edd_term_asigned($parameters);
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
    * Retrieve EDD Term Assigned
    *
    * @since 1.0.0
    */
    public function retrieve_edd_term_asigned($parameters)
    {
        global $wpdb;
        $per_page = trim($parameters['per_page']) != "" ? trim($parameters['per_page']) : 1;
        $offset = trim($parameters['offset']) != "" ? trim($parameters['offset']) : 0;
        $task = trim($parameters['task']) != "" ? trim($parameters['task']) : "";
        $term_id = trim($parameters['term_id']) != "" ? trim($parameters['term_id']) : 0;
        if($task == "get_totals") {
            $found_posts = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(`object_id`) FROM ".$wpdb->prefix."term_relationships WHERE term_taxonomy_id IN (%s)", $term_id));
            $term_assigned['found_posts'] = $found_posts;
            $max_num_pages = ceil($found_posts / $per_page);
            $term_assigned['max_num_pages'] = $max_num_pages;
        } else {
            $term_assigned = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."term_relationships WHERE term_taxonomy_id IN (%s) LIMIT %d,%d", $term_id, $offset, $per_page ));
        }
        return $term_assigned;
    }
}
