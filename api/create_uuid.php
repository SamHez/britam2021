<?php

require_once("paymentApi.php");

$api = new MomoPaymentApi();

echo $api->generateUUID();
