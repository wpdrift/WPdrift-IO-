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
	public function record_hit() {

		/**
		 * Exit early.
		 * @var [type]
		 */
		if ( is_admin() ) {
			return;
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( ! ( is_singular() || is_archive() || is_home() || is_front_page() ) ) {
			return;
		}

		/**
		 * [$hit description]
		 * @var Models
		 */
		$hit_recorder = new Models\Hit();

		/**
		 * [$hit_recorder->type description]
		 * @var string
		 */
		$hit_recorder->type = 'view';

		/**
		 * [$hit_data description]
		 * @var [type]
		 */
		$hit_data = $this->get_hit();
		foreach ( $hit_data as $key => $value ) {
			if ( ! empty( $value ) ) {
				$hit_recorder->$key = $value;
			}
		}

		/**
		 * [$hit->save description]
		 * @var [type]
		 */
		$hit_recorder->save();
	}

	/**
	 * [record_click description]
	 * @return [type] [description]
	 */
	public function record_click() {
		/**
		 * [$hit_data description]
		 * @var [type]
		 */
		$hit_data = $_POST['hit'];
		$host     = $_POST['host'];
		$url      = $_POST['url'];

		/**
		 * [$hit description]
		 * @var Models
		 */
		$hit_recorder = new Models\Hit();
		$hit_recorder->save();

		/**
		 * [$hit_recorder->type description]
		 * @var string
		 */
		$hit_recorder->type = 'click';

		/**
		 * [foreach description]
		 * @var [type]
		 */
		foreach ( $hit_data as $key => $value ) {
			if ( ! empty( $value ) ) {
				$hit_recorder->$key = $value;
			}
		}

		/**
		 * [$hit_recorder->host description]
		 * @var [type]
		 */
		$hit_recorder->host = $host;
		$hit_recorder->uri  = $url;

		/**
		 * [$hit->save description]
		 * @var [type]
		 */
		$hit_recorder->save();

		echo $url;
		wp_die();
	}

	/**
	 * [get_hit description]
	 * @return [type] [description]
	 */
	public function get_hit() {
		$hit = array();

		$hit['user_id'] = get_current_user_id();
		$hit['agent']   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$hit['host']    = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
		$hit['uri']     = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$hit['ip']      = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		$hit['referer'] = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		$hit['page_id'] = $this->get_page_id();

		/**
		 * [$dd description]
		 * @var DeviceDetector
		 */
		$dd = new DeviceDetector( $hit['agent'] );
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
		$hit['client_type']       = $this->set_client( $client, 'type' );
		$hit['client_name']       = $this->set_client( $client, 'name' );
		$hit['client_short_name'] = $this->set_client( $client, 'short_name' );
		$hit['client_version']    = $this->set_client( $client, 'version' );
		$hit['client_engine']     = $this->set_client( $client, 'engine' );

		/**
		 * [$hit->os_name description]
		 * @var [type]
		 */
		$hit['os_name']       = $this->set_os( $os, 'name' );
		$hit['os_short_name'] = $this->set_os( $os, 'short_name' );
		$hit['os_version']    = $this->set_os( $os, 'version' );
		$hit['os_platform']   = $this->set_os( $os, 'platform' );

		/**
		 * [$hit->os_platform description]
		 * @var [type]
		 */
		$hit['device_name'] = isset( $device_name ) ? $device_name : __( 'Others', 'wpdrift-worker' );

		/**
		 * [$hit description]
		 * @var [type]
		 */
		$hit['domain'] = $this->get_domain();

		/**
		 * [return description]
		 * @var [type]
		 */
		return $hit;
	}

	/**
	 * [set_page description]
	 */
	public function get_page_id() {
		/**
		 * [if description]
		 * @var [type]
		 */
		if ( is_singular() ) {
			return get_the_ID();
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return null;
	}

	/**
	 * [set_domain description]
	 */
	public function get_domain() {
		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$uri = Uri\parse( $_SERVER['HTTP_REFERER'] );

			if ( $uri['host'] ) {
				return $uri['host'];
			}
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return home_url();
	}

	/**
	 * [set_client description]
	 */
	public function set_client( $client, $meta ) {
		if ( isset( $client[ $meta ] ) && ! empty( $client[ $meta ] ) ) {
			return $client[ $meta ];
		}

		return __( 'Others', 'wpdrift-oi' );
	}

	/**
	 * [set_os description]
	 * @param [type] $client [description]
	 * @param [type] $meta   [description]
	 */
	public function set_os( $os, $meta ) {
		if ( isset( $os[ $meta ] ) && ! empty( $os[ $meta ] ) ) {
			return $os[ $meta ];
		}

		return __( 'Others', 'wpdrift-oi' );
	}

	/**
	 * [record_login_activity description]
	 * @param  [type] $user_login [description]
	 * @param  [type] $user       [description]
	 * @return [type]             [description]
	 */
	public function record_login_activity( $user_login, $user ) {
		/**
		 * [$session_tokens description]
		 * @var [type]
		 */
		$session_tokens = get_user_meta( $user->ID, 'session_tokens', true );
		$sessions       = array();

		if ( ! empty( $session_tokens ) ) {
			foreach ( $session_tokens as $key => $session ) {
				$session['token'] = $key;
				$sessions[]       = $session;
			}
		}

		/**
		 * [update_user_meta description]
		 * @var [type]
		 */
		update_user_meta( $user->ID, 'last_login', $session );

		/**
		 * [$ip_data description]
		 * @var [type]
		 */
		$ip_data = json_decode( $this->ip_data( $session['ip'] ), true );
		if ( ! empty( $ip_data ) && ( 'success' == $ip_data['status'] ) ) {
			update_user_meta( $user->ID, 'ip_data', $ip_data );
			foreach ( $ip_data as $key => $value ) {
				update_user_meta( $user->ID, 'ip_' . $key, $value );
			}
		}
	}

	/**
	 * [ip_data description]
	 * @param  [type] $ip [description]
	 * @return [type]     [description]
	 */
	public function ip_data( $ip ) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL            => 'http://ip-api.com/json/' . $ip,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'GET',
			CURLOPT_HTTPHEADER     => array(
				'Cache-Control: no-cache',
				'Postman-Token: 4aa2c721-fb17-47f9-b4ca-8b4d125d01b8',
			),
		));

		$response = curl_exec( $curl );
		$err      = curl_error( $curl );

		curl_close( $curl );

		if ( $err ) {
			return 'cURL Error #:' . $err;
		}

		return $response;
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

		/**
		 * [$localize_script_data description]
		 * @var [type]
		 */
		$localize_script_data = [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'hit'     => $this->get_hit(),
		];

		wp_localize_script( $this->plugin_name, 'wpdrift_io', $localize_script_data );
	}

}
