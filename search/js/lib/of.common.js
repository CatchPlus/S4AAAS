/**
 * @file: of.common.js
 */

// Function.method
Function.prototype.method = function (name, func) {
	this.prototype[name] = func;
	return this;
};

// Function.inherits
Function.method('inherits', function (parent) {
	var d = {},
		p = (this.prototype = new parent());

	this.method('uber', function uber(name) {
		if (!(name in d)) {
			d[name] = 0;
		}        
		var f, r, t = d[name], v = parent.prototype;

		if (t) {
			while (t) {
				v = v.constructor.prototype;
				t -= 1;
			}
			f = v[name];
		} else {
			f = p[name];
			if (f == this[name]) {
				f = v[name];
			}
		}
		d[name] += 1;
		r = f.apply(this, Array.prototype.slice.apply(arguments, [1]));
		d[name] -= 1;

		return r;
	});

	return this;
});

var OF = (function ($, OF) {

	OF.config = {
		log: false
	};

	// OF.namespace
	OF.namespace = function (name) {
		var ns		= name.split('.'),
			root	= window,
			i, il, n;

		for (i=0, il=ns.length; i<il; i++) {
			n = ns[i];
			if (typeof root[n] === 'undefined') {
				root[n] = {};
			}
			root = root[n];
		}
	};

	// OF.log
	OF.log = function (message) {
		if (OF.config.log === true) {
			if (typeof console !== 'undefined' && typeof console.log === 'function') {
				OF.log = function () {
					console.log.apply(console, arguments);
				};
				console.log.apply(console, arguments);
			}			
		} else {
			OF.log = function () {};
		}
	};

	return OF;
}(jQuery, OF || {}));