(function ($) {

/**
 * Monk.Data
 */
	OF.namespace('Monk.Data');

	// constructor
	Monk.Data = function (commands) {
		var self = this;

		if (typeof commands === 'object') {
			$.each(commands, function (command, options) {
				self[command] = self.request(options);
			});
		}

		return self;
	};


	// method.request
	Monk.Data.method('request', function (options) {
		return function (callback) {
			if (typeof options === 'object') {
				$.ajax($.extend({
					url		: options.url,
					type	: options.type,
					dataType: options.dataType,
					success	: function (data) {
						if (typeof callback === 'function') {
							callback(data);
						}
					},
					error	: function () {
						$.error('data request failure');
					}
				}, options));
			}
		};
	});

}(jQuery));