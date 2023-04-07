<?php

    $webhook_secret = 'GQX4XCQ9FGMNC4K7Q4GA3DE66KL6WEUE';
    
   $api_key = "VUGupggZc1QmUl7gec6zUZ1BxukQgSmB";
   
   // see https://telerivet.com/dashboard/api
   
$project_id = "PJ7fdf8389d2066c66";

require_once 'telerivet.php';
    
   if ($_POST['secret'] !== $webhook_secret)
    {
        header('HTTP/1.1 403 Forbidden');
        echo "Invalid webhook secret";
    }
    else 
    {
        if ($_POST['event'] == 'incoming_message')
        {
            $content = trim(strtolower($_POST['content']));
            $from_number = ($_POST['from_number']);
           // $phone_id = trim($_POST['phone_id']); 
           
            
             
include('../connect.php');

$api = new Telerivet_API($api_key);
    
$project = $api->initProjectById($project_id);

$myvalue = $content;
$arr = explode(' ',trim($myvalue));
$trigger = $arr[1];
$refNo = $arr[0];

 
	
if(isset($refNo,$from_number))
{
$from_number = substr_replace($from_number,"0",0,4);

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://xylem.clearbasics.ug/api/?cmd=getClientID&client_telephone=".$from_number,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",   
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache" 
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

$obj = json_decode($response);


$client_id = $obj->client_id; 

$status='approved';


$pdo = Database::connect();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $sql = "UPDATE motor_quotations SET status=? WHERE client_id=? AND quote_id=?";
                $q = $pdo->prepare($sql); 
                $q->execute(array($status,$client_id,$refNo));
                Database::disconnect();

}
 	  
						     
			$msg="[XYLEM Agency MGT]  Your quotation has been approved";
						  
			$contact = $project->sendMessage(array(
            'to_number' => $from_number,
            'content' => $msg
        ));
						   
  
		}
					   
	     
         
    }
    
    ?>