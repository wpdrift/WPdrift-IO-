<?php
/**
 * REST API: WPdrift_Clients_Controller class
 *
 * @package WPdrift Worker
 * @subpackage WPdrift Worker/rest-api
 * @since 1.0.0
 */

/**
 * [WPdrift_Clients_Controller description]
 */
class WPdrift_Clients_Controller extends WP_REST_Controller {

	/**
	 * Here initialize our namespace and resource name.
	 */
	public function __construct() {
		$this->namespace = 'wpdriftio/v1';
		$this->rest_base = 'clients';
	}

	/**
	 * Register our routes.
	 * @return [type] [description]
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		) );
	}

	/**
	 * Check permissions for the posts.
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_items_permissions_check( $request ) {
		/**
		 * Dev only.
		 * @var [type]
		 */
		return true;
		if ( ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}
	}

	/**
	 * Grabs the most recent users and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_items( $request ) {
		/**
		 * [$args description]
		 * @var array
		 */
		$args = array(
			'post_type' => 'wo_client',
		);

		/**
		 * Get clients.
		 * @var [type]
		 */
		$clients = get_posts( $args );

		/**
		 * [$data description]
		 * @var array
		 */
		$data = array();

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( empty( $clients ) ) {
			return rest_ensure_response( $data );
		}

		/**
		 * [foreach description]
		 * @var [type]
		 */
		foreach ( $clients as $client ) {
			$response = $this->prepare_item_for_response( $client, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		/**
		 * Return all of our comment response data.
		 * @var [type]
		 */
		return rest_ensure_response( $data );
	}

	/**
	 * Matches the post data to the schema we want.
	 *
	 * @param WP_Post $post The comment object whose response is being prepared.
	 */
	public function prepare_item_for_response( $client, $request ) {
		/**
		 * [$client_data description]
		 * @var array
		 */
		$client_data = array();

		/**
		 * [$client_data description]
		 * @var [type]
		 */
		$client_data['id']            = (int) $client->ID;
		$client_data['client_id']     = get_post_meta( $client->ID, 'client_id', true );
		$client_data['client_secret'] = get_post_meta( $client->ID, 'client_secret', true );
		$client_data['redirect_uri']  = get_post_meta( $client->ID, 'redirect_uri', true );

		/**
		 * [return description]
		 * @var [type]
		 */
		return rest_ensure_response( $client_data );
	}

	/**
	 * Sets up the proper HTTP status code for authorization.
	 * @return [type] [description]
	 */
	public function authorization_status_code() {

		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}
}
