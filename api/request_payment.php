<?php

require_once("paymentApi.php");
require_once("database.php");

$api = new MomoPaymentApi();
$db = new Database();

$referenceId = $api->referenceId;

$amount = $_GET['amount'];
$tel = $_GET['tel'];

$api->requestPayment($amount, $tel);

$results = $api->checkPaymentStatus($referenceId);

$data = json_decode($results, JSON_FORCE_OBJECT);
$data["referenceId"] = $referenceId;

if ($db->savePaymentTransaction($data) != 0) {
    echo json_encode([
        "status" => $data['status'],
        "referenceId" => $referenceId,
        "message" => "Payment Transaction Recorded"
    ]);
} else {
    echo json_encode([
        "status" => $data['status'],
        "referenceId" => $referenceId,
        "message" => "Payment Transaction NOT Recorded"
    ]);
}

