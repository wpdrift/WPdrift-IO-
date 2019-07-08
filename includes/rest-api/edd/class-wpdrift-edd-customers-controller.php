<?php
/**
 * REST API: WPdrift_EDD_Customers_Controller class
 *
 * @package WPdrift IO
 * @subpackage REST_API
 * @since 1.0.0
 */

/**
 * [WPdrift_EDD_Customers_Controller description]
 */
class WPdrift_EDD_Customers_Controller extends WP_REST_Controller {

	/**
	 * Here initialize our namespace and resource name.
	 */
	public function __construct() {
		$this->namespace = 'wpdriftio/v1';
		$this->rest_base = 'customers';
	}

	/**
	 * Register our routes.
	 * @return [type] [description]
	 */
	public function register_routes() {
		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_items' ),
					// 'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		/**
		 * [$post_endpoint description]
		 * @var string
		 */
		$customer_endpoint = '/' . $this->rest_base . '/(?P<id>[\d]+)';

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route(
			$this->namespace,
			$customer_endpoint,
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_item' ),
					// 'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
			)
		);

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route(
			$this->namespace,
			$customer_endpoint . '/metadata',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_metadata' ),
					// 'permission_callback' => array( $this, 'get_metadata_permissions_check' ),
				),
			)
		);

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/all',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_all' ),
					// 'permission_callback' => array( $this, 'get_all_permissions_check' ),
				),
			)
		);

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/updated',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_updated' ),
					// 'permission_callback' => array( $this, 'get_updated_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Grabs the most recent posts and outputs them as a rest response.
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_items( $request ) {
		global $wpdb;
		$data = [];
		return rest_ensure_response( $data );
	}

	/**
	 * Check permissions for the posts.
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * [get_item description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_item( $request ) {
		$id           = is_numeric( $request ) ? $request : (int) $request['id'];
		$customer_obj = new EDD_Customer( $id );

		if ( empty( $customer_obj ) ) {
			return rest_ensure_response( [] );
		}

		return rest_ensure_response( $customer_obj );
	}

	/**
	 * [get_item_permissions_check description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * [get_all description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_all( $request ) {
		global $wpdb;

		$table_name = EDD()->customers->table_name;
		$all        = $wpdb->get_col( "SELECT id FROM $table_name" );

		return $all;
	}

	/**
	 * [get_all_permissions_check description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_all_permissions_check( $request ) {
		if ( ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * [get_updated description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_updated( $request ) {
		$updated = get_option( 'edd_customers_updated' );
		update_option( 'edd_customers_updated', [] );
		return $updated;
	}

	/**
	 * [get_updated_permissions_check description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_updated_permissions_check( $request ) {
		if ( ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
	}

	/**
	 * [get_metadata description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_metadata( $request ) {
		$id       = is_numeric( $request ) ? $request : (int) $request['id'];
		$customer = new EDD_Customer( $id );
		$metadata = $customer->get_meta();
		return $metadata;
	}

	/**
	 * [get_metadata_permissions_check description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_metadata_permissions_check( $request ) {
		if ( ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the resource.' ), array( 'status' => $this->authorization_status_code() ) );
		}

		return true;
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
