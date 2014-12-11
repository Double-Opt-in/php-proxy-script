<?php namespace DoubleOptIn\Proxy;

use Exception;
use stdClass;

/**
 * Class DoubleOptInProxy
 *
 * Simply add one of these functions to your php script to get your access_token.
 *
 * DoubleOptIn\Proxy\DoubleOptInProxy::getAccessTokenAndRememberInSession($clientId, $clientSecret, $apiEndpoint);
 * DoubleOptIn\Proxy\DoubleOptInProxygetAccessToken($clientId, $clientSecret, $apiEndpoint);
 *
 * @package DoubleOptIn\Proxy
 */
class DoubleOptInProxy
{
	/**
	 * returns access token and remembers in session with automatically refresh
	 *
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param string $apiEndpoint
	 * @param bool $force refreshing the access_token call can be forced
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getAccessTokenAndRememberInSession($clientId, $clientSecret, $apiEndpoint, $force = false)
	{
		$key = '__double-opt-in_token';

		//  load from cache
		$apiResult = (isset($_SESSION[$key])) ? $_SESSION[$key] : null;

		//  retrieve
		if ($force || $apiResult === null || $apiResult->expired <= time()) {
			$accessToken = static::getAccessToken($clientId, $clientSecret, $apiEndpoint, false);

			$apiResult = new stdClass();
			$apiResult->access_token = $accessToken->access_token;
			$apiResult->expired = time() + $accessToken->expires_in;

			//  update cache
			$_SESSION[$key] = $apiResult;
		}

		return $apiResult->access_token;
	}

	/**
	 * returns the access token
	 *
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param string $apiEndpoint
	 * @param bool $returnAccessTokenOnly can also return the result as stdClass with "access_token" and "expires_in"
	 *     keys
	 *
	 * @throws Exception
	 * @return string|stdClass
	 */
	public static function getAccessToken($clientId, $clientSecret, $apiEndpoint, $returnAccessTokenOnly = true)
	{
		$url = $apiEndpoint . '/access_token';
		$data = array(
			'grant_type' => 'client_credentials',
			'client_id' => $clientId,
			'client_secret' => $clientSecret,
		);

		//  sending
		$defaults = array(
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $url,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 4,
			CURLOPT_POSTFIELDS => http_build_query($data)
		);

		$ch = curl_init();
		curl_setopt_array($ch, $defaults);
		if ( ! $result = curl_exec($ch)) {
			trigger_error(curl_error($ch));
		}
		curl_close($ch);

		$result_object = json_decode($result);

		if (isset($result_object->error))
			throw new Exception($result_object->error_description);

		if ( ! isset($result_object->access_token))
			throw new Exception('No access token in response');

		if ( ! $returnAccessTokenOnly)
			return $result_object;

		return $result_object->access_token;
	}
}
