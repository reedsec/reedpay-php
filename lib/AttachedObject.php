<?php

namespace Reedpay;

class AttachedObject extends ReedpayObject
{
	public function replaceWith($properties)
	{
		$removed = array_diff(array_keys($this->_values), array_keys($properties));
		foreach ($removed as $k) {
			$this->$k = null;
		}

		foreach ($properties as $k => $v) {
			$this->$k = $v;
		}
	}
}