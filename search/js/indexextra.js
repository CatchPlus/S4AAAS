(function ($) {

	$(document).ready(function () {

        $("#infoWordLabel, #infoAnnotation, #infoMatch, #infoCollectie").tooltip({
            track: true,
            delay: 0,
            showURL: false,
            opacity: 1,
            fixPNG: true,
            showBody: " - ",
            top: -15,
            left: 5
        });

        $('#pretty').tooltip({
            track: true,
            delay: 0,
            showURL: false,
            showBody: " - ",
            extraClass: "pretty",
            fixPNG: true,
            opacity: 0.95,
            left: -120
        });


	});

}(jQuery));