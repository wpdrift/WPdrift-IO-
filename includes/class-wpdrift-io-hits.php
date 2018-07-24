<?php

/**
 * [use description]
 * @var [type]
 */
use Models\Hit;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use League\Uri;

/**
 * [WPdrift_IO_Hits description]
 */
class WPdrift_IO_Hits {

	/**
	 * [__construct description]
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'hits' ) );
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
}

/**
 * [$wpdriftio_hits description]
 * @var WPdrift_IO_Hits
 */
$wpdriftio_hits = new WPdrift_IO_Hits();
