var PublicityDisplay = angular.module('PublicityDisplay', ['ajaxGetters', 'itemStorage'])
.controller('PublicityDisplay', ['$scope', '$interval', '$timeout', 'getPublicity', 'itemStorage', '$rootScope',
    function($scope, $interval, $timeout, getPublicity, itemStorage, $rootScope){
        //list of all publicities
        var publicities = [];
        //publicity being displayed
        $scope.publicity   = null;
        // 3 minutes default to publicity be displayed
        $rootScope.timeToNextPublicity = itemStorage.getItem('timeToNextPublicity', 3 * 60 * 1000);
        // 30 sec default of displaying publicity
        $rootScope.timeToHidePublicity = itemStorage.getItem('timeToHidePublicity', 30 * 1000);
        
        var getPublicities = function() {
            getPublicity().then(function(res){
                publicities = [];
                for (var i in res) {
                    if (isNaN(parseInt(i, 10))) continue;
                    publicities.push(res[i]);
                }
            });
        };
        //1 hour to get the updated list of publicities
        $interval(getPublicities, 60 * 60 * 1000);
        getPublicities();
        
        var timeoutHidePublicity = null;
        //Stop hidePublicity timeout, sets the next publicity timeout and clean the publicity
        $scope.stopPublicity = function() {
            $timeout.cancel(timeoutHidePublicity);
            $rootScope.setTimeoutNextPublicity();
            $scope.publicity = null;
        };
        
        var actualPublicity = -1;
        //Checks if there are any publicity on the list and display the next one. Sets the timeout to hide it.
        var nextPublicity = function() {
            if (publicities.length > 0) {
                actualPublicity  = (actualPublicity + 1) % publicities.length;
                $scope.publicity = publicities[actualPublicity];
                timeoutHidePublicity = $timeout($scope.stopPublicity, $rootScope.timeToHidePublicity);
            } else {
                //If there is no publicity in the list, just set the timeout to the next time, until it has something to display
                $rootScope.setTimeoutNextPublicity();
            }
        };
        
        //Just saves the item to the storage
        $rootScope.setTimeToHidePublicity = function() {
            itemStorage.setItem('timeToHidePublicity', $rootScope.timeToHidePublicity);
        };
        
        var timeoutNextPublicity = null;
        //Saves item to the storage, cancel the timeout previously running and start a new one with the new timing
        $rootScope.setTimeoutNextPublicity = function() {
            itemStorage.setItem('timeToNextPublicity', $rootScope.timeToNextPublicity);
            $timeout.cancel(timeoutNextPublicity);
            timeoutNextPublicity = $timeout(nextPublicity, $rootScope.timeToNextPublicity);
        };
        $rootScope.setTimeoutNextPublicity();
    }]);