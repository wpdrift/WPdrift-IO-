<?php
/**
 * WD_Dashboard_Endpoint class
 */

defined( 'ABSPATH' ) || exit;

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
		register_rest_route($this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
		));

		register_rest_route($this->namespace, '/' . $this->rest_base . '/bloginfo', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_bloginfo' ),
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

		$items                = array();
		$items['count_users'] = count_users();
		$items['count_posts'] = wp_count_posts();
		$items['count_pages'] = wp_count_posts( 'page' );
		$items['count_comments'] = wp_count_comments();
		$items['users']       = $this->get_users( $date_args );
		$items['posts']       = $this->get_posts( $date_args );
		$items['pages']       = $this->get_posts( $date_args, 'page' );
		$items['comments']    = $this->get_comments( $date_args );

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
		$data = array(
			'name' => get_bloginfo( 'name' ),
			'description' => get_bloginfo( 'description' ),
			'version' => get_bloginfo( 'version' ),
			'url' => get_bloginfo( 'url' ),
			'admin_email' => get_bloginfo( 'admin_email' ),
			'language' => get_bloginfo( 'language' ),
			'rss2_url' => get_bloginfo( 'rss2_url' ),
			'comments_rss2_url' => get_bloginfo( 'comments_rss2_url' ),
			'admin_url' => admin_url(),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		return rest_ensure_response( $data );
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

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
		return current_user_can( 'list_users' );
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
		$query_fields  = "COUNT(*), EXTRACT(DAY FROM user_registered) day, EXTRACT(MONTH FROM user_registered) month, EXTRACT(YEAR FROM user_registered) year";
		$query_from    = "FROM $wpdb->users";
		$query_where   = "WHERE 1=1";
		$query_where  .= $date_query->get_sql();
		$query_groupby = "GROUP BY day, month, year";
		$query_orderby = "ORDER BY user_registered ASC";

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
		$date_query = new WP_Date_Query( $date_args );

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
}
