
'use strict';
/**
 * controllers for ng-table
 * Simple table with sorting and filtering on AngularJS
 */

 // var dataUrl='http://localhost/visitkampala/api/'; 
 
 
 
app.controller('ReportController', ["$scope", "$filter","$state","$http","$rootScope","$interval","$timeout", "ngTableParams","toaster","SweetAlert","Data","$modal", function ($scope, $filter,$state,$http,$rootScope,$interval,$timeout, ngTableParams,toaster,SweetAlert,Data,$modal) {
  
    // $scope.data.start="";
    // $scope.data.end="";
	
	 // Chart.js Data
     // Chart.js Data
    $scope.data = [
      {
          value: 300,
          color: '#F7464A',
          highlight: '#FF5A5E',
          label: 'Red'
      },
      {
          value: 50,
          color: '#46BFBD',
          highlight: '#5AD3D1',
          label: 'Green'
      },
      {
          value: 100,
          color: '#FDB45C',
          highlight: '#FFC870',
          label: 'Yellow'
      }
    ];

    // Chart.js Options
    $scope.options = {

        // Sets the chart to be responsive
        responsive: false,

        //Boolean - Whether we should show a stroke on each segment
        segmentShowStroke: true,

        //String - The colour of each segment stroke
        segmentStrokeColor: '#fff',

        //Number - The width of each segment stroke
        segmentStrokeWidth: 2,

        //Number - The percentage of the chart that we cut out of the middle
        percentageInnerCutout: 0, // This is 0 for Pie charts

        //Number - Amount of animation steps
        animationSteps: 100,

        //String - Animation easing effect
        animationEasing: 'easeOutBounce',

        //Boolean - Whether we animate the rotation of the Doughnut
        animateRotate: true,

        //Boolean - Whether we animate scaling the Doughnut from the centre
        animateScale: false,

        //String - A legend template
        legendTemplate: '<ul class="tc-chart-js-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'

    };
	
	
	
		
	
	$scope.revenues=[]; 
	$http.get("api/?cmd=RevenueReports")
		.success(function(res)
		{
			$scope.revenues=res.results; 
			console.log($scope.revenues) ;
		});
		
	

 
	
	
	
}]);
  
app.controller('DailyReportsCtrl',['$scope','$rootScope','$http','$state','Data','toaster','$filter', function($scope,$rootScope,$http,$state,Data,toaster,$filter){
 		

	
 $scope.monthly_reports = {};

 $scope.loadMonthlyReports=function(row){
		
try
{	 
	 var start =$filter('date')(row.start, "yyyy-MM-dd");
	 var end =$filter('date')(row.end, "yyyy-MM-dd");
	 
		//console.log('$scope.monthly_reports ',row);
	 if(typeof start ==='undefined')
		{
			start ='';
		}
	 if(typeof end ==='undefined')
		{
			end ='';
		}
	 
		var Mytable=start;
		var MyAttributes=end;
		var MyValues="";
		var MyWhere=""; 
		var Messagein=""; 
		
	 $http.get("api/?cmd=viewMonthlyReports&Mytable="+Mytable+"&MyAttributes="+MyAttributes+"&MyValues="+MyValues+"&MyWhere="+MyWhere+"&Messagein="+Messagein)
     .success(function(res)
     {
		 //Data.toast(res);
        $scope.monthly_reports = res.results;   
		console.log('$scope.monthly_reports ',res.results);
					
        });
		} 		
catch(err) {
	
	Data.toast({status:"error",message:err.message+" contact administrator"});
 // err.message;
}
 
 }; 
 
	$scope.mtp=[];

 $scope.loadDailyMTP =function(d)
 { 
 
 // $scope.data.date =  
 	
	var date = $filter('date')(d.date, "yyyy-MM-dd");
	 
		
			//console.log('daily mtp reports -- ',date) ;	
	try
	{	//console.log('$scope.monthly_reports ',row);
		if(typeof date ==='undefined')
		{
			date ='';
		}
		
		$http.get("api/?cmd=MtpReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date="+date)
		.success(function(res)
		{
			$scope.mtp=res.results; 
			// toaster.pop('error', 'ERROR', "No daily reports generated on "+d);
			if(res.status=='empty' || $scope.mtp=='undefined')
			{
				toaster.pop('error', 'ERROR', "No daily reports generated on "+d.date);
			}
		});

		} 		
	catch(err) {
		
		Data.toast({status:"error",message:err.message+" contact administrator"});
	 // err.message;
	}

};

$scope.dreports=[];

 $scope.loadDailyReports =function(d)
 { 
 
 // $scope.data.date =  
 	
	var date = $filter('date')(d.date, "yyyy-MM-dd");
	var iclass =(d.iclass);
	 
		
			//console.log('daily mtp reports -- ',date) ;	
	try
	{	//console.log('$scope.monthly_reports ',row);
		if(typeof date ==='undefined')
		{
			date ='';
		}
		if(typeof iclass ==='undefined')
		{
			iclass ='';
		}
		
		$http.get("api/?cmd=DailyReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date="+date+"&iclass="+iclass)
		.success(function(res)
		{
			$scope.dreports=res.results; 
			// toaster.pop('error', 'ERROR', "No daily reports generated on "+d);
			if(res.status=='empty' || $scope.dreports=='undefined')
			{
				toaster.pop('error', 'ERROR', "No daily reports generated on "+d.date);
			}
		});

		} 		
	catch(err) {
		
		Data.toast({status:"error",message:err.message+" contact administrator"});
	 // err.message;
	}

};


	$scope.comprehensive=[];
	
 $scope.loadDailyComp =function(d)
 { 
 
	var date =$filter('date')(d.date2, "yyyy-MM-dd");
	 
			
	try
	{	//console.log('$scope.monthly_reports ',row);
		if(typeof date ==='undefined')
		{
			date ='';
		}
			
		$http.get("api/?cmd=ComprehensiveReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date="+date)
		.success(function(res)
		{
			$scope.comprehensive=res.results; 
			console.log($scope.comprehensive) ;
			if(res.status=='empty' || $scope.comprehensive=='undefined')
			{
				toaster.pop('error', 'ERROR', "No daily reports generated on "+d.date2);
			}
		});	
		
		} 		
		catch(err) {
			
			Data.toast({status:"error",message:err.message+" contact administrator"});
		 // err.message;
		}
 }

  			
}]);

app.controller('WeeklyReportsCtrl',['$scope','$rootScope','$http','$state','Data','toaster','$filter', function($scope,$rootScope,$http,$state,Data,toaster,$filter){
 		
 
 $scope.weekly_mtp=[]; 
 $scope.load_mtpreport=function(row)
		{
			var date_from =$filter('date')(row.start, "yyyy-MM-dd");
			var date_to =$filter('date')(row.end, "yyyy-MM-dd");
			var iclass =(row.iclass);
			
			if(typeof date_from ==='undefined')
			{
				date_from ='';
			}
			if(typeof date_to ==='undefined')
			{
				date_to ='';
			}
			$http.get("api/?cmd=WeeklyMtpReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date_from="+date_from+"&date_to="+date_to+"&iclass="+iclass)
			.success(function(res)
			{
				$scope.weekly_mtp=res.results;  
			
				if(res.status=='empty' || $scope.weekly_mtp=='undefined')
				{
					toaster.pop('error', 'ERROR', "No weekly reports generated");
				}
				
			});
		}
	  	
  	
	
		$scope.weekly_comprehensive=[]; 
		$scope.load_comprehensive_report=function(row)
		{
			var date_from =$filter('date')(row.start, "yyyy-MM-dd");
			var date_to =$filter('date')(row.end, "yyyy-MM-dd");
			
			if(typeof date_from ==='undefined')
			{
				date_from ='';
			}
			if(typeof date_to ==='undefined')
			{
				date_to ='';
			}
			$http.get("api/?cmd=WeeklyComprehensiveReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date_from="+date_from+"&date_to="+date_to)
			.success(function(res)
			{
				$scope.weekly_comprehensive=res.results; 
				console.log($scope.weekly_comprehensive) ;
				
				if(res.status=='empty' || $scope.weekly_comprehensive=='undefined')
				{
					toaster.pop('error', 'ERROR', "No weekly reports generated");
				}
			});
		} 
	  
  
  
  			
}]);
app.controller('MonthlyReportsCtrl',['$scope','$rootScope','$http','$state','Data','toaster','$filter', function($scope,$rootScope,$http,$state,Data,toaster,$filter){
 		
 
  $scope.weekly_mtp=[]; 
 $scope.load_mtpreport=function(row)
		{
			var date_from =$filter('date')(row.start, "yyyy-MM-dd");
			var date_to =$filter('date')(row.end, "yyyy-MM-dd");
			var iclass =(row.iclass);
			
			if(typeof date_from ==='undefined')
			{
				date_from ='';
			}
			if(typeof date_to ==='undefined')
			{
				date_to ='';
			}
			if(typeof iclass ==='undefined')
			{
				iclass ='';
			}
			$http.get("api/?cmd=WeeklyMtpReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date_from="+date_from+"&date_to="+date_to+"&iclass="+iclass)
			.success(function(res)
			{
				$scope.weekly_mtp=res.results;  
			
				if(res.status=='empty' || $scope.weekly_mtp=='undefined')
				{
					toaster.pop('error', 'ERROR', "No weekly reports generated");
				}
				
			});
		}
	  	
  	
	
		$scope.weekly_comprehensive=[]; 
		$scope.load_comprehensive_report=function(row)
		{
			var date_from =$filter('date')(row.start, "yyyy-MM-dd");
			var date_to =$filter('date')(row.end, "yyyy-MM-dd");
			
			if(typeof date_from ==='undefined')
			{
				date_from ='';
			}
			if(typeof date_to ==='undefined')
			{
				date_to ='';
			}
			$http.get("api/?cmd=WeeklyComprehensiveReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date_from="+date_from+"&date_to="+date_to)
			.success(function(res)
			{
				$scope.weekly_comprehensive=res.results; 
				console.log($scope.weekly_comprehensive) ;
				
				if(res.status=='empty' || $scope.weekly_comprehensive=='undefined')
				{
					toaster.pop('error', 'ERROR', "No weekly reports generated");
				}
			});
		} 
	  
  
  
  			
}]);

app.controller('MonthlyRevenueReportsCtrl',['$scope','$rootScope','$http','$state','Data','toaster','$filter', function($scope,$rootScope,$http,$state,Data,toaster,$filter){
 		
 
  $scope.revenues=[]; 
 $scope.load_monthly_revenue_report=function(row)
		{
			var date_from =$filter('date')(row.start, "yyyy-MM-dd");
			var date_to =$filter('date')(row.end, "yyyy-MM-dd");
			
			if(typeof date_from ==='undefined')
			{
				date_from ='';
			}
			if(typeof date_to ==='undefined')
			{
				date_to ='';
			}
			$http.get("api/?cmd=MonthlyRevenueReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date_from="+date_from+"&date_to="+date_to)
			.success(function(res)
			{
				$scope.revenues=res.results;  
			
				if(res.status=='empty' || $scope.revenues=='undefined')
				{
					toaster.pop('error', 'ERROR', "No Monthly revenue report generated");
				}
				
			});
		}
	  	
  	 
	
	// $scope.revenues=[]; 
	// $http.get("api/?cmd=RevenueReports")
		// .success(function(res)
		// {
			// $scope.revenues=res.results; 
			// console.log($scope.revenues) ;
		// });
		
  			
}]);

app.controller('AgentStickerReportsCtrl',['$scope','$rootScope','$http','$state','Data','toaster','$filter', function($scope,$rootScope,$http,$state,Data,toaster,$filter){
 		
 
  $scope.agentstickers=[]; 
 $scope.load_agent_report=function(row)
		{
			var date_from =$filter('date')(row.start, "yyyy-MM-dd");
			var date_to =$filter('date')(row.end, "yyyy-MM-dd");
			
			if(typeof date_from ==='undefined')
			{
				date_from ='';
			}
			if(typeof date_to ==='undefined')
			{
				date_to ='';
			}
			$http.get("api/?cmd=viewUserStickers&user_id="+$rootScope.uid+"&date_from="+date_from+"&date_to="+date_to)
			.success(function(res)
			{
				$scope.agentstickers=res.results;  
			
				if(res.status=='empty' || $scope.agentstickers=='undefined')
				{
					toaster.pop('error', 'ERROR', "No agent sticker reports generated");
				}
				
			});
		}
	  	
  	 
	
	// $scope.revenues=[]; 
	// $http.get("api/?cmd=RevenueReports")
		// .success(function(res)
		// {
			// $scope.revenues=res.results; 
			// console.log($scope.revenues) ;
		// });
		
  			
}]);

app.controller('DailyRevenueReportsCtrl',['$scope','$rootScope','$http','$state','Data','toaster','$filter', function($scope,$rootScope,$http,$state,Data,toaster,$filter){
 		
 
  $scope.dailyrevenues=[]; 
 $scope.load_daily_revenue_report=function(row)
		{  
			var date =$filter('date')(row.date, "yyyy-MM-dd"); 
			
			if(typeof date ==='undefined')
			{
				date ='';
			} 
			$http.get("api/?cmd=DailyRevenueReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&date="+date)
			.success(function(res)
			{
				$scope.dailyrevenues=res.results;  
			
				if(res.status=='empty' || $scope.dailyrevenues=='undefined')
				{
					toaster.pop('error', 'ERROR', "No Monthly revenue report generated");
				}
				
			});
		}
	  	 
  			
}]);

app.controller('StatusReportsCtrl',['$scope','$rootScope','$http','$state','Data','toaster', function($scope,$rootScope,$http,$state,Data,toaster){
 		
 
	 $scope.status_mtp=[]; 
	
	$scope.loadStatusReports=function(s,c)
	{
	$http.get("api/?cmd=statusStickerReports&role="+$rootScope.role+"&user_id="+$rootScope.uid+"&class="+c+"&status="+s)
		.success(function(res)
		{
			$scope.status_mtp=res.results; 
			console.log($scope.status_mtp) ;
			if(res.status=='empty' || $scope.status_mtp=='undefined')
			{
				toaster.pop('error', 'ERROR', "No status reports generated");
			}
		});
		
	}
		
  
  			
}]); 
 