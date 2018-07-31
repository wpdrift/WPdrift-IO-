<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpdrift.io
 * @since      1.0.0
 *
 * @package    WPdrift_IO
 * @subpackage WPdrift_IO/public
 */

/**
 * [use description]
 * @var [type]
 */
use Models\Hit;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use League\Uri;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WPdrift_IO
 * @subpackage WPdrift_IO/public
 * @author     Your Name <email@example.com>
 */
class WPdrift_IO_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * [hits description]
	 * @return [type] [description]
	 */
	public function hits() {

		/**
		 * [$hit description]
		 * @var Models
		 */
		$hit          = new Models\Hit();
		$hit->user_id = get_current_user_id();
		$hit->agent   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$hit->host    = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
		$hit->uri     = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$hit->ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		$hit->referer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

		/**
		 * [$dd description]
		 * @var DeviceDetector
		 */
		$dd = new DeviceDetector( $hit->agent );
		$dd->parse();

		/**
		 * [$client description]
		 * @var [type]
		 */
		$client      = $dd->getClient();
		$os          = $dd->getOs();
		$device_name = $dd->getDeviceName();

		/**
		 * [$hit->client_type description]
		 * @var [type]
		 */
		$hit->client_type       = isset( $client['type'] ) ? $client['type'] : '';
		$hit->client_name       = isset( $client['name'] ) ? $client['name'] : '';
		$hit->client_short_name = isset( $client['short_name'] ) ? $client['short_name'] : '';
		$hit->client_version    = isset( $client['version'] ) ? $client['version'] : '';
		$hit->client_engine     = isset( $client['engine'] ) ? $client['engine'] : '';

		/**
		 * [$hit->os_name description]
		 * @var [type]
		 */
		$hit->os_name       = isset( $os['name'] ) ? $os['name'] : '';
		$hit->os_short_name = isset( $os['short_name'] ) ? $os['short_name'] : '';
		$hit->os_version    = isset( $os['version'] ) ? $os['version'] : '';
		$hit->os_platform   = isset( $os['platform'] ) ? $os['platform'] : '';

		/**
		 * [$hit->os_platform description]
		 * @var [type]
		 */
		$hit->device_name = isset( $device_name ) ? $device_name : '';

		/**
		 * [if description]
		 * @var [type]
		 */
		$hit->domain = home_url();
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$uri = Uri\parse( $_SERVER['HTTP_REFERER'] );

			if ( $uri['host'] ) {
				$hit->domain = $uri['host'];
			}
		}

		/**
		 * [$hit->save description]
		 * @var [type]
		 */
		$hit->save();
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WPdrift_IO_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPdrift_IO_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpdrift-io-public.js', array( 'jquery' ), $this->version, true );

	}

}
