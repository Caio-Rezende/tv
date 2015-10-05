var moduleTV = angular.module('tv', [])
.factory('itemStorage', function(){
    return {
        getItem : function(nome, padrao) {
            var item = localStorage.getItem(nome);
            if (item) {
                return JSON.parse(item);
            } else {
                return padrao;
            }
        },
        setItem : function(nome, item) {
            localStorage.setItem(nome, JSON.stringify(item));
        }
    };
})
.factory('buscarSources', ['$http', '$q', function($http, $q){
    return function() {
        var promise = $q(function(resolve){
            $http({
                method: 'GET',
                url: 'ajax/sources.php'
            }).then(function (response) {
                resolve(response.data);
            });
        });
        return promise;
    };
}])
.factory('buscarMaisItens', ['$http', '$q', function($http, $q){
    var hrefs = {};
    var sourcesAnterior = [];
    return function($scope) {
        if ($scope.sources.length != sourcesAnterior.length) {
            var sourcesChanged = true;
        } else {
            var sourcesChanged = false;
            for (var i in $scope.sources) {
                if (isNaN(parseInt(i)) 
                    || $scope.sources[i].enabled == sourcesAnterior[i].enabled
                ) {
                    continue;
                }
                sourcesChanged = true;
                break;
            }
        }
        if (sourcesChanged) {
            hrefs = {};
            $scope.articles = [];
            angular.copy($scope.sources, sourcesAnterior);
            $scope.itemStorage.setItem('sources', $scope.sources);
        }
        var articles = $scope.articles;
        var sources  = [];
        for (var i in $scope.sources) {
            if (isNaN(parseInt(i)) || !$scope.sources[i].enabled) continue;
            sources.push($scope.sources[i].name);
        }
        var promise = $q(function(resolve){
            $http({
                method: 'POST',
                url: 'ajax/tv.php',
                params: {
                    'sources[]' : sources
                }
            }).then(function (response) {
                var data = response.data;
                for (var i in data) {
                    if (isNaN(parseInt(i))) continue;
                    var article = data[i];
                    if (!hrefs[article.href]) {
                        hrefs[article.href] = true;
                        articles.push(article);
                    }
                }
                articles.sort(function(a, b){ return parseFloat(b.ts) - parseFloat(a.ts); });
                articles.splice(10);
                resolve();
            });
        });
        return promise;
    };
}])
.factory('processarArticles', ['$q', function($q){
    return function ($scope) {        
        var articles = $scope.articles;
        
        var lastArticle = $scope.lastArticle;
        if (lastArticle >= 0) {
            articles[lastArticle].played = !articles[lastArticle].played;
            articles[lastArticle].destaque = false;
        }
        if (articles.length > 0) {
            lastArticle = ((lastArticle + 1) % articles.length);
        }
        if (lastArticle >= 0) {
            $scope.destaque = articles[lastArticle];
            $scope.destaque.destaque = true;
        } else {
            $scope.destaque = null;
        }
        $scope.lastArticle = lastArticle;
    };
}])
.controller('ArticlesDisplay', ['$scope', '$interval', 'processarArticles', 'buscarMaisItens', 'buscarSources', 'itemStorage', 
    function($scope, $interval, processarArticles, buscarMaisItens, buscarSources, itemStorage){
        $scope.colors      = ['#FF2626','#B300B3','#5B5BFF','#5EAE9E','#D9C400','#FFA04A','#C98A4B','#FF73B9','#A27AFE','#32DF00'];
        $scope.sources     = itemStorage.getItem('sources', []);
        $scope.articles    = [];
        $scope.destaque    = null;
        $scope.lastArticle = -1;
        $scope.avancarProximoItemTempo = itemStorage.getItem('avancarProximoItemTempo', 10 * 1000);
        var intervalProximoItem = null;
        
        $scope.itemStorage = itemStorage;
        
        var init = false;
        $scope.buscarSources = function() {
            var promiseSources = buscarSources();
            promiseSources.then(function(sources) {
                for (var j in sources) {
                    if (isNaN(parseInt(j, 10))) 
                        continue;
                    var tf = true;
                    for (var i in $scope.sources) {
                        if (isNaN(parseInt(i, 10))) 
                            continue;
                        if ($scope.sources[i].name == sources[j].name) {
                            tf = false;
                            if ($scope.sources[i].source != sources[j].source) {
                                $scope.sources[i].source = sources[j].source;
                            }
                            if (!$scope.sources[i].color) {
                                $scope.sources[i].color = $scope.colors[i % $scope.colors.length];
                            }
                            
                            $scope.sources[i].lastDate = sources[j].lastDate;
                            $scope.sources[i].nextDate = sources[j].nextDate;
                            break;
                        }
                    }
                    if (tf) {
                        sources[j].enabled = true;
                        sources[j].color   = $scope.colors[$scope.sources.length % $scope.colors.length];
                        $scope.sources.push(
                            sources[j]
                        );
                    }
                }
                if (!init) {
                    intervalProximoItem = $interval($scope.avancarProximoItem, $scope.avancarProximoItemTempo);
                    init = true;
                }
                $scope.buscarMaisItens();
            });
        };
        $scope.buscarSources();
        
        var vezes = 0;
        $scope.avancarProximoItem = function() {
            function continuar() {
                vezes++;
                processarArticles($scope);
            }
            if (!(vezes % 5)) {
                var promiseMais = buscarMaisItens($scope);
                promiseMais.then(continuar);
            } else {
                continuar();
            }
        };
        
        $scope.buscarMaisItens = function() {
            vezes = 0;
            $scope.avancarProximoItem();
        };
        
        $scope.trocarItem = function(article, index) {
            if ($scope.lastArticle >= 0) {
                $scope.articles[$scope.lastArticle].played = true;
                $scope.articles[$scope.lastArticle].destaque = false;
            }
            $scope.changeIntervalProximoItem();
            $scope.lastArticle = index;
            article.destaque   = true;
            $scope.destaque    = article;
        };
        
        $scope.changeIntervalProximoItem = function() {
            itemStorage.setItem('avancarProximoItemTempo', $scope.avancarProximoItemTempo);
            $interval.cancel(intervalProximoItem);
            intervalProximoItem = $interval($scope.avancarProximoItem, $scope.avancarProximoItemTempo);
        };
        
        $scope.getColor = function (source) {
            var color = 'transparent';
            for (var i in $scope.sources) {
                if (isNaN(parseInt(i, 10)) 
                    || $scope.sources[i].source != source
                ) {
                    continue;
                }
                color = $scope.sources[i].color;
            }
            return color;
        };

        $interval($scope.buscarSources, 10 * 60 * 1000);
    }]);