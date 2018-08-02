<?php
/**
 * REST API: WPdrift_Users_Controller class
 *
 * @package WPdrift IO
 * @subpackage REST_API
 * @since 1.0.0
 */

use Carbon\Carbon;
// use SebastianBergmann\Timer\Timer;

/**
 * [WPdrift_Users_Controller description]
 */
class WPdrift_Users_Controller extends WP_REST_Controller {

	/**
	 * Here initialize our namespace and resource name.
	 */
	public function __construct() {
		$this->namespace = 'wpdriftio/v1';
		$this->rest_base = 'users';
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
		// return array();

		/**
		 * [Timer description]
		 * @var [type]
		 */
		// Timer::start();

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
		 * [$date_args description]
		 * @var array
		 */
		$date_args = array();
		$mode      = isset( $request['mode'] ) ? $request['mode'] : 'day';

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $request['after'] ) ) {
			$date_args[0]['after'] = $request['after'];
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $request['before'] ) ) {
			$date_args[0]['before'] = $request['before'];
		}

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WP_Date_Query( $date_args, 'user_registered' );

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*), user_registered, MINUTE(user_registered) minute, HOUR(user_registered) hour, DAY(user_registered) day, WEEK(user_registered) week, MONTH(user_registered) month, YEAR(user_registered) year";
		$query_from    = "FROM $wpdb->users";
		$query_where   = "WHERE 1=1";
		$query_where  .= $date_query->get_sql();
		$query_groupby = $this->query_groupby( $mode );
		$query_orderby = "ORDER BY user_registered ASC";

		/**
		 * [$request description]
		 * @var string
		 */
		$query               = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby";
		$col                 = $wpdb->get_col( $query );
		$col_user_registered = $wpdb->get_col( $query, 1 );

		/**
		 * [$data description]
		 * @var array
		 */
		$data = array();

		/**
		 * [$response description]
		 * @var [type]
		 */
		$response = [
			// 'query'   => $query,
			'total'   => array_sum( $col ),
			'data'    => $col,
			'labels'  => $this->prepare_labels( $col_user_registered, $mode ),
			'filters' => $this->get_filters( $date_args, $mode ),
		];

		/**
		 * [$data description]
		 * @var [type]
		 */
		$data['results'] = $this->prepare_response_for_collection( $response );

		/**
		 * [$time description]
		 * @var [type]
		 */
		// $time         = Timer::stop();
		// $data['time'] = Timer::secondsToTimeString( $time );

		/**
		 * Return all of our comment response data.
		 * @var [type]
		 */
		return rest_ensure_response( $data );
	}

	/**
	 * [prepare_labels description]
	 * @param  [type] $labels [description]
	 * @return [type]         [description]
	 */
	public function prepare_labels( $dates, $mode ) {
		/**
		 * [$labels description]
		 * @var array
		 */
		$labels = array();

		foreach ( $dates as $date ) {
			// $labels[] = $date;
			$dt       = Carbon::createFromFormat( 'Y-m-d H:i:s', $date );
			$labels[] = $this->formated_date( $dt, $mode );
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $labels;
	}

	/**
	 * [query_groupby description]
	 * @return [type] [description]
	 */
	public function query_groupby( $mode ) {
		/**
		 * [switch description]
		 * @var [type]
		 */
		switch ( $mode ) {
			case 'minute':
				return "GROUP BY minute, hour, day, month, year";
				break;
			case 'hour':
				return "GROUP BY hour, day, month, year";
				break;
			case 'week':
				return "GROUP BY week, month, year";
				break;
			case 'month':
				return "GROUP BY month, year";
				break;
			case 'year':
				return "GROUP BY year";
				break;
			default:
				return "GROUP BY day, month, year";
		}
	}

	/**
	 * [formated_date description]
	 * @param  [type] $dt   [description]
	 * @param  [type] $mode [description]
	 * @return [type]       [description]
	 */
	public function formated_date( $dt, $mode ) {
		switch ( $mode ) {
			case 'minute':
				return $dt->format( 'h:i A' );
				break;
			case 'hour':
				return $dt->format( 'h A, d' );
				break;
			case 'week':
				return $dt->format( 'd M y' );
				break;
			case 'month':
				return $dt->format( 'M Y' );
				break;
			case 'year':
				return $dt->format( 'Y' );
				break;
			default:
				return $dt->toFormattedDateString();
		}
	}

	/**
	 * [get_filters description]
	 * @param  [type] $dates [description]
	 * @param  [type] $mode  [description]
	 * @return [type]        [description]
	 */
	public function get_filters( $dates, $mode ) {
		$filters = [
			'minute' => __( 'Minute', 'wpdrift-io' ),
			'hour'   => __( 'hour', 'wpdrift-io' ),
			'day'    => __( 'Day', 'wpdrift-io' ),
			'week'   => __( 'Week', 'wpdrift-io' ),
			'month'  => __( 'Month', 'wpdrift-io' ),
			'year'   => __( 'Year', 'wpdrift-io' ),
		];

		return $filters;
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
