(function ($) {

/********** PUBLIC **********/

/**
 * Monk.PointSelect
 */
	OF.namespace('Monk.PointSelect');

	// constructor
	Monk.PointSelect = function (controller) {
		var self = this;

		this.title			= 'point';
		this.controller		= controller;
		this.intersections	= [];

		// events
		this.events = {
			added: new OF.Event('point.select.intersection.added')
		};

		// listeners
		this.events.added.attach('point.select', function () { self.update(); });

		this.render();
	};

	// method.destruct
	Monk.PointSelect.method('destruct', function () {
		var container	= this.controller.model.tpl.find('.image'),
			tracker		= container.find('.tracker'),
			labels		= this.controller.model.tpl.find('.labels');

		this.controller.model.labels = [];
		tracker.remove();
		labels.empty();
	});

	// method.render
	Monk.PointSelect.method('render', function () {
		var self		= this,
			container	= this.controller.model.tpl.find('.image'),
			image		= container.find('> img'),
			tracker		= $('<div class="tracker" />');

		tracker
			.css({
				width		: image.width() + 'px',
				height		: image.height() + 'px',
				'margin-top': -image.height() + 'px' 
			})
			.bind({
				click: function (event) {
					var x = Math.round(event.pageX - $(this).offset().left),
						label, intsec, i, len;

					intsec = new Intersection(x);
					self.intersections.push(intsec);
					self.sortIntersections();

					i = $.inArray(intsec, self.intersections);
					len = self.intersections.length;

					// case: first intersection
					if (i === 0 && len === 1) {
						self.events.added.notify({ 'case': 'first intersection', intsec: intsec });
						return;
					}

					// case: intersection @ begin
					if (i === 0 && len > 1) {
						label = new Label({
							begin	: self.intersections[0],
							end		: self.intersections[1]
						});

						self.controller.addLabel(label);
						self.reorder({ 'case': 'intersection @ begin', label: label, intsec: intsec });
						return;
					}

					// case: intersection @ end
					if (i === len - 1 && len > 1) {
						label = new Label({
							begin	: self.intersections[i - 1],
							end		: self.intersections[i]
						});

						self.controller.addLabel(label);
						self.reorder({ 'case': 'intersection @ end', label: label, intsec: intsec });
						return;
					}

					// case: intersection @ between
					if (i > 0 && i < len - 1) {
						label = new Label({
							begin	: self.intersections[i - 1],
							end		: self.intersections[i]
						});

						self.controller.addLabel(label);
						self.reorder({ 'case': 'intersection @ between', label: label, intsec: intsec });
						return;
					}
				}
			})
			.appendTo(container);
	});

	// method.update
	Monk.PointSelect.method('update', function () {
		var self	= this,
			tracker	= this.controller.model.tpl.find('.tracker'),
			labelcontainer	= this.controller.model.tpl.find('.labels');

		tracker.empty();
		this.sortIntersections();

		labelcontainer.empty();
		this.sortLabels;

		$.each(this.intersections, function (i, intsec) {
			intsec.render(i, tracker, self.controller.model.key, self);
		});

		$.each(this.controller.model.labels, function (i, label) {
			label.intersections = {
				begin	: self.intersections[i],
				end		: self.intersections[i + 1]
			}
			label.render(labelcontainer);
		});

	});

	// method.sortIntersections
	Monk.PointSelect.method('sortIntersections', function () {
		this.intersections.sort(function (a, b) {
			if (a.x > b.x) {
				return 1;
			} else if (a.x < b.x) {
				return -1;
			}
			return 0;
		});
	});

	// method.sortLabels
	Monk.PointSelect.method('sortLabels', function () {
		this.controller.model.labels.sort(function (a, b) {
			if (a.intersections.begin.x > b.intersections.begin.x) {
				return 1;
			} else if (a.intersections.begin.x < b.intersections.begin.x) {
				return -1;
			}
			return 0;
		});
	});

	// method.rebuildLabels
	Monk.PointSelect.method('rebuildLabels', function () {
		var labels		= this.controller.model.labels,
			container	= this.controller.model.tpl.find('.labels');

		container.empty();

		$.each(labels, function (i, label) {
			label.render(container);
		});
	});

	// method.reorder
	Monk.PointSelect.method('reorder', function (notification) {
		var self = this,
			len = this.controller.model.labels.length;

		this.sortIntersections();
		this.sortLabels();

		$.each(this.controller.model.labels, function (index, value) {
			value.intersections = {
				begin	: self.intersections[index],
				end		: self.intersections[index + 1]
			};

			// register label index for highlighting
			self.intersections[index].label = index;
			if (index === len - 1) {
				self.intersections[index + 1].label = index;
			}
		});

		this.events.added.notify(notification);
	});


	// method.save
	Monk.PointSelect.method('save', function (line, label) {
		var output;

		output = {
			txt		: label.txt,
			dist	: 0,
			x		: Math.round(label.intersections.begin.x * line.model.image.ratio),
			y		: 0,
			w		: Math.round((label.intersections.end.x - label.intersections.begin.x) * line.model.image.ratio),
			h		: line.model.image.dim.h,
			lineid	: line.model.id
		};

		return output;
	});

	// method.createLabel
	Monk.PointSelect.method('createLabel', function (rect, readonly) {
		var intersections = {
			begin	: new Intersection(rect.x, readonly),
			end		: new Intersection(rect.x + rect.w, readonly)
		};

		return new Label(intersections, readonly);
	});

	// method.createSelection
	Monk.PointSelect.method('createSelection', function (label, last) {
		var	self		= this,
			container	= this.controller.model.tpl.find('.image'),
			tracker		= container.find('.tracker'),
			prevIntsec, tmpArr = {};

		this.intersections.push(label.intersections.begin);

		if (last) {
			this.intersections.push(label.intersections.end);
		}
	});


	// method.removeSelection
	Monk.PointSelect.method('removeSelection', function (index) {
		if (this.intersections.length - 1 === index) {
			this.controller.removeLabel(index - 1);
		} else {
			this.controller.removeLabel(index);
		}
		this.intersections.splice(index, 1);
		this.update({ 'case': 'intersection removed', index: index });
	});

/********** PRIVATE **********/

	var Intersection, Label;

/**
 * Intersection
 */

	Intersection = function (x, readonly) {
		this.x		= x;
		this.angle	= 45;
		this.color	= 'green';
		this.hcorr	= 0;
		this.readonly = readonly;
	};

	Intersection.method('render', function (index, tracker, key, parent) {
		var self = this,
			container, handle, image,
			h, w;

		h = tracker.height();
		w = Math.round(h / Math.tan(this.angle / (180 / Math.PI)));
		this.hcorr = w / 2;

		// container
		container = $('<div />')
			.addClass('intersection')
			.width(w)
			.css({ 'left': self.x - self.hcorr + 'px' });

		if (!this.readonly) {
			container
				.draggable({
					handle		: '.handle',
					containment	: [index, parent.intersections, w],
					axis		: 'x',
					stack		: '.handle',
					stop: function (event, ui) {	// update intersection x-coord
						event.stopPropagation();
						self.x += (ui.position.left - ui.originalPosition.left);
						parent.reorder();
					}
				});
		}

		if (!this.readonly) {
			// handle
			handle = $('<div />')
				.addClass('handle')
				.attr('title', Monk.i18n.handle)
				.data('i', index)
				.click(function (event) {
					event.stopPropagation();
					return false;
				})
				.dblclick(function () {
					parent.removeSelection($(this).data('i'));
				})
				.mouseenter(function () {
					// image.attr('src', 'css/images/line.orange.png');
					// $('#' + key).find('.label input[type=text]').eq(self.label).addClass('highlight');
				})
				.mouseleave(function () {
					// image.attr('src', 'css/images/line.green.png');
					// $('#' + key).find('.label input[type=text]').eq(self.label).removeClass('highlight');
				})
				.appendTo(container);
		}

		// image
		image = $('<img />')
			.addClass('image')
			.attr('src', 'css/images/line.' + self.color + '.png')
			.width(w)
			.height(h)
			.appendTo(container);

		if (!this.readonly) {
			handle
				.clone(true)
				.css({
					'float'		: 'left',
					'margin-top': '1px'
				})
				.appendTo(container);
		}

		tracker.append(container);
	});


/**
 * Label
 */

	// constructor
	Label = function (intersections, readonly) {
		this.txt			= '';
		this.element		= $('<input type="text" />');
		this.intersections	= {
			begin	: intersections.begin,
			end		: intersections.end
		};
		this.readonly = readonly || false;

	};

	// method.render
	Label.method('render', function (container) {
		var self	= this,
			intsec	= this.intersections;

		this.element
			.addClass('label')
			.val(this.txt)
			.width(intsec.end.x - intsec.begin.x)
			.css({
				left: this.intersections.begin.x + 'px'
			})
			.keydown(function (event) {
				switch (event.which) {
					case 32:	// space
						var $this = $(this);

						if ($this.next().is(':not([readonly])')) {
							$this.next().focus();
						} else {
							$this.nextUntil(':not([readonly])').last().next().focus();
						}
						return false;
						break;
					case 8:	// backspace
						var $this = $(this);

						if ($this.val() === '' || $this.is('[readonly]')) {
							if ($this.prev().is(':not([readonly])')) {
								$this.prev().focus();
								return false;
							} else {
								$this.prevUntil(':not([readonly])').last().prev().focus();
								return false;
							}
						}
						break;
				}
			})
			.keyup(function (event) {
				self.txt = $(this).val();
			});

		if (this.readonly) {
			this.element.prop('readonly', true);
		}

		container.append(this.element);
	});

	// extend ui.draggable: overwrite _setContainment
	$.ui.draggable.prototype._setContainment = function () {

		var o = this.options;

		if (o.containment.constructor === Array && o.containment.length === 3) {
			var index = o.containment[0],
				intersections = o.containment[1],
				intsecwidth = o.containment[2],
				ce, co, over;

			o.containment = this.helper[0].parentNode;

			ce = $(o.containment)[0]; if(!ce) return;
			co = $(o.containment).offset();
			over = ($(ce).css("overflow") != 'hidden');

			this.containment = [
				co.left + (parseInt($(ce).css("borderLeftWidth"),10) || 0) + (parseInt($(ce).css("paddingLeft"),10) || 0) - this.margins.left,
				co.top + (parseInt($(ce).css("borderTopWidth"),10) || 0) + (parseInt($(ce).css("paddingTop"),10) || 0) - this.margins.top,
				co.left + ce.offsetWidth - (parseInt($(ce).css("borderLeftWidth"),10) || 0) - (parseInt($(ce).css("paddingRight"),10) || 0) - this.helperProportions.width - this.margins.left,
				co.top + ce.offsetHeight - (parseInt($(ce).css("borderTopWidth"),10) || 0) - (parseInt($(ce).css("paddingBottom"),10) || 0) - this.helperProportions.height - this.margins.top
			];

			if (intersections.length > 1) {	// multiple intersections
				if (index > 0) {
					this.containment[0] += intersections[index - 1].x;
				}
				if (index < intersections.length - 1) {
					this.containment[2] -= (ce.offsetWidth - intersections[index + 1].x);
				}
			}

			// adjust containment
			this.containment[0] -= (.5 * intsecwidth) - 10;
			this.containment[2] += (.5 * intsecwidth) - 10;

		} else {

			if(o.containment == 'parent') o.containment = this.helper[0].parentNode;
			if(o.containment == 'document' || o.containment == 'window') this.containment = [
				(o.containment == 'document' ? 0 : $(window).scrollLeft()) - this.offset.relative.left - this.offset.parent.left,
				(o.containment == 'document' ? 0 : $(window).scrollTop()) - this.offset.relative.top - this.offset.parent.top,
				(o.containment == 'document' ? 0 : $(window).scrollLeft()) + $(o.containment == 'document' ? document : window).width() - this.helperProportions.width - this.margins.left,
				(o.containment == 'document' ? 0 : $(window).scrollTop()) + ($(o.containment == 'document' ? document : window).height() || document.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top
			];

			if(!(/^(document|window|parent)$/).test(o.containment) && o.containment.constructor != Array) {
				var ce = $(o.containment)[0]; if(!ce) return;
				var co = $(o.containment).offset();
				var over = ($(ce).css("overflow") != 'hidden');

				this.containment = [
					co.left + (parseInt($(ce).css("borderLeftWidth"),10) || 0) + (parseInt($(ce).css("paddingLeft"),10) || 0) - this.margins.left,
					co.top + (parseInt($(ce).css("borderTopWidth"),10) || 0) + (parseInt($(ce).css("paddingTop"),10) || 0) - this.margins.top,
					co.left+(over ? Math.max(ce.scrollWidth,ce.offsetWidth) : ce.offsetWidth) - (parseInt($(ce).css("borderLeftWidth"),10) || 0) - (parseInt($(ce).css("paddingRight"),10) || 0) - this.helperProportions.width - this.margins.left,
					co.top+(over ? Math.max(ce.scrollHeight,ce.offsetHeight) : ce.offsetHeight) - (parseInt($(ce).css("borderTopWidth"),10) || 0) - (parseInt($(ce).css("paddingBottom"),10) || 0) - this.helperProportions.height - this.margins.top
				];
			} else if(o.containment.constructor == Array) {
				this.containment = o.containment;

			}
		}

	};

}(jQuery));