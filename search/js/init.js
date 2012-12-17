(function ($) {

	OF.config = {
		log: true
	};

	$(document).ready(function () {
        
            var ow, oh, nw, nh, json;
            
            var ratio = 1;
            
            /* traverse the images */
            $.each($('.image-holder .shadow'), function(index, value) {
                
                var json;
                var img = $(new Image());
                var imgsrc;//    = $(this).find('.hidden').text();
                
                
                json = $.parseJSON($(value).find('.json').html());
                imgsrc = json["url"];

                img.load(function() {

                     var ow, oh, nw, nh, $this;
                    // original values
                    $this = $(this);
                    
                    ow = this.width;
                    oh = this.height;

//                    $('.image-holder .shadow').html(this);
                    $(value).html(this);

                    // new values
                    nw = $this.width();
                    nh = $this.height();
                    
                    ratio = nw / ow;
                    /* teken lijntje */
                    if((json.y >= 0) && (json.x >=0) && (json.h > 0) && (json.w > 0)) {
                        for(i = Math.round(json.y * ratio);i <= Math.min(nh, Math.round((json.y + json.h) * ratio)); i++) {
                            $('<div />')
                            .width(Math.round(json.w * ratio))
                            //.height(Math.round(json.h * ratio))
                            .height(1)
                            .css('top', Math.round(i))
                            .css('left', Math.round(json.x * ratio) - i)
                            .css('diaplay', 'inline')
                            .addClass('line')
                            .appendTo(value);
                        }
                        
                    }
                });



                img.attr('src', imgsrc);
                if(img.attr('src') == '')
                    img.attr('src', 'images/no-image.jpg')
//                if(img[0].length < 0)
//                    img.attr('src', 'http://localhost/monk2/images/no-image.jpg')
            });
	});

}(jQuery));