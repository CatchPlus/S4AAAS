// Filename: views/alert.js

define([
	'jquery',
	'underscore',
	'backbone',
	'text!templates/alert.html',
	'bootstrap'
], function (
	$,
	_,
	Backbone,
	alertTemplate
) {

	var AlertView = Backbone.View.extend({

		el: '#alert-view',

		render: function (type, message) {
			var compiledTemplate = _.template(alertTemplate, {
				alert_type: 'alert-' + type,
				alert_message: message
			});
			this.$el.html(compiledTemplate);

			return this;
		}

	});

	return AlertView;

});