var app = angular.module('clipApp', ['clip-two','toaster']);
app.run(['$rootScope', '$state', '$stateParams','Data','$http','$timeout','$templateCache',
function ($rootScope, $state, $stateParams,Data,$http,$timeout,$templateCache) {
	
	
	$rootScope.$on("$stateChangeStart", function (event, toState, toParams, fromState, fromParams) {
		
		
		$rootScope.$on('$stateChangeStart', function(event, next, current) {
			if (typeof(current) !== 'undefined'){
				$templateCache.remove(current.templateUrl);
			}
		});
		
		
            $rootScope.authenticated = false;
			
            Data.get("setsession").then(function (results) {
                if (results.user_id) {
                    $rootScope.user = results;
                    $rootScope.authenticated = true;
                    $rootScope.uid = results.user_id;
                    $rootScope.name = results.name;
                    $rootScope.email = results.email;
                    $rootScope.telephone = results.telephone;
                    $rootScope.role = results.role;
                    $rootScope.license_no = results.license_no;
                    $rootScope.NIN = results.NIN;
                    $rootScope.address = results.address;
                    $rootScope.gender = results.gender;
                    $rootScope.dob = results.dob;
                    $rootScope.branch_name = results.branch_name;
                    $rootScope.img = results.img;
					 
					$http.get('api/?cmd=getUserDashboard&user_id='+$rootScope.uid+'&role='+$rootScope.role)
					.success(function (data)
					{
						$rootScope.clients = data.clients;
						$rootScope.quotes = data.quotes;
						$rootScope.policies = data.policies;
						$rootScope.party = data.party;
						$rootScope.expectrevenue = data.expectrevenue;
						$rootScope.actualrevenue = data.actualrevenue;
						$rootScope.actualrevenue = data.actualrevenue;
						$rootScope.commission = data.commission;
						$rootScope.revenue = data.revenue;
						$rootScope.claim_notification = data.claim_notification;
						$rootScope.claim_settlement = data.claim_settlement;
					});
					
					$rootScope.motor_bike=[];
					$rootScope.motor_transit=[];
					$rootScope.motor_private=[]; 
					$rootScope.motor_commercial=[];
					 
					$http.get("api/?cmd=viewStickerNoBal")
						.success(function(res)
						{
							$rootScope.motor_bike=res.motor_bike;
							$rootScope.motor_transit=res.motor_transit;
							$rootScope.motor_private=res.motor_private;
							$rootScope.motor_commercial=res.motor_commercial;
							
								  
						});
					 
					
					$rootScope.Notifications ={};
	  
					  $rootScope.loadNotifications =function()
					  {
						 $http.get("api/?cmd=viewNotifications&user_id="+$rootScope.uid+"&role="+$rootScope.role)
							.success(function (data) {
								
								$rootScope.Notifications=data.results;
								 
					 console.log('$rootScope.Notifications',$rootScope.Notifications);
							});  
						  
					  }
				  
				  $rootScope.loadNotifications();
					
					 console.log('starting run');

					// Timeout timer value
					var TimeOutTimerValue = 600000;

					// Start a timeout
					var TimeOut_Thread = $timeout(function(){ LogoutByTimer() } , TimeOutTimerValue);
					var bodyElement = angular.element(document);
					// angular.element(document) 
					angular.forEach(['keydown', 'keyup', 'click', 'mousemove', 'DOMMouseScroll', 'mousewheel', 'mousedown', 'touchstart', 'touchmove', 'scroll', 'focus'], 
					function(EventName) {
						 bodyElement.bind(EventName, function (e) { TimeOut_Resetter(e) });  
					});

					function LogoutByTimer(){
						console.log('Logout');
						///////////////////////////////////////////////////
						/// redirect to another page(eg. Login.html) here
						///////////////////////////////////////////////////
						
						$http.get('api/?cmd=logout')
						.success(function (results) {
							//Data.toast(results);
							//toaster.pop('success', 'Log Out', results.message);
							$state.go('login.signin');
						});
					}

					function TimeOut_Resetter(e){
						console.log(' ' + e);

						/// Stop the pending timeout
						$timeout.cancel(TimeOut_Thread);

						/// Reset the timeout
						TimeOut_Thread = $timeout(function(){ LogoutByTimer() } , TimeOutTimerValue);
					}
					
                } else {
                    
					 if (toState.name == 'login.signin' || toState.name == 'login.registration' || toState.name == 'login.forgot') {
						//$location.path("/Login");
                    } else {
                        $state.go("login.signin");
                    }
					
					
                }
            });
        });

    // Attach Fastclick for eliminating the 300ms delay between a physical tap and the firing of a click event on mobile browsers
    FastClick.attach(document.body);

    // Set some reference to access them from any scope
    $rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;

    // GLOBAL APP SCOPE
    // set below basic information
    $rootScope.app = {
        name: 'Britam', // name of your project
        author: 'Clear Basics', // author's name or company name
        description: 'With you every step of the way', // brief description
        version: '1.0', // current version
        year: ((new Date()).getFullYear()), // automatic current year (for copyright information)
        isMobile: (function () {// true if the browser is a mobile device
            var check = false;
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                check = true;
            };
            return check;
        })(),
        layout: {
            isNavbarFixed: true, //true if you want to initialize the template with fixed header
            isSidebarFixed: true, // true if you want to initialize the template with fixed sidebar
            isSidebarClosed: false, // true if you want to initialize the template with closed sidebar
            isFooterFixed: false, // true if you want to initialize the template with fixed footer
            theme: 'theme-1', // indicate the theme chosen for your project
            logo: 'assets/images/logo-britam.png', // relative path of the project logo
        }
    };
    $rootScope.user = {
        name: 'Peter',
        job: 'ng-Dev',
        picture: 'app/img/user/02.jpg'
    };
}]);
// translate config
app.config(['$translateProvider',
function ($translateProvider) {

    // prefix and suffix information  is required to specify a pattern
    // You can simply use the static-files loader with this pattern:
    $translateProvider.useStaticFilesLoader({
        prefix: 'assets/i18n/',
        suffix: '.json'
    });

    // Since you've now registered more then one translation table, angular-translate has to know which one to use.
    // This is where preferredLanguage(langKey) comes in.
    $translateProvider.preferredLanguage('en');

    // Store the language in the local storage
    $translateProvider.useLocalStorage();

}]);
// Angular-Loading-Bar
// configuration
app.config(['cfpLoadingBarProvider',
function (cfpLoadingBarProvider) {
    cfpLoadingBarProvider.includeBar = true;
    cfpLoadingBarProvider.includeSpinner = false;

}]);
 
