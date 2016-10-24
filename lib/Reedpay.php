<?php

namespace Reedpay;

class Reedpay
{
	public static $apiKey;

	// public static $apiBase = 'https://xpay.reedsec.com';
	public static $apiBase = 'http://localhost';

	public static $apiVersion = '0.0.1';

	public static $verifySslCerts = false;

	const VERSION = '0.0.1';

	public static $privateKeyPath;

	public static $privateKey;

	public static function getApiKey()
	{
		return self::$apiKey;
	}

	public static function setApiKey($apiKey)
	{
		self::$apiKey = $apiKey;
	}

	public static function getApiVersion()
	{
		return self::$apiVersion;
	}

	public static function setApiVersion($apiVersion)
	{
		self::$apiVersion = $apiVersion;
	}

	public static function getVerifySslCerts()
	{
		return self::$verifySslCerts;
	}

	public static function setVerifySslCerts($verify)
	{
		self::$verifySslCerts = $verify;
	}

	public static function getPrivateKeyPath()
	{
		return self::$privateKeyPath;
	}

	public static function setPrivateKeyPath($path)
	{
		self::$privateKeyPath = $path;
	}

	public static function getPrivateKey()
	{
		return self::$privateKey;
	}

	public static function setPrivateKey($key)
	{
		self::$privateKey = $key;
	}
}