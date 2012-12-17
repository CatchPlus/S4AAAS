<?php
error_reporting(-1);
require 'users_model.php';
session_start();
if (!isset($_SESSION['token']))
    exit(header('Location:users_controller.php'));
$_SESSION['location'] = 'users_view_change_password.php';
$user_model = new userModel();
$user_model->set_actual_user($_SESSION['token']);
if (!isset($_SESSION['message']))
    $_SESSION['message'] = '';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Change password</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel='stylesheet' type='text/css' href='style.css'/>
  </head>
  <body>
<?php require 'menu.php';?>   
    <script type='text/javascript'>
      var message = '<?php print $_SESSION['message']; $_SESSION['message'] = ''?>';
      if (message.length != 0)
        alert(message);
    </script>   
    <h1>Change password</h1>
    <label>logged in as: "<?php print $user_model->actual_user['username']; ?>", with role: "<?php print $user_model->actual_user['global_permission_name']; ?>"</label>
    <br>
    <a href='users_controller.php?action=logout'>logout</a><br><br>
    <fieldset style='float: left;'>
      <legend>Change password</legend>
      <b style='width: 150px; float: left;'>New password</b>
      <b style='width: 150px; float: left;'>Retype password</b>
      <form style='clear: both;' action='users_controller.php' method='GET'>
        <input type='hidden' name='action' value='change_password'/>
        <input type='hidden' name='target_username' value='<?php print $user_model->actual_user['username']; ?>'/>
        <input style='width: 150px; float: left;'  title='new password' type='password' name='target_password' />
        <input style='width: 150px; float: left;'  title='retype password' type='password' name='target_password2'/>
        <input type='submit' value='Change' />
      </form>
    </fieldset>
  </body>
</html>
