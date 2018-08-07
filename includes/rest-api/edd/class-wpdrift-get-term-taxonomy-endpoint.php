<?php
/**
 * EDD_GetTerm_Taxonomy_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * EDD Term Taxonomy endpoints.
 *
 * @since 1.0.0
 */
class EDD_GetTerm_Taxonomy_Endpoint extends WP_REST_Controller
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->namespace = 'wpdriftio/v1';
        $this->rest_base = 'getterm-taxonomy';
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
        $items['edd_term_taxonomy'] = $this->retrieve_edd_term_taxonomy($parameters);
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
    * Retrieve EDD Term Taxonomy
    *
    * @since 1.0.0
    */
    public function retrieve_edd_term_taxonomy($parameters)
    {
        global $wpdb;
        $edd_term_taxonomy = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."term_taxonomy WHERE taxonomy LIKE %s OR taxonomy LIKE %s", 'download_category', 'download_tag'));
        $terms_tax_arry = array();
        $i = 0;
        foreach ($edd_term_taxonomy as $term_tax_value) {
            // get term name and slug //
            $term_details = $wpdb->get_results($wpdb->prepare("SELECT `name`, `slug` FROM ".$wpdb->prefix."terms WHERE term_id = %d", $term_tax_value->term_id));

            $terms_tax_arry[$i]['term_taxonomy_id'] = $term_tax_value->term_taxonomy_id;
            $terms_tax_arry[$i]['term_id'] = $term_tax_value->term_id;
            $terms_tax_arry[$i]['name'] = $term_details[0]->name;
            $terms_tax_arry[$i]['slug'] = $term_details[0]->slug;
            $terms_tax_arry[$i]['taxonomy'] = $term_tax_value->taxonomy;
            $terms_tax_arry[$i]['description'] = $term_tax_value->description;
            $terms_tax_arry[$i]['parent'] = $term_tax_value->parent;
            $terms_tax_arry[$i]['count'] = $term_tax_value->count;

            $i++;
        }
        return $terms_tax_arry;
    }
}
