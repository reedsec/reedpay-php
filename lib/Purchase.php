<?php

namespace Reedpay;

class Payment extends ApiResource
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

	public static function pay($params = null, $options = null)
	{
		return self::_pay($params, $options);
	}

	// public function save($options = null)
	// {
	// 	return $this->_save($options);
	// }
}