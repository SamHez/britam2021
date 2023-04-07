'use strict';
/** 
  * controller for User Profile Example
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
 
app.controller('UserCtrl', ["$scope", "flowFactory",'$rootScope', '$http', '$filter', '$timeout','$stateParams','$state','$modal','$interval','toaster','SweetAlert','fileUpload','ngTableParams', function ($scope, flowFactory, $rootScope, $http, $filter, $timeout,$stateParams,$state,$modal,$interval,toaster,SweetAlert,fileUpload,ngTableParams) {
    $scope.removeImage = function () {
        $scope.noImage = true;
    };
    $scope.obj = new Flow();

    $scope.userInfo = {
        firstName: 'Peter',
        lastName: 'Clark',
        url: 'www.example.com',
        email: 'peter@example.com',
        phone: '(641)-734-4763',
        gender: 'male',
        zipCode: '12345',
        city: 'London (UK)',
        avatar: 'assets/images/avatar-1-xl.jpg',
        twitter: '',
        github: '',
        facebook: '',
        linkedin: '',
        google: '',
        skype: 'peterclark82'
    };
    if ($scope.userInfo.avatar == '') {
        $scope.noImage = true;
    }
	
	
	$scope.uploadUser=function(id)
	{ 
	
	 var file = $scope.myFile;
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_files&name=users&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	 
	
	}
	
	$scope.add_Users = function(d)
	{ 
		
		var name = d.name;
		var license_no = d.license_no;
		var email = d.email;
		var gender = d.gender;
		var dob = d.dob;
		var NIN = d.NIN;
		var telephone = d.telephone;
		var address = d.address;
		var role = d.role;
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
				
		$http.post('api/?cmd=addUsers&name='+name+'&license_no='+license_no+'&email='+email+'&gender='+gender+'&dob='+dob+'&NIN='+NIN+'&telephone='+telephone+'&address='+address+'&role='+role+'&branch_name='+branch_name+'&user_id_added='+$rootScope.uid)
		.success(function(res)
		{
			if(res.status=='ok')
			{
				toaster.pop('success', 'User Successful', res.message);
				$scope.uploadUser(res.user_id);
			}
			else
			{
				toaster.pop('error', 'User Registration Error', res.message);
			}
			
			console.log('out ',res);
			
		})
		.finally(function()
		{
			$scope.data=[];
			$scope.myFile=null;
		});
		
		
	} 
    
   
	var data=[];
	
	$scope.loadUsers = function()
	{
	$http.get("api/?cmd=viewUsers&role="+$rootScope.role+"&user_id="+$rootScope.uid)
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
	
	$scope.loadUsers();
	// $interval($scope.loadUsers, 500);
		
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
	$http.get('api/?cmd=viewUserIssuedStickers&user_id='+user_id)
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


app.controller('UserProfileCtrl', ["$scope","$http","$rootScope", "$location", "$window","$state","Data","FileUploader", function ($scope,$http,$rootScope,$location,$window,$state,Data,FileUploader) {


		var id = $rootScope.uid;
		var folder_name = 'users'; 
		
  var uploaderImages = $scope.uploaderImages = new FileUploader({
        url: "api/?cmd=upload_edited_files&folder_name="+folder_name+"&id="+id
    });
 
	uploaderImages.filters.push({
        name: 'imageFilter',
        fn: function (item/*{File|FileLikeObject}*/, options) {
            var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    });

    // CALLBACKS

    uploaderImages.onWhenAddingFileFailed = function (item/*{File|FileLikeObject}*/, filter, options) {
        console.info('onWhenAddingFileFailed', item, filter, options);
    };
    uploaderImages.onAfterAddingFile = function (fileItem) {
        console.info('onAfterAddingFile', fileItem);
    };
    uploaderImages.onAfterAddingAll = function (addedFileItems) {
        console.info('onAfterAddingAll', addedFileItems);
    };
    uploaderImages.onBeforeUploadItem = function (item) {
        console.info('onBeforeUploadItem', item);
    };
    uploaderImages.onProgressItem = function (fileItem, progress) {
        console.info('onProgressItem', fileItem, progress);
    };
    uploaderImages.onProgressAll = function (progress) {
        console.info('onProgressAll', progress);
    };
    uploaderImages.onSuccessItem = function (fileItem, response, status, headers) {
        console.info('onSuccessItem', fileItem, response, status, headers);
    };
    uploaderImages.onErrorItem = function (fileItem, response, status, headers) {
        console.info('onErrorItem', fileItem, response, status, headers);
    };
    uploaderImages.onCancelItem = function (fileItem, response, status, headers) {
        console.info('onCancelItem', fileItem, response, status, headers);
    };
    uploaderImages.onCompleteItem = function (fileItem, response, status, headers) {
        console.info('onCompleteItem', fileItem, response, status, headers);
    };
    uploaderImages.onCompleteAll = function () {
		Data.toast({status:"success",message:"Profile Image successfully uploaded"});
        console.info('onCompleteAll');
    };

    console.info('uploader', uploaderImages);
	
	 
	 
	$scope.data={};
	
	$scope.data={email:$rootScope.email,img:$rootScope.img,role:$rootScope.role,name:$rootScope.name,gender:$rootScope.gender,telephone:$rootScope.telephone,address:$rootScope.address,license_no:$rootScope.license_no,NIN:$rootScope.NIN,dob:$rootScope.dob,branch_name:$rootScope.branch_name};
	
	
	  $scope.updateUserProfile=function(d)
	{ 
				var uID = $rootScope.uid;
				var license_no = d.license_no;
				var name = d.name;
				var NIN = d.NIN;
				var gender = d.gender;
				var email = d.email;
				var telephone = d.telephone; 
				var address = d.address;
				var dob= d.dob;
				// var role = d.role;
				// var status = d.status;
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
		
				
		$http.post("api/?cmd=editUserProfile&user_id="+uID+"&license_no="+license_no+"&name="+name+"&NIN="+NIN+"&email="+email+"&gender="+gender+"&telephone="+telephone+"&address="+address+"&dob="+dob+"&branch_name="+branch_name)
		.success(function(data)
		{ 
			
			if(data.status=='ok')
			{
			$scope.uploadUser(user_id); 
			toaster.pop(data.type, "", data.message, 10000, 'trustedHtml');	
				
				$state.go('app.dashboard');
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
		});
		 
		 
				 
			 
	}
	
	
	$scope.updateUserPassword=function(d)
	{ 
		var uID = $rootScope.uid;
		var password = d.PassWord_U; 
				
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
		});
		
		
	}

   
}]);
 