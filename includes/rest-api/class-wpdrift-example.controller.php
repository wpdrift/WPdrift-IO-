<?php
/**
 * REST API: WPdrift_Statistics_Controller class
 *
 * @package WPdrift IO
 * @subpackage REST_API
 * @since 1.0.0
 */

use Carbon\Carbon;

/**
 * [WPdrift_Statistics_Controller description]
 */
class WPdrift_Statistics_Controller extends WP_REST_Controller {
	/**
	 * Here initialize our namespace and resource name.
	 */
	public function __construct() {
		$this->namespace = 'wpdriftio/v1';
		$this->rest_base = 'statistics';
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
		 * [if description]
		 * @var [type]
		 */
		if ( ! current_user_can( 'list_users' ) ) {
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
	 * [get_posts description]
	 * @return [type] [description]
	 */
	public function get_posts( $request ) {
		/**
		 * [$date description]
		 * @var [type]
		 */
		$date_query      = $this->prepare_date( $request );
		$query_arguments = array(
			'post_type'  => 'post',
			'date_query' => $date_query,
		);

		/**
		 * [$data description]
		 * @var [type]
		 */
		$data                = $this->query_posts( $query_arguments );
		$date_query_compared = $this->date_query_compared( $date_query );

		/**
		 * [$query_arguments_compared description]
		 * @var array
		 */
		$query_arguments_compared = array(
			'post_type'  => 'post',
			'date_query' => $date_query_compared,
		);
		$data_compared            = empty( $date_query_compared ) ? [] : $this->query_posts( $query_arguments_compared );
		$progress                 = $this->get_progress( $data, $data_compared );

		/**
		 * [return description]
		 * @var [type]
		 */
		return [
			'data'          => $data,
			'data_compared' => $data_compared,
			'progress'      => $progress,
		];
	}

	/**
	 * [query_posts description]
	 * @param  [type] $date_args [description]
	 * @return [type]            [description]
	 */
	public function query_posts( $query_arguments ) {
		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WP_Date_Query( $query_arguments['date_query'] );
		$post_type  = $query_arguments['post_type'];

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*), EXTRACT(DAY FROM post_date) day, EXTRACT(MONTH FROM post_date) month, EXTRACT(YEAR FROM post_date) year";
		$query_from    = "FROM $wpdb->posts";
		$query_where   = "WHERE post_type = '$post_type'";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY day, month, year";
		$query_orderby = "ORDER BY post_date ASC";

		/**
		 * [$request description]
		 * @var string
		 */
		$request = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby";
		$col     = $wpdb->get_col( $request );

		/**
		 * [return description]
		 * @var [type]
		 */
		return [
			'counts' => array_sum( $col ),
			'data'   => $col,
		];
	}

	/**
	 * [date_args_compared description]
	 * @return [type] [description]
	 */
	public function date_query_compared( $date_query ) {
		/**
		 * [if description]
		 * @var [type]
		 */
		if ( ! isset( $date_query[0]['after'] ) ) {
			return array();
		}

		/**
		 * [$date_args_compared description]
		 * @var array
		 */
		$date_query_compared = array();

		/**
		 * [$after description]
		 * @var [type]
		 */
		$after    = Carbon::parse( $date_args[0]['after'] );
		$before   = isset( $date_query[0]['before'] ) ? Carbon::parse( $date_query[0]['before'] ) : Carbon::now();
		$diffdays = $after->diffInDays( $before, false );

		/**
		 * [$dt3 description]
		 * @var [type]
		 */
		$previous_after  = Carbon::parse( $date_query[0]['after'] );
		$previous_before = Carbon::parse( $date_query[0]['after'] );
		$previous_after->subDays( $diffdays );

		$date_query_compared[0]['after']  = $previous_after->toFormattedDateString();
		$date_query_compared[0]['before'] = $previous_before->toFormattedDateString();

		/**
		 * [return description]
		 * @var [type]
		 */
		return $date_query_compared;
	}

	/**
	 * [prepare_date description]
	 * @return [type] [description]
	 */
	public function prepare_date( $request ) {
		/**
		 * [$date description]
		 * @var array
		 */
		$date = array();

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $request['after'] ) ) {
			$date[0]['after'] = $request['after'];
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $request['before'] ) ) {
			$date[0]['before'] = $request['before'];
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $date;
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
