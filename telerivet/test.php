<?php

$from_number = '256782924742'; 
 
if(strlen($from_number) == 12)
{
$from_number;
}
else
{ 
$from_number = substr_replace($from_number,"256",0,1);   
}


echo $from_number;