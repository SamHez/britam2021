<?php

// include database settings
include_once "functions.php";

//Make sure that this is a POST request.
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
	//If it isn't, send back a 405 Method Not Allowed header.
	header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed", true, 405);
	exit;
}


//Get the raw POST data from PHP's input stream.
//This raw data should contain XML.
$postData = trim(file_get_contents('php://input'));


//Use internal errors for better error handling.
libxml_use_internal_errors(true);


//Parse the POST data as XML.
$xml = simplexml_load_string($postData);


//If the XML could not be parsed properly.
if ($xml === false) {
	//Send a 400 Bad Request error.
	header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request", true, 400);
	//Print out details about the error and kill the script.
	foreach (libxml_get_errors() as $xmlError) {
		echo $xmlError->message . "\n";
	}
	exit;
}

// get url
$url = $_SERVER['REQUEST_URI'];

if (preg_match("/\/getinformation$/", strtolower(trim($url, " /"))) || preg_match("/\/getfinancialresourceinformation$/", strtolower(trim($url, " /")))) { // check if the url ends with getinformation, convert it to lowercase, trim spaces and slashes

	// extract Vehicle number
	preg_match('/:(.*?)\@/s', $xml->resource, $value);
	$vehicleNumber = $value[1];


	// $vehicleNumber = "UDG 435T"; //Britam Test V-Number
	// $vehicleNumber = "YOGIRAJ"; // Clear Basics Test V-Number

	//@TODO -> query the database to get the details by subscriber number or vehicle number and pass amount to the $amount variable

	//check if vehicle is present in britam db
	$brittVehicle = getBritVehicleNumber($vehicleNumber);

	if (is_array($brittVehicle) && !empty($brittVehicle)) {
		$vehicle = $brittVehicle;
	} else {
		// else check if vehicle is present in clear basics vehicle db
		$vehicle = getCbVehicleNumber($vehicleNumber);
	}

	if (is_string($vehicle) && $vehicle === "Vehicle Not Registered") {

		$resp = <<<RESP
        <ns0:getfinancialresourceinformationresponse xmlns:ns0="http://www.ericsson.com/em/emm/serviceprovider/v1_0/backend/client">
            <message>Please visit any nearby Britam or MTN agent for your vehicle registration.</message>
            <extension>
                <status>102</status>
            </extension>
        </getfinancialresourceinformationresponse>
RESP;

	} elseif (is_array($vehicle) && !empty($vehicle)) {
	
		//compute for amount
		$amount = computeForVehiclePayment($vehicle);

		$resp = <<<RESP
        <ns0:getfinancialresourceinformationresponse xmlns:ns0="http://www.ericsson.com/em/emm/serviceprovider/v1_0/backend/client">
            <message> Y’ello, you are paying Britam third party Insurance for $vehicleNumber, total cost $amount.</message>
            <extension>
                <status>101</status>
            </extension>
        </getfinancialresourceinformationresponse>
RESP;

	}

} elseif (preg_match("/\/payment$/", strtolower(trim($url, " /")))) { // check if the url ends with payment, convert it to lowercase, trim spaces and slashes

	// extract Vehicle number
	preg_match('/:(.*?)\@/s', $xml->receivingfri, $value);
	$vehicleNumber = $value[1];

	// extract accountholderid / mobile number
	preg_match('/:(.*?)\//s', $xml->accountholderid, $value);
	$accountholderid = $value[1];

	$transactionId = $xml->transactionid;
	$amount = $xml->amount->amount;
	$currency = $xml->amount->currency;
	$message = $xml->message;

	$status = "PENDING"; // transaction status

	if (empty($message) || is_null($message) || $message == "") {
		$message = "Y'ello, your insurance purchase of " . $amount . " " . $currency . " to Britam was successful";
	}


	$transactionDetails = array(
		'vehicleNumber' => $vehicleNumber, 
		'accountholderid' => $accountholderid, 
		'transactionId' => $transactionId, 
		'amount' => $amount, 
		'currency' => $currency,  
		'status' => $status
	);

	// store transaction details in the database;
	saveTransactionDetails($transactionDetails);

	$resp = <<<RESP
        <ns0:paymentresponse xmlns:ns4="http://www.ericsson.com/em/emm/serviceprovider/v1_0/backend/client">
            <providertransactionid>$transactionId</providertransactionid>
            <message>$message</message>
            <status>$status</status>
        </ns4:paymentresponse>
RESP;

} else {
	//Send a 400 Bad Request error.
	header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request", true, 400);

	//Print out details about the error and kill the script.
	echo "invlalid api resource \n";
	exit;
}

echo $resp;
