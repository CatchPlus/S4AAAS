<?php
error_reporting(-1);
session_start();
$_SESSION['location'] = 'users_view_login.php';
if (!isset($_SESSION['message']))
  $_SESSION['message'] = '';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Login</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel='stylesheet' type='text/css' href='style.css'/>
  </head>
  <body>
<?php require 'menu.php';?>   
    <script type='text/javascript'>
      var message = '<?php print $_SESSION['message']; $_SESSION['message'] = '' ?>';
      if (message.length != 0)
        alert(message);
    </script>
    <h1>Login</h1>   
    <fieldset style='float: left;'>
      <legend>Login</legend>
      <b style='width: 150px; float: left;'>Username</b>
      <b style='width: 150px; float: left;'>Password</b>
      <form style='clear: both;' action='users_controller.php' method='GET'>
        <input type='hidden' name='action' value='login'/>
        <input style='width: 150px; float: left;' type='text' name='username'/>
        <input style='width: 150px; float: left;' type='password' name='password'/>
        <input type='submit' value='Login'/>        
      </form>
    </fieldset>
  </form>
</body>
</html>