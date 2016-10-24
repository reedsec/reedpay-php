<?php

namespace Reedpay\Utils;

use Reedpay\ReedpayObject;
use stdClass;

abstract class Util
{
	public static function isList($array)
	{
		if (!is_array($array)) {
			return false;
		}

		foreach (array_keys($array) as $k) {
			if (!is_numeric($k)) {
				return false;
			}
		}
		return true;
	}

	public static function convertReedpayObjectToArray($values, $keep_object = false)
	{
		$results = array();
		foreach ($values as $k => $v) {
			// FIXME: this is an encapsulation violation
			if ($k[0] == '_') {
				continue;
			}
			if ($v instanceof ReedpayObject) {
				$results[$k] = $keep_object ? $v->__toStdObject(true) : $v->__toArray(true);
			}
			else if (is_array($v)) {
				$results[$k] = self::convertReedpayObjectToArray($v, $keep_object);
			}
			else {
				$results[$k] = $v;
			}
		}
		return $results;
	}

	public static function convertReedpayObjectToStdObject($values)
	{
		$results = new stdClass;
		foreach ($values as $k => $v) {
			// FIXME: this is an encapsulation violation
			if ($k[0] == '_') {
				continue;
			}

			if ($v instanceof ReedpayObject) {
				$results->$k = $v->__toStdObject(true);
			}
			else if (is_array($v)) {
				$results->$k = self::convertReedpayObjectToArray($v, true);
			}
			else {
				$results->$k = $v;
			}
		}
		return $results;
	}

	public static function convertToReedpayObject($resp, $opts)
	{
		$types = array(
			'payment' => 'Reedpay\\Payment',
			'refund' => 'Reedpay\\Refund',
			'transfer' => 'Reedpay\\Transfer',
			'redpack' => 'Reedpay\\Redpack',
			'notification' => 'Reedpay\\Notification'
		);

		if (self::isList($resp)) {
			$mapped = array();
			foreach ($resp as $i) {
				array_push($mapped, self::convertToReedpayObject($i, $opts));
			}
			return $mapped;
		}
		else if (is_object($resp)) {
			if (isset($resp->object)
					&& is_string($resp->object)
					&& isset($types[$resp->object])) {
				$class = $types[$resp->object];
			}
			else {
				$class = 'Reedpay\\ReedpayObject';
			}
			return $class::constructFrom($resp, $opts);
		}
		else {
			return $resp;
		}
	}

	public static function getRequestHeaders()
	{
		if (function_exists('getallheaders')) {
			$headers = array();
			foreach (getallheaders() as $name => $value) {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))))] = $value;
			}
			return $headers;
		}
		$headers = array();
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}

	public static function utf8($value)
	{
		if (is_string($value)
				&& mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8") {
			return utf8_encode($value);
		}
		else {
			return $value;
		}
	}
}