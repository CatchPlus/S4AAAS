/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 5/25/11
 * Time: 2:37 PM
 * Copyright 2011 De Ontwikkelfabrieks.
 */

$(document).ready(function () {
    jQuery(function(){
            options = {
                serviceUrl:'suggestions.php',
                width: 300,
                minChars:3
            }
            a = $('#needle').autocomplete(options);
            b = $('#minisearchform').autocomplete(options);

        });

        $('#needle').focus();

        $('.current').click(function(e) {
            e.preventDefault();
        });

        $('.nolink').click(function(e) {
            e.preventDefault();
        });
});