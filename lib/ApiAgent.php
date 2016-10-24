<?php

namespace Reedpay;

class ApiAgent
{
	public $apiKey;

	private $_apiBase;

	public function __construct($apiKey = null, $apiBase = null)
	{
		$this->_apiKey = $apiKey;
		if (!$apiBase) {
			$apiBase = Reedpay::$apiBase;
		}
		$this->_apiBase = $apiBase;
	}

	private static function _encodeObjects($d, $is_post = false)
	{
		if ($d instanceof ApiResource) {
			return Utils\Util::utf8($d->id);
		}
		else if ($d === true && !$is_post) {
			return 'true';
		}
		else if ($d === false && !$is_post) {
			return 'false';
		}
		else if (is_array($d)) {
			$res =  array();
			foreach ($d as $k => $v) {
				$res[$k] = self::_encodeObjects($v, $is_post);
			}
			return $res;
		}
		else {
			return Utils\Util::utf8($d);
		}
	}

	public static function encode($arr, $prefix = null)
	{
		if (!is_array($arr)) {
			return $arr;
		}

		$r = array();
		foreach ($arr as $k => $v) {
			if (is_null($v)) {
				continue;
			}

			if ($prefix && $k && !is_int($k)) {
				$k = $prefix . "[" . $k . "]";
			}
			else if ($prefix) {
				$k = $prefix . "[]";
			}

			if (is_array($v)) {
				$r[] = self::encode($v, $k, true);
			}
			else {
				$r[] = urlencode($k) ."=". urlencode($v);
			}
		}
		return implode("&", $r);
	}

	public function request($method, $url, $params = null, $headers = null)
	{
		if (!$params) {
			$params = array();
		}
		if (!$headers) {
			$headers = array();
		}
		list($rbody, $rcode, $myApiKey) = $this->_requestRaw($method, $url, $params, $headers);
		$resp = $this->_interpretResponse($rbody, $rcode);
		return array($resp, $myApiKey);
	}

	public function handleApiError($rbody, $rcode, $resp)
	{
		if (!is_object($resp) || !isset($resp->error)) {
			$msg = "Invalid response object from API: $rbody "
						. "(HTTP response code was $rcode)";
			throw new Error\Api($msg, $rcode, $rbody, $resp);
		}

		$error = $resp->error;
		$msg = isset($error->message) ? $error->message : null;
		$param = isset($error->param) ? $error->param : null;
		$code = isset($error->code) ? $error->code : null;

		switch ($rcode) {
			case 400:
				if ($code == 'rate_limit') {
					throw Error\RateLimit(
						$msg, $param, $rcode, $rbody, $resp
					);
				}
				break;
			case 404:
				throw new Error\InvalidRequest(
					$msg, $param, $rcode, $rbody, $resp
				);
				break;
			case 401:
				throw new Error\Authentication(
					$msg, $rcode, $rbody, $resp
				);
				break;
			case 402:
				throw new Error\Channel(
					$msg, $code, $param, $rcode, $rbody, $resp
				);
				break;
			default:
				throw new Error\Api($msg, $rcode, $rbody, $resp);
				break;
		}
	}

	private function _requestRaw($method, $url, $params, $headers)
	{
		$myApiKey = $this->_apiKey;
		if (!$myApiKey) {
			$myApiKey = Reedpay::$apiKey;
		}

		if (!$myApiKey) {
			$msg = 'No API key provided. (HINT: set your API key using '
						. '"Reedpay::setApiKey(<API-KEY>)". You can generate API keys from '
						. 'Reedpay web interface. See https://xpay.reedsec.com/doc/api for '
						. 'details.';
			throw new Error\Authentication($msg);
		}

		$absUrl = $this->_apiBase . $url;
		$params = self::_encodeObjects($params, $method == 'post');
		$langVersion = phpversion();
		$uname = php_uname();
		$ua = array(
			'binding_version' => Reedpay::VERSION,
			'lang' => 'php',
			'lang_version' => $langVersion,
			'publisher' => 'reedsec',
			'uname' => $uname
		);
		$defaultHeaders = array(
			'X-Reedpay-Client-User-Agent' => json_encode($ua),
			'User-Agent' => 'Reedpay/v1 PhpBinding' . Reedpay::VERSION,
			'Authorization' => 'Bearer ' . $myApiKey
		);
		if (Reedpay::$apiVersion) {
			$defaultHeaders['Reedpay-Version'] = Reedpay::$apiVersion;
		}
		if ($method == 'post' || $method == 'put') {
			$defaultHeaders['Content-type'] = 'application/json;charset=UTF-8';
		}
		if ($method == 'put') {
			$defaultHeaders['X-HTTP-Method-Override'] = 'PUT';
		}
		$requestHeaders = Utils\Util::getRequestHeaders();
		if (isset($requestHeaders['Reedpay-Sdk-Version'])) {
			$defaultHeaders['Reedpay-Sdk-Version'] = $requestHeaders['Reedpay-Sdk-Version'];
		}

		$combinedHeaders = array_merge($defaultHeaders, $headers);

		$rawHeaders = array();

		foreach ($combinedHeaders as $header => $value) {
			$rawHeaders[] = $header . ': ' . $value;
		}

		list($rbody, $rcode) = $this->_curlRequest(
			$method,
			$absUrl,
			$rawHeaders,
			$params
		);
		return array($rbody, $rcode, $myApiKey);
	}

	private function _interpretResponse($rbody, $rcode)
	{
		try {
			$resp = json_decode($rbody);
		}
		catch (Exception $e) {
			$msg = "Invalid response body from API: $rbody "
						. "(HTTP response code was $rcode";
			throw new Error\Api($msg, $rcode, $rbody);
		}

		if ($rcode < 200 || $rcode >= 300) {
			$this->handleApiErro($rbody, $code, $resp);
		}
		return $resp;
	}

	private function _curlRequest($method, $absUrl, $headers, $params)
	{
		$curl = curl_init();
		$method = strtolower($method);
		$opts = array();
		$requestSignature = NULL;
		if ($method == 'get') {
			$opts[CURLOPT_HTTPGET] = 1;
			if (count($params) > 0) {
				$encoded = self::encode($params);
				$absUrl = "$absUrl?$encoded";
			}
		}
		else if ($method == 'post' || $method == 'put') {
			if ($method == 'post') {
				$opts[CURLOPT_POST] = 1;
			}
			else {
				$opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
			}
			$rawRequestBody = json_encode($params);
			$opts[CURLOPT_POSTFIELDS] = $rawRequestBody;
			if ($this->privateKey()) {
				$signResult = openssl_sign($rawRequestBody, $requestSignature, $this->privateKey(), 'sha1');
				if (!$signResult) {
					throw new Error\Api("Generate signature failed");
				}
			}
		}
		else if ($method == 'delete') {
			$opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			if (count($params) > 0) {
				$encoded = self::encode($params);
				$absUrl = "$absUrl?$encoded";
			}
			else {
				throw new Error\Api("Unrecognized method $method");
			}
		}

		if ($requestSignature) {
			$headers[] = 'Reedpay-Signature: ' . base64_encode($requestSignature);
		}

		$absUrl = Utils\Util::utf8($absUrl);
		$opts[CURLOPT_URL] = $absUrl;
		$opts[CURLOPT_RETURNTRANSFER] = true;
		$opts[CURLOPT_CONNECTTIMEOUT] = 30;
		$opts[CURLOPT_TIMEOUT] = 80;
		$opts[CURLOPT_HTTPHEADER] = $headers;
		if (!Reedpay::$verifySslCerts) {
			$opts[CURLOPT_SSL_VERIFYPEER] = false;
		}

		curl_setopt_array($curl, $opts);
		$rbody = curl_exec($curl);
		
		if (!defined('CURLE_SSL_CACERT_BADFILE')) {
			define('CURLE_SSL_CACERT_BADFILE', 77); // constant not defined in PHP
		}

		$errno = curl_errno($curl);
		if ($errno == CURLE_SSL_CACERT ||
				$errno == CURLE_SSL_PEER_CERTIFICATE ||
				$errno == CURLE_SSL_CACERT_BADFILE) {
			array_push(
				$headers,
				'X-Reedpay-Client-Info: {"ca":"using Reedpay-supplied CA bundle"}'
			);
			$cert = $this->caBundle();
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_CAINFO, $cert);
			$rbody = curl_exec($curl);
		}

		if ($rbody === false) {
			$errno = curl_errno($curl);
			$message = curl_error($curl);
			curl_close($curl);
			$this->handleCurlError($errno, $message);
		}

		$rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return array($rbody, $rcode);
	}

	public function handleCurlError($errno, $message)
	{
		$apiBase = Reedpay::$apiBase;
		switch ($errno) {
			case CURLE_COULDNT_CONNECT:
			case CURLE_COULDNT_RESOLVE_HOST:
			case CURLE_OPERATION_TIMEOUTED:
				$msg = "Could not connect to Reedpay ($apiBase). Please check your "
							. "internet connection and try again. If this problem persists, "
							. "you should check Reedpay's service status at "
							. "https://xpay.reedsec.com/status.";
				break;
			case CURLE_SSL_CACERT:
			case CURLE_SSL_PEER_CERTIFICATE:
				$msg = "Could not verify Reedpay's SSL certificate. Please make sure "
							. "that your network is not intercepting certificates. "
							. "(Try going to $apiBase in your browser.)";
				break;
			default:
				$msg = "Unexpected error communicating with Reedpay.";
		}

		$msg .= "\n\n(Network error [errno $errno]: $message";
		throw new Error\ApiConnection($msg);
	}

	private function caBundle()
	{
		return dirname(__FILE__) . '../data/ca-certificates.crt';
	}

	private function privateKey()
	{
		if (!Reedpay::$privateKey) {
			if (!Reedpay::$privateKeyPath) {
				return NULL;
			}
			if (!file_exists(Reedpay::$privateKeyPath)) {
				throw new Error\Api('Private key file not found at: ' . Reedpay::$privateKeyPath);
			}
			Reedpay::$privateKey = file_get_contents(Reedpay::$privateKeyPath);
		}
		return Reedpay::$privateKey;
	}

}