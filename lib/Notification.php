<?php

namespace Reedpay;

class Notification extends ApiResource
{
	public static function query($id, $options = null)
	{
		return self::_query($id, $options);
	}

	public static function history($params = null, $options = null)
	{
		return self::_history($params, $options);
	}
}