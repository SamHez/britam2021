<?php

require_once "telerivet.php";
   
$API_KEY = '944wg_AAQIeQJ5P2LxXct10474n1xrrfLhTW';           // from https://telerivet.com/api/keys
$PROJECT_ID = 'PJ7fdf8389d2066c66';

$telerivet = new Telerivet_API($API_KEY);

$project = $telerivet->initProjectById($PROJECT_ID);

// Send a SMS message
$project->sendMessage(array(
    'to_number' => '256782924742',
    'content' => 'Testing sms'
));   

 
?>