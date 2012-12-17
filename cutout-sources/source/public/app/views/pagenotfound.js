// Filename: views/pagenotfound.js

define([
	'jquery',
	'underscore',
	'backbone',
	'i18n!nls/dictionary',
	'text!templates/pagenotfound.html'
], function (
	$,
	_,
	Backbone,
	i18n,
	pageNotFoundTemplate
) {

	var PageNotFoundView = Backbone.View.extend({

		el: '#app-body .container',

		render: function () {
			var compiledTemplate = _.template(pageNotFoundTemplate, {
				i18n: i18n
			});
			this.$el.html(compiledTemplate);

			return this;
		}

	});

	return PageNotFoundView;

});