'use strict';
/** 
  * controller for organisationCtrl.js
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
 
app.controller('clientCtrl', ['$scope','$rootScope', '$http', '$filter', '$timeout','$stateParams','$state','$interval','$modal','SweetAlert','toaster','fileUpload','ngTableParams', function($scope, $rootScope, $http, $filter, $timeout,$stateParams,$state,$interval,$modal,SweetAlert,toaster,fileUpload,ngTableParams) {
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
	
				var name = d.name; 
				var gender = d.gender;
				var email = d.email;
				var telephone = d.telephone; 
				var address = d.address;
				var dob= d.dob; 
				
				if(typeof name ==='undefined')
				{
					name ='';
				}
				if(typeof gender ==='undefined')
				{
					gender ='';
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
				if(typeof dob ==='undefined')
				{
					dob ='';
				}
				
		$http.post('api/?cmd=addClients&name='+name+'&dob='+dob+'&email='+email+'&gender='+gender+'&telephone='+telephone+'&address='+address+'&user_id='+$rootScope.uid)
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
	$scope.loadClients=function()
	{
		$http.get("api/?cmd=viewClients&user_id="+$rootScope.uid+"&role="+$rootScope.role)
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
	
	$scope.loadClients();
	// $interval($scope.loadClients, 500);
		
	$scope.editClient=function(client_id)
	{
		 var modalInstance = $modal.open({
						controller: 'clientCont',
						backdrop: "static",
						templateUrl: 'assets/views/edit_client.html',
						size: 'lg',
		resolve: {
		  client_id: function () { return client_id;    }
		}
		});
		
	}
	
		 
	
	$scope.deleteClient=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this Client!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=removeClients&client_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'Client details successfully removed', 10000, 'trustedHtml'); 
					$state.go('app.dashboard');
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'Client details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'Client details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Deleted!", "Client details has been deleted.", "success");
            } else {
                SweetAlert.swal("Cancelled", "Client details is safe :)", "error");
            }
        });
		
	
	}
		 


}]);


app.controller('clientCont', ['$scope','$rootScope','$modalInstance','$http','client_id','toaster','fileUpload',function ($scope,$rootScope, $modalInstance,$http,client_id,toaster,fileUpload) {
           
 
	$scope.uploadClient=function(id)
	{ 
	
	 var file = $scope.myFile;
	 var folder = 'clients';
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_edited_files&folder_name='+folder+'&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	  
	}
	 
			  
				//console.log('testing',selectedProduct);
			 
			$scope.data = [];
			
		$scope.loadClientDetails = function(client_id)
		{		
		$http.get("api/?cmd=viewClient&client_id="+client_id)
		.success(function(data)
		{
			$scope.client=data.results; 
			
			
			for(var i =0; i < $scope.client.length; i++)
			{  
				$scope.data['name']=$scope.client[i].name;
				$scope.data['email']=$scope.client[i].email;
				$scope.data['telephone']=$scope.client[i].telephone; 
				$scope.data['address']=$scope.client[i].address;
				$scope.data['gender']=$scope.client[i].gender; 
				$scope.data['img']=$scope.client[i].avatar;  
				$scope.data['dob']=$scope.client[i].dob;  
				
				 console.log($scope.data);
			}
			 
			

		});
		
		
		}
		
		$scope.loadClientDetails(client_id);
		
		
		
			$scope.editClientData = function(d)
			{
				var uID = client_id; 
				var name = d.name; 
				var gender = d.gender;
				var email = d.email;
				var telephone = d.telephone; 
				var address = d.address;
				var dob= d.dob; 
				
				if(typeof name ==='undefined')
				{
					name ='';
				}
				if(typeof gender ==='undefined')
				{
					gender ='';
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
				if(typeof dob ==='undefined')
				{
					dob ='';
				}
				
				
		$http.post("api/?cmd=editClients&client_id="+uID+"&name="+name+"&email="+email+"&gender="+gender+"&telephone="+telephone+"&address="+address+"&dob="+dob)
		.success(function(data)
		{ 
			
			if(data.status=='ok')
			{
			$scope.uploadClient(client_id); 
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
  
		
		 
	   
	  $scope.close = function () {
                $modalInstance.dismiss('cancel');
            };
			
		
			
}]); 