(function ($) {

	$(document).ready(function () {

        var _size = {
			width: $(window).width() * .8,
			height: $(window).height() * .8
		};

        $(".links a.showpage").fancybox({
         type : 'html',
         content :  ' <div id="viewer" style="width: '+_size.width+'px; height: '+_size.height+'px; " class="viewer"></div>',
          onComplete : function(link){

           // ------------- start iviewer --------------------
                  $("#viewer").iviewer(
                       {
                       src: $(link).attr('href'),
                       update_on_resize: false,
                       initCallback: function ()
                       {
                           var object = this;
                           $("#in").click(function(){object.zoom_by(1);});
                           $("#out").click(function(){object.zoom_by(-1);});
                           $("#fit").click(function(){object.fit();});
                           $("#orig").click(function(){object.set_zoom(100);});
                           $("#update").click(function(){object.update_container_info();});


                       },
                       onMouseMove: function(object, coords) { },
                       onStartDrag: function(object, coords) { }, // by " return false;" the image will not be dragged
                       onDrag: function(object, coords) { }
                  });
           // ------------- end iviewer --------------------
          }
        });
	});

}(jQuery));