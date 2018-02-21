var app = angular.module('blogApp', [
        'ngRoute'
    ])
        .config(function ($interpolateProvider, $locationProvider, $routeProvider) {
            $interpolateProvider.startSymbol('{[{').endSymbol('}]}');

            $locationProvider.hashPrefix('!');

            $routeProvider.when('/posts', {
                template: '<posts-list></posts-list>'
            }).when('/posts/:id', {
                template: '<post-detail></post-detail>'
            }).otherwise('/posts');
        })
;

var component = app.component('postsList', {
    //template: 'OK, BRO',
    templateUrl: '../test.template.html',
    controller: ['$scope', '$http', function PostsListController($scope, $http) {
        var self = this;
        this.test = 'OK';
        $http.get('http://test_blog.local/app_dev.php/api/v1/blogs/1/10', {'Accept': 'application/json'}).then(function (response) {
            $scope.items = response.data.items;
            self.items = response.data.items;
            console.clear();
            console.log('Response here');
            console.log(response.data);
        }, function (response) {
            alert('err');
        });
    }]
});

var component_detail = app.component('postDetail', {
    //template: 'TBD: Detail view for <span>{{$ctrl.phoneId}}</span>',
    templateUrl: '../post.html',
    controller: ['$routeParams', '$http', function PostDetailController($routeParams, $http) {
        this.id = $routeParams.id;
        var self = this;
        this.test = 'OK';
        $http.get('http://test_blog.local/app_dev.php/api/v1/blogs/' + this.id, {'Accept':'application/json'}).then(function(response) {
            self.item = response.data;
        }, function (response) {
            alert('err');
            console.log(arguments);
        });
    }]
});
// component_detail.controller('PostDetailController', function ($http, $routeParams) {
//     alert('e');
//     this.phoneId = $routeParams.phoneId;
//     var self = this;
//     this.test = 'OK';
//     $scope.test = 'OK';
//     $http.get('http://test_blog.local/app_dev.php/api/v1/blogs/637', {'Accept':'application/json'}).then(function(response) {
//         alert('blog resp');
//         console.clear();
//         console.log('Blog resp here');
//         console.log(response);
//         /*$scope.items = response.data.items;
//         self.items = response.data.items;
//         console.clear();
//         console.log('Response here');
//         console.log(response.data);*/
//     }, function (response) {
//         alert('err');
//     });
// });
// angular.module('blogApp').component('postsList', {
//     /*template:
//     '<table class="table">' +
//     '<tr ng-repeat="item in $ctrl.items">' +
//     '<td><img onload="$(this).show();" style="display: none; width: 150px" src="/uploads/images/{{item.pic}}"/></td>' +
//     '<td><a>{[{item.title}]}</a></td>' +
//     '<td>{[{item.body}]}</td>' +
//     '</tr>' +
//     '</table>',*/
//     controller: function PostsListController($scope, $http) {
//         var self = this;
//         $scope.items = [{title: 'wtf1'}, {title: 'wtf2'}];
//         /*$http.get('http://test_blog.local/app_dev.php/api/v1/blogs/1/10', {'Accept':'application/json'}).then(function(response) {
//             /!*console.clear();
//             console.log('Response here');
//             console.log(response.data.items);
//             self.items = response.data.items;*!/
//             $scope.items = response.data.items;
//             alert($scope);
//         }, function(response) {
//             alert('err');
//         });*/
//
//     }
// });