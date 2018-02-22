// urll for api calls
var apiUrl = 'http://test_blog.local/app_dev.php/api/v1/';
var ipp = 10;
var currentPage = 123;
//new angular app
var app = angular.module('blogApp', [
        'ngRoute'
    ])
        .config(function ($interpolateProvider, $locationProvider, $routeProvider) {
            $interpolateProvider.startSymbol('{[{').endSymbol('}]}'); // replace {{ to {[{ (for twig)

            // routes, smth like http://som_url#!/posts
            $locationProvider.hashPrefix('!');
            $routeProvider
                .when('/posts/:page/:ipp', {
                    template: '<posts-list></posts-list>'
                })
                .when('/posts', {
                    template: '<posts-list></posts-list>'
                })
                .when('/posts/:id', {
                    template: '<post-detail></post-detail>'
                })
                .otherwise('/posts');
        })
;

// component for posts list
var component = app.component('postsList', {
    //template: 'OK, BRO',
    templateUrl: '../test.template.html', // using templateurl beacouse of routing, i can't do this another way
    controller: ['$routeParams', '$scope', '$http', function PostsListController($routeParams, $scope, $http) {
        var self = this;
        this.test = 'OK';
        var page = $routeParams.hasOwnProperty('page') ? $routeParams.page : 1;
        var ipp = $routeParams.hasOwnProperty('ipp') ? $routeParams.ipp : 10;
        console.log($routeParams.page + '_' + $routeParams.ipp);
        console.log(page + '_' + ipp);
        currentPage = page;
        // i don't know hot to use here symfony routes %) it's api calls, ok?
        $http.get(apiUrl + 'blogs/' + page + '/' + ipp, {'Accept': 'application/json'}).then(function (response) {
            $scope.items = response.data.items;
            self.items = response.data.items;
            /*console.clear();
            console.log('Response here');
            console.log(response.data);*/
        }, function (response) {
            alert('err');
        });
    }]
});

// component for post details view
var component_detail = app.component('postDetail', {
    //template: 'TBD: Detail view for <span>{{$ctrl.phoneId}}</span>',
    templateUrl: '../post.html',
    controller: ['$routeParams', '$http', function PostDetailController($routeParams, $http) {
        this.id = $routeParams.id;
        console.log($routeParams);
        var self = this;
        this.test = 'OK';
        $http({
            method: 'GET',
            url: apiUrl + 'blogs/' + this.id,
            headers: {
                'Accept': 'application/json'
            },
            /*data: {
                id: this.id
            }*/
        }).then(function (response) {
            self.item = response.data;
        }, function (response) {
            alert('err');
            console.log(arguments);
        });
        /*$http.get(apiUrl + 'blogs/' + this.id, {'Accept': 'application/json'}).then(function (response) {
            self.item = response.data;
        }, function (response) {
            alert('err');
            console.log(arguments);
        });*/
    }]
});

var component_paginator = app.component('customPag', {
    //template: 'TBD: Detail view for <span>{{$ctrl.phoneId}}</span>',
    templateUrl: '../custom_pag.html',
    controller: ['$http', function PaginationController($http) {
        var self = this;
        this.pages_count = 1;
        this.ipp = ipp;
        this.current_page = currentPage;
        this.url = apiUrl;
        $http({
            method: 'GET',
            url: apiUrl + 'blogs-count',
            headers: {
                'Accept': 'application/json'
            }
        }).then(function (response) {
            self.pages_count = Math.ceil(response.data.count / ipp);
            self.pages_links = Array();
            for (var i = 0; i < self.pages_count; i++) {
                self.pages_links[i] = {
                    page: i + 1,
                    href: /*apiUrl + */'posts/' + (i + 1) + '/' + ipp
                };
            }
            console.log(self.pages_links);
            self.item = response.data;
        }, function (response) {
            alert('err');
            console.log(arguments);
        });
        /*$http.get(apiUrl + 'blogs/' + this.id, {'Accept': 'application/json'}).then(function (response) {
            self.item = response.data;
        }, function (response) {
            alert('err');
            console.log(arguments);
        });*/
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