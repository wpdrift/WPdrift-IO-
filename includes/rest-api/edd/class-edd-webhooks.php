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
		$url = "{$this->edd_site}/handle_customer_create";
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'post_id' => $post_ID ),
			'cookies' => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
		} else {
			// success
		}
	}
	function send_customer_updated($updated, $post_ID)  {
		$url = "{$this->edd_site}/handle_customer_update";
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'post_id' => $post_ID ),
			'cookies' => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
		} else {
			// success
		}
	}
	function send_customer_deleted($updated, $post_ID)  {
		$url = "{$this->edd_site}/handle_customer_delete";
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'post_id' => $post_ID ),
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
// send post upon edd customer post create action
add_action('edd_customer_post_create', array($edd_webhook_obj, 'send_customer_created', 10, 1));
// send post upon edd customer post update action
add_action('edd_customer_post_update', array($edd_webhook_obj, 'send_customer_updated'), 10, 2);
// send post upon edd customer post delete action
add_action('edd_pre_delete_customer', array($edd_webhook_obj, 'send_customer_deleted'), 10, 1);

?>