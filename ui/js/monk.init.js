$.extend({
  getUrlVars: function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },
  getUrlVar: function(name){
    return $.getUrlVars()[name];
  }
});


(function ($) {

	// set debugger
	OF.config = {
		log: false
	};

	// set error handler
	$.error = function (msg) {
		$('#error .message').html(msg);
	};

	// main
	$(document).ready(function () {

		var page, data;

		// firebug warning
		if (window.console && window.console.firebug) {
			$.error('firebug is enabled. this may cause memory leaks');
		}


		// create page
		page = new Monk.Page({
			//id: 'navis-NL_HaNa_H2_7823_0132',
            id:  $.getUrlVar('page'),
			view: {
				elements: {
					container	: $('#page'),
					template	: $('#template'),
					current		: $('#current'),
					save		: $('#save'),
                    remove      : $('#remove')
				}
			},
			line: {
				selectionMethods: {
					point		: Monk.PointSelect,
					rectangle	: Monk.RectangleSelect
				},
				defaultMethod	: 'point',
//				imgSrc			: 'http://application01.target.rug.nl/monk/',
				imgSrc			: 'http://localhost/transcribe/?cmd=image&line=',
				imgExt			: 'jpg',
				angle			: 45
			}
		});

		// set ajax requests
		data = new Monk.Data({
			load: {
				url		: 'index.php?cmd=read',
				type	: 'get',
				dataType: 'json',
				data	: { page: page.id }
			}
			// save: {
				// url		: 'php/save.page.data.php',
				// type	: 'post',
				// dataType: 'json',
				// data	: { page: page.id, labels: {} }
			// }
		});

		// request data
		data.load(function (result) {

			// attach save request
			// page.save(data.save);
			page.save({
				url		: 'index.php?cmd=save',
				type	: 'post',
				dataType: 'json',
				data	: { page: page.id },

                success : function(data) {
                    // should be called on succes but instead....
                    alert('Opgeslagen, pagina wordt herladen.');
                    //window.location.reload();
                },
                error   : function(data) {
                    // hacking, error is called even when on succes...on a machine
                    alert('Opgeslagen, pagina wordt herladen.');
                    //window.location.reload();
                }
			});

			// set request result
			page.set(result);

            // remove loaders
		    $('.loader').remove();

			// show page
			page.show();
		});

	});

}(jQuery));