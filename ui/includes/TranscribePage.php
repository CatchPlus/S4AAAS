<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/21/11
 * Time: 11:42 AM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class TranscribePage {

    private $smarty;

    function __construct()
    {
        $page = '';
        if(isset($_GET['page']))
            $page = explode('.', $_GET['page']);
        $trainer = count($page) > 1 ? true : false;

        $smartVars = array(
            'trainer' => $trainer,
            'javascript' => $this->getInitJS(),
            'role' => MonkUser::getInstance()->getRole()
        );

        $this->smarty = new SmartyPage('transcribe.tpl', $smartVars);
    }

    private function getInitJS()
    {
        $shear = Config::DEFAULT_SHEAR;
        if(isset($_SESSION[Config::PAGE_STORE]))
        {
            $page = $_SESSION[Config::PAGE_STORE];
            if($page instanceof Page)
            {
                $shear = $page->getShear();
            }
        }
        $js = <<<INIT
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
		log: true
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
//            id:  $.getUrlVar('page'),
            id:  $.getUrlVar('bookid'),
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
				imgSrc			: 'http://s4aaas.target-imedia.nl/ui/?cmd=image&line=',
				imgExt			: 'jpg',
				angle			: {$shear}
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
				dataType: 'html',
				data	: { page: page.id },

                success : function(dat) {
                    // should be called on succes but instead....
                    if(dat == 'OK')
                    {
                        alert('Opgeslagen, pagina wordt herladen.');
                        window.location.reload();
                    }
                    else
                        alert(dat);
                    //window.location.reload();
                },
                error   : function(dat) {
                    alert(dat);
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
INIT;

        return $js;
    }

    public function render()
    {
        $this->smarty->render();
    }

}
