<?php

namespace Reedpay;

class Notification extends ApiResource
{
	public static function query($id, $options = null)
	{
		return self::_query($id, $options);
	}

	public static function retireve($params = null, $options = null)
	{
		return self::_retrieve($params, $options);
	}
}