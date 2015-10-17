var ajaxGetters = angular.module('ajaxGetters', [])
.factory('getAjax', ['$http', '$q', function($http, $q){
    return function(url, params) {
        return $q(function(resolve, reject){
            $http({
                method: 'GET',
                url: url,
                params: params
            }).then(function (response) {
                resolve(response.data);
            }, function(){
                reject();
            });
        });
    };
}])
.factory('getWeather', ['getAjax', function(getAjax){
    return function() {
        return getAjax('ajax/weather.php');
    };
}])
.factory('getSources', ['getAjax', function(getAjax){
    return function() {
        return getAjax('ajax/sources.php');
    };
}])
.factory('getPublicity', ['getAjax', function(getAjax){
    return function() {
        return getAjax('ajax/publicity.php');
    };
}])
.factory('getItens', ['getAjax', function(getAjax){
    return function(params) {
        return getAjax('ajax/tv.php', params);
    };
}])
.factory('getContent', ['getAjax', '$q', function(getAjax, $q){
    return function(media, item) {
        return getAjax('ajax/content.php', {
            link: item.href,
            media: media
        });
    };
}]);