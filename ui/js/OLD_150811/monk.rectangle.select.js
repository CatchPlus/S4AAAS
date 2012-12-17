(function ($) {

/********** PUBLIC **********/

/**
 * Monk.RectangleSelect
 */
	OF.namespace('Monk.RectangleSelect');

	// constructor
	Monk.RectangleSelect = function (controller) {
		this.title		= 'rectangle';
		this.controller	= controller;
		this.api		= null;
		this.selections	= [];

		this.render();
	};

	// method.render
	Monk.RectangleSelect.method('render', function () {
		var self		= this,
			container	= this.controller.model.tpl.find('.image'),
			image		= container.find('img');

		this.drawn = false;

		this.api = $.Jcrop(image, {
			ext		: this,
			bgColor	: '#333',
			minSelect: [25, 25],
			// onChange: function () { OF.log('change'); },
			onSelect: function (rect) {
				var data	= this.ui.holder.data('current') || {},
					label	= data.label || {},
					i;				

				self.drawRectangle(rect, label.txt);
				self.drawn = true;
			},
			onRelease: function () {
				var data	= this.ui.holder.data('current') || false,
					label	= data.label || {},
					i;

				this.ui.holder.data('current', false);

				if (data) {
					i = $.inArray(label, self.controller.model.labels);

					if (i > -1) {
						self.controller.model.labels.splice(i, 1);
						self.rebuildLabels();
					}

					if (!self.drawn) {
						self.drawRectangle(data.label.rect, label.txt);
						// self.drawn = false;
					}
					this.ui.holder.data('current', false);
				}
			}
		});
	});

	// method.destruct
	Monk.RectangleSelect.method('destruct', function () {
		var labels = this.controller.model.tpl.find('.labels');

		this.api.destroy();
		this.controller.model.labels = [];
		labels.empty();
	});

	// method.drawRectangle
	Monk.RectangleSelect.method('drawRectangle', function (rect, txt) {
		var	container	= this.controller.model.tpl.find('.image'),
			tracker		= container.find('.jcrop-holder'),
			label		= new Label(rect),
			sel			= new Selection(rect, tracker, this.api, label);

		if (txt) {
			label.txt = txt;
		}

		this.selections.push(sel);
		this.api.release();
		sel.render();

		this.controller.addLabel(label);
	});

	// method.rebuildLabels
	Monk.RectangleSelect.method('rebuildLabels', function () {
		var labels		= this.controller.model.labels,
			container	= this.controller.model.tpl.find('.labels');

		container.empty();

		$.each(labels, function (i, label) {
			label.render(container);
		});
	});

	// method.save
	Monk.RectangleSelect.method('save', function (line, label) {
		var output;

		output = {
			txt		: label.txt,
			dist	: 0,
			x		: Math.round(label.rect.x * line.model.image.ratio),
			y		: Math.round(label.rect.y * line.model.image.ratio),
			w		: Math.round(label.rect.w * line.model.image.ratio),
			h		: Math.round(label.rect.h * line.model.image.ratio),
			lineid	: line.model.id
		};

		return output;
	});

	// method.createLabel
	Monk.RectangleSelect.method('createLabel', function (rect, readonly) {
		return new Label(rect, readonly);
	});

	// method.createSelection
	Monk.RectangleSelect.method('createSelection', function (label) {
		var	container	= this.controller.model.tpl.find('.image'),
			tracker		= container.find('.jcrop-holder'),
			rectangle	= $('<div />');

		rectangle
			.addClass('selection')
			.width(label.rect.w)
			.height(label.rect.h)
			.css({
				top	: label.rect.y + 'px',
				left: label.rect.x + 'px'
			})
			.appendTo(tracker);
	});


/********** PRIVATE **********/

	var Selection, Label;

/**
 * Selection
 */

	// constructor
	Selection = function (rect, tracker, api, label) {
		this.rect		= rect;
		this.tracker	= tracker;
		this.api		= api;
		this.label		= label;
	};

	// method.render
	Selection.method('render', function () {
		var self		= this,
			rectangle	= $('<div />');

		rectangle
			.addClass('selection')
			.width(this.rect.w - 2)		// border (1px) correction (left/right)
			.height(this.rect.h - 2)	// border (1px) correction (top/bottom)
			.css({
				top	: this.rect.y + 'px',
				left: this.rect.x + 'px'
			})
			.appendTo(this.tracker);

		rectangle.data({ obj: this });

		rectangle.bind({
			click: function () {
				var bounds	= [self.rect.x, self.rect.y, self.rect.x2, self.rect.y2],
					data	= $(this).data('obj'),
					holder	= $(this).closest('.jcrop-holder');

				holder.data('current', self);

				self.api.setSelect(bounds);
				self.api.setOptions({ allowResize: true });

				$(this).remove();
				data.label.element.remove();
			}
			
		});
	});


/**
 * Label
 */

	// constructor
	Label = function (rect, readonly) {
		this.txt		= '';
		this.element	= $('<input type="text" />');
		this.rect		= rect;
		this.x			= 0 || rect.x;
		this.w			= 0 || rect.w;
		this.readonly	= readonly || false;
	};

	// method.render
	Label.method('render', function (container) {
		var self = this; 
	
		this.element
			.addClass('label')
			.val(this.txt)
			.width(this.w)
			.css({
				left: this.x + 'px'
			})
			.keyup(function (event) {
				self.txt = $(this).val();
			});

		if (this.readonly) {
			this.element.prop('readonly', true);
		}

		container.append(this.element);
	});

}(jQuery));