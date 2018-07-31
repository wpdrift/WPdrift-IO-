<?php
/**
 * REST API: WPdrift_Hits_Controller class
 *
 * @package WPdrift IO
 * @subpackage REST_API
 * @since 1.0.0
 */

/**
 * [WPdrift_Hits_Controller description]
 */
class WPdrift_Hits_Controller extends WP_REST_Controller {

	/**
	 * Here initialize our namespace and resource name.
	 */
	public function __construct() {
		$this->namespace = 'wpdriftio/v1';
		$this->rest_base = 'hits';
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
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/clicks', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_clicks' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
		) );

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/posts', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_posts' ),
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
	 * Grabs the most top clicks and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_clicks( $request ) {

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
		 * [$query_like description]
		 * @var string
		 */
		$query_fields  = "COUNT(*) as counts, host, uri";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE type='click'";
		$query_groupby = "GROUP BY host";
		$query_orderby = "ORDER BY counts DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$request = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$results = $wpdb->get_results( $request );

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( empty( $results ) ) {
			return rest_ensure_response( $data );
		}

		foreach ( $results as $result ) {
			$data[]   = $this->prepare_response_for_collection( $result );
		}

		/**
		 * Return all of our comment response data.
		 * @var [type]
		 */
		return rest_ensure_response( $data );
	}

	/**
	 * [get_posts description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_posts( $request ) {

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
		 * [$query_like description]
		 * @var string
		 */
		$query_fields  = "COUNT(*) as counts, page_id";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE type='view' AND page_id > 0";
		$query_groupby = "GROUP BY page_id";
		$query_orderby = "ORDER BY counts DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$request = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$results = $wpdb->get_results( $request );

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( empty( $results ) ) {
			return rest_ensure_response( $data );
		}

		foreach ( $results as $result ) {
			$response = $this->prepare_page_counts_for_response( $result, $request );
			$data[]   = $this->prepare_response_for_collection( $response );
		}

		/**
		 * Return all of our comment response data.
		 * @var [type]
		 */
		return rest_ensure_response( $data );
	}

	/**
	 * [prepare_page_counts_for_response description]
	 * @param  [type] $result  [description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function prepare_page_counts_for_response( $result, $request ) {
		$result_data = array();

		$result_data['counts']  = (int) $result->counts;
		$result_data['content'] = get_permalink( $result->page_id );

		/**
		 * [return description]
		 * @var [type]
		 */
		return rest_ensure_response( $result_data );
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
