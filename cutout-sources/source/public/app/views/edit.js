// Filename: views/edit.js

define([
	'jquery',
	'underscore',
	'backbone',
	'config',
	'i18n!nls/dictionary',
	'text!templates/edit.html',
	'views/alert',
	'models/image',
	'jcrop'
], function (
	$,
	_,
	Backbone,
	Config,
	i18n,
	editTemplate,
	AlertView,
	ImageModel
) {

	var EditView = Backbone.View.extend({

		el: '#app-body .container',

		events: {
			'keypress input[type=text]': 'evt_submit_form',
			'click #btn-rotate': 'evt_rotate_image',
			'click #btn-next': 'evt_next_page'
		},

		model: new ImageModel(),

		initialize: function () {
			var self = this;

			this.model.on('change:angle', function (model, value) {
				self.render();
			});
		},

		render: function () {
			var self = this,
				compiledTemplate = _.template(editTemplate, {
					i18n: i18n,
					debug: Config.debug ? "class='debug'" : '',
					angle: this.model.get('angle'),	
					source: this.model.getSource()
				});

			// add template to DOM
			this.$el.html(compiledTemplate);

			// add sub views
			this.alert = new AlertView();

			// preload image
			var img = new Image();

			$(img).on('load', function () {
				var el = self.$('#image img');

				self.model.set({
					size: {
						real: { width: this.width, height: this.height },
						screen: { width: el.width(), height: el.height() }
					},
					cropArea: {
						x1: 0, y1: 0,
						x2: el.width(), y2: el.height()
					}
				});
			});
			img.src = this.$('#image img').attr('src');

			// apply jcrop
			this.$('#image img').Jcrop({
				onSelect: function (c) {
					self.model.set({
						cropArea: {
							x1: c.x, y1: c.y, x2: c.x2, y2: c.y2
						}
					});
				}
			});

			return this;
		},

		evt_submit_form: function (evt) {

			if (evt.keyCode == 13) { // Enter key
				this.evt_rotate_image();

				// prevent form submit
				evt.preventDefault();
			}
		},

		evt_rotate_image: function (evt) {
			var self = this,
				angle = this.$('input[type=text]').val();

			// validate angle
			if (/^-?\d+$/.test(angle)) {

				// set button state to loading
				this.$('#rotate').button('loading');

				// update model
				this.model.set({ angle: angle });

			} else {

				// TODO: handle invalid angle

			}
		},

		evt_next_page: function (evt) {

			// store model in session
			$.post('./model/', { model: this.model.attributes }, function () {
				window.location.href = '#/download';
			});

			// TODO: handle ajax error
		}

	});

	return EditView;

});