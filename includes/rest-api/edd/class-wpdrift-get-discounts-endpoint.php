<?php
/**
 * EDD_GetDiscounts_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * EDD Discounts endpoints.
 *
 * @since 1.0.0
 */
class EDD_GetDiscounts_Endpoint extends WP_REST_Controller
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->namespace = 'wpdriftio/v1';
        $this->rest_base = 'getdiscounts';
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
        $items['edd_discounts'] = $this->retrieve_edd_discounts($parameters);
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
    * Retrieve EDD Discounts
    *
    * @since 1.0.0
    */
    public function retrieve_edd_discounts($parameters)
    {
        $posts_per_page = trim($parameters['per_page']) != "" ? trim($parameters['per_page']) : 1;
        $offset = trim($parameters['offset']) != "" ? trim($parameters['offset']) : 0;
        $task = trim($parameters['task']) != "" ? trim($parameters['task']) : "";

        $args = array(
            'post_type'              => 'edd_discount',
            'post_status'            => 'any',
            'posts_per_page'         => $posts_per_page,
            'orderby'                => 'ID',
            'order'                  => 'ASC',
        );
        if($task == "get_totals") {
            $discounts = new WP_Query( $args );
            $edd_discounts['found_posts'] = $discounts->found_posts;
            $edd_discounts['max_num_pages'] = $discounts->max_num_pages;
        } else {
            $args['offset'] = $offset;
            $edd_discounts = get_posts( $args );
        }
        return $edd_discounts;
    }
}
