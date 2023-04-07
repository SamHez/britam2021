<?php

require_once "paymentApi.php";
require_once "database.php";

$api = new MomoPaymentApi();
$db = new Database();

if (isset($_GET['referenceId']) && !is_null($_GET['referenceId'])) {

    $paymentDetails = $api->checkPaymentStatus($_GET['referenceId']);

    echo json_encode([
        "paymentDetails" => $paymentDetails,
        "referenceId" => $_GET['referenceId'],
        "message" => "Payment Transaction Information",
    ]);
}
else {
    echo json_encode([
        "status" => "error",
        "message" => "Please provide a referenceId",
    ]);
}