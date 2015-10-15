<!DOCTYPE html>
<html>
    <head>
        <title>TV</title>
        <base href="./" target="_blank">
        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.6/angular.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.6/angular-sanitize.js"></script>
        <script src="js/factory/ajaxGetters.js"></script>
        <script src="js/factory/itemStorage.js"></script>
        <script src="js/factory/articles.js"></script>
        <script src="js/controller/BarDisplay.js"></script>
        <script src="js/controller/TVDisplay.js"></script>
        <script src="js/tv.js"></script>
        <link type="text/css" rel="stylesheet" href="css/tv.css" media="all"/>
    </head>
    <body ng-app="tv" ng-controller="TVDisplay">
        <a href="#paused" ng-click="pauseFunction()" 
            class="pauseButton" 
            ng-show="playPauseExhibit && pause"
            title="Pause"
            target="_self"
            >&RightTriangleEqual;</a>
        <a href="#playing" ng-click="playFunction()" 
            class="playButton" 
            ng-show="playPauseExhibit && play"
            title="Play"
            target="_self"
            >&RightTriangle;</a>
        <div ng-include="'view/content.html'"></div>
        <div ng-include="'view/lista.html'"></div>
        <div ng-show="destaque" class="highlightFundo" ng-include="'view/destaque.html'"></div>
        <div ng-show="destaque" class="highlight" ng-include="'view/destaque.html'"
             ng-style="{
                'border-color' : getMediaAttr(destaque.source, 'color', 'transparent')
             }"
             ></div>
        <div ng-include="'view/configuracao.html'"></div>
        <div ng-include="'view/bar.html'"></div>
    </body>
</html>