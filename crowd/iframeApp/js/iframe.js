var iframe = angular.module('iframeApp',[]);

iframe.controller("iframeController",function($scope,$window){
    $scope.canMarkHere= true;
    $scope.alerts = -10;
    $window.updateAlerts=function(number){
        $scope.$apply($scope.alerts=number);
        console.log("Alerts updated "+ number);
    };
    $scope.cantMarkHere=function(){
        $scope.canMarkHere = false;
    };
    $scope.closeWarning=function(){
        $scope.canMarkHere = true;
    };
}).filter('onlyIfPositive', function() {
    return function(input,message,failmessage) {
        if (input <= 0) return failmessage;
        else return input+message;
    }
});
iframe.directive('delphiResize',function(){
    return {
        restrict:'A',
        replace:false,
        link: function(scope, element, attrs, tabsCtrl) {
            var draghandler = angular.element('<div class="handle draghandle" style="">:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: <i class="icon icon-minus"></i></div>');

            element.prepend(draghandler);
            var nheight = 100;
            var lastheight = 100;
            draghandler.children('i').bind("click",function(e){

                if(nheight>42){
                    lastheight = nheight;
                    nheight = 41;
                    element.css("height",nheight+"px");
                    $('iframe').hide();
                }else{
                    nheight= lastheight;
                    element.css("height",lastheight+"px");
                }
                if(nheight>50){
                    $('iframe').show();
                }
            });
            draghandler.bind("mousedown",function(e){
                e.preventDefault();

                var height = "innerHeight" in window
                    ? window.innerHeight
                    : document.documentElement.offsetHeight;
                $('iframe').hide();
                angular.element(window).bind("mousemove",function(e){
                    nheight = height-e.pageY+20;
                    nheight=nheight>height-100?height-100:nheight;
                    nheight=nheight<41?41:nheight;
                    element.css("height",nheight+"px");
                })
            });
            angular.element(window).bind("mouseup",function(e){
                angular.element(window).unbind('mousemove');
                if(nheight>50){
                    $('iframe').show();
                }
            });

        }
    }
});