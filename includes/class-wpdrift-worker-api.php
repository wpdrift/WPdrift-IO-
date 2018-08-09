<?php

/**
 * The api-specific functionality of the plugin.
 *
 * @link       http://wpdrift.io/
 * @since      1.0.0
 *
 * @package    WPdrift_Worker
 * @subpackage WPdrift_Worker/includes
 * @author     WPdrift <kishore@upnrunn.com>
 */

class WPdrift_Worker_Api {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Function to register our new routes from the controller.
	 * @return [type] [description]
	 */
	public function register_rest_routes() {
		/**
		 * [require_once description]
		 * @var [type]
		 */
		require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-site-controller.php' );
		$site_controller = new WPdrift_Site_Controller();
		$site_controller->register_routes();

		/**
		 * [require_once description]
		 * @var [type]
		 */
		require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-dashboard-controller.php' );
		$dashboard_controller = new WPdrift_Dashboard_Controller();
		$dashboard_controller->register_routes();

		/**
		 * [require_once description]
		 * @var [type]
		 */
		require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-clients-controller.php' );
		$clients_controller = new WPdrift_Clients_Controller();
		$clients_controller->register_routes();

		/**
		 * [require_once description]
		 * @var [type]
		 */
		require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-users-controller.php' );
		$users_controller = new WPdrift_Users_Controller();
		$users_controller->register_routes();

		/**
		 * [require_once description]
		 * @var [type]
		 */
		require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-hits-controller.php' );
		$hits_controller = new WPdrift_Hits_Controller();
		$hits_controller->register_routes();

		/**
		 * Register new recent events end points
		 * @var [type]
		 */
		require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/class-wpdrift-events-controller.php' );
		$events_controller = new WPdrift_Events_Controller();
		$events_controller->register_routes();

		/**
		 * Detect EDD plugin. Then add edd all api end points
		 */
		if ( in_array( 'easy-digital-downloads/easy-digital-downloads.php', (array) get_option( 'active_plugins', array() ) ) ) {
			require_once( dirname( WPDRIFT_WORKER_FILE ) . '/includes/rest-api/edd/edd-end-points.php' );
		}

	}

}
