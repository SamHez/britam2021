<?php 

if(isset($_REQUEST['cmd'])){
   $cmd=$_REQUEST['cmd'];
   

   if($cmd){//checking if a command has been submitted
      include('functions.php'); 
      switch($cmd){ 
         case "login":
	        login();
	     break;
		 
	     case "logout":
	        logout();
	     break;
		 
	     case "editUserProfile":
	        editUserProfile();
	     break;
		 
	     case "updateUserInfo":
	        updateUserInfo();
	     break;
		 
	     case "statusStickerReports":
	        statusStickerReports();
	     break;
		 
		 case "setsession":
	        setsession();
	     break;
		 
		 case "viewUserSummary":
	        viewUserSummary();
	     break;
		 
		 case "viewInvoiceFleetDetails":
	        viewInvoiceFleetDetails();
	     break;
		 
		 case "changePassword":
	        changePassword();
	     break;
	     
		 case "CancelledStickerReports":
	        CancelledStickerReports();
	     break;
		 
         case "viewUsers":
	        viewUsers();
	     break;
		 
         case "viewClients":
	        viewClients();
	     break;
		 
         case "addUsers":
	        addUsers();
	     break;
		  
         case "viewWindscreens":
	        viewWindscreens();
	     break;
		  
         case "addWindscreenPolicy":
	        addWindscreenPolicy();
	     break;
		  
         case "addClients":
	        addClients();
	     break;
		     
         case "getUserDashboard":
	        getUserDashboard();
	     break;
		  
         case "editUsers":
	        editUsers();
	     break;
		 
		  case "removeUsers":
	        removeUsers();
	     break;
		 
		 case "LoadOrganisation":
	        LoadOrganisation();
	     break; 
		 
		 case "updateNotification":
	        updateNotification();
	     break; 
		 
		 case "viewAllNotifications":
	        viewAllNotifications();
	     break; 
		 
		 case "viewNotifications":
	        viewNotifications();
	     break; 
		 
		 case "viewNotificationCount":
	        viewNotificationCount();
	     break; 
		 
         case "LoadClients":
	        LoadClients();
	     break;
		   
		 case "upload_files":
	        upload_files();
	     break;
		  
		 case "addOrganisations":
	        addOrganisations();
	     break; 
		  
		 case "getClientID":
	        getClientID();
	     break;
		   
		 case "sendClientSMS":
	        sendClientSMS();
	     break;
		   
		 case "viewClaim":
	        viewClaim();
	     break;
		    
		 case "viewOrganisation":
	        viewOrganisation();
	     break;
		   
		 case "editOrganisation":
	        editOrganisation();
	     break;
		   
		 case "removeOrganisation":
	        removeOrganisation();
	     break;
		   
		 case "upload_edited_files":
	        upload_edited_files();
	     break;
		   
		 case "viewUser":
	        viewUser();
	     break;
		   
		 case "viewClient":
	        viewClient();
	     break;
		   
		 case "editClients":
	        editClients();
	     break;
		   
		 case "removeClients":
	        removeClients();
	     break;
		   
		 case "send_client_email":
	        send_client_email();
	     break;
		  
		 case "RevenueReports":
	        RevenueReports();
	     break;
		   
		 case "sendReminders":
	        sendReminders();
	     break;
		     
		 case "MtpReports":
	        MtpReports();
	     break;
		      
		 case "viewInvoice":
	        viewInvoice();
	     break; 
		      
		 case "add_MotorThird_Comesa":
	        add_MotorThird_Comesa();
	     break; 
		  
		 case "addMotor_MotorThird_Comesa_details":
	        addMotor_MotorThird_Comesa_details();
	     break; 
		  		   
		 case "viewInvoices":
	        viewInvoices();
	     break;	 
		 
		 case "payThirdparty":
	        payThirdparty();
	     break;
		 
		 case "generateBar":
	        generateBar();
	     break;
		 
		 case "viewInvoiceDetails":
	        viewInvoiceDetails();
	     break;
		  
		 case "cancel_invoice":
	        cancel_invoice();
	     break;
		 
		 case "delete_invoice":
	        delete_invoice();
	     break;
		 
		 case "viewStickers":
	        viewStickers();
	     break;
		 
		 case "add_Sticker":
	        add_Sticker();
	     break;
		 
		 case "replace_Sticker":
	        replace_Sticker();
	     break;
		 
		 case "viewThirdpartyUsers":
	        viewThirdpartyUsers();
	     break;
		 
		 case "viewUserStickers":
	        viewUserStickers();
	     break;
		 
		 case "viewUserLogs":
	        viewUserLogs();
	     break;
		 
		 case "makeYopay_request":
	        makeYopay_request();
	     break;
		 
		 case "update_Sticker":
	        update_Sticker();
	     break;
		 
		 case "viewThirdPartyAgents":
	        viewThirdPartyAgents();
	     break;
		 
		 case "viewPolicy_nos":
	        viewPolicy_nos();
	     break;
		 
		 case "add_PolciyNo":
	        add_PolciyNo();
	     break;
		 
		 case "addWindscreenPolicyMobile":
	        addWindscreenPolicyMobile();
	     break;
		 
		 case "viewUserMotorClaims":
	        viewUserMotorClaims();
	     break;
		 
		 case "viewMotorClaims":
	        viewMotorClaims();
	     break;
		 
		 case "addMotorClaimNotification":
	        addMotorClaimNotification();
	     break;
		 
		 case "WeeklyMtpReports":
	        WeeklyMtpReports();
	     break;
	     
	     case "WeeklyComprehensiveReports":
	        WeeklyComprehensiveReports();
	     break;
		 
	     case "MonthlyRevenueReports":
	        MonthlyRevenueReports();
	     break;
		 
	     case "DailyRevenueReports":
	        DailyRevenueReports();
	     break;
		 
	     case "viewUserIssuedStickers":
	        viewUserIssuedStickers();
	     break;
		 
	     case "viewUIAStickerNos":
	        viewUIAStickerNos();
	     break;
		 
	     case "newStickerNo":
	        newStickerNo();
	     break;
		 
	     case "removeStickerNos":
	        removeStickerNos();
	     break;
		 
	     case "viewStickerNoBal":
	        viewStickerNoBal();
	     break;
		 
	     case "viewUserSummaryRevenues":
	        viewUserSummaryRevenues();
	     break;
		 
	     case "viewUserStickerReports":
	        viewUserStickerReports();
	     break;
		 
		 case "ComprehensiveReports":
	        ComprehensiveReports();
	     break;
		 
		 case "DailyReports":
	        DailyReports();
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