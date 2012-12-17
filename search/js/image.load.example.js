(function () {
	var img, ratio, source;

	img     = $(new Image());
	ratio   = 1;
	source  = '';

	// callback after image is loaded
	img.load(function () {
		var ow, oh, nw, nh, $this;

		$this = $(this);

		// original values
		ow = this.width;
		oh = this.height;
                
                $('.image .shadow').html(this);

		// new values
		nw = $this.width();
		nh = $this.height();

		// set ratio
		ratio = ow / nw;
	});

	// set image source, triggers load event
	img.attr('src', source);
}());