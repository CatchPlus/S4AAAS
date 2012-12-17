// Filename: main.js

// require configuration
require.config({
	baseUrl: './app',
	paths: {
		backbone: '../lib/backbone/backbone.min',
		underscore: '../lib/underscore/underscore.min',
		jquery: '../lib/jquery/jquery-1.7.2.min',
		bootstrap: '../lib/bootstrap/js/bootstrap.min',
		domReady: '../lib/require/plugins/domReady',
		text: '../lib/require/plugins/text',
		i18n: '../lib/require/plugins/i18n',
		jcrop: '../lib/jquery/plugins/jcrop/jquery.Jcrop.min'
	},
	shim: {
		'underscore': {
			exports: '_'
		},
		'backbone': {
			deps: ['underscore', 'jquery'],
			exports: 'Backbone'
		},
		'bootstrap': ['jquery']
	}
});

// start application
require([
	'app',
	'domReady!'
], function (App) {

	App.initialize();

});