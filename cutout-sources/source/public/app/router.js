// Filename: router.js

define([
	'jquery',
	'underscore',
	'backbone',
	'views/pagenotfound',
	'views/upload',
	'views/edit',
	'views/download'
], function (
	$,
	_,
	Backbone,
	PageNotFoundView,
	UploadView,
	EditView,
	DownloadView
) {

	var AppRouter = Backbone.Router.extend({

		// define routes
		routes: {
			'': 'uploadRoute',
			'edit': 'editRoute',
			'download': 'downloadRoute',
			'*route': 'pageNotFoundRoute'
		},

		// upload route
		uploadRoute: function () {
			var view = new UploadView();
			view.render();
		},

		// edit route
		editRoute: function () {
			var view = new EditView();
			view.render();
		},

		// download route
		downloadRoute: function () {
			var view = new DownloadView();
			view.render();
		},

		// page not found
		pageNotFoundRoute: function (route) {
			var view = new PageNotFoundView();
			view.render();
		}

	}),

	initialize = function () {
		var app_router = new AppRouter();
		Backbone.history.start();
	};

	return {
		initialize: initialize
	};

});