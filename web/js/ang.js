// urll for api calls
var apiUrl = 'http://test_blog.local/app_dev.php/api/v1/';
var ipp = 10;
var currentPage = 123;
var postsList = [];

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
                .when('/post/:id', {
                    template: '<post-form></post-form>'
                })
                .when('/post-add', {
                    template: '<post-form></post-form>'
                })
                .otherwise('/posts');
        })
;

// component for add/edit new post
var component_form = app.component('postForm', {
    //template: 'OK, BRO',
    templateUrl: '../form.html', // using templateurl beacouse of routing, i can't do this another way
    controller: ['$routeParams', '$scope', '$http', function PostsListController($routeParams, $scope, $http) {
        var self = this,
            id = $routeParams.hasOwnProperty('id') ? $routeParams.id : 0
        ;

        if (id !== 0) {
            self.newItem = false;
            if (postsList.length) { // not page reload, get item from array
                for (var i in postsList) {
                    if (postsList[i].id == id) {
                        self.item = postsList[i];
                    }
                }
            } else { // lets get item from api
                $http.get(apiUrl + 'blogs/' + id, {'Accept': 'application/json'}).then(function (response) {
                    self.item = response.data;
                    console.log(self.item);
                }, function (response) {
                    alert('err');
                });
            }
        } else {
            self.newItem = true;
            self.item = {
                title: '',
                href: '',
                short: '',
                body: '',
                enabled: 1
            };
        }

        $scope.saveItem = function (element) {
            var btn = $('#submit_btn');
            var form = btn.closest('form');
            var data = form.serialize();

            if (self.newItem) {
                $http.post(apiUrl + 'blogs', self.item, {'Accept': 'application/json'}).then(function (response) {
                    
                    location.href = '#!/posts';
                }, function (err) {
                    alert('save err')
                });
            } else {
                $http.put(apiUrl + 'blog', self.item, {'Accept': 'application/json'}).then(function (response) {
                    
                    location.href = '#!/posts';
                }, function (err) {
                    alert('save err')
                });
            }
        }
    }]
});

// component for posts list
var component = app.component('postsList', {
    //template: 'OK, BRO',
    templateUrl: '../test.template.html', // using templateurl beacouse of routing, i can't do this another way
    controller: ['$routeParams', '$scope', '$http', function PostsListController($routeParams, $scope, $http) {

        $scope.delete_post = function (id) {
            if (!confirm('Are you shure?')) {
                return;
            }
            $http.delete(apiUrl + 'blogs/' + id, {'Accept': 'application/json'}).then(function (response) {
                for (var i in $scope.items) {
                    var item = $scope.items[i];
                    if (item.id === id) {
                        $scope.items.splice(i, 1);
                    }
                }
            }, function (response) {
                alert('err');
            });
        };


        var self = this;
        var page = $routeParams.hasOwnProperty('page') ? $routeParams.page : 1;
        var ipp = $routeParams.hasOwnProperty('ipp') ? $routeParams.ipp : 10;

        currentPage = page;
        // i don't know hot to use here symfony routes %) it's api calls, ok?
        $http.get(apiUrl + 'blogs/' + page + '/' + ipp, {'Accept': 'application/json'}).then(function (response) {
            //$scope.items = response.data.items;
            self.items = response.data.items;
            postsList = response.data.items;
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

        });
        /*$http.get(apiUrl + 'blogs/' + this.id, {'Accept': 'application/json'}).then(function (response) {
            self.item = response.data;
        }, function (response) {
            alert('err');

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