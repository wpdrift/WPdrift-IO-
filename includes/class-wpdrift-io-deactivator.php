<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://upnrunn.com
 * @since      1.0.0
 *
 * @package    WPdrift_IO
 * @subpackage WPdrift_IO/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WPdrift_IO
 * @subpackage WPdrift_IO/includes
 * @author     upnrunn <admin@upnrunn.com>
 */

use Illuminate\Database\Capsule\Manager as Capsule;

class WPdrift_IO_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
		Capsule::schema()->drop( $wpdb->prefix . 'wpdriftio_hits' );
	}

}
