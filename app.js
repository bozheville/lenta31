var App = function($scope, $http, $cookieStore){
    $scope.new = {};
    $scope.links = [];
    $scope.viewedArticles = [];

    $scope.add = function(){
        $http.get('http://lenta31.grybov.com/api.php?add='+$scope.new.lnk+'&num='+$scope.new.num).success(function(e){
            console.log(e);
//            $scope.new = {};
        });
    };

    $scope.get = function(){
        $http.get('http://lenta31.grybov.com/api.php?get=all').success(function(e){
            $scope.links = e;
            for(var i in $scope.links){
                $scope.viewedArticles[$scope.links[i]._id] = 0;
            }
            markViewed();
        });
    };

    $scope.setViewed = function(_id){
        var viewed = $cookieStore.get('viewed');
        viewed = viewed ? viewed.split(';') : [];
        if(viewed.indexOf(_id) < 0){
            viewed.push(_id);
            viewed = viewed.join(';');
            $cookieStore.put('viewed', viewed);
            $scope.viewedArticles[_id] = 1;
        }
    };

    var markViewed = function(){
        var viewed = $cookieStore.get('viewed');
        viewed = viewed ? viewed.split(';') : [];
        for(var i in viewed) {
            $scope.viewedArticles[viewed[i]] = 1;
        }
    };

    $scope.get();

}
angular.module('lenta31', ['ngCookies']);