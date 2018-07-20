<?php

/**
 * [use description]
 * @var [type]
 */
use Models\Hit;

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
		$hit          = new Models\Hit();
		$hit->user_id = get_current_user_id();
		$hit->agent   = $_SERVER['HTTP_USER_AGENT'];
		$hit->host    = $_SERVER['HTTP_HOST'];
		$hit->uri     = $_SERVER['REQUEST_URI'];
		$hit->ip      = $_SERVER['REMOTE_ADDR'];
		$hit->referer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		$hit->save();
	}
}

/**
 * [$wpdriftio_hits description]
 * @var WPdrift_IO_Hits
 */
$wpdriftio_hits = new WPdrift_IO_Hits();
