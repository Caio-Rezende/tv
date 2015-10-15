var articles = angular.module('articles', ['ajaxGetters'])
.factory('buscarMaisItens', ['$q', 'getItens', function($q, getItens){
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
            var length   = sources.length;
            var answered = 0;
            function answer() {
                if (length == ++answered) {
                    var resolve;
                    while (resolve = alreadyLoading.pop()) {
                        resolve();
                    }
                    if (forcar) {
                        buscarSources();
                    }
                }
            }
            for (var i in sources) {
                if (isNaN(parseInt(i, 10))) continue;
                var params = {
                    'sources[]' : sources[i]
                };
                if (forcar) {
                    params.forcar = 1;
                }
                getItens(params).then(function (data) {
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
                    answer();
                }, answer);
            }
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
});