<?php
/**
 * REST API: WPdrift_Hits_Controller class
 *
 * @package WPdrift IO
 * @subpackage REST_API
 * @since 1.0.0
 */

use Carbon\Carbon;
use League\Uri;

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

		$date_args = array();

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
		$date_query = new WP_Date_Query( $date_args, 'created_at' );

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$uri    = Uri\parse( get_site_url() );
		$domain = $uri['host'];

		/**
		 * [$query_like description]
		 * @var string
		 */
		$query_like    = "'%{$domain}%'";
		$query_fields  = "COUNT(*) as counts, domain, referer";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE domain NOT LIKE {$query_like} AND type='view'";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY domain";
		$query_orderby = "ORDER BY counts DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$query = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$results = $wpdb->get_results( $query );

		/**
		 * [$referers description]
		 * @var array
		 */
		$referers = array();
		foreach ( $results as $result ) {
			$data = array();
			$uri  = Uri\parse( $result->referer );

			$data['counts'] = $result->counts;
			$data['domain'] = $result->domain;
			$data['link']   = "{$uri['scheme']}://{$uri['host']}";

			$referers[] = $data;
		}

		/**
		 * [$data description]
		 * @var array
		 */
		$today = getdate();
		$data  = [
			'results'       => $referers,
			'browsers'      => $this->get_browsers( $date_args ),
			'oss'           => $this->get_oss( $date_args ),
			'days_hits'     => $this->get_hits( array(
				array(
					'year'  => $today['year'],
					'month' => $today['mon'],
					'day'   => $today['mday'],
				),
			) ),
			'best_hits_day' => $this->best_days_hits(),
			'all_hits'      => $this->all_hits(),
			'clicks'        => $this->get_clicks( $request ),
			'posts'         => $this->get_posts( $request ),
		];

		/**
		 * [return description]
		 * @var [type]
		 */
		return rest_ensure_response( $data );
	}

	/**
	 * [get_hits description]
	 * @return [type] [description]
	 */
	public function get_hits( $date_args ) {

		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WPdrift_Date_Query( $date_args, 'created_at' );

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*) as count, created_at";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE type='view'";
		$query_where  .= $date_query->get_sql();

		/**
		 * [$request description]
		 * @var string
		 */
		$query   = "SELECT $query_fields $query_from $query_where";
		$results = $wpdb->get_row( $query );

		/**
		 * [$data description]
		 * @var array
		 */
		$data = array();

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $results->created_at ) ) {
			$dt          = new Carbon( $results->created_at );
			$data['day'] = $dt->toFormattedDateString();
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $results->count ) ) {
			$data['counts'] = $results->count;
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $data;

	}

	/**
	 * [get_hits description]
	 * @return [type] [description]
	 */
	public function best_days_hits() {

		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*) as count, created_at, DAY(created_at) day, MONTH(created_at) month, YEAR(created_at) year";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE type='view'";
		$query_groupby = "GROUP BY day, month, year";
		$query_orderby = "ORDER BY count DESC";

		/**
		 * [$request description]
		 * @var string
		 */
		$query   = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby";
		$results = $wpdb->get_row( $query );

		/**
		 * [$data description]
		 * @var array
		 */
		$data = array();

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $results->created_at ) ) {
			$dt          = new Carbon( $results->created_at );
			$data['day'] = $dt->toFormattedDateString();
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $results->count ) ) {
			$data['counts'] = $results->count;
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $data;

	}

	/**
	 * [get_hits description]
	 * @return [type] [description]
	 */
	public function all_hits() {

		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*) as count";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE type='view'";

		/**
		 * [$request description]
		 * @var string
		 */
		$query   = "SELECT $query_fields $query_from $query_where";
		$results = $wpdb->get_var( $query );

		/**
		 * [return description]
		 * @var [type]
		 */
		return $results;

	}

	/**
	 * [get_browsers description]
	 * @return [type] [description]
	 */
	public function get_browsers( $date_args ) {
		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WP_Date_Query( $date_args, 'created_at' );

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*) as count, client_name";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE type='view'";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY client_name";
		$query_orderby = "ORDER BY count DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$query = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$counts                 = $wpdb->get_col( $query );
		$labels                 = $wpdb->get_col( $query, 1 );

		return [
			'counts' => $counts,
			'labels' => $labels,
		];
	}

	/**
	 * [get_oss description]
	 * @param  [type] $date_args [description]
	 * @return [type]            [description]
	 */
	public function get_oss( $date_args ) {
		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WP_Date_Query( $date_args, 'created_at' );

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT( DISTINCT os_name ) as count, os_name";
		$query_from    = "FROM {$wpdb->prefix}wpdriftio_hits";
		$query_where   = "WHERE type='view'";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY os_name";
		$query_orderby = "ORDER BY count DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$query = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$counts                 = $wpdb->get_col( $query );
		$labels                 = $wpdb->get_col( $query, 1 );

		return [
			'counts' => $counts,
			'labels' => $labels,
		];
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
		$query = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$results = $wpdb->get_results( $query );

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( empty( $results ) ) {
			return $data;
		}

		foreach ( $results as $result ) {
			$data[] = $result;
		}

		/**
		 * Return all of our comment response data.
		 * @var [type]
		 */
		return $data;
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
		$query   = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$results = $wpdb->get_results( $query );

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( empty( $results ) ) {
			return $data;
		}

		foreach ( $results as $result ) {
			$data[] = $this->prepare_page_counts_for_response( $result, $request );
		}

		/**
		 * Return all of our comment response data.
		 * @var [type]
		 */
		return $data;
	}

	/**
	 * [prepare_page_counts_for_response description]
	 * @param  [type] $result  [description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function prepare_page_counts_for_response( $result, $request ) {
		$result_data = array();

		$result_data['counts'] = (int) $result->counts;
		$result_data['link']   = get_permalink( $result->page_id );

		/**
		 * [return description]
		 * @var [type]
		 */
		return $result_data;
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
