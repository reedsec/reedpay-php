<?php

namespace Reedpay;

abstract class SingletonApiResource extends ApiResource
{
	protected static function _singletonRetrieve($options = null)
	{
		$opts = Utils\RequestOptions::parse($options);
		$instance = new static(null, $opts);
		$instance->refresh();
		return $instance;
	}

	public static function classUrl()
	{
		$base = static::className();
		return "/secapi/v1/${base}";
	}

	public function instanceUrl()
	{
		return static::classUrl();
	}
}