// Filename: views/download.js

define([
	'jquery',
	'underscore',
	'backbone',
	'config',
	'i18n!nls/dictionary',
	'text!templates/download.html',
	'views/alert',
	'models/image',
], function (
	$,
	_,
	Backbone,
	Config,
	i18n,
	downloadTemplate,
	AlertView,
	ImageModel
) {
	var DownloadView = Backbone.View.extend({

		el: '#app-body .container',

		events: {
			'click #btn-download': 'evt_download_clicked',
			'click #btn-rdf-download': 'evt_rdf_download_clicked',
			'click #btn-start-again': 'evt_start_again_clicked'
		},

		model: new ImageModel(),

		initialize: function () {
			var self = this;

			// restore model
			$.getJSON('./model/', function (data) {
				self.model.attributes = data;
				self.render();
			});

		},

		render: function () {
			var self = this,
				compiledTemplate = _.template(downloadTemplate, {
					i18n: i18n,
					debug: Config.debug ? "class='debug'" : ''
				});

			// add template to DOM
			this.$el.html(compiledTemplate);

			// add sub views
			this.alert = new AlertView();

			// start cutout process
			this.start_process();

			return this;
		},

		start_process: function () {
			var self = this;

			$.ajax({
				url: './cutout/',
				type: 'get',
				dataType: 'json',
				success: function (data) {
					if (data.success) {
						self.check_process();
					} else {
						self.alert.render('error', i18n.alert_cutout_failed);
					}
				}
			});
		},

		check_process: function () {
			var self = this;

			$.ajax({
				url: './check/',
				type: 'get',
				dataType: 'json',
				success: function (data) {
					if (data.success) {

						// enable buttons
						self.$('#btn-download').removeClass('disabled');
						self.$('#btn-rdf-download').removeClass('disabled');

						// display cutout
						self.render_cutout(data.xml);
					} else {
						self.check_process();
					}
				}
			});
		},

		render_cutout: function (xml) {
			var self = this;

			// preload image
			var img = new Image();

			$(img).on('load', function () {
				var factor, x_before, x_after;

				x_before = this.width;

				// add image to DOM
				self.$('#image').html(img);

				x_after = $(this).width();

				factor = x_after / x_before;

				// display lines
				_.each(xml.linestrips.linestrip, function (value, key, list) {
					var y = Math.round(value.y2 * factor);

					if (value.id == list.length - 1) {
						return;
					}

					$('<hr />')
						.addClass('line')
						.css({
							top: y,
							width: x_after
						})
						.appendTo($('#image'));
				});


			});

			// set image source
			img.src = './render';
		},

		evt_download_clicked: function (evt) {
			window.location.href = './zip';
		},

		evt_rdf_download_clicked: function (evt) {
			window.location.href = './rdf';
		},

		evt_start_again_clicked: function (evt) {
			window.location.href = './';
		}

	});

	return DownloadView;

});