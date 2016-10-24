<?php

require dirname(__FILE__) . '/../init.php';

$api_key = '1234';

$app_id = 'app_57ede786d4d7a81549645e56';

// $channel = 'wx_native';
$channel = 'ali_qr_offline';
// $channel = 'ali_qr';
$amount = 1;

\Reedpay\Reedpay::setPrivateKeyPath(__DIR__ . '/reedpay_private_key.pem');

$channel_extra = array();
switch ($channel) {
	case 'ali_web':
		$channel_extra = array(
			'show_url' => 'http://example.com/success',
			'return_url' => 'http://example.com/return'
		);
		break;
	case 'ali_qr_offline':
		$channel_extra = array(
			'code' => '286991484838699143',
		);
		break;
}

\Reedpay\Reedpay::setApiKey($api_key);
try {
	$payment = \Reedpay\Payment::create(
		array(
			'subject' => '支付测试测试测',
			'body' => '支付测试',
			'amount' => $amount,
			'mch_order_no' => '20161020-006',
			'currency' => 'cny',
			'channel_extra' => $channel_extra,
			'channel' => $channel,
			// 'client_ip' => $_SERVER['REMOTE_ADDR'],
			'client_ip' => '127.0.0.1',
			// 'app' => array('id' => $app_id)
			'app_id' => $app_id
		)
	);
}
catch (\Reedpay\Error\Base $e) {
	if ($e->getHttpStatus() != NULL) {
		// header('Status: ' . $e->getHttpStatus());
		echo 'In example payment: ' . $e->getHttpBody();
	}
	else {
		echo 'Error example payment: ' . $e->getMessage();
	}
}