<?php

namespace Reedpay;

class Transfer extends ApiResource
{
	public static function query($id, $options = null)
	{
		return self::_query($id, $options);
	}

	public static function history($params = null, $options = null)
	{
		return self::_history($params, $options);
	}

	public static function create($params = null, $options = null)
	{
		return self::_create($params, $options);
	}

	public function save($options = null)
	{
		return $this->_save($options);
	}
}