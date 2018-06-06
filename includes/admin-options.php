<?php

/**
 * WPOauth_Admin Class
 * Add admin functionkaity to the backend of WordPress
 */
class WPOAuth_Admin {

	/**
	 * WO Options Name
	 * @var string
	 */
	protected $option_name = 'wo_options';

	/**
	 * WP OAuth Server Admin Setup
	 * @return [type] [description]
	 */
	public static function init() {
		add_action( 'admin_init', array( new self, 'admin_init' ) );
		add_action( 'admin_menu', array( new self, 'add_page' ), 1 );
	}

	/**
	 * [admin_init description]
	 * @return [type] [description]
	 */
	public function admin_init() {
		register_setting( 'wo-options-group', $this->option_name );

		// New Pages Layout
		require_once( dirname( __FILE__ ) . "/admin/pages/add-new-client.php" );
		require_once( dirname( __FILE__ ) . "/admin/pages/manage-clients.php" );
		require_once( dirname( __FILE__ ) . "/admin/pages/edit-client.php" );
	}

	/**
	 * [add_page description]
	 */
	public function add_page() {
		add_menu_page( 'OAuth Server', 'OAuth Server', 'manage_options', 'wo_manage_clients', "wo_admin_manage_clients_page", 'dashicons-groups' );
	}

	/**
	 * WO options validation
	 *
	 * @param  [type] $input [description]
	 *
	 * @return [type]        [description]
	 */
	public function validate_options( $input ) {
		return $input;
	}
}

WPOAuth_Admin::init();
