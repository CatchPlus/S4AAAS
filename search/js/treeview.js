/**
 * Created by De Ontwikkelfabriek
 * User: Postie
 * Date: 5/18/11
 * Time: 9:15 AM
 */


$(function(){
    $(document).ready(function () {
        $("#tree").dynatree({
            checkbox: true,
            selectMode: 3,
            initAjax: {
    //                url: "sample-data1.json"
                url: "?cmd=getcollection"
            },
            minExpandLevel: 2,
            strings: {
                loading: "Laden...",
                loadError: "Fout met laden!"
            },
            onSelect: function(select, node) {
                // Display list of selected nodes
                var selNodes = node.tree.getSelectedNodes();
                // convert to title/key array
                var selKeys = $.map(selNodes, function(node){
                       return "[" + node.data.key + "]: '" + node.data.title + "'";
                });
                $("#echoSelection2").text(selKeys.join(", "));
            },
            onClick: function(node, event) {
                // We should not toggle, if target was "checkbox", because this
                // would result in double-toggle (i.e. no toggle)
                if( node.getEventTargetType(event) == "title" )
                    node.toggleSelect();
            },
            onKeydown: function(node, event) {
                if( event.which == 32 ) {
                    node.toggleSelect();
                    return false;
                }
            },
            // The following options are only required, if we have more than one tree on one page:
            cookieId: "dynatree-Cb2",
            idPrefix: "dynatree-Cb2-"
        });

        $("#btnToggleSelect").click(function(){
            $("#tree2").dynatree("getRoot").visit(function(node){
                node.toggleSelect();
            });
            return false;
        });
        $("#btnDeselectAll").click(function(){
            $("#tree2").dynatree("getRoot").visit(function(node){
                node.select(false);
            });
            return false;
        });
        $("#btnSelectAll").click(function(){
            $("#tree2").dynatree("getRoot").visit(function(node){
                node.select(true);
            });
            return false;
        });
        <!-- Start_Exclude: This block is not part of the sample code -->
    });
});
