var itemStorage = angular.module('itemStorage', [])
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
});