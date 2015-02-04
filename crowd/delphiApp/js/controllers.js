angular.module('delphiApp.controllers',[])
.controller("DebatesController",function($scope,$rootScope,debatesService,$filter,$window,$modal,$log,$interval){

    $scope.loaded=false;
    $scope.showingArgumentForm=false;
    $scope.debates=[];

    var debateWatch = function(debates){
        var alerts = $filter('filter')(debates,function(item){return item.alerts.length > 0;});
        var numalerts=0;
        if(alerts != undefined){
            numalerts = alerts.length;
        }
        if($window.parent.window.updateAlerts!=undefined){
            $window.parent.window.updateAlerts(numalerts);
        }
    };

    $scope.refresh = function(){
        return debatesService.getDebates().then(function(data){
            $scope.debates = $filter('filter')(data,function(item){return item.mysub == $rootScope.ids&&item.alerts.length>0;});

            debateWatch($scope.debates);
        });
    };
    $interval($scope.refresh,10000);
    $scope.refresh().then(function(){
        $scope.loaded=true;
    });

    $scope.rowActive=function(ids){
        return ids == $rootScope.ids;
    };
    $scope.showArguments=function(debate){
        $scope.activeDebate=debate;
        var studentid = debate.student;
        var description = debate.description;
        var modalInstance = $modal.open({
            templateUrl: 'templates/argumentsModal.html',
            controller: "ArgumentsController",
            resolve: {
                studentid: function(){
                    return studentid;
                },
                critDescription: function(){
                    return description;
                }
            }
        });

        modalInstance.result.then(function () {
            $log.info('Modal closed at: ' + new Date());
            $scope.refresh();
        }, function () {
            $log.info('Modal dismissed at: ' + new Date());
            $scope.refresh();
        });
    };
    $scope.dismissDebate=function(debate){
        var studentid = debate.student;
        var description = debate.description;
        debatesService.dismissDebate(studentid,description).then(function(data){
            if(data.msg){
                $scope.refresh();
            }
        });
    };
    $scope.undismissDebate=function(debate){
        var studentid = debate.student;
        var description = debate.description;
        debatesService.undismissDebate(studentid,description).then(function(data){
            if(data.msg){
                $scope.refresh();
            }
        });
    };
    $rootScope.debatesRefresh = function(){
        console.log("Debates got event ");
        $scope.refresh();
    }



    var tareaCellTemplate = '<div class="ngCellText" ng-class="col.colIndex()">' +
        '{{row.getProperty(col.field)}}' +
        '</div>';
    var scoreTemplate = '<div class="ngCellText" ng-class="col.colIndex()">' +
        ' {{row.getProperty(col.field) | number:2}}' +
        '</div>';
    var actionTemplate = '<button type="button" class="btn btn-default btn-xs" ng-click="showArguments(row.entity)">Debatir</button>' +
        '<button type="button" class="btn btn-default btn-xs" ng-click="goToSubmission(row.entity.mysub)" ng-hide="rowActive(row.entity.mysub)">Ir a la entrega</button>';
        var alertTemplate = '<div class="ngCellText" ng-class="col.colIndex()"><img width="15" ng-repeat="al in row.getProperty(col.field)" src="{{processAlert(al)}}" title="{{processAlertTitle(al)}}"></div>';

        $scope.processAlert=function(alert){
            switch(alert){
                case "noarg":
                    return "img/noHasOpinado.png";
                    break;
                case "newvotes":
                    return "img/cambioVotos.png";
                    break;
                case "newargs":
                    return "img/cambioArgumentos.png";
                    break;
                case "noagreement":
                    return "img/noAcuerdo.png";
                    break;
                default:
                    return "img/ninguno.png";
                    break;
            }
        }
        $scope.processAlertTitle=function(alert){
            switch(alert){
                case "noarg":
                    return "No has dado argumentos";
                    break;
                case "newvotes":
                    return "Hay cambios en los votos";
                    break;
                case "newargs":
                    return "Hay cambios en los argumentos";
                    break;
                case "noagreement":
                    return "No hay acuerdo";
                    break;
                default:
                    return "Requiere tu atención";
                    break;
            }
        }
    $scope.gridOptions = {
        data: 'debates',
        enableRowSelection:false,
        columnDefs: [
            //{field: 'student', displayName: 'ID Entrega', enableCellEdit: false,cellTemplate:tareaCellTemplate},
            {field: 'description', displayName: 'Criterio', enableCellEdit: false},
            {field: 'myscore',displayName:"Mi puntaje",cellTemplate:scoreTemplate},
            {field: 'groupscore',displayName:"Puntaje de Grupo",cellTemplate:scoreTemplate},
            {field: 'alerts',displayName:"Notificaciones",cellTemplate:alertTemplate},
            {field: 'actions',displayName:"Acciones",cellTemplate:actionTemplate}
        ]
    };
})
.controller("ArgumentsController",function($scope, $modalInstance,studentid,critDescription,debatesService,$log,$interval,$filter,$rootScope){
        $scope.loaded=false;
        $scope.newArgument={text:""};
        $scope.arguments = arguments;
        $scope.studentid = studentid;
        $scope.critDescription = critDescription;
        $scope.myscore=[];
        $rootScope.argumentsRefresh = function(){
            console.log("Arguments got event ");
            $scope.refresh();
        }
        $scope.refresh = function(){
            return debatesService.getArguments(studentid,critDescription).then(function(data){
                $scope.arguments=data;
                $scope.myscore=$filter('filter')(data,function(item){return item.mypage > 0;});
            });
        };
        $scope.refresh().then(function(){
            $scope.loaded=true;
        });
        $interval($scope.refresh,5000);
        $scope.close = function () {

            $modalInstance.dismiss('close');
        };


        $scope.toggleArgumentForm = function(){
            if($scope.showingArgumentForm){
                $scope.closeArgumentForm();
            }else{
                $scope.showingArgumentForm = true;

            }
        };
        $scope.closeArgumentForm = function(){
                $scope.newArgument={};
                $scope.showingArgumentForm = false;
        };
        $scope.submitNewArgument=function(levelid,bonus){
            var submittedArg ={
                levelid:levelid,
                bonus:bonus,
                studentid:$scope.studentid,
                argument:$scope.newArgument.text
            };
            debatesService.submitArgument(submittedArg).then(function(data){
                if(data.msg){
                    $scope.closeArgumentForm();
                    $scope.refresh();
                }else{
                    $log.info(data);
                }

            });
        };

        $scope.agree=function(argumentid){
            debatesService.sendAgreement(argumentid).then(function(data){
                if(data.msg){
                    $scope.refresh();
                }
            });

        };
        $scope.disagree=function(argumentid){
            debatesService.sendDisagreement(argumentid).then(function(data){
                if(data.msg){
                    $scope.refresh();
                }
            });
        };
        $scope.deleteArgument=function(argumentid){
            debatesService.deleteArgument(argumentid).then(function(data){
                if(data.msg){
                    $scope.refresh();
                }
            });
        };
        $scope.changeGrade=function(level,bonus){
            var score = undefined;
            var posx = undefined;
            var posy = undefined;
            var page = undefined;
            var comment = undefined;
            if($scope.myscore.length>=1){
                score = $scope.myscore[0];
                posx = score.myx*1;
                posy = score.myy*1;
                page = score.mypage-1;
                comment = score.mycomment;
            }
            if(posx==undefined||posy==undefined||page==undefined){
                alert("Para cambiar su opinion debe haber puesto una nota válida");
            }else{
                console.log("("+page+","+posx+","+posy+","+level.mylevelid*1+","+bonus*1+")");
                window.parent.window.notifyEmarking(page,posx,posy,level.mylevelid*1,bonus*1)
            }
        }
})
.controller("MainController",function($scope,$location){
    $scope.name="maincontroller";
    $scope.isRouteActive = function(route) {
        return route === $location.path();
    }
});

angular.module('embedApp.controllers',[])

    .controller("TasksController",["$scope","taskService","$interval",function($scope,taskService,$interval){
        $scope.name="tasks";

        $scope.refresh = function(){
            taskService.getTasks().then(function(resp){
                if(resp.data){
                    $scope.tasks = resp.data;
                }
            });
        };
        $scope.refresh();
        $interval($scope.refresh,5000);
        /*
         student: "4"
         fillings: "3"
         ncriteria: 3
         alerts: 2
         id: "4002"
         maxscore: 6
         score: "4.00000"
         timemodified: "1399565872"
         */
    }]);