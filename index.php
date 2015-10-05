<!DOCTYPE html>
<html>
    <head>
        <title>TV</title>
        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.6/angular.min.js"></script>
        <script src="js/tv.js"></script>
        <link type="text/css" rel="stylesheet" href="css/tv.css" media="all"/>
    </head>
    <body ng-app="tv" ng-controller="ArticlesDisplay">
        <img ng-if="destaque" style="width:100%; height: 100%; position: fixed; z-index: -1" src="{{destaque.img}}">
        <div ng-if="destaque" class="destaqueFundo">
            <span style="float: left" ng-if="destaque.source">
                {{destaque.source}}
            </span>
            <span style="float: right" ng-if="destaque.datetime">
                {{destaque.datetime}}
            </span>
            <h1 ng-if="destaque.titulo">
                {{destaque.titulo}}
            </h1>
            <h2 ng-if="destaque.subtitulo">
                {{destaque.subtitulo}}
            </h2>
            <a href="{{destaque.href}}" style="float: right" target="_blank">Link</a>
        </div>
        <div ng-if="destaque" class="destaque">
            <span style="float: left" ng-if="destaque.source">
                {{destaque.source}}
            </span>
            <span style="float: right" ng-if="destaque.datetime">
                {{destaque.datetime}}
            </span>
            <h1 ng-if="destaque.titulo">
                {{destaque.titulo}}
            </h1>
            <h2 ng-if="destaque.subtitulo">
                {{destaque.subtitulo}}
            </h2>
        </div>
        <center class="lista">
            <ul>
                <li ng-repeat="article in articles" ng-class="{'destaque' : article.destaque, 'played' : article.played}" 
                    ng-click="trocarItem(article, $index);">
                    <span class="datetime" style="float: right; font-size: 1.2em" ng-if="article.datetime">
                        {{article.datetime}}
                    </span>
                    <h2 ng-if="article.titulo">
                        <span class="colorBlock" ng-style="{'background-color' : getColor(article.source)}">&nbsp;</span>
                        {{article.titulo}}
                    </h2>
                    <center ng-if="article.img">
                        <img style="width:100%" src="{{article.thumbnail ? article.thumbnail : article.img}}">
                    </center>
                </li>
            </ul>
        </center>
        <div class="configuracoes" ng-show="configuracoes == true">
            <ul>
                <li ng-repeat="source in sources" class="sourceBlock">
                    <label>
                        <input type="checkbox" ng-model="source.enabled"/>
                        <span class="colorBlock" ng-style="{'background-color' : getColor(source.source)}">&nbsp;</span>
                        {{source.name}} <span class="lastDate">{{source.lastDate}}</span><span class="nextDate">{{source.nextDate}}</span>
                    </label>
                </li>
            </ul>
            <center>
                Velocidade de troca dos itens ({{avancarProximoItemTempo / 1000}}s):
                <input type="range" max="60000" min="2500" step="500" ng-model="avancarProximoItemTempo"
                       ng-change="changeIntervalProximoItem()"/>
                <button type="button" ng-click="buscarMaisItens()">Carregar Itens</button>
                <button type="button" ng-click="buscarSources()">Carregar Fontes</button>
                <br/>
                <button type="button" ng-click="configuracoes = null;">Fechar</button>
            </center>
            <br/>
        </div>
        <img src="img/gear.svg" alt="Configurações" title="Configurações" height="30" style="position: absolute; right: 10px; bottom: 10px"
             ng-click="configuracoes = true;"/>
    </body>
</html>