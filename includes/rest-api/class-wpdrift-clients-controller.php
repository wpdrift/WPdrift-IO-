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
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$data description]
		 * @var array
		 */
		$data = array();

		/**
		 * Return all of our comment response data.
		 * @var [type]
		 */
		return rest_ensure_response( $data );
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
