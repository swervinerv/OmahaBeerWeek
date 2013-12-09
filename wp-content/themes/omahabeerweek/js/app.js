var app = angular.module('app', []).
	config(function($routeProvider){
	    $routeProvider.when('/', { controller: 'EventsController', templateUrl: THEME_ROOT + 'js/views/events.html' });
	    // $routeProvider.when('/about', { controller: 'EventsController', templateUrl: THEME_ROOT + 'js/views/movies.html' });
	    $routeProvider.when('/events', { controller: 'EventsController', templateUrl: THEME_ROOT + 'js/views/events.html' });
	    // $routeProvider.when('/faq', { controller: 'EventsController', templateUrl: THEME_ROOT + 'js/views/movies.html' });
	    // $routeProvider.when('/members', { controller: 'EventsController', templateUrl: THEME_ROOT + 'js/views/movies.html' });
	    // $routeProvider.when('/sponsors', { controller: 'EventsController', templateUrl: THEME_ROOT + 'js/views/movies.html' });
	    // $routeProvider.when('/tours', { controller: 'EventsController', templateUrl: THEME_ROOT + 'js/views/movies.html' });
	 
	    $routeProvider.otherwise({'redirectTo': '/'});
	});