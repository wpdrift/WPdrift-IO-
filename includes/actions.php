<?php
/**
 * WPdrift Worker actions
 *
 * @author  Support HQ <support@upnrunn.com>
 * @package WPdrift Worker
 */

// hide all error on api response
error_reporting( 0 );

// Debugging part
if (!function_exists('_custlog')) {
	function _custlog($message)
	{
		if (WP_DEBUG === true) {
			if (is_array($message) || is_object($message)) {
				error_log('<<<<<<<< :: DEBUG Array :: >>>>>>>>');
				error_log(print_r($message, true));
			} else {
				error_log('<<<<<<<< :: DEBUG String :: >>>>>>>>');
				error_log($message);
			}
		}
	}
}
