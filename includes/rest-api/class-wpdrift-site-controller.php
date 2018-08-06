<?php
/**
 * REST API: WPdrift_Site_Controller class
 *
 * @package WPdrift IO
 * @subpackage REST_API
 * @since 1.0.0
 */

/**
 * [WPdrift_Site_Controller description]
 */
class WPdrift_Site_Controller extends WP_REST_Controller {

	/**
	 * Here initialize our namespace and resource name.
	 */
	public function __construct() {
		$this->namespace = 'wpdriftio/v1';
		$this->rest_base = 'site';
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

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route($this->namespace, '/' . $this->rest_base . '/plugin-status', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_plugin_status' ),
				// 'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		));
	}

	/**
	 * [public description]
	 * @var [type]
	 */
	public function get_plugin_status() {
		/**
		 * Detect plugin.
		 * @var [type]
		 */
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// check for plugin using plugin name
		if ( ! is_plugin_active( 'WPdrift-IO/wpdrift-io.php' ) ) {
			return rest_ensure_response( array() );
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return [ 'version' => WPDRIFT_WORKER_VERSION ];
	}

	/**
	 * Check permissions for the posts.
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_items_permissions_check( $request ) {
		/**
		 * [if description]
		 * @var [type]
		 */
		if ( '167.99.167.87' != $_SERVER['REMOTE_ADDR'] ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
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
