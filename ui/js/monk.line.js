(function ($) {

/********** PUBLIC **********/

/**
 * Monk.Line
 */
	OF.namespace('Monk.Line');

	// constructor
	Monk.Line = function (id, options) {
		this.opts		= options;
		this.model		= new Model(id, options);
		this.controller	= new Controller(this.model);
		this.view		= new View(this.model, this.controller, options.view);
	};


/********** PRIVATE **********/

	var Model, Controller, View;

/**
 * Model
 */

	// constructor
	Model = function (id, options) {
        /*
        imgsrc = 'http://application01.target.rug.nl/monk/' + self.model.opts.request.page.substr(self.model.opts.request.page.indexOf('.') + 1, self.model.opts.request.page.length - (5 + self.model.opts.request.page.indexOf('.') + 1)) +
                        '/Pages/' + self.model.opts.request.page.substr(self.model.opts.request.page.indexOf('-') + 1) +
                        '/Lines/web-grey/' + self.model.opts.id.substr(self.model.opts.id.indexOf('.') + 1),
         */
		this.id		= id;
//		this.key	= id.match(/^.*-line-[0-9]{3}/)[0];
		this.key	= id;
		this.opts	= options;
		this.image	= {
			//src		: options.line.imgSrc + '/' + id + '.' + options.line.imgExt,
//            src     : options.line.imgSrc + options.id.substr(options.id.indexOf('.') + 1, options.id.length - (5 + options.id.indexOf('.') + 1)) +
//                        '/Pages/' + options.id.substr(options.id.indexOf('-') + 1) +
//                        '/Lines/web-grey/' + id.substr(id.indexOf('.') + 1),
//            src     : options.line.imgSrc + options.id.substr(0, options.id.length - 5) +
//                '/Pages/' + options.id.substr(options.id.indexOf('-') + 1) +
//                '/Lines/web-grey/' + id,
            src     : options.line.imgSrc + id,
			dim		: {},
			ratio	: 1
		};
//        OF.log(this.image.src);
		this.selectionInstance = null;
		this.labels = [];
		this.tpl	= null;

		// events
		this.events = {
			methodSwitched	: new OF.Event('line.model.method.switched'),
			labelAdded		: new OF.Event('line.model.label.added'),
			labelRemoved	: new OF.Event('line.model.label.removed')
		};
	};

	// method.sort
	Model.method('sort', function () {
		return;
	});


/**
 * Controller
 */

	// constructor
	Controller = function (model) {
		this.model = model;
	};

	// method.setMethod
	Controller.method('setMethod', function (method) {
		if (this.model.selectionInstance) {
			this.model.selectionInstance.destruct();
		}
		this.model.selectionInstance = new this.model.opts.line.selectionMethods[method](this);
		this.model.events.methodSwitched.notify({ method: this.model.selectionInstance });
	});

	// method.addLabel
	Controller.method('addLabel', function (label) {
		this.model.labels.push(label);
		this.model.events.labelAdded.notify({ label: label });
	});

	// method.removeLabel
	Controller.method('removeLabel', function (index) {
		this.model.labels.splice(index, 1);
		this.model.events.labelRemoved.notify({ index: index });
	});

	// method.requestLabels
	Controller.method('requestLabels', function () {
		var self = this;

		$.ajax({
			url		: 'index.php?cmd=readlabel',
			type	: 'get',
			dataType: 'json',
			data	: {
				page: this.model.opts.id,
				line: this.model.id
			},
			success	: function (data) {
				var ratio = self.model.image.ratio,
					rSelect = false,
					i = 0, methods,
					tempData = [], prevX = null,
                    marge = 1;

				if (data.length <= 0) {
					return false;
				}

				// disable method switch
				methods = self.model.tpl.find('.methods');
				methods
					.unbind()
					.click(function () {
						return false;
					});

				while (i < data.length && !rSelect) {
					if (parseInt(data[i][2]) > 0) {
						rSelect = true;
					}
					i++;
				}

				if (rSelect) {
					self.setMethod('rectangle');
				}
				methods.find('a:not(.selected)').addClass('disabled');

				// sort label data and search for gaps between labels
				if (!rSelect) {

					data.sort(function (a, b) {
						a[1] = parseInt(a[1]);
						b[1] = parseInt(b[1]);

						if (a[1] > b[1]) {
							return 1;
						} else if (a[1] < b[1]) {
							return -1;
						}
						return 0;
					});

					$.each(data, function (i, l) {
						if (i > 0) {
							if ((prevX+marge) < l[1] ) {
								tempData.push(['', prevX, 0, l[1] - prevX, l[4], 'dummy']);
							}
						}
						prevX = l[1] + parseInt(l[3]);
					});
					data = data.concat(tempData);

					data.sort(function (a, b) {
						a[1] = parseInt(a[1]);
						b[1] = parseInt(b[1]);

						if (a[1] > b[1]) {
							return 1;
						} else if (a[1] < b[1]) {
							return -1;
						}
						return 0;
					});
				}

				$.each(data, function (i, label) {
					var rect, labelInst;

					rect = {
						x: label[1] / ratio,
						y: label[2] / ratio,
						w: label[3] / ratio,
						h: label[4] / ratio
					};

					if (label[5] == 'dummy') {
						labelInst = self.model.selectionInstance.createLabel(rect, false);
					} else {
						labelInst = self.model.selectionInstance.createLabel(rect, true);
					}
					labelInst.txt = label[0];
                    labelInst.element.attr('title', label[0]);
					self.addLabel(labelInst);

					if (i === data.length - 1) {
						self.model.selectionInstance.createSelection(labelInst, true);
					} else {
						self.model.selectionInstance.createSelection(labelInst, false);
					}
				});

				if (typeof self.model.selectionInstance.update === 'function') {
					self.model.selectionInstance.update();
				}
			}
		});
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
		this.model.events.methodSwitched.attach('line.view', function () { self.rebuildMethods(); });
		this.model.events.labelAdded.attach('line.view', function () { self.model.selectionInstance.rebuildLabels(); });
	};

	// method.show
	View.method('show', function () {
		var self		= this,
			container	= this.opts.elements.container,
			tpl			= this.opts.elements.template.clone(),
			img			= $(new Image()),
			nonce		= null;

		// template
		tpl.hover(
			function () {
				if (!tpl.hasClass('current')) {
					tpl.addClass('hover');
				}
			},
			function () {
				tpl.removeClass('hover');
			}
		);


		tpl.find('.methods a[href=#point]').text(Monk.i18n.point);
		tpl.find('.methods a[href=#rectangle]').text(Monk.i18n.rectangle);

		// switch selection methods
		tpl.find('.methods').click(function (e) {
			var answer, method;

			if ($(e.target).is('a')) {
				if (self.model.labels.length == 0) {
					answer = true;
				} else {
					answer = confirm(Monk.i18n.confirm);
				}

				if (answer) {
					method = $(e.target).attr('href').match(/^#([a-z]+)$/i)[1];
					self.controller.setMethod(method);
				}
			}
			return false;
		});

		tpl.attr('id', this.model.key).removeClass('hidden');
		tpl.find('.info').text(this.model.key);
		container.append(tpl);

		// image load event
		img.load(function () {
			var ow, oh, nw, nh;

			// get original/resized dimensions
			ow = this.width;
			oh = this.height;
			tpl.find('.image').html(this);
			nw = $(this).width();
			nh = $(this).height();

			// ratio
			self.model.image.ratio = ow / nw;

			self.model.image.dim = { w: ow, h: oh};
			self.model.selectionInstance = new self.model.opts.line.selectionMethods[self.model.opts.line.defaultMethod](self.controller);
			self.rebuildMethods();
			self.controller.requestLabels();
		});

		// load image
		nonce = '?' + new Date().getTime();	// force IE to trigger img.load
		img.attr('src', this.model.image.src + nonce);

		this.model.tpl = tpl;
	});

	// method.rebuildMethods
	View.method('rebuildMethods', function () {
		var methods = this.model.tpl.find('.methods');

		methods.find('a').removeClass('selected');
		methods.find('a[href=#' + this.model.selectionInstance.title + ']').addClass('selected');
	});


}(jQuery));