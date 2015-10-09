var moduleTV = angular.module('tv', ['ngSanitize'])
.factory('getWeather', ['$http', '$q', function($http, $q){
    return function() {
        return $q(function(resolve, reject){
            $http({
                method: 'GET',
                url: 'ajax/weather.php',
                params: {
                    
                }
            }).then(function (response) {
                resolve(response.data);
            });
        });
    };
}])
.factory('getContent', ['$http', '$q', function($http, $q){
    return function(media, item) {
        return $q(function(resolve, reject){
            if (!item.href) {
                reject();
                return;
            }
            $http({
                method: 'GET',
                url: 'ajax/content.php',
                params: {
                    link: item.href,
                    media: media
                }
            }).then(function (response) {
                resolve(response.data);
            });
        });
    };
}])
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
        return $q(function(resolve){
            $http({
                method: 'GET',
                url: 'ajax/sources.php'
            }).then(function (response) {
                resolve(response.data);
            });
        });
    };
}])
.factory('buscarMaisItens', ['$http', '$q', function($http, $q){
    var hrefs = {};
    var sourcesAnterior = [];
    var alreadyLoading = [];
    return function($scope, forcar) {
        if (alreadyLoading.length) {
            return $q(function(resolve) {
                alreadyLoading.push(resolve);
            });
        }
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
        var buscarSources = $scope.buscarSources;
        var sources  = [];
        for (var i in $scope.sources) {
            if (isNaN(parseInt(i)) || !$scope.sources[i].enabled) continue;
            sources.push($scope.sources[i].name);
        }
        return $q(function(resolve){
            alreadyLoading.push(resolve);
            var params = {
                'sources[]' : sources
            };
            if (forcar) {
                params.forcar = 1;
            }
            $http({
                method: 'POST',
                url: 'ajax/tv.php',
                params: params
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
                var resolve;
                while (resolve = alreadyLoading.pop()) {
                    resolve();
                }
                if (forcar) {
                    buscarSources();
                }
            });
        });
    };
}])
.factory('processarArticles', function(){
    return function ($scope) {
        var articles = $scope.articles;
        
        var lastArticle = $scope.lastArticle;
        if (lastArticle >= 0 && articles[lastArticle]) {
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
})
.controller('ArticlesDisplay', ['$scope', '$interval', 'processarArticles', 'buscarMaisItens', 'buscarSources', 'itemStorage', 'getContent',
    function($scope, $interval, processarArticles, buscarMaisItens, buscarSources, itemStorage, getContent){
        $scope.colors        = ['#FF2626','#B300B3','#5B5BFF','#5EAE9E','#D9C400','#FFA04A','#C98A4B','#FF73B9','#A27AFE','#32DF00'];
        $scope.sources       = itemStorage.getItem('sources', []);
        $scope.listaExhibit  = itemStorage.getItem('listaExhibit', false);
        $scope.articles      = [];
        $scope.destaque      = null;
        $scope.chosenArticle = null;
        $scope.lastArticle   = -1;
        $scope.avancarProximoItemTempo = itemStorage.getItem('avancarProximoItemTempo', 10 * 1000);
        var intervalProximoItem = null;
        
        $scope.itemStorage = itemStorage;
        
        var init = false;
        $scope.buscarSources = function() {
            buscarSources().then(function(sources) {
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
                    $scope.avancarProximoItem();
                    init = true;
                }
            });
        };
        $scope.buscarSources();
        
        var vezes = 0;
        $scope.avancarProximoItem = function(forcar) {
            if (!(vezes % 5)) {
                buscarMaisItens($scope, forcar).then((function(size){
                    return function() {
                        if (size == 0) {
                            $scope.avancarProximoItem();
                        }
                    };
                })($scope.articles.length));
            }
            if ($scope.articles.length > 0) {
                processarArticles($scope);
            }
            vezes++;
        };
        
        $scope.trocarItem = function(article, index) {
            if ($scope.lastArticle >= 0 && $scope.articles.length >= $scope.lastArticle) {
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
        
        $scope.getMediaAttr = function (source, attr, def) {
            for (var i in $scope.sources) {
                if (isNaN(parseInt(i, 10)) 
                    || $scope.sources[i].source != source
                ) {
                    continue;
                }
                return $scope.sources[i][attr];
            }
            return def ? def : null;
        };
        
        $scope.getContent = function(item) {
            if (item.content) {
                $scope.chosenArticle = item;
            } else {
                getContent($scope.getMediaAttr(item.source, 'name'), item).then(function (html){
                    $scope.chosenArticle = item;
                    item.content = html;
                });
            }
        };
        $scope.toggleListaExhibit = function() {
            itemStorage.setItem('listaExhibit', $scope.listaExhibit);
        };

        $interval($scope.buscarSources, 10 * 60 * 1000);
    }])
    .controller('BarDisplay', ['$scope', '$interval', 'getWeather', function($scope, $interval, getWeather){
        $scope.hora = '00:00';
        $scope.clima = '-º';
        
        function changeHora() {
            var data = new Date();
            $scope.hora = data.getHours() + ':' + data.getMinutes();
        }
        $interval(changeHora, 60 * 1000);
        changeHora();
        
        function changeClima() {
            getWeather().then(function(res){
                $scope.clima = res + ' ºC';
            });
        }
        $interval(changeClima, 60 * 1000);
        changeClima();
    }]);