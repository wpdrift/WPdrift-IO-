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
        $edd_term_taxonomy = $wpdb->get_results($wpdb->prepare( "SELECT `term_taxonomy_id`, `taxonomy` FROM ".$wpdb->prefix."term_taxonomy WHERE taxonomy LIKE %s OR taxonomy LIKE %s", 'download_category', 'download_tag'  ));
        $term_ids = "";
        foreach ($edd_term_taxonomy as $term_tax_value) {
            // terms id
            $term_ids .= $term_tax_value->term_taxonomy_id.",";
        }
        // remove last comma from term_ids
        $term_ids = rtrim($term_ids, ',');
        $term_assigned = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."term_relationships WHERE term_taxonomy_id IN (%s)", $term_ids));

        return $term_assigned;
    }
}
