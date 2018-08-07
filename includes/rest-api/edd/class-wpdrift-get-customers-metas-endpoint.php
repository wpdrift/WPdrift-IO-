<?php
/**
 * EDD_GetCustomers_Metas_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * EDD Get Customers Meta endpoints.
 *
 * @since 1.0.0
 */
class EDD_GetCustomers_Metas_Endpoint extends WP_REST_Controller
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->namespace = 'wpdriftio/v1';
        $this->rest_base = 'getcustomers-metas';
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
        $items['edd_customers_metas'] = $this->retrieve_edd_customers_metas($parameters);
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
    * Retrieve EDD Customers Meta
    *
    * @since 1.0.0
    */
    public function retrieve_edd_customers_metas($parameters)
    {
        global $wpdb;
        $edd_customers = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."edd_customers");
        $new_array = array();
        $i = 0;
        foreach ($edd_customers as $edd_customer) {
            $new_array[$i]['customer_id'] = $edd_customer->id;
            // get posts meta
            $customers_metas = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."edd_customermeta");
            $i++;
        }
        return $customers_metas;
    }
}
