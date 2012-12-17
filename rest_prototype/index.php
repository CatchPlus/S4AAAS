<script type='text/javascript'>
  function submit()
  {
    var params = new Array;
    params[document.getElementById('parameter1').value] = document.getElementById('value1').value;
    post_to_url(document.getElementById('URL').value, params);
    
    
  }
  
  function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
      if(params.hasOwnProperty(key)) {
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", params[key]);
        form.appendChild(hiddenField);
      }
    }
    document.body.appendChild(form);
    form.submit();
  }
</script>

URL (With optional GET PARAMETER/VALUE)<br>
<input style='width:800px' type="text" id="URL" value='http://s4aaas.target-imedia.nl/rest/login/user1'/><br>
POST PARAMETER/VALUE<br>
<input type="text" id="parameter1" value="password" /><input type="text" id="value1" value="1234" /><br>
<button onclick='javascript:submit()'>Submit</button>

