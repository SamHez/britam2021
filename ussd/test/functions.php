<?php
/*
function cbDatabaseConnection()
{
	$servername = "localhost";
	$databasename = "clearbasics_vehicle_db";
	$username = "clearbas_root";
	$password = "Skycode@2018";

	try {
		$conn = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	} catch (PDOException $e) {
		echo "Connection failed: " . $e->getMessage();
	}
}
*/

function britDatabaseConnection()
{
	$servername = "localhost";
	$databasename = "clearbas_britam";
	$username = "clearbas_root";
	$password = "Skycode@2018";

	try {
		$conn = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	} catch (PDOException $e) {
		echo "Connection failed: " . $e->getMessage();
	}
}

function getBritVehicleNumber($vehicleNumber)
{
	try {
		$conn = britDatabaseConnection();

		$sql = "SELECT * FROM motor_invoice_details WHERE vehicle_plate_no = '$vehicleNumber'";

		$results = $conn->query($sql);

		if ($results->rowCount() > 0) {
			return $results->fetch();
		} else {
			return "Vehicle Not Registered";
		}
	} catch (PDOException $e) {
		return "Error: " . $e->getMessage();
	}
}

function getCbVehicleNumber($vehicleNumber)
{
   
$curl = curl_init(); 
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://britam.clearbasics.ug/ussd/call_functions.php?cmd=getCbVehicleNumber&vehicleNumber=".$vehicleNumber,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache" 
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  return "cURL Error #:" . $err;
} else {
  return json_decode($response, true);
}  
}

function computeForVehiclePayment($vehicleDetails)
{
    
    // check for vehicle basic premium
    if (isset($vehicleDetails['basic_premium']) && !empty($vehicleDetails['basic_premium']) && !is_null($vehicleDetails['basic_premium'])) {
        $bp = $vehicleDetails['basic_premium'];
	}
	else {
    	// get car type and filter
    	$carTypeAndFilter = getCarTypeAndFilter($vehicleDetails);
    
    	$carType = $carTypeAndFilter['carType'];
    	$filter = $carTypeAndFilter['filter'];
    
    	$bp = calcBasicPremium($carType, $filter);
	}
	
	$tl = (1.5 / 100) * $bp;
	$sd = 35000;
	$sf = 6000;

	$VAT = ($bp + $tl + $sf) * 0.18;

	$tp = $VAT + $sf + $sd + $tl + $bp; 
	
	$amount = (int) $tp;
	
	return number_format($amount);
}

function calcBasicPremium($carType, $filter)
{
	$bp = null;

	switch ($carType) {
		
		case 'private':
			if ($filter <= 5) {
				$bp = 21910;
			} elseif ($filter > 5) {
				$bp = (int) round((($filter - 5) * 1000) + 21910);
			}
			break;
		case 'commercial':
			if ($filter <= 1500) {
				$bp = 18260;
			} elseif ($filter >= 1501 && $filter <= 3000) {
				$bp = 27390;
			} elseif ($filter >= 3001 && $filter <= 6000) {
				$bp = 40780;
			} elseif ($filter >= 6001 && $filter <= 10000) {
				$bp = 54780;
			} elseif ($filter > 10000) {
				$bp = (int) round(((($filter - 10000) / 10000) * 2390) + 54780);
			}
			break;
		case 'motorbike':
			if ($filter <= 50) {
				$bp = 3260;
			} elseif ($filter >= 51 && $filter <= 150) {
				$bp = 3915;
			} elseif ($filter >= 151 && $filter <= 300) {
				$bp = 6520;
			} elseif ($filter >= 301 && $filter <= 500) {
				$bp = 9785;
			} elseif ($filter > 500) {
				$bp = 13045;
			}
			break;
	}

	return (int) round($bp);
}

function getCarTypeAndFilter($vehicleDetails)
{
	$carType = $filter = null;

	// check vehicle type in britm db
	if (isset($vehicleDetails['vehicle_category']) && strtolower($vehicleDetails['vehicle_category']) == "motorcycles") {

		//motorbike
		$carType = strtolower("motorbike");
		$filter = $vehicleDetails['vehicle_cc'];

	} elseif (isset($vehicleDetails['vehicle_category']) && strtolower($vehicleDetails['vehicle_category']) == "private") {

		//private
		$carType = strtolower("private");
		$filter = $vehicleDetails['vehicle_no_seats'];

	} elseif (isset($vehicleDetails['vehicle_category']) && strtolower($vehicleDetails['vehicle_category']) == "commercial") {

		//commercial
		$carType = strtolower("commercial");
		$filter = $vehicleDetails['gross_weight'];

	}

	// check vehicle type in clear basic vehicle db
	if (isset($vehicleDetails['purpose']) && strtolower($vehicleDetails['purpose']) == "motorcycles") {

		//motorbike
		$carType = strtolower("motorbike");
		$filter = $vehicleDetails['power'];

	} elseif (isset($vehicleDetails['purpose']) && strtolower($vehicleDetails['purpose']) == "private") {

		//private
		$carType = strtolower("private");
		$filter = $vehicleDetails['vehicle_no_seats'];

	} elseif (isset($vehicleDetails['purpose']) && strtolower($vehicleDetails['purpose']) == "commvhcl") {

		//commercial
		$carType = strtolower("commercial");
		$filter = $vehicleDetails['gross_weight'];
		
	}


	return array(
		'carType' => $carType,
		'filter' => $filter,
	);
}

function saveTransactionDetails($details) {
	$conn = britDatabaseConnection();

	// insert record into transaction table in the brittam database
	$sql = "INSERT INTO new_payment_transactions (`vehicleNumber`, `accountholderid`, `transactionId`, `amount`, `currency`, `status`) VALUES ('".$details['vehicleNumber']."', '".$details['accountholderid']."', '".$details['transactionId']."', '".$details['amount']."', '".$details['currency']."', '".$details['status']."')";

	$stmt = $conn->prepare($sql);
	$stmt->execute();

	// return the insertedId
	$insertedId = $conn->lastInsertId();

	return $insertedId;
}

function updatePendingTransactions() {

	try {
		$conn = britDatabaseConnection();

		$sql = "SELECT * FROM new_payment_transactions WHERE status = 'PENDING'";

		$results = $conn->query($sql);

		if ($results->rowCount() > 0) {
			$transactions = $results->fetchAll();
		} else {
			$transactions = 0;
		}
	} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}
	
	if ($transactions > 0) {
		foreach ($transactions as $transaction) {

			$transactionId = $transaction["transactionId"];
			$providertransactionid = $transaction["accountholderid"];
			$amount = $transaction["amount"];
			$currency = $transaction["currency"];
			$status = $transaction["status"];
			
			echo "$transactionId";

			$xmlRequestData = <<<RESP
				<?xml version="1.0" encoding="UTF-8"?>
				<ns0:paymentcompletedrequest xmlns:ns0="http://www.ericsson.com/em/emm/serviceprovider/v1_1/backend">
						<transactionid>$transactionId</transactionid>
						<providertransactionid>$providertransactionid</providertransactionid>
						<newbalance>
							<amount>$amount</amount>
							<currency>$currency</currency>
						</newbalance>
						<status>$status</status>
				</ns0:paymentcompletedrequest>
RESP;
	
			$url = "https://10.156.145.219:8017/poextvip/v1_1/paymentcompleted";
			$user = "tnsubura";
			$password = "T0nyCB@s!c5";

			$curlHandler = curl_init();

			curl_setopt_array($curlHandler, [
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => $xmlRequestData,
					CURLOPT_HTTPHEADER,    array(
						'Content-Type: text/plain'
					),
					CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
					CURLOPT_USERPWD => "$user:$password",
			]);

			$response = curl_exec($curlHandler);
			$error = curl_error($curlHandler);

			curl_close($curlHandler);

			if ($error) {
				echo "cURL Error #:" . $error;
			} else {
				
				echo $response;

				// if ($response) {
				// 	try {

				// 		$status = $response['status'];

				// 		$conn = britDatabaseConnection();
				
				// 		$sql = "UPDATE new_payment_transactions SET status = '$status' WHERE transactionId = '$transactionId' AND status = 'PENDING'";
				
				// 		$stmt = $conn->prepare($sql);
	
				// 		$stmt->execute();
	
				// 		if ($stmt->rowCount() > 0) {
				// 			echo $response;
				// 		} else {
				// 			echo "Failed";
				// 		}
				// 	} catch (PDOException $e) {
				// 		echo "Error: " . $e->getMessage();
				// 	}
				// }
			}

		}
	}

}