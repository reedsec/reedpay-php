<?php

namespace Reedpay;

abstract class ApiResource extends ReedpayObject
{
	private static $HEADERS_TO_PERSIST = array('Reedpay-Version' => true);

	public static function baseUrl()
	{
		return Reedpay::$apiBase;
	}

	public function refresh()
	{
		$agent = new ApiAgent($this->_opts->apiKey, static::baseUrl());
		$url = $this->instanceUrl();

		list($response, $this->_opts->apiKey) = $agent->request(
				'get',
				$url,
				$this->_retrieveOptions,
				$this->_opts->headers
			);
		$this->refreshFrom($response, $this->_opts);
		return $this;	
	}

	public static function className()
	{
		$class = get_called_class();
		// Useful for namespace: Foo\Payment
		if ($postfix = strrchr($class, '\\')) {
			$class = substr($postfix, 1);
		}
		// Useful for underscored 'namespaces': Foo_Payment
		if ($postfixFakeNamespaces = strrchr($class, '')) {
			$class = $postfixFakeNamespaces;
		}
		if (substr($class, 0, strlen('Reedpay')) == 'Reedpay') {
			$class = substr($class, strlen('Reedpay'));
		}
		$class = str_replace('_', '', $class);
		$name = urlencode($class);
		$name = strtolower($name);
		return $name;
	}

	public static function classUrl()
	{
		$base = static::className();
		return "/secapi/v1_1/${base}";
	}

	public function instanceUrl()
	{
		$id = $this['id'];
		$class = get_called_class();
		if ($id === null) {
			$message = "Could not determine which URL to request: "
						. "$class instance hash invalid ID: $id";
			throw new Error\InvalidRequest($message, null);
		}
		$id = Utils\Util::utf8($id);
		$base = static::classUrl();
		$extn = urlencode($id);
		return "$base/$extn";
	}

	private static function _validateParams($params = null)
	{
		if ($params && !is_array($params)) {
			$message = "You must pass an array as the first argument to Reedpay API "
						. "method calls.";
			throw new Error\Api($message);
		}
	}

	protected function _request($method, $url, $params = array(), $options = null)
	{
		$opts = $this->_opts->merge($options);
		return static::_staticRequest($method, $url, $params, $opts);
	}

	protected static function _staticRequest($method, $url, $params, $options)
	{
		$opts = Utils\RequestOptions::parse($options);
		$agent = new ApiAgent($opts->apiKey, static::baseUrl());
		list($response, $opts->apiKey) = $agent->request($method, $url, $params, $opts->headers);
		foreach ($opts->headers as $k => $v) {
			if (!array_key_exists($k, self::$HEADERS_TO_PERSIST)) {
				unset($opts->headers[$k]);
			}
		}
		return array($response, $opts);
	}

	protected static function _query($id, $options = null)
	{
		$opts = Utils\RequestOptions::parse($options);
		$instance = new static($id, $opts);
		$instance->refresh();
		return $instance;
	}

	protected static function _retrieve($params = null, $options = null)
	{
		self::_validateParams($params);
		$url = static::classUrl();

		list($response, $opts) = static::_staticRequest('get', $url, $params, $options);
		return Utils\Util::convertToReedpayObject($response, $opts);
	}

	protected static function _create($params = null, $options = null)
	{
		self::_validateParams($params);
		$url = static::classUrl();

		list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
		return Utils\Util::convertToReedpayObject($response, $opts);
	}

	protected static function _pay($params = null, $options = null)
	{
		self::_validateParams($params);
		$url = static::classUrl() . '/directdebit';

		list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
		return Utils\Util::convertToReedpayObject($response, $opts);
	}

	// protected function _save($options = null)
	// {
	// 	$params = $this->serializeParameters();
	// 	if (count($params) > 0) {
	// 		$url = $this->instanceUrl();
	// 		list($response, $opts) = $this->_request('put', $url, $params, $options);
	// 		$this->refreshFrom($response, $opts);
	// 	}
	// 	return $this;
	// }

	// protected function _delete($params = null, $options = null)
	// {
	// 	self::_validateParams($params);

	// 	$url = $this->instanceUrl();
	// 	list($response, $opts) = $this->_request('delete', $url, $params, $options);
	// 	$this->refreshFrom($response, $opts);
	// 	return $this;
	// }
}