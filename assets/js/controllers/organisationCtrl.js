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
 
app.controller('organisationCtrl', ['$scope','$rootScope', '$http', '$filter', '$timeout','$stateParams','$state','$modal','$interval','toaster','SweetAlert','fileUpload','ngTableParams', function($scope, $rootScope, $http, $filter, $timeout,$stateParams,$state,$modal,$interval,toaster,SweetAlert,fileUpload,ngTableParams) {
    
	
	
	$scope.uploadUser=function(id)
	{ 
	
	 var file = $scope.myFile;
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_files&name=organisations&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	 
	
	}
	
	$scope.editOrganisation=function(organ_id)
	{
	 var modalInstance = $modal.open({
                    controller: 'organCont',
                    backdrop: "static",
                    templateUrl: 'assets/views/edit_organisation.html',
                    size: 'lg',
    resolve: {
      organ_id: function () { return organ_id;    }
    }
                });
		
	}
	
	
	$scope.addOrgan = function(d)
	{ 
		$http.post('api/?cmd=addOrganisations&name='+d.name+'&code='+d.code+'&contact_name='+d.contact_name+'&contact_email='+d.contact_email+'&address='+d.address+'&contact_tel='+d.contact_tel+'&user_id='+$rootScope.uid)
		.success(function(res)
		{
			if(res.status=='ok')
			{
				toaster.pop('success', 'Organisation Successful', res.message);
				$scope.uploadUser(res.organ_id);
			}
			else
			{
				toaster.pop('error', 'Organisation Error', res.message);
			}
			
			console.log('out ',res);
			
		})
		.finally(function()
		{
			$scope.data="";
		});
		
		
	}
	
  var vm = this;

   $scope.quoteID =$stateParams.quote_id;
   
   
    $scope.quotationDetails ={};
   
	 $scope.loadQuotationDetails = function(quoteID)
	 {
	 $http.get('api/?cmd=viewQuote&quote_id='+quoteID)
     .success(function(res)
     {
        $scope.quotationDetails = res.results; 
		console.log(res); 
	});
	 
	 }
	 
	$scope.loadQuotationDetails($scope.quoteID);
   
	var data=[];
	
	$scope.loadOrgans=function()
	{
	$http.get("api/?cmd=LoadOrganisation&user_id="+$rootScope.uid+"&role="+$rootScope.role)
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
			console.log('organ results ',data);
			 

		});
	}
	
	$scope.loadOrgans();
	// $interval($scope.loadOrgans, 500);
		
		 
		 
  
  $scope.deleteOrgan=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this Organisation!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=removeOrganisation&organ_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'Organisation details successfully removed', 10000, 'trustedHtml'); 	
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'Organisation details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'Organisation details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Deleted!", "Organisation details has been deleted.", "success");
            } else {
                SweetAlert.swal("Cancelled", "Organisation details is safe :)", "error");
            }
        });
		
	
	}
		
				
 


}]);


app.controller('organCont', ['$scope','$rootScope','$modalInstance','$http','organ_id','toaster','fileUpload',function ($scope,$rootScope, $modalInstance,$http,organ_id,toaster,fileUpload) {
           
 	   
			 
	$scope.uploadLogo=function(id)
	{ 
	
	 var file = $scope.myFile;
	 var folder = 'organisations';
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_edited_files&folder_name='+folder+'&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	  
	}
	 
	 
			  
				//console.log('testing',selectedProduct);
			 
		$scope.data = [];
			
		$scope.loadOrganDetails = function(organ_id)
		{		
		$http.get("api/?cmd=viewOrganisation&organ_id="+organ_id)
		.success(function(data)
		{
			$scope.organ=data.results; 
			
			
			for(var i =0; i < $scope.organ.length; i++)
			{ 
				$scope.data['name']=$scope.organ[i].name;
				$scope.data['code']=$scope.organ[i].code;
				$scope.data['address']=$scope.organ[i].address;
				$scope.data['contact_name']=$scope.organ[i].contact_name;
				$scope.data['contact_email']=$scope.organ[i].contact_email;  
				$scope.data['contact_tel']=$scope.organ[i].contact_tel;
				$scope.data['logo']=$scope.organ[i].logo;
				
				 console.log($scope.data);
			}
			 
			

		});
		
		
		}
		
		$scope.loadOrganDetails(organ_id);
		
		
		
			$scope.editOrganData = function(d)
			{
				var oID = organ_id;
				var code = d.code;
				var name = d.name;
				var address = d.address;
				var contact_name = d.contact_name;
				var contact_email = d.contact_email; 
				var contact_tel = d.contact_tel; 
				
		$http.post("api/?cmd=editOrganisation&organ_id="+oID+"&code="+code+"&name="+name+"&address="+address+"&contact_name="+contact_name+"&contact_email="+contact_email+"&contact_tel="+contact_tel)
		.success(function(res)
		{
			//$scope.users=data; 
			
			if(res.status=='ok')
			{
			$scope.uploadLogo(organ_id); 
			toaster.pop(res.type, "", res.message, 10000, 'trustedHtml');	
				
			}
			else if(res.status=='notfound')
			{
				toaster.pop(res.type, "", res.message, 10000, 'trustedHtml');	
			}
			else
			{
				toaster.pop(res.type, "", res.message, 10000, 'trustedHtml');	
			}
			 

		}).finally(function()
		{
		//$scope.loadUsers();
		$scope.close();	
		});
			} 
 
	   
	  $scope.close = function () {
                $modalInstance.dismiss('cancel');
            };
			
		
			
}]);
 