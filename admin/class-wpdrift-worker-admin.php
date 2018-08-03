<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wpdrift.io/
 * @since      1.0.0
 *
 * @package    WPdrift_Worker
 * @subpackage WPdrift_Worker/admin
 * @author     WPdrift <kishore@upnrunn.com>
 */

class WPdrift_Worker_Admin {

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
	 * Check incompatibility with WordPress version.
	 * @var [type]
	 */
	public function incompatibility_with_wp_version() {
		global $wp_version;

		/**
		 * Exit early.
		 * @var [type]
		 */
		if ( $wp_version >= 4.3 ) {
			return;
		}

		?>
		<div class="notice notice-error">
			<p>
				<?php _e( 'WPdrift IO Worker requires that WordPress 4.4 or greater be used. Update to the latest WordPress version.', 'wpdrift-worker' ); ?>
				<a href="<?php echo admin_url( 'update-core.php' ); ?>">
					<?php _e( 'Update Now', 'wpdrift-worker' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

}
