(function ($) {

/********** PUBLIC **********/

/**
 * Monk.Page
 */
	OF.namespace('Monk.Page');

	// constructor
	Monk.Page = function (options) {
		this.opts		= options;
		this.id			= options.id;

		this.model		= new Model();
		this.controller	= new Controller(this.model);
		this.view		= new View(this.model, this.controller, options.view);
	};

	// method.set
	Monk.Page.method('set', function (data) {
		var self = this;

		// sort data
		data.sort();

		// fill model
		$.each(data, function (i, id) {
			self.model.lines[id] = new Monk.Line(id, self.opts);
		});
		this.model.events.load.notify({ data: data, lines: self.model.lines });
	});

	// method.show
	Monk.Page.method('show', function () {
		this.view.show();
	});

	// method.save
	Monk.Page.method('save', function (saveObj) {
		this.controller.saveObj = saveObj;
		// this.model.events.save.attach('page', listener);
	});

/********** PRIVATE **********/

	var Model, Controller, View;

/**
 * Model
 */

	// constructor
	Model = function () {
		this.lines = {};
		this.current = -1;

		// events
		this.events = {
			update	: new OF.Event('page.model.update'),
			load	: new OF.Event('page.model.load'),
			save	: new OF.Event('page.model.save'),
			current	: new OF.Event('page.model.current')
		};
	};

/**
 * Controller
 */

	// constructor
	Controller = function (model) {
		this.model = model;
	};

	// method.save
	Controller.method('save', function () {
		var self = this;

		this.saveObj.data.labels = [];

		$.each(this.model.lines, function (i, line) {

			$.each(line.model.labels, function (j, label) {
				var v, outputs;

				if (label.txt == '' || label.readonly) {
					return;
				}

				outputs = line.model.selectionInstance.save(line, label);
				self.saveObj.data.labels.push(outputs);
			});
		});

		$.ajax($.extend({
			success	: function (data) {
				if (typeof callback === 'function') {
					callback(data);
				}
			},
			error	: function () {
				$.error('data request failure');
			}
		}, self.saveObj));

		this.model.events.save.notify({ data: this.model.lines });
	});

	// method.current
	Controller.method('current', function (id) {
		if (this.model.current !== id) {
			this.model.current = id;
			this.model.events.current.notify({ current: this.model.current });
		}
	});

/**
 * View
 */

	// constructor
	View = function (model, controller, options) {
		var self = this;

		this.model		= model;
		this.controller	= controller;
		this.opts		= options;

		// listeners
		this.model.events.update.attach('page.view', function () {
			self.rebuild();
		});
	};

	// method.show
	View.method('show', function () {
		var self	= this,
			el		= this.opts.elements;

		// current
		el.current.click(function () {
			if (self.model.current !== -1) {
				$('html, body').animate({
					scrollTop: $('#' + self.model.current).offset().top - 100
				}, 1000);
			}
			return false;
		});

		// save
		el.save.click(function () {
			self.controller.save();
			return false;
		});

        el.remove.click(function() {
            $.ajax({
                url     : 'index.php',
                type    : 'get',
                data    : {
                        cmd : 'delete',
                        page: $.getUrlVar('page')
                },
                success : function(data)
                {
                    alert(data)
                    window.history.back();
                }
            });
        });

		// show lines
		$.each(this.model.lines, function (i, line) {
			line.view.show();
		});

		// click (event delegation)
		el.container.click(function (e) {
			var el = $(e.target).closest('.line');

			if (el.is('.line')) {
				$('#' + self.model.current).removeClass('current');
				el.removeClass('hover');
				el.addClass('current');
				self.controller.current(el.attr('id'));
			}
		});

	});

	// method.rebuild
	View.method('rebuild', function () {

	});

}(jQuery));