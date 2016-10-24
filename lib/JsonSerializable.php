<?php

namespace Reedpay;

// JsonSerializable only exists in PHP 5.4+. Stub out if it doesn't exist
if (interface_exists('\JsonSerializable', false)) {
	interface JsonSerializable extends \JsonSerializable
	{}
}
else {
	interface JsonSerializable
	{
		public function jsonSerialize();
	}
}
