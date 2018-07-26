<?php
/**
 * WD_Dashboard_Endpoint class
 */

defined( 'ABSPATH' ) || exit;
use Carbon\Carbon;
use League\Uri;

/**
 * Dashboard endpoints.
 *
 * @since 1.0.0
 */
class WD_Dashboard_Endpoint extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = 'wpdriftsupporter/v1';
		$this->rest_base = 'dashboard';
	}

	/**
	 * Register the component routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
		));

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route($this->namespace, '/' . $this->rest_base . '/bloginfo', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_bloginfo' ),
				'args'     => array(),
			),
		));

		/**
		 * [register_rest_route description]
		 * @var [type]
		 */
		register_rest_route($this->namespace, '/' . $this->rest_base . '/referers', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_referers' ),
				// 'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
		));
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
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

		$items                   = array();
		$items['count_users']    = count_users();
		$items['count_posts']    = wp_count_posts();
		$items['count_pages']    = wp_count_posts( 'page' );
		$items['count_comments'] = wp_count_comments();
		$items['users']          = $this->get_users( $date_args );
		$items['posts']          = $this->get_posts( $date_args );
		$items['pages']          = $this->get_posts( $date_args, 'page' );
		$items['comments']       = $this->get_comments( $date_args );

		/**
		 * [$data description]
		 * @var array
		 */
		$data = array();
		foreach ( $items as $key => $item ) {
			$itemdata     = $this->prepare_item_for_response( $item, $request );
			$data[ $key ] = $this->prepare_response_for_collection( $itemdata );
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return rest_ensure_response( $data );
	}

	/**
	 * [get_version description]
	 * @return [type] [description]
	 */
	public function get_bloginfo() {

		/**
		 * [$data description]
		 * @var array
		 */
		$data = array(
			'name'              => get_bloginfo( 'name' ),
			'description'       => get_bloginfo( 'description' ),
			'version'           => get_bloginfo( 'version' ),
			'url'               => get_bloginfo( 'url' ),
			'admin_email'       => get_bloginfo( 'admin_email' ),
			'language'          => get_bloginfo( 'language' ),
			'rss2_url'          => get_bloginfo( 'rss2_url' ),
			'comments_rss2_url' => get_bloginfo( 'comments_rss2_url' ),
			'admin_url'         => admin_url(),
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
		);

		/**
		 * [return description]
		 * @var [type]
		 */
		return rest_ensure_response( $data );
	}

	/**
	 * [get_referers description]
	 * @return [type] [description]
	 */
	public function get_referers( $request ) {
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
		$query_where   = "WHERE domain NOT LIKE {$query_like}";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY domain";
		$query_orderby = "ORDER BY counts DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$request = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$results = $wpdb->get_results( $request );

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
		$query_where   = "WHERE 1=1";
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
		$query_where   = "WHERE 1=1";
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

		/**
		 * [$request description]
		 * @var string
		 */
		$query   = "SELECT $query_fields $query_from";
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
		$query_where   = "WHERE 1=1";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY client_name";
		$query_orderby = "ORDER BY count DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$request = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$counts                 = $wpdb->get_col( $request );
		$labels                 = $wpdb->get_col( $request, 1 );

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
		$query_where   = "WHERE 1=1";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY os_name";
		$query_orderby = "ORDER BY count DESC";
		$query_limit   = "LIMIT 10";

		/**
		 * [$request description]
		 * @var string
		 */
		$request = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby $query_limit";
		$counts                 = $wpdb->get_col( $request );
		$labels                 = $wpdb->get_col( $request, 1 );

		return [
			'request' => $request,
			'counts' => $counts,
			'labels' => $labels,
		];
	}

	/**
	 * [get_users description]
	 * @return [type] [description]
	 */
	public function get_users( $date_args ) {
		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WP_Date_Query( $date_args, 'user_registered' );

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*), user_registered, EXTRACT(DAY FROM user_registered) day, EXTRACT(MONTH FROM user_registered) month, EXTRACT(YEAR FROM user_registered) year";
		$query_from    = "FROM $wpdb->users";
		$query_where   = "WHERE 1=1";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY day, month, year";
		$query_orderby = "ORDER BY user_registered ASC";

		/**
		 * [$request description]
		 * @var string
		 */
		$request             = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby";
		$col                 = $wpdb->get_col( $request );
		$col_user_registered = $wpdb->get_col( $request, 1 );

		/**
		 * [$data description]
		 * @var array
		 */
		$data = [
			'total'  => array_sum( $col ),
			'data'   => $col,
			'labels' => $col_user_registered,
		];

		/**
		 * [return description]
		 * @var [type]
		 */
		return $data;
	}

	/**
	 * [get_posts description]
	 * @return [type] [description]
	 */
	public function get_posts( $date_args, $post_type = 'post' ) {
		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WP_Date_Query( $date_args );

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
		 * [$data description]
		 * @var array
		 */
		$data = [
			'total' => array_sum( $col ),
			'data'  => $col,
		];

		/**
		 * [return description]
		 * @var [type]
		 */
		return $data;
	}

	/**
	 * [get_comments description]
	 * @param  [type] $date_args [description]
	 * @return [type]            [description]
	 */
	public function get_comments( $date_args ) {
		/**
		 * [global description]
		 * @var [type]
		 */
		global $wpdb;

		/**
		 * [$date_query description]
		 * @var WP_Date_Query
		 */
		$date_query = new WP_Date_Query( $date_args, 'comment_date' );

		/**
		 * [$query_fields description]
		 * @var string
		 */
		$query_fields  = "COUNT(*), EXTRACT(DAY FROM comment_date) day, EXTRACT(MONTH FROM comment_date) month, EXTRACT(YEAR FROM comment_date) year";
		$query_from    = "FROM $wpdb->comments";
		$query_where   = "WHERE 1=1";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY day, month, year";
		$query_orderby = "ORDER BY comment_date ASC";

		/**
		 * [$request description]
		 * @var string
		 */
		$request = "SELECT $query_fields $query_from $query_where $query_groupby $query_orderby";
		$col     = $wpdb->get_col( $request );

		/**
		 * [$data description]
		 * @var array
		 */
		$data = [
			'total' => array_sum( $col ),
			'data'  => $col,
		];

		/**
		 * [return description]
		 * @var [type]
		 */
		return $data;
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'list_users' );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		return $item;
	}

}
