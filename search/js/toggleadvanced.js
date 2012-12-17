    $(document).ready(function() {
        $("#settings").hide();
        $("#advanced").hide();

        $("#settingshead").hover(function() {
          $(this).addClass('hoverp');
        }, function() {
              $(this).removeClass('hoverp');
        });

        $("#settingshead").click(function()
        {
          $(this).next("#settings").fadeToggle(300);
        });

        $("#advancedhead").hover(function()
        {
        $(this).addClass('hoverp');
        }, function() {
         $(this).removeClass('hoverp');
        });

        $("#advancedhead").click(function()
        {
        $(this).next("#advanced").fadeToggle(300);
        });
    });
