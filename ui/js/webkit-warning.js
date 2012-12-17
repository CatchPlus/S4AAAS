(function ($) {
	$(document).ready(function () {

		var message = "This page has some problems running on webkit browsers (Chrome and Safari). Please use a different browser (Firefox or IE).";

		if ($.browser.webkit) {
			$('<p class="webkit-warning">')
				.html(message)
				.css({
					'background':	'#ce0000',
					'color':		'#fff',
					'font-weight':	'bold',
					'height':		'3em',
					'line-height':	'3em',
					'text-align':	'center',
					'position':		'absolute',
					'z-index':		'502',
					'top':			'30px',
					'width':		'100%'
				})
				.prependTo($('body'));
		}

	});
})(jQuery);