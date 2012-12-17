/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 5/18/11
 * Time: 1:52 PM
 * Copyright 2011 De Ontwikkelfabrieks.
 */
$(function(){
    $(document).ready(function () {

        $("#needle").keypress(function (e) {
		    if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                $('#send').click();
                return false;
            } else {
                return true;
            }
        });

        $('#send').click(function () {
            var form	= $('#form');

            //window.location.href = form.attr('action') + '?needle=' + needle.val() + '&books=' + encodeURIComponent(books.val()) + '&match=' + match.val();
            var $tree           = $("#tree").dynatree("getTree"),     // the tree
                institutes      = [],
                collections     = [],
                books           = [],
                wordzonetypes   = [],
                match           = [],
                annotations     = [],
                needle,
                uri,
                nodes       = $tree.getSelectedNodes(true),
                keys;

            if($('#needle').val() == '')
            {
                $('#output')
                     .html('U heeft geen zoekterm ingevuld')
                     .dialog({
                        title: 'Informatie',
                        modal: true,
                        width: 300,
                        resizable: false,
                        buttons: { "Sluiten": function() { $(this).dialog("close"); }}
                    });
            } else {
            
                $.each(nodes, function(index, value) {
                    switch(value.getLevel()) {
                        case 2: // institute
                            if($.inArray(value.data.key, institutes) == -1)
                                institutes.push(value.data.key);
                            break;
                        case 3: // collection
                            keys = value.getKeyPath(true).split('/');

//                            if($.inArray(keys[2], institutes) == -1)
//                                institutes.push(keys[2]);

                            if($.inArray(value.data.key, collections) == -1)
                                collections.push(value.data.key);

                            break;
                        case 4: // book
                            keys = value.getKeyPath(true).split('/');

//                            if($.inArray(keys[2], institutes) == -1)
//                                institutes.push(keys[2]);
//                            if($.inArray(keys[3], collections) == -1)
//                                collections.push(keys[3]);
                            books.push(value.data.key);
                            break;
                    };
                });

                $('input[class=wordzonetypes]').each(function(i) {
                    if($(this).is(':checked'))
                        wordzonetypes.push($(this).val());
                });

                $('input[class=annotations]').each(function(i) {
                    if($(this).is(':checked'))
                        annotations.push($(this).val());
                });

                $('input[class=match]').each(function(i) {
                    if($(this).is(':checked'))
                        match.push($(this).val());
                });

                needle = $('#needle').val();

                uri = 'needle=' + needle;
                if(match.length)
                    uri += '&match=' + match.join('|');
                if(annotations.length)
                    uri += '&annotations=' + annotations.join('|');
                if(wordzonetypes.length)
                    uri += '&wordzonetypes=' + wordzonetypes.join('|');
                if(institutes.length)
                    uri += '&institutions=' + institutes.join('|');
                if(collections.length)
                    uri += '&collections=' + collections.join('|');
                if(books.length)
                    uri += '&books=' + books.join('|');

                window.location.href = '?' + uri;
            }
            
            return false;
        });
    });
});
