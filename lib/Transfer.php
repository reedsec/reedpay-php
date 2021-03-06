<?php

namespace Reedpay;

class Transfer extends ApiResource
{
	public static function query($id, $options = null)
	{
		return self::_query($id, $options);
	}

	public static function retrieve($params = null, $options = null)
	{
		return self::_retrieve($params, $options);
	}

	public static function create($params = null, $options = null)
	{
		return self::_create($params, $options);
	}

	// public function save($options = null)
	// {
	// 	return $this->_save($options);
	// }
}