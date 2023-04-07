'use strict';

/**
 * Config for the router
 */
app.config(['$stateProvider', '$urlRouterProvider', '$controllerProvider', '$compileProvider', '$filterProvider', '$provide', '$ocLazyLoadProvider', 'JS_REQUIRES',
function ($stateProvider, $urlRouterProvider, $controllerProvider, $compileProvider, $filterProvider, $provide, $ocLazyLoadProvider, jsRequires) {

    app.controller = $controllerProvider.register;
    app.directive = $compileProvider.directive;
    app.filter = $filterProvider.register;
    app.factory = $provide.factory;
    app.service = $provide.service;
    app.constant = $provide.constant;
    app.value = $provide.value;

    // LAZY MODULES

    $ocLazyLoadProvider.config({
        debug: false,
        events: true,
        modules: jsRequires.modules
    });

    // APPLICATION ROUTES
    // -----------------------------------
    // For any unmatched url, redirect to /app/dashboard
    $urlRouterProvider.otherwise("login/signin");
    //
    // Set up the states
    $stateProvider.state('app', {
        url: "/app",
        templateUrl: "assets/views/app.html",
        resolve: loadSequence('modernizr', 'moment', 'angularMoment', 'uiSwitch', 'perfect-scrollbar-plugin', 'toaster', 'ngAside', 'vAccordion', 'sweet-alert', 'chartjs', 'tc.chartjs', 'oitozero.ngSweetAlert', 'chatCtrl'),
        abstract: true
    }).state('app.dashboard', {
        url: "/dashboard",
        templateUrl: "assets/views/dashboard.html",
        resolve: loadSequence('jquery-sparkline', 'dashboardCtrl'),
        title: 'Dashboard',
        ncyBreadcrumb: {
            label: 'Dashboard'
        }
    }).state('app.organisation', {
        url: '/organisation',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Company Profile',
        ncyBreadcrumb: {
            label: 'Company Profile'
        }
    }).state('app.organisation.vieworgan', {
        url: '/vieworgan',
        templateUrl: "assets/views/organisation.html",
        title: 'View Company Profile',
        icon: 'ti-layout-media-left-alt',
        resolve: loadSequence('ngTable','modernizr', 'toaster','oitozero.ngSweetAlert', 'organisationCtrl'),
        ncyBreadcrumb: {
            label: 'View Company Profile'
        }
    }).state('app.clients', {
        url: '/clients',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Client Management',
        ncyBreadcrumb: {
            label: 'Client Management'
        }
    }).state('app.clients.viewclients', {
        url: '/viewclients',
        templateUrl: "assets/views/clients.html",
        title: 'Client List',
        resolve: loadSequence('ngTable','modernizr', 'toaster','oitozero.ngSweetAlert', 'clientCtrl'),
        ncyBreadcrumb: {
            label: 'Client List'
        }
    }).state('app.clients.addclients', {
        url: '/addclients',
        templateUrl: "assets/views/new_client.html",
        title: 'Add Client',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','clientCtrl'),
        ncyBreadcrumb: {
            label: 'Add Client'
        }
    }).state('app.stickernos', {
        url: '/stickernos',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Sticker No Management',
        ncyBreadcrumb: {
            label: 'Sticker No Management'
        }
    }).state('app.stickernos.viewstickernos', {
        url: '/viewstickernos',
        templateUrl: "assets/views/sticker_no_lists.html",
        title: 'Sticker No List',
        resolve: loadSequence('ngTable','modernizr', 'toaster','oitozero.ngSweetAlert', 'stickerNoCtrl'),
        ncyBreadcrumb: {
            label: 'Sticker No List'
        }
    }).state('app.stickernos.addstickernos', {
        url: '/addstickernos',
        templateUrl: "assets/views/new_sticker_nos.html",
        title: 'Add Sticker Nos',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerNoCtrl'),
        ncyBreadcrumb: {
            label: 'Add Sticker Nos'
        }
    }).state('app.stickers', {
        url: '/stickers',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Thirdparty Management',
        ncyBreadcrumb: {
            label: 'Thirdparty Management'
        }
    }).state('app.stickers.viewstickers', {
        url: '/viewstickers',
        templateUrl: "assets/views/stickers.html",
        title: 'Sticker Lists',
        resolve: loadSequence('ngTable','modernizr', 'toaster','oitozero.ngSweetAlert', 'stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Sticker Lists'
        }
    }).state('app.stickers.addstickers', {
        url: '/addstickers',
        templateUrl: "assets/views/new_sticker.html",
        title: 'Add new sticker',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Add new sticker'
        }
    }).state('app.stickers.loadinvoice', {
        url: '/loadinvoice/{invoice_id}',
        templateUrl: "assets/views/load_sticker.html",
        title: 'Load Sticker Details',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Load Sticker Details'
        }
    }).state('app.stickers.renewinvoice', {
        url: '/renewinvoice/{invoice_id}',
        templateUrl: "assets/views/renew_sticker.html",
        title: 'Renew Sticker Details',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Renew Sticker Details'
        }
    }).state('app.stickers.viewwindscreen', {
        url: '/viewwindscreen',
        templateUrl: "assets/views/windscreen.html",
        title: 'Windscreen Policy Lists',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Windscreen Policy Lists'
        }
    }).state('app.stickers.addwindscreen', {
        url: '/addwindscreen',
        templateUrl: "assets/views/new_windscreen.html",
        title: 'Add Windscreen Policy',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Add Windscreen Policy'
        }
    }).state('app.stickers.viewstickerno', {
        url: '/viewstickerno',
        templateUrl: "assets/views/sticker_no.html",
        title: 'Policy & Sticker No',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Policy & Sticker No'
        }
    }).state('app.stickers.viewpolicyno', {
        url: '/viewpolicyno',
        templateUrl: "assets/views/policy_no.html",
        title: 'Policy No',
        resolve: loadSequence('ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster','stickerCtrl'),
        ncyBreadcrumb: {
            label: 'Policy No'
        }
    }).state('app.settings', {
        url: '/settings',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Account Settings',
        ncyBreadcrumb: {
            label: 'Account Settings'
        }
    }).state('app.settings.user', {
        url: '/user',
        templateUrl: "assets/views/pages_user_profile.html",
        title: 'User Profile',
        ncyBreadcrumb: {
            label: 'User Profile'
        },
        resolve: loadSequence('flow','angularFileUpload','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster',  'userCtrl')
    }).state('app.pages', {
        url: '/pages',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Pages',
        ncyBreadcrumb: {
            label: 'Pages'
        }
    }).state('app.pages.user', {
        url: '/user',
        templateUrl: "assets/views/pages_user_profile.html",
        title: 'User Profile',
        ncyBreadcrumb: {
            label: 'User Profile'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster',  'userCtrl')
    }).state('app.pages.viewusers', {
        url: '/viewusers',
        templateUrl: "assets/views/view_users.html",
        title: 'User Lists',
        ncyBreadcrumb: {
            label: 'User Lists'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'userCtrl')
    }).state('app.pages.addusers', {
        url: '/addusers',
        templateUrl: "assets/views/new_users.html",
        title: 'Add User Details',
        ncyBreadcrumb: {
            label: 'Add User Details'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'userCtrl')
    }).state('app.pages.viewagents', {
        url: '/viewagents',
        templateUrl: "assets/views/view_agents.html",
        title: 'Agent Lists',
        ncyBreadcrumb: {
            label: 'Agent Lists'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'userCtrl')
    }).state('app.pages.viewlogtrail', {
        url: '/viewlogtrail',
        templateUrl: "assets/views/view_logtrail.html",
        title: 'System Logtrails',
        ncyBreadcrumb: {
            label: 'System Logtrails'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'userCtrl')
    }).state('app.pages.invoice', {
        url: '/invoice',
        templateUrl: "assets/views/pages_invoice.html",
        title: 'Invoice',
        ncyBreadcrumb: {
            label: 'Invoice'
        }
    }).state('app.pages.timeline', {
        url: '/timeline',
        templateUrl: "assets/views/pages_timeline.html",
        title: 'Timeline',
        ncyBreadcrumb: {
            label: 'Timeline'
        },
        resolve: loadSequence('ngMap')
    }).state('app.pages.calendar', {
        url: '/calendar',
        templateUrl: "assets/views/pages_calendar.html",
        title: 'Calendar',
        ncyBreadcrumb: {
            label: 'Calendar'
        },
        resolve: loadSequence('moment', 'mwl.calendar', 'calendarCtrl')
    }).state('app.pages.messages', {
        url: '/messages',
        templateUrl: "assets/views/pages_messages.html",
        resolve: loadSequence('truncate', 'htmlToPlaintext', 'inboxCtrl')
    }).state('app.pages.messages.inbox', {
        url: '/inbox/:inboxID',
        templateUrl: "assets/views/pages_inbox.html",
        controller: 'ViewMessageCrtl'
    }).state('app.pages.blank', {
        url: '/blank',
        templateUrl: "assets/views/pages_blank_page.html",
        ncyBreadcrumb: {
            label: 'Starter Page'
        }
    }).state('app.claims', {
        url: '/claims',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Claim Management',
        ncyBreadcrumb: {
            label: 'Claim Management'
        }
    }).state('app.claims.viewclaims', {
        url: '/viewclaims',
        templateUrl: "assets/views/claims.html",
        title: 'Claim Notifications',
        ncyBreadcrumb: {
            label: 'Claim Notifications'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'claimsCtrl')
    }).state('app.claims.addclaims', {
        url: '/addclaims',
        templateUrl: "assets/views/new_claim.html",
        title: 'Add Claim Notifications',
        ncyBreadcrumb: {
            label: 'Add Claim Notifications'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'claimsCtrl')
    }).state('app.reports', {
        url: '/reports',
        template: '<div ui-view class="fade-in-up"></div>',
        title: 'Generate Reports',
        ncyBreadcrumb: {
            label: 'Generate Reports'
        }
    }).state('app.reports.dailyreports', {
        url: '/dailyreports',
        templateUrl: "assets/views/daily_reports.html",
        title: 'Daily Reports',
        ncyBreadcrumb: {
            label: 'Daily Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.weeklyreports', {
        url: '/weeklyreports',
        templateUrl: "assets/views/weekly_reports.html",
        title: 'Weekly Reports',
        ncyBreadcrumb: {
            label: 'Weekly Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.monthlyreports', {
        url: '/monthlyreports',
        templateUrl: "assets/views/monthly_reports.html",
        title: 'Monthly Reports',
        ncyBreadcrumb: {
            label: 'Monthly Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.newstatusreports', {
        url: '/newstatusreports',
        templateUrl: "assets/views/new_status_reports.html",
        title: 'New Status Reports',
        ncyBreadcrumb: {
            label: 'New Status Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.cancelledstatusreports', {
        url: '/cancelledstatusreports',
        templateUrl: "assets/views/cancelled_status_reports.html",
        title: 'Cancelled Status Reports',
        ncyBreadcrumb: {
            label: 'Cancelled Status Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.paidstatusreports', {
        url: '/paidstatusreports',
        templateUrl: "assets/views/paid_status_reports.html",
        title: 'Paid Status Reports',
        ncyBreadcrumb: {
            label: 'Paid Status Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.expiredstatusreports', {
        url: '/expiredstatusreports',
        templateUrl: "assets/views/expired_status_reports.html",
        title: 'Expired Status Reports',
        ncyBreadcrumb: {
            label: 'Expired Status Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.deletedstatusreports', {
        url: '/deletedstatusreports',
        templateUrl: "assets/views/deleted_status_reports.html",
        title: 'Deleted Status Reports',
        ncyBreadcrumb: {
            label: 'Deleted Status Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.monthlyrevenuereports', {
        url: '/monthlyrevenuereports',
        templateUrl: "assets/views/monthly_revenue_reports.html",
        title: 'Monthly Revenue Reports',
        ncyBreadcrumb: {
            label: 'Monthly Revenue Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.dailyrevenuereports', {
        url: '/dailyrevenuereports',
        templateUrl: "assets/views/daily_revenue_reports.html",
        title: 'Daily Revenue Reports',
        ncyBreadcrumb: {
            label: 'Daily Revenue Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.reports.agentreports', {
        url: '/agentreports',
        templateUrl: "assets/views/agent_sticker_reports.html",
        title: 'Agent Sticker Reports',
        ncyBreadcrumb: {
            label: 'Agent Sticker Reports'
        },
        resolve: loadSequence('flow','ngTable','ui.select', 'monospaced.elastic', 'ui.mask', 'touchspin-plugin','modernizr','toaster', 'ReportController')
    }).state('app.maps', {
        url: "/maps",
        templateUrl: "assets/views/maps.html",
        resolve: loadSequence('ngMap', 'mapsCtrl'),
        title: "Maps",
        ncyBreadcrumb: {
            label: 'Maps'
        }
    }).state('app.charts', {
        url: "/charts",
        templateUrl: "assets/views/charts.html",
        resolve: loadSequence('chartjs', 'tc.chartjs', 'chartsCtrl'),
        title: "Charts",
        ncyBreadcrumb: {
            label: 'Charts'
        }
    }).state('app.documentation', {
        url: "/documentation",
        templateUrl: "assets/views/documentation.html",
        title: "Documentation",
        ncyBreadcrumb: {
            label: 'Documentation'
        }
    }).state('error', {
        url: '/error',
        template: '<div ui-view class="fade-in-up"></div>'
    }).state('error.404', {
        url: '/404',
        templateUrl: "assets/views/utility_404.html",
    }).state('error.500', {
        url: '/500',
        templateUrl: "assets/views/utility_500.html",
    })

	// Login routes

	.state('login', {
	    url: '/login',
	    template: '<div ui-view class="fade-in-right-big smooth"></div>',
	    abstract: true
	}).state('login.signin', {
	    url: '/signin',
	    templateUrl: "assets/views/login_login.html"
	}).state('login.forgot', {
	    url: '/forgot',
	    templateUrl: "assets/views/login_forgot.html"
	}).state('login.registration', {
	    url: '/registration',
	    templateUrl: "assets/views/login_registration.html"
	}).state('login.lockscreen', {
	    url: '/lock',
	    templateUrl: "assets/views/login_lock_screen.html"
	});

    // Generates a resolve object previously configured in constant.JS_REQUIRES (config.constant.js)
    function loadSequence() {
        var _args = arguments;
        return {
            deps: ['$ocLazyLoad', '$q',
			function ($ocLL, $q) {
			    var promise = $q.when(1);
			    for (var i = 0, len = _args.length; i < len; i++) {
			        promise = promiseThen(_args[i]);
			    }
			    return promise;

			    function promiseThen(_arg) {
			        if (typeof _arg == 'function')
			            return promise.then(_arg);
			        else
			            return promise.then(function () {
			                var nowLoad = requiredData(_arg);
			                if (!nowLoad)
			                    return $.error('Route resolve: Bad resource name [' + _arg + ']');
			                return $ocLL.load(nowLoad);
			            });
			    }

			    function requiredData(name) {
			        if (jsRequires.modules)
			            for (var m in jsRequires.modules)
			                if (jsRequires.modules[m].name && jsRequires.modules[m].name === name)
			                    return jsRequires.modules[m];
			        return jsRequires.scripts && jsRequires.scripts[name];
			    }
			}]
        };
    }
}]);