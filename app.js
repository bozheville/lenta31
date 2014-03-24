var App = function($scope, $http, $cookieStore){
    $scope.new = {};
    $scope.links = [];
    $scope.viewedArticles = [];
    $scope.show_article = false;
    $scope.current_article = {};

    $scope.add = function(){
        $http.get('http://lenta31.grybov.com/api.php?add='+$scope.new.lnk+'&num='+$scope.new.num).success(function(e){
            $scope.new = {};
        });
    };

    $scope.viewArticle = function(id){
        document.location.hash = id;
        loadArticle();
    };

    $scope.closePopup = function(){
        $scope.show_article = false;
        clearHash();
    };

    var getAll = function(){
        $http.get('http://lenta31.grybov.com/api.php?get=all').success(function(e){
            $scope.links = e;
            for(var i in $scope.links){
                $scope.viewedArticles[$scope.links[i]._id] = 0;
            }
            markViewed();
        });
    };

    var setViewed = function(_id){
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

    var loadArticle = function(){
        var id = parseInt(document.location.hash.replace(/^#/, ''));
        if(isNaN(id)){
            clearHash();
        } else{
            $http.get('http://lenta31.grybov.com/api.php?get='+id).success(function(e){
                if(e){
                    $scope.current_article = e;
                    $scope.show_article = true;
                    setViewed(id);
                    scrollTop();
                } else{
                    clearHash();
                }
            });
        }

    };

    var clearHash = function(){
        document.location.hash = '';
        var loc = window.location.href,
            index = loc.indexOf('#');
        if (index > 0) {
            window.location = loc.substring(0, index);
        }
    };

    getAll();
    loadArticle();

}
angular.module('lenta31', ['ngCookies']);
angular.module('lenta31')
    .filter('to_trusted', ['$sce', function($sce){
        return function(text) {
            return $sce.trustAsHtml(text);
        };
    }]);
