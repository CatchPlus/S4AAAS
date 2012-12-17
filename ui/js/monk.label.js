(function ($) {

/********** PUBLIC **********/

/**
 * Monk.Label
 */
	OF.namespace('Monk.Label');

	// constructor
	Monk.Label = function (x, w, container) {
		this.container	= container;
		this.txt		= '';
		this.element	= $('<input type="text" />');
		this.x			= 0 || x;
		this.w			= 0 || w;
	};

	// method.render
	Monk.Label.method('render', function () {
		this.element
			.addClass('label')
			.val('')
			.width(this.w)
			.css({
				left: this.x
			});

		this.container.append(this.element);
	});

}(jQuery));