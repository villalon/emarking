angular.module('delphiApp.services', [])
    .service('debatesService',
    ['$rootScope', '$http', '$q',
        function($rootScope, $http, $q) {

            var getDebates = function() {
                var d = $q.defer();
                $http({
                    method: 'GET',
                    url: $rootScope.ajaxRoot+'&act=debates'
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            var getArguments = function(studentid,description) {
                var d = $q.defer();
                $http({
                    method: 'GET',
                    url: $rootScope.ajaxRoot+'&act=detail&stdid='+studentid+'&critd='+encodeURIComponent(description)
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            var submitArgument = function(argument) {
                var d = $q.defer();
                $http({
                    method: 'POST',
                    url: $rootScope.ajaxRoot+'&act=newargument',
                    data:"levelid="+argument.levelid+"&bonus="+argument.bonus+"&studentid="+argument.studentid+"&argument="+argument.argument,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            var sendAgreement = function(argumentid){
                var d = $q.defer();
                $http({
                    method: 'POST',
                    url: $rootScope.ajaxRoot+'&act=agree',
                    data:"argumentid="+argumentid,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            var sendDisagreement = function(argumentid){
                var d = $q.defer();
                $http({
                    method: 'POST',
                    url: $rootScope.ajaxRoot+'&act=disagree',
                    data:"argumentid="+argumentid,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            var deleteArgument = function(argumentid){
                var d = $q.defer();
                $http({
                    method: 'POST',
                    url: $rootScope.ajaxRoot+'&act=deleteArgument',
                    data:"argumentid="+argumentid,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            var dismissDebate = function(studentid,description) {
                var d = $q.defer();
                $http({
                    method: 'GET',
                    url: $rootScope.ajaxRoot+'&act=dismiss&stdid='+studentid+'&critd='+encodeURIComponent(description)
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            var undismissDebate = function(studentid,description) {
                var d = $q.defer();
                $http({
                    method: 'GET',
                    url: $rootScope.ajaxRoot+'&act=undismiss&stdid='+studentid+'&critd='+encodeURIComponent(description)
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };
            return {
                getDebates: getDebates,
                getArguments:getArguments,
                submitArgument:submitArgument,
                sendAgreement:sendAgreement,
                sendDisagreement:sendDisagreement,
                deleteArgument:deleteArgument,
                dismissDebate:dismissDebate,
                undismissDebate:undismissDebate
            }
        }])
    .service('taskService',
    ['$rootScope', '$http', '$q',
        function($rootScope, $http, $q) {

            var getTasks = function() {
                var d = $q.defer();
                $http({
                    method: 'GET',
                    url: $rootScope.ajaxRoot+'&act=gettasks'
                }).success(function(data, status) {
                    if (status == 200) {
                        d.resolve(data);
                    }
                });
                return d.promise;
            };

            return {
                getTasks:getTasks
            }
        }]);