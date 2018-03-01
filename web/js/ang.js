// urll for api calls
let apiUrl = 'http://test_blog.local/app_dev.php/api/v1/',
    ipp = 10,
    currentPage = 123,
    postsList = [],
    currentItem = null;

//new angular app
var app = angular.module('blogApp', [
        'ngRoute', 'angularFileUpload'
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
    controller: ['$routeParams', '$scope', '$http', 'FileUploader', function PostsListController($routeParams, $scope, $http, FileUploader) {

        $scope.uploader = new FileUploader({
            url: '/app_dev.php/upload-angular'
        });

        // a sync filter
        $scope.uploader.filters.push({
            name: 'syncFilter',
            fn: function(item /*{File|FileLikeObject}*/, options) {
                console.log('syncFilter');
                return this.queue.length < 10;
            }
        });

        // an async filter
        $scope.uploader.filters.push({
            name: 'asyncFilter',
            fn: function(item /*{File|FileLikeObject}*/, options, deferred) {
                console.log('asyncFilter');
                setTimeout(deferred.resolve, 1e3);
            }
        });

        // CALLBACKS
        $scope.uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
            //console.info('onWhenAddingFileFailed', item, filter, options);
        };
        $scope.uploader.onAfterAddingFile = function(fileItem) {
            //console.info('onAfterAddingFile', fileItem);
        };
        $scope.uploader.onAfterAddingAll = function(addedFileItems) {
            //console.info('onAfterAddingAll', addedFileItems);
        };
        $scope.uploader.onBeforeUploadItem = function(item) {
            //console.info('onBeforeUploadItem', item);
        };
        $scope.uploader.onProgressItem = function(fileItem, progress) {
            //console.info('onProgressItem', fileItem, progress);
        };
        $scope.uploader.onProgressAll = function(progress) {
            //console.info('onProgressAll', progress);
        };
        $scope.uploader.onSuccessItem = function(fileItem, response, status, headers) {
            //console.info('onSuccessItem', fileItem, response, status, headers);
        };
        $scope.uploader.onErrorItem = function(fileItem, response, status, headers) {
            //console.info('onErrorItem', fileItem, response, status, headers);
        };
        $scope.uploader.onCancelItem = function(fileItem, response, status, headers) {
            //console.info('onCancelItem', fileItem, response, status, headers);
        };
        $scope.uploader.onCompleteItem = function(fileItem, response, status, headers) {
            //console.info('onCompleteItem', fileItem, response, status, headers);
            if(response.status) {
                $('#image_path').val(response.file);
                self.item.pic = response.file;
                $('#image-preview').attr('src', response.file);
                console.log('___________________________');
                console.log(self.item);
                console.log('---------------------------');
            }
        };
        $scope.uploader.onCompleteAll = function() {
            //console.info('onCompleteAll');
        };

        //console.info('uploader', $scope.uploader);

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
                enabled: true
            };
        }
        currentItem = self.item;
        $scope.saveItem = function (element) {
            var btn = $('#submit_btn');
            var form = btn.closest('form');
            var data = form.serialize();

            if (self.newItem) {
                $http.post(apiUrl + 'blogs', self.item, {'Accept': 'application/json'}).then(function (response) {
                    console.log(response);
                    location.href = '#!/posts';
                }, function (err) {
                    alert('save err')
                });
            } else {
                $http.put(apiUrl + 'blog', self.item, {'Accept': 'application/json'}).then(function (response) {
                    console.log(response);
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
                for (var i in self.items) {
                    var item = self.items[i];
                    if (item.id === id) {
                        self.items.splice(i, 1);
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