<?php 

if(isset($_REQUEST['cmd'])){
   $cmd=$_REQUEST['cmd'];
   

   if($cmd){//checking if a command has been submitted
        include('api.php'); 
        switch($cmd){ 
            case "getCbVehicleNumber":
	            echo json_encode(getCbVehicleNumber());
	            break;
		  
		 
            default:
	            $json=array();
	            $json['status']="invalid request";
	            echo json_encode($json);
	            break;
	  }
  }else{//if no command was submitted return error message
     $json=array();
	 $ary=array("status"=>"invalid request");
	 $json['result'][]=$ary;
	 echo json_encode($json);
   } 
}else{// if no command was submitted return error message
    $json=array();
	 $ary=array("status"=>"invalid request");
	 $json['result'][]=$ary;
	 echo json_encode($json);
}
?>