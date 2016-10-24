<?php

if (!function_exists('curl_init')) {
	throw new Exception('Reedpay needs CURL PHP extension.');
}

if (!function_exists('json_decode')) {
	throw new Exception('Reedpay needs JSON PHP extension.');
}

if (!function_exists('mb_detect_encoding')) {
	throw new Exception('Reedpay needs Multibyte String PHP extension.');
}

// Reedpay singleton
require(dirname(__FILE__) . '/lib/Reedpay.php');

// Utilities
require(dirname(__FILE__) . '/lib/Utils/Util.php');
require(dirname(__FILE__) . '/lib/Utils/Set.php');
require(dirname(__FILE__) . '/lib/Utils/RequestOptions.php');

// Errors
require(dirname(__FILE__) . '/lib/Error/Base.php');
require(dirname(__FILE__) . '/lib/Error/Api.php');
require(dirname(__FILE__) . '/lib/Error/ApiConnection.php');
require(dirname(__FILE__) . '/lib/Error/Authentication.php');
require(dirname(__FILE__) . '/lib/Error/InvalidRequest.php');
require(dirname(__FILE__) . '/lib/Error/RateLimit.php');
require(dirname(__FILE__) . '/lib/Error/Channel.php');

// Plumbing
require(dirname(__FILE__) . '/lib/JsonSerializable.php');
require(dirname(__FILE__) . '/lib/ReedpayObject.php');
require(dirname(__FILE__) . '/lib/ApiAgent.php');
require(dirname(__FILE__) . '/lib/ApiResource.php');
require(dirname(__FILE__) . '/lib/SingletonApiResource.php');
require(dirname(__FILE__) . '/lib/AttachedObject.php');
require(dirname(__FILE__) . '/lib/Collection.php');

// Reedpay API Resources
require(dirname(__FILE__) . '/lib/Payment.php');
require(dirname(__FILE__) . '/lib/Refund.php');
require(dirname(__FILE__) . '/lib/Transfer.php');
require(dirname(__FILE__) . '/lib/Redpack.php');
require(dirname(__FILE__) . '/lib/Notification.php');
