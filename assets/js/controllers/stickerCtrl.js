'use strict';
/** 
  * controller for stickerCtrl.js
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
 
app.controller('stickerCtrl', ['$scope', '$rootScope', '$http', '$timeout','$stateParams','$state','toaster', '$filter','$modal','$interval','SweetAlert', 'ngTableParams', function($scope, $rootScope, $http, $timeout,$stateParams,$state,toaster, $filter,$modal,$interval,SweetAlert, ngTableParams) {
  'use strict';
  
	 	$scope.policies_no=[]; 
		$http.get("api/?cmd=viewPolicy_nos&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(data)
		{
			$scope.policies_no=data.results; 
			console.log('pol',$scope.policies_no);
		});
		
 
	
   $scope.invoiceID =$stateParams.invoice_id;
   $scope.qprint=0;
   $scope.print=function()
   {
	   $scope.qprint=1;
   }
   
   
    $scope.makePayments=function(invoice_id)
	{
		 var modalInstance = $modal.open({
						controller: 'paymentCont',
						backdrop: "static",
						templateUrl: 'assets/views/make_payments.html',
						size: '250px',
		resolve: {
		  invoice_id: function () { return invoice_id;    }
		}
		});
		
	}
	 
	
	$scope.loadSticker=function(invoice_detail_id)
	{
		 var modalInstance = $modal.open({
						controller: 'stickerCont',
						backdrop: "static",
						templateUrl: 'assets/views/print_sticker.html',
						size: '500x200',
		resolve: {
		  invoice_detail_id: function () { return invoice_detail_id;    }
		}
		});
		
	}
	
	$scope.loadFleetSticker=function(invoice_id)
	{
		 var modalInstance = $modal.open({
						controller: 'fleetstickerCont',
						backdrop: "static",
						templateUrl: 'assets/views/fleet_print.html',
						size: 'lg',
		resolve: {
		  invoice_id: function () { return invoice_id;    }
		}
		});
		
	}
	
	$scope.loadRSticker=function(invoice_detail_id)
	{
		 var modalInstance = $modal.open({
						controller: 'stickerCont',
						backdrop: "static",
						templateUrl: 'assets/views/replace_sticker.html',
						size: '500x200',
		resolve: {
		  invoice_detail_id: function () { return invoice_detail_id;    }
		}
		});
		
	}
	
	
    $scope.invoiceDetails ={};
   
	 $scope.loadInvoiceDetails = function(invoiceID)
	 {
	 $http.get('api/?cmd=viewInvoice&invoice_id='+invoiceID)
     .success(function(res)
     {
        $scope.invoiceDetails = res.results; 
		console.log(res); 
	});
	 
	 }
	 
	$scope.loadInvoiceDetails($scope.invoiceID); 
	
	$interval(
	function(){$scope.loadInvoiceDetails($scope.invoiceID);}
	, 5000);
   
	$scope.organisation=[]; 
	$http.get("api/?cmd=LoadOrganisation&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(data)
		{
			$scope.organisation=data.results; 

		});
		
		
	$scope.sendSms=function(t,no)
	{
		$http.post('api/?cmd=sendClientSMS&refNo='+no+'&client_telephone='+t)
		.success(function(res)
		{
			if(res.status=='ok')
			{
				toaster.pop('success', 'SMS Successful', res.message);
			}
		});
		
		
	}
		
	$scope.sendEmail=function(email,quoteID)
	{
		$http.post('api/?cmd=send_client_email&client_email='+email+'&quote_id='+quoteID)
		.success(function(res)
		{
			if(res.status=='sent')
			{
				toaster.pop('success', 'Email Successful', res.message);
			}
			else
			{
				toaster.pop('error', 'Email Error', res.message);
			}
		});
		 
	}
		 
	
	var data=[]; 
	
	$scope.loadInvoicesData=function()
	{
	$http.get("api/?cmd=viewInvoices&role="+$rootScope.role+"&user_id="+$rootScope.uid)
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
	
	$scope.loadInvoicesData();
	// $interval($scope.loadInvoicesData,500);
	 
	$scope.clients=[];
	
	$http.get("api/?cmd=LoadClients")
		.success(function(data)
		{
			$scope.clients=data.results; 

		});
		
		
		//mtp motor details
  $scope.motor_details2=[];
 
 $scope.addNewChoice2 = function() {
    var newItemNo = $scope.motor_details2.vehicle_plate_no; 
	// $scope.total_valuation += $scope.motor_details.value;
    $scope.motor_details2.push({newItemNo});
  };
    
  $scope.removeChoice2 = function() {
    var lastItem = $scope.motor_details2.length-1;
	// $scope.total_valuation -= $scope.motor_details.value;
    $scope.motor_details2.splice(lastItem);
  };
  
  //comesa motor details
  $scope.motor_details=[];
 
 $scope.addNewChoice = function() {
    var newItemNo = $scope.motor_details.vehicle_plate_no; 
	// $scope.total_valuation += $scope.motor_details.value;
    $scope.motor_details.push({newItemNo});
  };
    
  $scope.removeChoice = function() {
    var lastItem = $scope.motor_details.length-1;
	// $scope.total_valuation -= $scope.motor_details.value;
    $scope.motor_details.splice(lastItem);
  };
  
  
$scope.total_valuation = 0;
$scope.total_rate = 0;
//$scope.total_p = 0;

$scope.getTotal = function(){
    var total = 0;
    var total_rate = 0;
    var total_p = 0;
    for(var i = 0; i < $scope.motor_details.length; i++){
        var product = $scope.motor_details[i];
        total += (product.basic_premium2*1);
        //total_rate += (product.policies1.rate/100);
		//total_p += 1;
    }
	
	//$scope.total_p = total_p;
	$scope.total_valuation=total;
	//$scope.total_rate=total_rate;
    return total;
}  
 
$scope.total_valuation2 = 0; 

$scope.getTotal2 = function(){
    var total = 0; 
    for(var i = 0; i < $scope.motor_details2.length; i++){
        var product2 = $scope.motor_details2[i];
        total += (product2.basic_premium2*1);
        //total_rate += (product.policies1.rate/100);
		//total_p += 1;
    }
	 
	$scope.total_valuation2=total; 
    return total;
}  
 	  
   
	
 $scope.getInvoice=function(row){ 
		
		$scope.isRefreshing = true;  
		var organ_id=1; 
		var start_date=row.start_date; 
		var currency=row.currency; 
		var basic_premium = $scope.total_valuation;
		var training_levy=row.total_levy;
		
		 
		//thirdparty entry
		if(row.motor_category=='3rd Party')
		{
			
					var quote_type='thirdparty'; 
					var country='Uganda';
					var status='new';
					var policy_no = ''; 
					$scope.invoice_id=0;
					
					if(row.n_client=='new')
					{ 
						
						var name = row.client_name;
						var email = row.client_email;
						var telephone = row.client_telephone; 
						var address = row.client_address;
						var gender = row.gender;
						var dob = row.dob;
						
						$http.post("api/?cmd=addClients&name="+name+"&email="+email+"&telephone="+telephone+"&address="+address+"&gender="+gender+"&dob="+dob+"&user_id="+$rootScope.uid)
						.success(function(data)
						{
							var client_id=data.client_id;
							
										
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var basic_premium2 =(tt.basic_premium2); 
												var vehicle_category =(tt.category); 
												var training_levy2 =(0.5/100)*(basic_premium2); 
												// var vehicle_type=tt.type;
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use;  
												  
							
								
								$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&vehicle_plate_no="+vehicle_plate_no+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id;
									  
									console.log('add_MotorThird_Comesa status -- ',data);
									
									if(data.status=='ok')
									{
										
										
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
													
										
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
									toaster.pop('error', 'Sticker Error', data.message);	
									}

								});
							
											}
							
						})
						.finally(function()
						{
							$scope.isRefreshing = false;
							$scope.data=""; 
							if($scope.invoice_id > 0)
							{ $state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id}); }
							
									
						});
						
						
						
					}
					else
					{ 
				
						var client_id=row.client.client_id;
						
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var vehicle_category =(tt.category); 
												var basic_premium2 =(tt.basic_premium2); 
												var training_levy2 =(0.5/100)*(basic_premium2);  
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use; 
												 
								$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&vehicle_plate_no="+vehicle_plate_no+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id;
									  
 
									
									if(data.status=='ok')
									{
										 
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
												
												
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
										
									toaster.pop('error', 'Sticker Error', data.message);
									
									}

								})
								.finally(function()
								{
									$scope.isRefreshing = false;
									$scope.data="";
									
									if($scope.invoice_id>0){$state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id});}
									
								});
							 
										
					 	}
					}
			} 
		if(row.motor_category=='Transit')
		{
			
					var quote_type='transit'; 
					var country='Uganda';
					var status='new';
					var policy_no = '';
					
					$scope.invoice_id=0;
					
					if(row.n_client=='new')
					{
						
						//new client first and sticker last
						
						var name = row.client_name;
						var email = row.client_email;
						var telephone = row.client_telephone; 
						var address = row.client_address;
						var gender = row.gender;
						var dob = row.dob;
						
						$http.post("api/?cmd=addClients&name="+name+"&email="+email+"&telephone="+telephone+"&address="+address+"&gender="+gender+"&dob="+dob+"&user_id="+$rootScope.uid)
						.success(function(data)
						{
							var client_id=data.client_id;
							
										
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var basic_premium2 =(tt.basic_premium2); 
												var vehicle_category =(tt.category); 
												var training_levy2 =(0.5/100)*(basic_premium2);  
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use;  
												  
							
								
								$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&vehicle_plate_no="+vehicle_plate_no+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id; 
									
									if(data.status=='ok')
									{
										
										
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
													
										
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
									toaster.pop('error', 'Sticker Error', data.message);	
									}

								});
							
											}
							
						})
						.finally(function()
						{
							$scope.isRefreshing = false;
							$scope.data=""; 
							if($scope.invoice_id > 0)
							{ $state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id}); }
							
									
						});
						
						
						
					}
					else
					{ 
				
						var client_id=row.client.client_id;
						
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var vehicle_category =(tt.category); 
												var basic_premium2 =(tt.basic_premium2); 
												var training_levy2 =(0.5/100)*(basic_premium2);  
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use; 
												 
								$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&vehicle_plate_no="+vehicle_plate_no+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id;
									  

									  
									console.log('status -- ',data);
									
									if(data.status=='ok')
									{
										 
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
												
												
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
										
									toaster.pop('error', 'Sticker Error', data.message);
									
									}

								})
								.finally(function()
								{
									$scope.isRefreshing = false;
									$scope.data="";
									
									if($scope.invoice_id>0){$state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id});}
									
								});
							 
										
											}
					}
			} 
		if(row.motor_category=='New Import')
		{
			
					var quote_type='newimport'; 
					var country='Uganda';
					var status='new';
					var policy_no = '';
					
					$scope.invoice_id=0;
					
					if(row.n_client=='new')
					{
						
						//new client first and sticker last
						
						var name = row.client_name;
						var email = row.client_email;
						var telephone = row.client_telephone; 
						var address = row.client_address;
						var gender = row.gender;
						var dob = row.dob;
						
						$http.post("api/?cmd=addClients&name="+name+"&email="+email+"&telephone="+telephone+"&address="+address+"&gender="+gender+"&dob="+dob+"&user_id="+$rootScope.uid)
						.success(function(data)
						{
							var client_id=data.client_id;
							
										
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var basic_premium2 =(tt.basic_premium2); 
												var vehicle_category =(tt.category); 
												var training_levy2 =(0.5/100)*(basic_premium2);  
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use;  
												  
							
								
								$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&vehicle_plate_no="+vehicle_plate_no+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id; 
									
									if(data.status=='ok')
									{
										
										
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
													
										
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
									toaster.pop('error', 'Sticker Error', data.message);	
									}

								});
							
											}
							
						})
						.finally(function()
						{
							$scope.isRefreshing = false;
							$scope.data=""; 
							if($scope.invoice_id > 0)
							{ $state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id}); }
							
									
						});
						
						
						
					}
					else
					{ 
				
						var client_id=row.client.client_id;
						
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var vehicle_category =(tt.category); 
												var basic_premium2 =(tt.basic_premium2); 
												var training_levy2 =(0.5/100)*(basic_premium2);  
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use; 
												 
								$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&vehicle_plate_no="+vehicle_plate_no+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id;
									  

									  
									console.log('status -- ',data);
									
									if(data.status=='ok')
									{
										 
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
												
												
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
										
									toaster.pop('error', 'Sticker Error', data.message);
									
									}

								})
								.finally(function()
								{
									$scope.isRefreshing = false;
									$scope.data="";
									
									if($scope.invoice_id>0){$state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id});}
									
								});
							 
										
											}
					}
			} 
//if statement adding comprehensive data			
			else if(row.motor_category=='Comprehensive')
			{
			
					var quote_type='comprehensive'; 
					var country='Uganda';
					var status='paid';
					var policy_no = row.policy_no;
					var debit_note = row.debit_note;
					var comprehensive_paid = row.comprehensive_paid;
					 
					
					if(row.n_client=='new')
					{
						
						//new client first and sticker last
						
						var name = row.client_name;
						var email = row.client_email;
						var telephone = row.client_telephone; 
						var address = row.client_address;
						var gender = row.gender;
						var dob = row.dob;
						
						$http.post("api/?cmd=addClients&name="+name+"&email="+email+"&telephone="+telephone+"&address="+address+"&gender="+gender+"&dob="+dob+"&user_id="+$rootScope.uid)
						.success(function(data)
						{
							
							$scope.invoice_id=data.invoice_id;
							var client_id=data.client_id;
								
								$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&debit_note="+debit_note+"&comprehensive_paid="+comprehensive_paid+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id;
									  

									if(data.status=='ok')
									{
										
										
										
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var basic_premium2 =(tt.basic_premium2); 
												var vehicle_category =(tt.category); 
												var training_levy2 =(0.5/100)*(basic_premium2);  
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use; 
												 
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
													
										
											}
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									$state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id});
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
									toaster.pop('error', 'Sticker Error', data.message);	
									}

								});
							
							
						})
						.finally(function()
						{
							$scope.isRefreshing = false;
							$scope.data="";
						});
						
						
						
					}
					else
					{ 
				
						var client_id=row.client.client_id;
						 
						
						$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&debit_note="+debit_note+"&comprehensive_paid="+comprehensive_paid+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id;
									  

									if(data.status=='ok')
									{
										 
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var vehicle_category =(tt.category); 
												var basic_premium2 =(tt.basic_premium2); 
												var training_levy2 =(0.5/100)*(basic_premium2);  
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.use; 
												 
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use+"&vehicle_category="+vehicle_category+"&user_id="+$rootScope.uid)
													.success(function(data2)
													{
														var d=data2;
													});	
												
													 
										
											}
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									$state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id});
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
									toaster.pop('error', 'Sticker Error', data.message);	
									}

								})
								.finally(function()
								{
									$scope.isRefreshing = false;
									$scope.data="";
								});
						
					}
		}  
		
		}

$scope.cancelInvoice=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this Policy!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, cancel it!",
            cancelButtonText: "No, cancel!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=cancel_invoice&invoice_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'Sticker details successfully cancelled', 10000, 'trustedHtml'); 	
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'Sticker details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'Sticker details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Cancelled!", "Sticker details has been cancelled.", "success");
            } else {
                SweetAlert.swal("Cancelled", "Sticker details is safe :)", "error");
            }
        });
		
	
	}
	
	 
$scope.deleteInvoice=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this Policy!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=delete_invoice&invoice_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'Sticker details successfully removed', 10000, 'trustedHtml'); 	
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'Sticker details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'Sticker details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Deleted!", "Sticker details has been deleted.", "success");
            } else {
                SweetAlert.swal("Cancelled", "Sticker details is safe :)", "error");
            }
        });
		
	
	}	



	$scope.renew_Policy = function(q,d)
	{
		var organ_id = q.organ_id;
		var policy_no = q.policy_no;
		var quote_type = q.iclass;
		var training_levy = q.training_levy;
		var country = q.country;
		var start_date = d;
		var client_id = q.client_id;
		var basic_premium = q.basic_premium;
		var currency = q.currency;
		var status = 'renewal';
		
		if(typeof policy_no ==='undefined')
				{
					policy_no ='';
				}
				if(typeof quote_type ==='undefined')
				{
					quote_type ='';
				}
				if(typeof training_levy ==='undefined')
				{
					training_levy ='';
				}
				if(typeof country ==='undefined')
				{
					country ='';
				}
				if(typeof basic_premium ==='undefined')
				{
					basic_premium ='';
				}
				if(typeof currency ==='undefined')
				{
					currency ='';
				}
		
		var motor_details = q.motor_thirdparty_details;
		
		$http.post("api/?cmd=add_MotorThird_Comesa&organ_id="+organ_id+"&policy_no="+policy_no+"&quote_type="+quote_type+"&training_levy="+training_levy+"&country="+country+"&start_date="+start_date+"&client_id="+client_id+"&basic_premium="+basic_premium+"&currency="+currency+"&status="+status+"&user_id="+$rootScope.uid)
								.success(function(data)
								{
									$scope.invoice_id=data.invoice_id;
									  

									if(data.status=='ok')
									{
										
										
										
											var ii=0; 
											for(ii;motor_details.length > ii; ii++)
											{ 
												
												var tt=motor_details[ii];
												
												var basic_premium2 =(tt.basic_premium); 
												var training_levy2 =(tt.training_levy); 
												var vehicle_category=tt.vehicle_category;
												var vehicle_make=tt.vehicle_make;
												var vehicle_chasis_no=tt.vehicle_chasis_no;
												var vehicle_cc=tt.vehicle_cc;
												var vehicle_no_seats=tt.vehicle_no_seats;
												var vehicle_plate_no=tt.vehicle_plate_no;
												var gross_weight=tt.gross_weight;
												var vehicle_use=tt.vehicle_use; 
												//var other=tt.other; 
												if(typeof vehicle_category ==='undefined')
												{
													vehicle_category ='';
												}
												if(typeof vehicle_make ==='undefined')
												{
													vehicle_make ='';
												}
												if(typeof vehicle_no_seats ==='undefined')
												{
													vehicle_no_seats ='';
												}
												if(typeof vehicle_chasis_no ==='undefined')
												{
													vehicle_chasis_no ='';
												}
												if(typeof vehicle_plate_no ==='undefined')
												{
													vehicle_plate_no ='';
												}
												if(typeof vehicle_use ==='undefined')
												{
													vehicle_use ='';
												}
												if(typeof gross_weight ==='undefined')
												{
													gross_weight ='';
												}
												if(typeof vehicle_cc ==='undefined')
												{
													vehicle_cc ='';
												}
												
													$http.post("api/?cmd=addMotor_MotorThird_Comesa_details&invoice_id="+$scope.invoice_id+"&basic_premium="+basic_premium2+"&training_levy="+training_levy2+"&vehicle_chasis_no="+vehicle_chasis_no+"&vehicle_category="+vehicle_category+"&gross_weight="+gross_weight+"&vehicle_make="+vehicle_make+"&vehicle_no_seats="+vehicle_no_seats+"&vehicle_plate_no="+vehicle_plate_no+"&vehicle_cc="+vehicle_cc+"&vehicle_use="+vehicle_use)
													.success(function(data2)
													{
														var d=data2;
													});	
													
										
											}
									 
									toaster.pop('success', 'Sticker Successful', data.message);
									$state.go('app.stickers.loadinvoice', {invoice_id:$scope.invoice_id});
									
									}
									else if(data.status=='duplicate')
									{
										toaster.pop('error', 'Sticker Unsuccessful', data.message);
									}
									else
									{
									toaster.pop('error', 'Sticker Error', data.message);	
									}

								})
								.finally(function()
								{
									$scope.isRefreshing = false;
									$scope.data="";
								});
		
	}
		 

}]);

app.controller('paymentCont', ['$scope','$rootScope','$modalInstance','$http','invoice_id','toaster','fileUpload',function ($scope,$rootScope, $modalInstance,$http,invoice_id,toaster,fileUpload) {
           
 
	$scope.uploadUser=function(id)
	{ 
	
	 var file = $scope.myFile;
	 var folder = 'users';
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_edited_files&folder_name='+folder+'&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	  
	}
	 
			  
				//console.log('testing',selectedProduct);
			 
			$scope.data = [];
			$scope.organ_id=0;
		$scope.loadInvoiceDetails = function(invoice_id)
		{		
		$http.get("api/?cmd=viewInvoice&invoice_id="+invoice_id)
		.success(function(data)
		{
			$scope.invoices=data.results; 
			
			
			for(var i =0; i < $scope.invoices.length; i++)
			{ 
				$scope.data['invoice_id']=$scope.invoices[i].invoice_id;
				$scope.data['iclass']=$scope.invoices[i].iclass;
				$scope.data['organ_id']=$scope.invoices[i].organ_id;
				$scope.data['policy_no']=$scope.invoices[i].policy_no;
				
				if($scope.data['iclass']=='newimport')
				{
				$scope.data['total_amount']=(($scope.invoices[i].basic_premium *1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
				
					if($scope.data['total_amount'] > 35000)
					{
						$scope.data['total_amount']=(($scope.invoices[i].basic_premium *1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
				
					}else
					{
					$scope.data['total_amount']=35000; 
					
					}	
				}
				else if($scope.data['iclass']=='transit')
				{
					
					$scope.data['total_amount']=(($scope.invoices[i].basic_premium *0.2)+($scope.invoices[i].training_levy*1)+(35000)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*0.2)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
					
					if($scope.data['total_amount'] > 49000)
					{
						$scope.data['total_amount']=(($scope.invoices[i].basic_premium *0.2)+($scope.invoices[i].training_levy*1)+(35000)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*0.2)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
				
					}else
					{
					$scope.data['total_amount']=49000; 
						
					}
				
				}
				else
				{
				$scope.data['total_amount']=(($scope.invoices[i].basic_premium *1)+($scope.invoices[i].training_levy*1)+(35000)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
					
				}
				$scope.data['currency']=$scope.invoices[i].currency; 
				$scope.data['payment_method']=$scope.invoices[i].payment_method; 
				$scope.data['status']=''; 
				$scope.organ_id=$scope.invoices[i].organ_id;
				console.log('Testing -- ',$scope.data);
			}
			 
			

		});
		
		
		}
		
		$scope.loadInvoiceDetails(invoice_id); 
		
		$scope.policies_no=[]; 
		$http.get("api/?cmd=viewPolicy_nos&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(data)
		{
			$scope.policies_no=data.results; 
			console.log('pol',$scope.policies_no);
		});
		
			$scope.initiatePay = function(t,a,id)
			{
				var telephone = t;
				var amount = Math.round(a);
				var invoice_id = id; 
				
				
				// var params = {
								// // Request parameters
							// };
      
				// $.ajax({
					// url: "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay?" + $.param(params),
					// beforeSend: function(xhrObj){
						// // Request headers
						// xhrObj.setRequestHeader("Authorization","Basic OWNlZjFlZjQ5MzZmNGY5NzkzZGMyYWRlZjkzNzljNGE6OGVhNjkwMzkyMWU1NDA3Y2E5NDAyMTkxYzEzZWFkNzc=");
						// xhrObj.setRequestHeader("X-Callback-Url","");
						// xhrObj.setRequestHeader("X-Reference-Id","e996501c-e721-4ac1-97ff-dc6887b85e8c");
						// xhrObj.setRequestHeader("X-Target-Environment","sandbox"); 
						// xhrObj.setRequestHeader("Content-Type","application/json");
						// xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key","9cef1ef4936f4f9793dc2adef9379c4a");
					// },
					// type: "POST",
					// // Request body
					// data: 
					// {"amount": amount,
					  // "currency": "UGX", 
					  // "externalId": "e996501c-e721-4ac1-97ff-dc6887b85e8c",
					  // "payer": {
						// "partyIdType": "MSISDN",
						// "partyId": telephone
					  // }
					// }
				// })
				// .done(function(data) {
					// //alert("success "+data);
					 // toaster.pop("success", "", "success "+data, 10000, 'trustedHtml');	
				// })
				// .fail(function(error) {
					// // alert("error");
					// toaster.pop("error", "", "error "+error.code+" message "+error.message, 10000, 'trustedHtml');	
					// console.log('Error - ',error);
				// });
		
		
		$http.post("api/?cmd=makeYopay_request&invoice_id="+invoice_id+"&amount="+amount+"&phone="+telephone)
		.success(function(data)
		{ 
			$scope.data=data;
			
			if(data.status=='ok')
			{
			//$scope.uploadUser(user_id); 
			toaster.pop("success", "", data.message, 10000, 'trustedHtml');	
				
			}
			else if(data.status=='missing')
			{
				toaster.pop("error", "", data.message, 10000, 'trustedHtml');	
			}
			else
			{
				toaster.pop("error", "", data.message, 10000, 'trustedHtml');	
			}
			 

		});
		}
		
			$scope.pay_Thirdparty = function(d)
			{
				var invoice_id = d.invoice_id;
				var policy_no = d.policy_no;
				var total_amount = d.total_amount;
				var payment_method = d.payment_method;
				var user_id = $rootScope.uid; 
				
				
				$http.post("api/?cmd=payThirdparty&invoice_id="+invoice_id+"&total_amount="+total_amount+"&payment_method="+payment_method+"&user_id="+user_id)
				.success(function(data)
				{ 
					
					if(data.status=='ok')
					{
					//$scope.uploadUser(user_id); 
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

app.controller('stickerCont', ['$scope','$rootScope','$modalInstance','$http','invoice_detail_id','toaster','fileUpload',function ($scope,$rootScope, $modalInstance,$http,invoice_detail_id,toaster,fileUpload) {
           
	var invoice_detail_id = invoice_detail_id;
	
	$scope.uploadUser=function(id)
	{ 
	
	 var file = $scope.myFile;
	 var folder = 'users';
        console.log('file is ' );
        console.dir(file);

        var uploadUrl = 'api/?cmd=upload_edited_files&folder_name='+folder+'&id='+id;
        fileUpload.uploadFileToUrl(file, uploadUrl);
	  
	}
	
	$scope.organisation=[]; 
	$http.get("api/?cmd=LoadOrganisation&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(data)
		{
			$scope.organisation=data.results; 

		});
		
		
	$scope.agents=[]; 
	$http.get("api/?cmd=viewThirdPartyAgents&user_id="+$rootScope.uid)
		.success(function(data)
		{
			$scope.agents=data.results; 

		});
	 
	$scope.stickers=[]; 
	$http.get("api/?cmd=viewStickers&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(data)
		{
			$scope.stickers=data.results; 

		});
		
	 
			  
				//console.log('testing',selectedProduct);
			 
			$scope.data = [];
			
		$scope.loadInvoiceDetails = function(invoice_detail_id)
		{		
		$http.get("api/?cmd=viewInvoiceDetails&invoice_detail_id="+invoice_detail_id)
		.success(function(data)
		{
			$scope.invoices=data.results; 
			
			
			for(var i =0; i < $scope.invoices.length; i++)
			{ 
				$scope.data['organ_id']=$scope.invoices[i].organ_id; 
				$scope.data['iclass']=$scope.invoices[i].iclass;
				$scope.data['invoice_id']=$scope.invoices[i].invoice_id;
				$scope.data['policy_no']=$scope.invoices[i].policy_no;
				
				if($scope.data['iclass']=='newimport')
				{
				$scope.data['total_amount']=(($scope.invoices[i].basic_premium *1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
				
					if($scope.data['total_amount'] > 35000)
					{
						$scope.data['total_amount']=(($scope.invoices[i].basic_premium *1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
				
					}else
					{
					$scope.data['total_amount']=35000; 
					
					}	
				}
				else if($scope.data['iclass']=='transit')
				{
					
					$scope.data['total_amount']=(($scope.invoices[i].basic_premium *0.2)+($scope.invoices[i].training_levy*1)+(35000)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*0.2)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
					
					if($scope.data['total_amount'] > 49000)
					{
						$scope.data['total_amount']=(($scope.invoices[i].basic_premium *0.2)+($scope.invoices[i].training_levy*1)+(35000)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*0.2)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
				
					}else
					{
					$scope.data['total_amount']=49000; 
						
					}
				
				}
				else
				{
				$scope.data['total_amount']=(($scope.invoices[i].basic_premium *1)+($scope.invoices[i].training_levy*1)+(35000)+($scope.invoices[i].sticker_fees*1) + ((($scope.invoices[i].basic_premium*1)+($scope.invoices[i].training_levy*1)+($scope.invoices[i].sticker_fees*1))*0.18)); 
					
				}
				
				$scope.data['currency']=$scope.invoices[i].currency; 
				$scope.data['invoice_detail_id']=$scope.invoices[i].invoice_detail_id; 
				$scope.data['vehicle_chasis_no']=$scope.invoices[i].vehicle_chasis_no; 
				$scope.data['vehicle_category']=$scope.invoices[i].vehicle_category; 
				$scope.data['vehicle_plate_no']=$scope.invoices[i].vehicle_plate_no; 
				$scope.data['vehicle_no_seats']=$scope.invoices[i].vehicle_no_seats; 
				$scope.data['gross_weight']=$scope.invoices[i].gross_weight; 
				$scope.data['vehicle_make']=$scope.invoices[i].vehicle_make; 
				$scope.data['start_date']=$scope.invoices[i].start_date; 
				$scope.data['end_date']=$scope.invoices[i].end_date; 
				$scope.data['organ_name']=$scope.invoices[i].organ_name; 
				$scope.data['client_name']=$scope.invoices[i].client_name; 
				$scope.data['agent_details']=$scope.invoices[i].agent_details; 
				
				console.log($scope.data);
			}
			 
			

		});
		
		
		}
		
		$scope.loadInvoiceDetails(invoice_detail_id);
		
		
		$scope.ad_Policy=function(row)
		{
			// var organ_id = row.organ_id;
			var organ_id = 1;
			var policy_no = row.policy_no; 
			
			var user_id = $rootScope.uid;
			
			$http.post('api/?cmd=add_PolciyNo&organ_id='+organ_id+'&user_id='+user_id+'&policy_no='+policy_no)
			.success(function(data)
			{
				if(data.status=='ok')
				{
					toaster.pop('success', "", data.message, 10000, 'trustedHtml');	
					
				}
				else if(data.status=='duplicate')
				{
					toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				}else if(data.status=='missing')
				{
					toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				}
				
			}).finally(function()
		{ 
		$scope.close();	
		});
		}
		
		
		$scope.list = [];
		
		$scope.range= function (start,end)
		{ 
			// var ans = [];
			// for (let i = start; i <= end; i++) {
				// ans.push(i);
			    // }
			// return $scope.list = ans;
				 return $scope.list =new Array((end*1+1) - start).fill().map((d, i) => (start*1+i) );
			  // return $scope.list =(start, end) => new Array(end - start + 1).fill(undefined).map((_, i) => i + start);
			  
		}
		
		 
		
		
		$scope.ad_Sticker=function(row)
		{ 
			var policy_no = row.policy_no;
			var category = row.category;
			
			if(row.agent_user_id=='admin')
			{
				var agent_user_id = $rootScope.uid;	
			}
			else
			{
				var agent_user_id = row.agent_user_id;
			}
			
			
			var user_id = $rootScope.uid;
			
			for(var i =0; i < $scope.list.length; i++)
			{  
		
			$http.post('api/?cmd=add_Sticker&policy_no='+policy_no+'&user_id='+user_id+'&sticker_no='+$scope.list[i]+'&category='+category+'&agent_user_id='+agent_user_id)
			.success(function(data)
			{
				if(data.status=='ok')
				{
					toaster.pop('success', "", data.message, 10000, 'trustedHtml');	
					
				}
				else if(data.status=='duplicate')
				{
					toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				}else if(data.status=='missing')
				{
					toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				}
				
			})
			.finally(function()
		{ 
		$scope.close();	
		});
		
			}
			
			
		}
		
		$scope.replace_stickerNo=function(row)
		{
			var invoice_detail_id = row.invoice_detail_id;
			// var sticker_no = row.sticker_no;
			var charge = row.charge;
			var payment_method = row.payment_method;
			
			var user_id = $rootScope.uid;
			
			$http.post('api/?cmd=replace_Sticker&invoice_detail_id='+invoice_detail_id+'&user_id='+user_id+'&charge='+charge+'&payment_method='+payment_method)
			.success(function(data)
			{
				if(data.status=='ok')
				{
					//$scope.update_stickerNo(row);
					toaster.pop('success', "", data.message, 10000, 'trustedHtml');	
					
				}
				else if(data.status=='duplicate')
				{
					toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				}else if(data.status=='missing')
				{
					toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				}else if(data.status=='error')
				{
					toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				}
				
			}).finally(function()
		{ 
		$scope.close();	
		});
		}
		
		$scope.update_stickerNo=function(row)
		{ 
			var invoice_detail_id = row.invoice_detail_id;  
			var user_id = $rootScope.uid;
			
			$http.post('api/?cmd=update_Sticker&invoice_detail_id='+invoice_detail_id+'&user_id='+$rootScope.uid)
			.success(function(data)
			{
				console.log('row ',row);
				if(data.status=='ok')
				{
					toaster.pop('success', "", data.message, 10000, 'trustedHtml');	
					
				}
				// else if(data.status=='duplicate')
				// {
					// toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				// }else if(data.status=='missing')
				// {
					// toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				// }else if(data.status=='error')
				// {
					// toaster.pop('error', "", data.message, 10000, 'trustedHtml');	
				// }
				
			}).finally(function()
		{ 
		$scope.close();	
		});
		}
		
		
		
			$scope.pay_Thirdparty = function(d)
			{
				var invoice_id = d.invoice_id;
				var policy_no = d.policy_no;
				var total_amount = d.total_amount;
				var payment_method = d.payment_method;
				var user_id = $rootScope.uid; 
				
				$http.post("api/?cmd=payThirdparty&invoice_id="+invoice_id+"&policy_no="+policy_no+"&total_amount="+total_amount+"&payment_method="+payment_method+"&user_id="+user_id)
				.success(function(data)
				{ 
					
					if(data.status=='ok')
					{
					//$scope.uploadUser(user_id); 
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

app.controller('fleetstickerCont', ['$scope','$rootScope','$modalInstance','$http','invoice_id','toaster','fileUpload',function ($scope,$rootScope, $modalInstance,$http,invoice_id,toaster,fileUpload) {
           
     
			  
				//console.log('testing',selectedProduct);
			 
			$scope.data = [];
			
		$scope.loadInvoiceFleetDetails = function(invoice_id)
		{		
		$http.get("api/?cmd=viewInvoiceFleetDetails&invoice_id="+invoice_id)
		.success(function(data)
		{
			$scope.data=data.results; 
			
			  
		});
		
		
		}
		
		$scope.loadInvoiceFleetDetails(invoice_id);
		     
		 
	  $scope.close = function () {
                $modalInstance.dismiss('cancel');
            };
			
		
			
}]);

app.controller('WindscrenController', ['$scope','$rootScope','$modal','$http','toaster','fileUpload','ngTableParams','$filter','SweetAlert',function ($scope,$rootScope, $modal,$http,toaster,fileUpload,ngTableParams,$filter,SweetAlert) {
           
     
	
		$scope.policies_no=[]; 
		$http.get("api/?cmd=viewPolicy_nos&user_id="+$rootScope.uid+"&role="+$rootScope.role)
		.success(function(data)
		{
			$scope.policies_no=data.results; 
			//console.log('pol',$scope.policies_no);
		});
			  
			
	var data=[]; 
	
	$scope.loadWindscreenData=function()
	{
	$http.get("api/?cmd=viewWindscreens&role="+$rootScope.role+"&user_id="+$rootScope.uid)
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
	
	$scope.loadWindscreenData();
	
	
	
	$scope.clients=[];
	
	$http.get("api/?cmd=LoadClients")
		.success(function(data)
		{
			$scope.clients=data.results; 

		});
		
		
		//mtp motor details
  $scope.motor_details2=[];
 
 $scope.addNewChoice2 = function() {
    var newItemNo = $scope.motor_details2.vehicle_registration_no; 
	// $scope.total_valuation += $scope.motor_details.value;
    $scope.motor_details2.push({newItemNo});
  };
    
  $scope.removeChoice2 = function() {
    var lastItem = $scope.motor_details2.length-1;
	// $scope.total_valuation -= $scope.motor_details.value;
    $scope.motor_details2.splice(lastItem);
  };
  
  //comesa motor details
  $scope.motor_details=[];
 
 $scope.addNewChoice = function() {
    var newItemNo = $scope.motor_details.vehicle_registration_no; 
	// $scope.total_valuation += $scope.motor_details.value;
    $scope.motor_details.push({newItemNo});
  };
    
  $scope.removeChoice = function() {
    var lastItem = $scope.motor_details.length-1;
	// $scope.total_valuation -= $scope.motor_details.value;
    $scope.motor_details.splice(lastItem);
  };
  
  
$scope.total_valuation = 0;
$scope.total_rate = 0;
//$scope.total_p = 0;

$scope.getTotal = function(){
    var total = 0; 
	
    for(var i = 0; i < $scope.motor_details.length; i++){
        var product = $scope.motor_details[i];
        total += (product.basic_premium*1);
        
    }
	
	console.log('total - ',total);
	$scope.total_valuation=total; 
    return total;
}  

 
$scope.total_valuation2 = 0; 

$scope.getTotal2 = function(){
    var total = 0; 
    for(var i = 0; i < $scope.motor_details2.length; i++){
        var product2 = $scope.motor_details2[i];
        total += (product2.basic_premium2*1);
        //total_rate += (product.policies1.rate/100);
		//total_p += 1;
    }
	 
	$scope.total_valuation2=total; 
    return total;
}  
 	   
	
 $scope.getInvoice=function(row){ 
		
		$scope.isRefreshing = true;   
		var start_date=row.start_date; 
		var currency=row.currency; 
		var payment_mode = row.payment_mode;
		var payment_ref = row.payment_ref;
		var training_levy=row.total_levy;
		var user_id_charged=$rootScope.uid;
		 
					if(payment_mode=='cash' || payment_mode=='bank')
					{
				
					}
					else
					{
						payment_mode=='mobile_money';
					}
					
					if(payment_ref==='undefined')
					{
						payment_ref=='mobile_money';
					} 
					
					if(row.n_client=='new')
					{
						
						//new client first and sticker last
						
						var name = row.client_name;
						var email = row.client_email;
						var telephone = row.client_telephone; 
						var address = row.client_address;
						var gender = row.gender;
						var dob = row.dob;
						
						$http.post("api/?cmd=addClients&name="+name+"&email="+email+"&telephone="+telephone+"&address="+address+"&gender="+gender+"&dob="+dob+"&user_id="+$rootScope.uid)
						.success(function(data)
						{
							$scope.invoice_id=data.invoice_id;
							var client_id=data.client_id;
								  
								  
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												
												var vehicle_registration_no =(tt.vehicle_registration_no); 
												var vehicle_make =(tt.vehicle_make); 
												var vehicle_chasis_no =(tt.vehicle_chasis_no); 
												var policy_no=tt.policy_no;
												var windscreen_valuation=tt.windscreen_valuation;
												var premium_charged=(windscreen_valuation*0.1);
												var training_levy=(premium_charged*0.05); 
												var amount_paid=(premium_charged*1)+(35000)+(training_levy*1); 
												 
												
													$http.post("api/?cmd=addWindscreenPolicy&vehicle_registration_no="+vehicle_registration_no+"&client_id="+client_id+"&policy_no="+policy_no+"&windscreen_valuation="+windscreen_valuation+"&premium_charged="+premium_charged+"&training_levy="+training_levy+"&user_id_charged="+user_id_charged+"&vehicle_make="+vehicle_make+"&vehicle_chasis_no="+vehicle_chasis_no+"&payment_ref="+payment_ref+"&payment_mode="+payment_mode+"&amount_paid="+amount_paid+"&currency="+currency+"&start_date="+start_date)
													.success(function(data2)
													{
														var d=data2;
													});	
										
											}
									 
									toaster.pop('success', 'Windscreen Successful', d.message);
									// $state.go('app.loadinvoice', {invoice_id:$scope.invoice_id});
									 
							
							
						})
						.finally(function()
						{
							$scope.isRefreshing = false;
							$scope.data="";
						});
						
						
						
					}
					else
					{ 
				
						var client_id=row.client.client_id;
						 
									 
										 
											var ii=0; 
											for(ii;$scope.motor_details.length > ii; ii++)
											{ 
												
												var tt=$scope.motor_details[ii];
												
												var vehicle_registration_no =(tt.vehicle_registration_no); 
												var vehicle_make =(tt.vehicle_make); 
												var vehicle_chasis_no =(tt.vehicle_chasis_no); 
												var policy_no=tt.policy_no;
												var windscreen_valuation=tt.windscreen_valuation;
												var premium_charged=(windscreen_valuation*0.1);
												var training_levy=(premium_charged*0.05); 
												var amount_paid=(premium_charged*1)+(35000)+(training_levy*1); 
												 
												
													$http.post("api/?cmd=addWindscreenPolicy&vehicle_registration_no="+vehicle_registration_no+"&client_id="+client_id+"&policy_no="+policy_no+"&windscreen_valuation="+windscreen_valuation+"&premium_charged="+premium_charged+"&training_levy="+training_levy+"&user_id_charged="+user_id_charged+"&vehicle_make="+vehicle_make+"&vehicle_chasis_no="+vehicle_chasis_no+"&payment_ref="+payment_ref+"&payment_mode="+payment_mode+"&amount_paid="+amount_paid+"&currency="+currency+"&start_date="+start_date)
													.success(function(data2)
													{
														var d=data2;
													});	
												
													 
										
											}
									 
									toaster.pop('success', 'Windscreen Successful', data.message);
									// $state.go('app.loadinvoice', {invoice_id:$scope.invoice_id});
									 
								  
								 
					}
			}  
		 

		
			
}]);

app.controller('StickerController', ['$scope', '$rootScope', '$http', '$timeout','$stateParams','$state','toaster', '$filter','$modal','$interval','SweetAlert', 'ngTableParams', function($scope, $rootScope, $http, $timeout,$stateParams,$state,toaster, $filter,$modal,$interval,SweetAlert, ngTableParams) {
  'use strict';
  
	   
	var data=[]; 
	
	$scope.loadStickerData=function()
	{
	$http.get("api/?cmd=viewStickers&role="+$rootScope.role+"&user_id="+$rootScope.uid)
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
	
	$scope.loadStickerData();
	// $interval($scope.loadStickerData,500);
	  
	$scope.stickernos={};
	$scope.loadStickerNos=function()
	{
	$http.get("api/?cmd=viewUIAStickerNos")
		.success(function(res)
		{
			$scope.stickernos=res.results; 
			 

		});
		
	}
	
	$scope.loadStickerNos();
	$interval($scope.loadStickerNos,500);
	  
	 
	$scope.add_Sticker=function(invoice_detail_id)
	{
		 var modalInstance = $modal.open({
						controller: 'stickerCont',
						backdrop: "static",
						templateUrl: 'assets/views/addSticker.html',
						size: '20%',
		resolve: {
		  invoice_detail_id: function () { return invoice_detail_id;    }
		}
		});
		
	}
	
$scope.deleteSticker=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this Policy!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=delete_invoice&invoice_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'Sticker details successfully removed', 10000, 'trustedHtml'); 	
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'Sticker details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'Sticker details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Deleted!", "Sticker details has been deleted.", "success");
            } else {
                SweetAlert.swal("Cancelled", "Sticker details is safe :)", "error");
            }
        });
		
	
	}	

		 

}]);

app.controller('PolicyNoController', ['$scope', '$rootScope', '$http', '$timeout','$stateParams','$state','toaster', '$filter','$modal','$interval','SweetAlert', 'ngTableParams', function($scope, $rootScope, $http, $timeout,$stateParams,$state,toaster, $filter,$modal,$interval,SweetAlert, ngTableParams) {
  'use strict';
  
	  
	
	var data=[]; 
	
	$scope.loadPolicyData=function()
	{
	$http.get("api/?cmd=viewPolicy_nos&role="+$rootScope.role+"&user_id="+$rootScope.uid)
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
	
	$scope.loadPolicyData();
	// $interval($scope.loadPolicyData,500);
	 
	$scope.clients=[];
	
	$http.get("api/?cmd=LoadClients")
		.success(function(data)
		{
			$scope.clients=data.results; 

		});
		
	 
	 
	$scope.add_Policy=function(invoice_detail_id)
	{
		 var modalInstance = $modal.open({
						controller: 'stickerCont',
						backdrop: "static",
						templateUrl: 'assets/views/addPolicy.html',
						size: '20%',
		resolve: {
		  invoice_detail_id: function () { return invoice_detail_id;    }
		}
		});
		
	}
	
$scope.deleteSticker=function(d)
		{
	 
		var ID=d;
		
		SweetAlert.swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this Policy!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel plx!",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                 
						$http.post('api/?cmd=delete_invoice&invoice_id='+ID)
						 .success(function(data)
						 {
							if(data.status=='ok')
								{  
								
					toaster.pop('success', "", 'Sticker details successfully removed', 10000, 'trustedHtml'); 	
					 
								}
							   else if(data.status=='empty' || data.status=='missing')
								   { 
					toaster.pop('error', "", 'Sticker details missing fields', 10000, 'trustedHtml'); 				
								   }
						 })
						.error(function(data,status)
										 {
											toaster.pop('error', "", 'Sticker details not removed', 10000, 'trustedHtml');  
						   }); 
	   
                SweetAlert.swal("Deleted!", "Sticker details has been deleted.", "success");
            } else {
                SweetAlert.swal("Cancelled", "Sticker details is safe :)", "error");
            }
        });
		
	
	}	

		 

}]);

