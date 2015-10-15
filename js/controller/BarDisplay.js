var BarDisplay = angular.module('BarDisplay', ['ajaxGetters'])
.controller('BarDisplay', ['$scope', '$interval', 'getWeather', function($scope, $interval, getWeather){
    $scope.hora = '00:00';
    $scope.climaTemp = '';
    $scope.climaIcon = '';

    function changeHora() {
        var data = new Date();
        $scope.hora = data.getHours() 
            + ':' 
            + (parseInt(data.getMinutes(), 10) >= 10 
                ? '' 
                : '0'
            ) + data.getMinutes();
    }
    $interval(changeHora, 60 * 1000);
    changeHora();

    function changeClima() {
        getWeather().then(function(res){
            $scope.climaTemp = res.temp + ' ºC';
            $scope.climaIcon = res.icon;
        });
    }
    //the api.openweathermap free account only updates from 10 to 2h...
    $interval(changeClima, 15 * 60 * 1000);
    changeClima();
}]);