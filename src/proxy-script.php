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
	 * returns access token and remembers in file
	 *
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param string $apiEndpoint
	 * @param string $file
	 * @param bool $force refreshing the access_token call can be forced
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function getAccessTokenAndRememberInFile($clientId, $clientSecret, $apiEndpoint, $file, $force = false) {
		//  load from cache
		$apiResult = (is_readable($file)) ? unserialize(file_get_contents($file)) : null;

		//  retrieve
		if ($force || $apiResult === null || $apiResult->expired <= time()) {
			$accessToken = static::getAccessToken($clientId, $clientSecret, $apiEndpoint, false);

			$apiResult = new stdClass();
			$apiResult->access_token = $accessToken->access_token;
			$apiResult->expired = time() + $accessToken->expires_in;

			//  update cache
			file_put_contents($file, serialize($apiResult));
		}

		return $apiResult->access_token;
	}

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

		if ( ! static::isSessionStarted())
			session_start();

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
	 * is a session already started
	 *
	 * @return bool
	 */
	private static function isSessionStarted()
	{
		if ( php_sapi_name() === 'cli' )
			return false;

		if (version_compare(phpversion(), '5.4.0', '>='))
			return session_status() === PHP_SESSION_ACTIVE ? true : false;

		return session_id() === '' ? false : true;
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

		$accessTokenObject = json_decode($result);

		if (isset($accessTokenObject->error))
			throw new Exception($accessTokenObject->error);

		if ( ! isset($accessTokenObject->access_token))
			throw new Exception('No access token in response');

		if ( ! $returnAccessTokenOnly)
			return $accessTokenObject;

		return $accessTokenObject->access_token;
	}
}
