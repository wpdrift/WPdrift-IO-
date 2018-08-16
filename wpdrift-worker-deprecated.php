<?php

/**
 * Blowfish Encryptions
 *
 * @param  [type]  $input  [description]
 * @param  integer $rounds [description]
 *
 * @return [type]          [description]
 *
 * REQUIRES ATLEAST 5.3.x
 */
function wpdrift_worker_crypt($input, $rounds = 7)
{
	$salt       = "";
	$salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
	for ($i = 0; $i < 22; $i ++) {
		$salt .= $salt_chars[ array_rand($salt_chars) ];
	}

	return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
}

/**
 * Get the client IP multiple ways since REMOTE_ADDR is not always the best way to do so
 * @return [type] [description]
 */
function client_ip()
{
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP')) {
		$ipaddress = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('HTTP_X_FORWARDED')) {
		$ipaddress = getenv('HTTP_X_FORWARDED');
	} elseif (getenv('HTTP_FORWARDED_FOR')) {
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	} elseif (getenv('HTTP_FORWARDED')) {
		$ipaddress = getenv('HTTP_FORWARDED');
	} elseif (getenv('REMOTE_ADDR')) {
		$ipaddress = getenv('REMOTE_ADDR');
	} else {
		$ipaddress = 'UNKNOWN';
	}

	return $ipaddress;
}

/**
 * Check if server is running windows
 * @return boolean [description]
 */
function wpdrift_worker_os_is_win()
{
	if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
		return true;
	}

	return false;
}

/**
 * Check to see if there is certificates that have been generated
 *
 * @return boolean [description]
 */
function wpdrift_worker_has_certificates()
{
	$keys = apply_filters('wpdrift_worker_server_keys', array(
		'public'  => WOABSPATH . '/oauth/keys/public_key.pem',
		'private' => WOABSPATH . '/oauth/keys/private_key.pem',
	));

	if (is_array($keys)) {
		foreach ($keys as $key) {
			if (! file_exists($key)) {
				return false;
			}
		}

		return true;
	} else {
		return false;
	}
}

/**
 * Check if the server is using a secure connection or not.
 * @return bool
 */
function wpdrift_worker_is_protocol_secure()
{
	$isSecure = false;
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
		$isSecure = true;
	} elseif (! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || ! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
		$isSecure = true;
	}

	return $isSecure;
}
