<?php
/**
 * EDD_GetPayments_Endpoint class
 */

defined('ABSPATH') || exit;

/**
 * Edd Webhooks
 *
 * @since 1.0.0
 */
class EDD_WebHooks {
	public $edd_site = "http://eddlumen.local";
	
	function send_customer_created($post_ID)  {
		$this->send_webhook('handle_customer_create', array( 'post_id' => $post_ID ));
	}
	function send_customer_updated($updated, $post_ID) {
		$this->send_webhook('handle_customer_update', array( 'post_id' => $post_ID ));
	}
	function send_customer_deleted($updated, $post_ID)  {
		$this->send_webhook('handle_customer_delete', array( 'post_id' => $post_ID ));
	}
	// USER
	function send_user_created($user_id)  {
		$this->send_webhook('handle_user_create', array( 'user_id' => $user_id ));
	}
	function send_user_updated($user_id)  {
		$this->send_webhook('handle_user_update', array( 'user_id' => $user_id ));
	}
	function send_user_deleted($user_id)  {
		$this->send_webhook('handle_user_delete', array( 'user_id' => $user_id ));
	}
	// TERM ASSIGNED
	function send_term_assigned($object_id)  {
		$this->send_webhook('handle_term_assign', array( 'object_id' => $object_id ));
	}
	// ALL POST TYPES INSERTS
	function send_post_created($post_id)  {
		$post_type = get_post_type($post_id);
		$params = array( 'post_id' => $post_id );
		// if not any required post type return
		if ( !in_array($post_type, array('edd_discount', 'download', 'edd_log', 'edd_payment')) ) return;
		switch ($post_type) {
			case 'edd_discount':
				$this->send_webhook('handle_discount_created', $params);
				break;
			case 'download':
				$this->send_webhook('handle_download_created', $params);
				break;
			case 'edd_log':
				$this->send_webhook('handle_eddlog_created', $params);
				break;
			case 'edd_payment':
				$this->send_webhook('handle_payment_created', $params);
				break;
		}
	}
	// ALL POST TYPES UPDATES
	function send_post_updated($post_id)  {
		$post_type = get_post_type($post_id);
		$params = array( 'post_id' => $post_id );
		// if not any required post type return
		if ( !in_array($post_type, array('edd_discount', 'download', 'edd_log', 'edd_payment')) ) return;
		switch ($post_type) {
			case 'edd_discount':
				$this->send_webhook('handle_discount_created', $params);
				break;
			case 'download':
				$this->send_webhook('handle_download_created', $params);
				break;
			case 'edd_log':
				$this->send_webhook('handle_eddlog_created', $params);
				break;
			case 'edd_payment':
				$this->send_webhook('handle_payment_created', $params);
				break;
		}
	}
	// ALL POST TYPES DELETES
	function send_post_deleted($post_id)  {
		global $post_type;
		$params = array( 'post_id' => $post_id );
		// if not any required post type return
		if ( !in_array($post_type, array('edd_discount', 'download', 'edd_log', 'edd_payment')) ) return;
		switch ($post_type) {
			case 'edd_discount':
				$this->send_webhook('handle_discount_delete', $params);
				break;
			case 'download':
				$this->send_webhook('handle_download_delete', $params);
				break;
			case 'edd_log':
				$this->send_webhook('handle_eddlog_delete', $params);
				break;
			case 'edd_payment':
				$this->send_webhook('handle_payment_delete', $params);
				break;
		}
	}
	protected function send_webhook($end_point, $params) {
		$url = "{$this->edd_site}/{$end_point}";
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $params,
			'cookies' => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
		} else {
			// success
		}
	}
}
$edd_webhook_obj = new EDD_WebHooks();
// edd customer post create action
add_action('edd_customer_post_create', array($edd_webhook_obj, 'send_customer_created', 10, 1));
// edd customer post update action
add_action('edd_customer_post_update', array($edd_webhook_obj, 'send_customer_updated'), 10, 2);
// edd customer post delete action
add_action('edd_pre_delete_customer', array($edd_webhook_obj, 'send_customer_deleted'), 10, 1);
// new user add
add_action( 'user_register', array($edd_webhook_obj, 'send_user_created'), 10, 1 );
// user profile update
add_action( 'profile_update', array($edd_webhook_obj, 'send_user_updated'), 10, 1 );
// user delete
add_action( 'delete_user', array($edd_webhook_obj, 'send_user_deleted') );
// post type created
add_action( 'wp_insert_post', array($edd_webhook_obj, 'send_post_created'), 10, 1 );
// post type updated
add_action( 'post_updated', array($edd_webhook_obj, 'send_post_updated'), 10, 1 );
// post type delete
add_action( 'before_delete_post', array($edd_webhook_obj, 'send_post_deleted') );
// edd logs add
add_action( 'edd_post_insert_log', array($edd_webhook_obj, 'send_eddlog_deleted') );

// taxonmy assigned
add_action( 'set_object_terms', array($edd_webhook_obj, 'send_term_assigned'), 10, 1 );
?>