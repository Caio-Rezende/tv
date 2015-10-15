var TVDisplay = angular.module('TVDisplay', ['ajaxGetters', 'itemStorage', 'articles'])
.controller('TVDisplay', ['$scope', '$interval', 'processarArticles', 'buscarMaisItens', 'getSources', 'itemStorage', 'getContent',
    function($scope, $interval, processarArticles, buscarMaisItens, getSources, itemStorage, getContent){
        $scope.colors           = ['#FF2626','#B300B3','#5B5BFF','#5EAE9E','#D9C400','#FFA04A','#C98A4B','#FF73B9','#A27AFE','#32DF00'];   
        $scope.sources          = itemStorage.getItem('sources', []);
        $scope.listaExhibit     = itemStorage.getItem('listaExhibit', false);
        $scope.showSubtitle     = itemStorage.getItem('showSubtitle', false);
        $scope.articles         = [];
        $scope.destaque         = null;
        $scope.chosenArticle    = null;
        $scope.lastArticle      = -1;
        $scope.playPauseExhibit = itemStorage.getItem('playPauseExhibit', false);
        $scope.play             = false;
        $scope.pause            = true;
        
        $scope.playFunction = function() {
            $scope.play  = false;
            $scope.pause = true;
            $interval.cancel(intervalProximoItem);
            intervalProximoItem = $interval($scope.avancarProximoItem, $scope.avancarProximoItemTempo);
        };
        $scope.pauseFunction = function() {
            $scope.pause = false;
            $scope.play  = true;
            $interval.cancel(intervalProximoItem);
        };
        
        $scope.avancarProximoItemTempo = itemStorage.getItem('avancarProximoItemTempo', 10 * 1000);
        var intervalProximoItem = null;
        
        $scope.itemStorage = itemStorage;
        
        var init = false;
        $scope.buscarSources = function() {
            getSources().then(function(sources) {
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
                    $interval.cancel(intervalProximoItem);
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
        $scope.toggleShowSubtitle = function() {
            itemStorage.setItem('showSubtitle', $scope.showSubtitle);
        };
        $scope.togglePlayPauseExhibit = function() {
            itemStorage.setItem('playPauseExhibit', $scope.playPauseExhibit);
        };

        $interval($scope.buscarSources, 10 * 60 * 1000);
    }]);