<?php

namespace Reedpay;

class Refund extends ApiResource
{
	public function instanceUrl()
	{
		$id = $this['id'];
		$payment = $this['payment'];
		if (!$id) {
			throw new Error\InvalidParameter(
				"Could not determin which URL to request: "
				. " class instance has invalid ID: $id",
				null
			);
		}
		$id = Utils\Util::utf8($id);
		$payment = Utils\Util::utf8($payment);

		$base = Payment::classUrl();
		$paymentExtn = urlencode($payment);
		$extn = urlencode($id);
		return "$base/$paymentExtn/refund/$extn";
	}

	public function save($opts = null)
	{
		return $this->_save($opts);
	}
}