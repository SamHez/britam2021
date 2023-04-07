<?php

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


/*function getCbVehicleNumber($vehicleNumber)
{

  
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://britam.clearbasics.ug/ussd/call_functions.php?cmd=getCbVehicleNumber&vehicleNumber='$vehicleNumber'",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "postman-token: 33a814ad-2ddb-f743-b76c-d0362168a95b"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  return "cURL Error #:" . $err;
} else {
  return $response;
}
 
}
*/

function getCbVehicleNumber()
{

try {
	$con = cbDatabaseConnection();

	//$json = [];

	if (isset($_GET['vehicleNumber'])) {
	    
		$vehicleNumber = addslashes($_GET['vehicleNumber']);
		
		$sel = $con->query("SELECT * FROM vehicles WHERE regno = '$vehicleNumber'");

		if ($sel->rowCount() > 0) { 
		    while($row = $sel->fetch()){ 
				$json['id'] = $row['_id'];
				$json['regno'] = $row['regno'];
				$json['power'] = $row['power'];
				$json['gross_weight'] = $row['gross_weight'];
				$json['netweight'] = $row['netweight'];
				$json['chasis_no'] = $row['chasis_no'];
				$json['make_name'] = $row['make_name'];
				$json['plate_category'] = $row['plate_category'];
				$json['purpose'] = $row['purpose'];
				$json['ownertin'] = $row['ownertin'];
				$json['ownername'] = $row['ownername'];
				$json['year_of_manufacture'] = $row['year_of_manufacture'];
				$json['owner_mobile_no'] = $row['owner_mobile_no'];
				$json['mvtype'] = $row['mvtype'];
				$json['vehicle_no_seats'] = $row['vehicle_no_seats']; 
		    }
		} else {

			$json= "Vehicle Not Registered";
		}
	} else {
		$json = "missing";
	}
	
	return $json;
	
} catch (PDOException $e) {
		echo "Error: " . $e->getMessage();
	}
}


?>