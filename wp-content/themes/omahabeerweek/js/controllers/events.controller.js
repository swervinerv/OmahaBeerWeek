app.controller('EventsController', function ($scope, $http) {

    $http({
        method: 'GET',
        url: ROOT + 'events/'
    }).success(function(events) {
        $scope.events = events;
    }).
    error(function(err) {
        console.log(err);
    })
    
    // $http.get(ROOT + 'events/').then(function(res) {
    //     console.log(res);
    // }, function (err) {
    //     console.log(err);
    // });
 
    // $scope.movies = movies;
});