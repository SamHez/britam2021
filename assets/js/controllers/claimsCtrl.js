'use strict';
/** 
  * controller for claimsCtrl.js Example
*/

app.directive('fileModel', ['$parse', function ($parse) {
    return {
    restrict: 'A',
    link: function(scope, element, attrs) {
        var model = $parse(attrs.fileModel);
        var modelSetter = model.assign;

        element.bind('change', function(){
            scope.$apply(function(){
                modelSetter(scope, element[0].files[0]);
            });
        });
    }
   };
}]);

// We can write our own fileUpload service to reuse it in the controller
app.service('fileUpload', ['$http', function ($http) {
    this.uploadFileToUrl = function(file, uploadUrl){
         var fd = new FormData();
         fd.append('file', file);
         $http.post(uploadUrl, fd, {
             transformRequest: angular.identity,
             headers: {'Content-Type': undefined,'Process-Data': false}
         })
         .success(function(){
            console.log("Success");
         })
         .error(function(){
            console.log("Success");
         });
     }
 }]);
 
app.controller('claimsCtrl', ['$scope','$rootScope', '$http', '$filter', '$timeout','$stateParams','$state','$modal','$interval','toaster','SweetAlert','fileUpload','ngTableParams','Data', function ($scope, $rootScope, $http, $filter, $timeout,$stateParams,$state,$modal,$interval,toaster,SweetAlert,fileUpload,ngTableParams,Data) {
    
	
	var data=[];
	
	$scope.loadClaims = function()
	{
	
	try
	 {
		$http.get("api/?cmd=viewMotorClaims")
		.success(function(res)
		{
			data=res.results;
			
								
			$scope.tableParams = new ngTableParams({
				page: 1,
				count: 10
			}, {
				total: data.length,
				getData: function ($defer, params) {
					var orderedData = params.sorting() ? $filter('orderBy')(data, params.orderBy()) : data;
					$defer.resolve(orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count()));
				}
			});

			console.log('users results ',data);
			 

		});
		} 		
		catch(err) {
			
			Data.toast({status:"error",message:err.message});
		 // err.message;
		}
	}
	
	$scope.loadClaims();
	
	$scope.uploadFile1=function(id)
	{ 
	
	 var file = $scope.myFile;
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_files&name=claims&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl); 
	
	}
	$scope.uploadFile2=function(id)
	{ 
	
	 var file = $scope.myFile2;
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_files&name=claims&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl); 
	
	}
	$scope.uploadFile3=function(id)
	{ 
	
	 var file = $scope.myFile3;
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_files&name=claims&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl); 
	
	}
	 
	
	$scope.addClaimNotify = function(d)
	{ 
	
	 var claimant_name =d.claimant_name;
	 var date_reported =d.date_reported;
	 var claimant_email =d.claimant_email;
	 var claimant_telephone =d.claimant_telephone;
	 var number_plate =d.number_plate;
	 var sticker_no =d.sticker_no;
	 var policy_type =d.policy_type;
	 var date_of_incident =d.date_of_incident;
	 var details_of_incident =d.details_of_incident;
	 var claim_estimate =d.claim_estimate;
	 
		//console.log('$scope.monthly_reports ',row);
	 if(typeof claimant_name ==='undefined')
		{
			claimant_name ='';
		}
	 if(typeof date_reported ==='undefined')
		{
			date_reported ='';
		}
	 if(typeof claimant_email ==='undefined')
		{
			claimant_email ='';
		}
	 if(typeof claimant_telephone ==='undefined')
		{
			claimant_telephone ='';
		}
	 if(typeof number_plate ==='undefined')
		{
			number_plate ='';
		}
	 if(typeof sticker_no ==='undefined')
		{
			sticker_no ='';
		}
	 if(typeof policy_type ==='undefined')
		{
			policy_type ='';
		}
	 if(typeof date_of_incident ==='undefined')
		{
			date_of_incident ='';
		}
	 if(typeof details_of_incident ==='undefined')
		{
			details_of_incident ='';
		}
	 if(typeof claim_estimate ==='undefined')
		{
			claim_estimate ='';
		}
	
	
		$http.post('api/?cmd=addMotorClaimNotification&claimant_name='+claimant_name+'&date_reported='+date_reported+'&claimant_email='+claimant_email+'&claimant_telephone='+claimant_telephone+'&number_plate='+number_plate+'&sticker_no='+sticker_no+'&policy_type='+policy_type+'&date_of_incident='+date_of_incident+'&details_of_incident='+details_of_incident+'&claim_estimate='+claim_estimate+'&user_id='+$rootScope.uid)
		.success(function(res)
		{
			if(res.status=='ok')
			{
				toaster.pop('success', 'Claim Notification Successful', res.message);
				$scope.uploadFile1(res.claim_ref);
				$scope.uploadFile2(res.claim_ref);
				$scope.uploadFile3(res.claim_ref);
				// $state.go('app.loadclaimnotification', {claim_reference:res.claim_ref});
			}
			else if(res.status=='missing')
			{
				toaster.pop('error', 'Claim Notification Error', res.message);
			}
			else
			{
				toaster.pop('error', 'Claim Notification Error', res.message);
			}
			
			console.log('out ',res);
			
		})
		.finally(function()
		{
			$scope.data="";
		});
		
		
	}
	
	
  var vm = this;

   $scope.claim_reference =$stateParams.claim_reference;
   
   
    $scope.claimNoticationDetails ={};
   
	 $scope.claimNoticationDetails = function(claim_reference)
	 {
	 $http.get('api/?cmd=viewClaim&claim_ref='+claim_reference)
     .success(function(res)
     {
        $scope.claimNoticationDetails = res.results; 
		console.log(res); 
	});
	 
	 }
	 
	$scope.claimNoticationDetails($scope.claim_reference);
   
	$scope.organisation=[];
	
	$http.get("api/?cmd=LoadOrganisation&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(data)
		{
			$scope.organisation=data.results;

			console.log('organ results ',$scope.organisation);
			 

		});
		
	
		
		 
		
	$scope.clients=[];
	
	$http.get("api/?cmd=LoadClients")
		.success(function(data)
		{
			$scope.clients=data.results; 

		});
		
		$scope.policies=[];

		$scope.LoadPolicies=function(id){ 
	
		$http.get("api/?cmd=viewOrganPolicies&organ_id="+id)
		.success(function(data)
		{
			$scope.policies=data.results; 
			// console.log($scope.policies); 

		});

		}	
 
	
  $scope.deleteUser=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this User!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=removeUsers&user_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'User details successfully removed', 10000, 'trustedHtml'); 	
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'User details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'User details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Deleted!", "User details has been deleted.", "success");
            } else {
                SweetAlert.swal("Cancelled", "User details is safe :)", "error");
            }
        });
		
	
	}
		
	
}]);

app.controller('userCont', ['$scope','$rootScope','$modalInstance','$http','user_id','toaster','fileUpload',function ($scope,$rootScope, $modalInstance,$http,user_id,toaster,fileUpload) {
           
 
	$scope.uploadUser=function(id)
	{ 
	
	 var file = $scope.myFile;
	 var folder = 'users';
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_edited_files&folder_name='+folder+'&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	  
	}
	
	$scope.userStickers=[];
	
	$scope.loadUserStickers = function(user_id)
	{
	$http.get('api/?cmd=viewUserStickers&user_id='+user_id)
		.success(function(data)
		{
			$scope.userStickers=data.results;
		});
	}
	
	$scope.loadUserStickers(user_id);
	
	$scope.uploadUserSigniture=function(id)
	{ 
	
	 var file = $scope.myFile2;
	 var folder = 'signitures';
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_edited_files&folder_name='+folder+'&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	 
	
	}
	 
			  
				//console.log('testing',selectedProduct);
			 
			$scope.data = [];
			
		$scope.loadUserDetails = function(user_id)
		{		
		$http.get("api/?cmd=viewUser&user_id="+user_id)
		.success(function(data)
		{
			$scope.users=data.results; 
			
			
			for(var i =0; i < $scope.users.length; i++)
			{ 
				$scope.data['license_no']=$scope.users[i].license_no;
				$scope.data['name']=$scope.users[i].name;
				$scope.data['email']=$scope.users[i].email;
				$scope.data['telephone']=$scope.users[i].telephone;
				$scope.data['NIN']=$scope.users[i].NIN;
				$scope.data['address']=$scope.users[i].address;
				$scope.data['gender']=$scope.users[i].gender; 
				$scope.data['branch_name']=$scope.users[i].branch_name; 
				$scope.data['img']=$scope.users[i].avatar; 
				$scope.data['role']=$scope.users[i].role;
				$scope.data['dob']=$scope.users[i].dob; 
				$scope.data['status']=$scope.users[i].status; 
				
				 console.log($scope.data);
			}
			 
			

		});
		
		
		}
		
		$scope.loadUserDetails(user_id);
		
		
		
			$scope.editUserData = function(d)
			{
				var uID = user_id;
				var license_no = d.license_no;
				var name = d.name;
				var NIN = d.NIN;
				var gender = d.gender;
				var email = d.email;
				var telephone = d.telephone; 
				var address = d.address;
				var dob= d.dob;
				var role = d.role;
				var status = d.status;
				var branch_name = d.branch_name;
				
				
				if(typeof license_no ==='undefined')
				{
					license_no ='';
				}
				if(typeof name ==='undefined')
				{
					name ='';
				}
				if(typeof NIN ==='undefined')
				{
					NIN ='';
				}
				if(typeof email ==='undefined')
				{
					email ='';
				}
				if(typeof telephone ==='undefined')
				{
					telephone ='';
				}
				if(typeof address ==='undefined')
				{
					address ='';
				}
				if(typeof branch_name ==='undefined')
				{
					branch_name ='';
				}
		
				
		$http.post("api/?cmd=editUsers&user_id="+uID+"&license_no="+license_no+"&name="+name+"&NIN="+NIN+"&email="+email+"&gender="+gender+"&telephone="+telephone+"&address="+address+"&role="+role+"&dob="+dob+"&status="+status+"&branch_name="+branch_name)
		.success(function(data)
		{ 
			
			if(data.status=='ok')
			{
			$scope.uploadUser(user_id); 
			toaster.pop(data.type, "", data.message, 10000, 'trustedHtml');	
				
			}
			else if(data.status=='notfound')
			{
				toaster.pop(data.type, "", data.message, 10000, 'trustedHtml');	
			}
			else
			{
				toaster.pop(data.type, "", data.message, 10000, 'trustedHtml');	
			}
			 

		}).finally(function()
		{ 
		$scope.close();	
		});
		}
  
		
		
			$scope.edit_UserPassord = function(d)
			{
				var uID = user_id;
				var password = d.password; 
				
		$http.post("api/?cmd=changePassword&user_id="+uID+"&password="+password)
		.success(function(data)
		{
			//$scope.users=data; 
			
			if(data.status=='ok')
			{ 
		
			toaster.pop("success", "", data.message, 10000, 'trustedHtml');	
				
			} 
			else
			{
				toaster.pop("error", "", data.message, 10000, 'trustedHtml');	
			}
			 

		}).finally(function()
		{
		$scope.loadUsers();
		$scope.close();	
		});
			}
 
 
 
			$scope.edit_UserPassord = function(d)
			{
				var uID = user_id;
				var password = d.password; 
				
		$http.post("api/?cmd=changePassword&user_id="+uID+"&password="+password)
		.success(function(data)
		{
			//$scope.users=data; 
			
			if(data.status=='ok')
			{ 
		
			toaster.pop("success", "", data.message, 10000, 'trustedHtml');	
				
			} 
			else
			{
				toaster.pop("error", "", data.message, 10000, 'trustedHtml');	
			}
			 

		}).finally(function()
		{ 
		$scope.close();	
		});
			}
 
	   
	  $scope.close = function () {
                $modalInstance.dismiss('cancel');
            };
			
		
			
}]);

app.controller('AgentController', ['$scope','$rootScope', '$http', '$filter', '$timeout','$stateParams','$state','$interval','$modal','SweetAlert','toaster','fileUpload','ngTableParams', function($scope, $rootScope, $http, $filter, $timeout,$stateParams,$state,$interval,$modal,SweetAlert,toaster,fileUpload,ngTableParams) {
  'use strict';
  
  
  
	$scope.uploadClient=function(id)
	{ 
	
	 var file = $scope.myFile;
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_files&name=users&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	 
	
	}
	
	$scope.add_Clients = function(d)
	{ 
		$http.post('api/?cmd=addClients&name='+d.name+'&dob='+d.dob+'&email='+d.email+'&gender='+d.gender+'&telephone='+d.telephone+'&address='+d.address+'&user_id='+$rootScope.uid)
		.success(function(res)
		{
			if(res.status=='ok')
			{
				toaster.pop('success', 'Client Successful', res.message);
				$scope.uploadClient(res.client_id);
			}
			else
			{
				toaster.pop('error', 'Client Registration Error', res.message);
			}
			
			console.log('out ',res);
			
		})
		.finally(function()
		{
			$scope.data="";
			$scope.myFile=null;
		}); 
		
	}
	
	
	var data=[]; 
	$scope.loadAgents=function()
	{
		$http.get("api/?cmd=viewThirdpartyUsers&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(res)
		{
			data=res.results;
			
			$scope.tableParams = new ngTableParams({
				page: 1,
				count: 10
			}, {
				total: data.length,
				getData: function ($defer, params) {
					var orderedData = params.sorting() ? $filter('orderBy')(data, params.orderBy()) : data;
					$defer.resolve(orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count()));
				}
			});

		});
	}
	
	$scope.loadAgents();
	// $interval($scope.loadAgents, 5000);
		
	$scope.editUser=function(user_id)
	{
		 var modalInstance = $modal.open({
						controller: 'userCont',
						backdrop: "static",
						templateUrl: 'assets/views/edit_user.html',
						size: 'lg',
		resolve: {
		  user_id: function () { return user_id;    }
		}
		});
		
	}
		
	$scope.loadUserStickers=function(user_id)
	{
		 var modalInstance = $modal.open({
						controller: 'userCont',
						backdrop: "static",
						templateUrl: 'assets/views/agent_stickers.html',
						size: 'lg',
		resolve: {
		  user_id: function () { return user_id;    }
		}
		});
		
	}
	
		 
	
  $scope.deleteUser=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this User!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=removeUsers&user_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'User details successfully removed', 10000, 'trustedHtml'); 	
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'User details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'User details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Deleted!", "User details has been deleted.", "success");
            } else {
                SweetAlert.swal("Cancelled", "User details is safe :)", "error");
            }
        });
		
	
	}
		 


}]);

app.controller('LogController', ['$scope','$rootScope', '$http', '$filter', '$timeout','$stateParams','$state','toaster','$interval','fileUpload','ngTableParams', function($scope, $rootScope, $http, $filter, $timeout,$stateParams,$state,toaster,$interval,fileUpload,ngTableParams) {
  'use strict';
  
	 
	
	var data=[]; 
	$scope.load_userLogs=function()
	{
	$http.get("api/?cmd=viewUserLogs&user_id="+$rootScope.uid)
		.success(function(res)
		{
			data=res.results; 
			
				
			$scope.tableParams = new ngTableParams({
				page: 1,
				count: 10
			}, {
				total: data.length,
				getData: function ($defer, params) {
					var orderedData = params.sorting() ? $filter('orderBy')(data, params.orderBy()) : data;
					$defer.resolve(orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count()));
				}
			});

		});
	}
           
	$scope.load_userLogs();
	// $interval($scope.load_userLogs, 500);
	
}]);


 