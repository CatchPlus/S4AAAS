// Filename: views/upload.js

define([
	'jquery',
	'underscore',
	'backbone',
	'config',
	'i18n!nls/dictionary',
	'text!templates/upload.html',
	'views/alert',
	'bootstrap'
], function (
	$,
	_,
	Backbone,
	Config,
	i18n,
	uploadTemplate,
	AlertView
) {

	var UploadView = Backbone.View.extend({

		el: '#app-body .container',

		events: {
			'click #btn-upload': 'evt_upload_clicked'
		},

		render: function () {
			var self = this,
				compiledTemplate = _.template(uploadTemplate, {
					i18n: i18n,
					debug: Config.debug ? "class='debug'" : ''
				});

			// add template to DOM
			this.$el.html(compiledTemplate);

			// add sub views
			this.alert = new AlertView();

			// catch response from iframe
			this.$('iframe').on('load', function () {
				var response = null,
					alert = self.alert;

				// prevent first time load event
				if (self.$('form').attr('action') == '') {
					return;
				}

				try {
					response = $.parseJSON($(this).contents().find('body').html());
				} catch (e) {
					alert.render('error', i18n.alert_upload_failed);
				}

				// reset button state
				self.$('#btn-upload').button('reset');

				if (response && response.success) {
					window.location.href = '#/edit';
				} else if (response && response.status == 'FILE IS NOT AN IMAGE') {
					alert.render('warn', i18n.alert_not_image);
				} else {
					alert.render('error', i18n.alert_upload_failed);
				}
			});

			return this;
		},

		evt_upload_clicked: function (evt) {
			var filename = this.$('form input[name=filename]'),
				form = this.$('form');

			// set button state to loading
			this.$('#btn-upload').button('loading');

			// submit form using iframe
			if (filename.val().length > 0) {
				form.attr('action', './upload/').submit();
			} else {
				// reset button state
				$('#btn-upload').button('reset');
			}
		}

	});

	return UploadView;

});