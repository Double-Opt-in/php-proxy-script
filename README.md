# php-proxy-script

[![Latest Stable Version](https://poser.pugx.org/double-opt-in/php-proxy-script/v/stable.svg)](https://packagist.org/packages/double-opt-in/php-proxy-script) [![Latest Unstable Version](https://poser.pugx.org/double-opt-in/php-proxy-script/v/unstable.svg)](https://packagist.org/packages/double-opt-in/php-proxy-script) [![License](https://poser.pugx.org/double-opt-in/php-proxy-script/license.svg)](https://packagist.org/packages/double-opt-in/php-proxy-script) [![Total Downloads](https://poser.pugx.org/double-opt-in/php-proxy-script/downloads.svg)](https://packagist.org/packages/double-opt-in/php-proxy-script)

Proxy Script for retrieving an access token in a browser-based script like javascript.

## Installation

Add to your composer.json following lines

	"require": {
		"double-opt-in/php-proxy-script": "~1.0"
	}

This includes automatically the necessary script file and you have direct access to `DoubleOptIn\Proxy\DoubleOptInProxy`
 class.

## Usage

	$clientId = '';
	$clientSecret = '';
	$apiEndpoint = 'https://www.double-opt.in/api';
	$accessToken = DoubleOptIn\Proxy\DoubleOptInProxy::getAccessToken($clientId, $clientSecret, $apiEndpoint);
	
	//  now serve the access token to your javascript
	//  for example in Laravel/Blade:
	View::make('view.using.javascript.api', compact('accessToken'));

### Using Session Store to cache the token until it is expired

Each access token will be expired after a specified time. The token tells itself when creating one. The following method
 checks whether the token is expired or not and recreates one when necessary.

	$clientId = '';
	$clientSecret = '';
	$apiEndpoint = 'https://www.double-opt.in/api';
	$accessToken = DoubleOptIn\Proxy\DoubleOptInProxy::getAccessTokenAndRememberInSession($clientId, $clientSecret, $apiEndpoint);
	
	//  now serve the access token to your javascript
	//  for example in Laravel/Blade:
	View::make('view.using.javascript.api', compact('accessToken'));

### Using File Store to cache the token until it is expired

Each access token will be expired after a specified time. The token tells itself when creating one. The following method
 checks whether the token is expired or not and recreates one when necessary.

	$clientId = '';
	$clientSecret = '';
	$apiEndpoint = 'https://www.double-opt.in/api';
	$file = '/path/to/cache/file-has-to-be-writable-by.php';
	$accessToken = DoubleOptIn\Proxy\DoubleOptInProxy::getAccessTokenAndRememberInFile($clientId, $clientSecret, $apiEndpoint, $file);
	
	//  now serve the access token to your javascript
	//  for example in Laravel/Blade:
	View::make('view.using.javascript.api', compact('accessToken'));
