var app = angular.module('delphiApp',[
    'delphiApp.services',
    'delphiApp.directives',
    'delphiApp.filters',
    'delphiApp.controllers',
    'ui.bootstrap',
    'ngRoute',
    'ngGrid',
    'ngAnimate'
]);
var embedApp = angular.module('embedApp',[
    'delphiApp.services',
    'delphiApp.directives',
    'delphiApp.filters',
    'embedApp.controllers',
    'ui.bootstrap',
    'ngRoute',
    'ngGrid',
    'ngAnimate'
]);
embedApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider
        .when('/', {
            templateUrl: window.wwwroot+"/mod/emarking/crowd/delphiApp/templates/tasks.html",
            controller: "TasksController"
        })
        .otherwise({redirect_to: '/'});

}]);
app.config(['$routeProvider','$tooltipProvider', function($routeProvider,$tooltipProvider) {
    $routeProvider
        .when('/', {
            templateUrl: window.wwwroot+"/mod/emarking/crowd/delphiApp/templates/debates.html",
            controller: "DebatesController"
        })
        .otherwise({redirect_to: '/'});
    $tooltipProvider.setTriggers({
        'mouseenter': 'mouseleave',
        'click': 'click',
        'focus': 'blur',
        'never': 'mouseleave' // <- This ensures the tooltip will go away on mouseleave
    });
    $tooltipProvider.options({placement: 'right',
        animation: false,
        popupDelay: 0,
        appendToBody: true});
}]);
app.run(['$rootScope','$window', function($rootScope,$window) {

    $window.parent.window.notifyDelphi = function(){
        console.log("Sending event ");
        if($rootScope.debatesRefresh){
            $rootScope.debatesRefresh();
        }
        if($rootScope.argumentsRefresh){
            $rootScope.argumentsRefresh();
        }
    }
    $rootScope.initConfig=function(cmid,ids,wwwroot){
        $rootScope.cmid = cmid;
        $rootScope.ids = ids;
        $rootScope.wwwroot = wwwroot;
        $rootScope.ajaxRoot = wwwroot+"/mod/emarking/crowd/ajax.php?cmid="+cmid;
        $rootScope.rtmtoken = $window.rtmtoken;
    }
    $rootScope.goToSubmission=function(submissionId){
        window.parent.location=$rootScope.getSubmissionUrl(submissionId);
    }
    $rootScope.getSubmissionUrl=function(submissionId){
        return $rootScope.wwwroot+"/mod/emarking/ajax/a.php?ids="+submissionId+"&action=emarking#";
    }
}]);
embedApp.run(['$rootScope','$window', function($rootScope,$window) {

    $rootScope.initConfig=function(cmid,ids,wwwroot){
        $rootScope.cmid = cmid;
        $rootScope.ids = ids;
        $rootScope.wwwroot = wwwroot;
        $rootScope.ajaxRoot = wwwroot+"/mod/emarking/crowd/ajax.php?cmid="+cmid;
        $rootScope.rtmtoken = $window.rtmtoken;
    }
    $rootScope.goToSubmission=function(submissionId){
        window.parent.location=$rootScope.getSubmissionUrl(submissionId);
    }
    $rootScope.getSubmissionUrl=function(submissionId){
        return $rootScope.wwwroot+"/mod/emarking/ajax.php?ids="+submissionId+"&action=emarking#";
    }
}]);