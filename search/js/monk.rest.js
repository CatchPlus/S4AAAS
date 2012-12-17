(function ($) {

	OF.namespace('Monk.Rest');

/**
 * Monk.Rest
 */
	Monk.Rest = function (options) {
            this.opts = $.extend({}, defaults, options);
	};

        // Monk.Rest.test
        Monk.Rest.method('images', function() {
            var container = this.opts.container,
                source = this.opts.source,
                json = this.opts.json;
            
            $.each(container, function(index, value) {
                $this = $(this);
                j = $.parseJSON($this.find(json).text());
                
                var img = new Image();
                var imgSource = $this.find(source).text();
                
                $(img).attr('src', imgSource);
                $(img).load(function() {
                    ow = img.width;
                    $this.html(img);
                    console.log($this);
                    nw = img.width;
                    ratio = nw / ow;
                });
                
            });
            
            /* traverse the images */
//            $.each(container, function(index, value) {
//                
//                var img = new Image();
//                var imgsrc = $(this).find(source).text();
//                
//                $(img).attr('src', imgsrc);
//                
//                
//                $(img).load(function() {
//                    $(img).attr('src', imgsrc);
//                    oh = img.height;
//                    ow = img.width;
//                    
//                    console.log('original: ' + ow + 'x' + oh);
//                    
//                    $(this).html($(img));
//                    
//                    nw = img.width;
//                    nh = img.height;
//                    ratio = nw/ow;
//                    /* do something with drawing lines and use ratio? */
//                    console.log(nw + 'x' + nh);
//                });
                
                
//            });
        });

	// Monk.Rest.show
	Monk.Rest.method('show', function () {
		var container	= this.opts.container,
                    source      = this.opts.source,
                    json        = this.opts.json;
                    
                    
		$.each(container, function (index, value) {
                    
                    var img = $(new Image());
                    
                    img.load(function() {
                        var ow, oh, nw, nh;
                        

                        ow = this.width;
                        oh = this.height;
                        
//                        console.log(ow, oh);
                        
//                        container.html(this);                                 
                        //console.log($(this));
                        
                        nw = $(this).width();
                        nh = $(this).height();
                        
//                       console.log(nw, nh);
                        
                    });
                    img.attr('src', $(this).find(source).text());
                    
                    //$(this).html(img);
                    
                    
//                    j = $.parseJSON(json.html());
//
//
//                    img = $(new Image());
//                    
//                    $(img).load(function() {
//                       console.log('ok'); 
//                    });
//                    
//                    //json = jQuery.getJSON($('.image-holder .json').html());
//                    
//                    
//                    
//                });
//			var $this	= $(this),
//                            img         = document.createElement('img'),
//                            callback;
//
//			callback = function () {
//				$this.html(img);
//			};
//
// 			if (img.addEventListener) {	// fixes image.load bug in IE
//				img.addEventListener('load', callback, true);
//			} else if (img.attachEvent) {
//				img.attachEvent('onload', callback);
//			} else {
//				img.load = callback;
//			}
//
//			$(img).error(function () {
//				$this.html('<span class="error">Afbeelding kan niet geladen worden</span>');
//			});
//			img.src = $this.find(source).text();
//                        console.log($this.find(source).text());
                        //var obj = jQuery.parseJSON($this.find(imgsrc).text());
                        

//                        console.log($this.find(imgsrc).text());
//                        console.log(obj);
        	});
	});

	var defaults;

	// defaults
	defaults = {
		container	: '',
		source		: ''
	};

}(jQuery));