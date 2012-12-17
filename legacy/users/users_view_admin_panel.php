<?php 
error_reporting(-1);
require 'users_model.php';
require 'include/navis-id2path.php';
session_start();
if (!isset($_SESSION['username']))
  exit(header('Location:users_controller.php'));
$_SESSION['location'] = 'users_view_admin_panel.php';
$user_model = new userModel();
$user_model->make_subset($_SESSION['username']);
$is_admin = $user_model->global_admin($_SESSION['username']);

if (!isset($_SESSION['message']))
  $_SESSION['message'] = '';
?>
<!DOCTYPE HTML PUBLIC '-//W3C//Dtd XHTML 1.0 Transitional//EN>
<html>
  <head>
    <title>Admin panel</title>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
    <link rel='stylesheet' type='text/css' href='style.css'/>
  </head>
  <body>
    <script type='text/javascript'>
      var message = '<?php print $_SESSION['message']; $_SESSION['message'] = ''?>';
      if (message.length != 0)
        alert(message);
    </script>    
    <h1>Admin panel</h1>
    <label>logged in as: "<?php print $_SESSION['username']; ?>", with role: "<?php print $_SESSION['role']; ?>"</label>
    <br>
    <a href='users_controller.php?action=logout'>logout</a> | 
    <a href='users_view_add_user.php'>add user</a>
    <br>
    <fieldset style='float: left;'>
      <legend>users</legend>
      <table class='tbl'>
        <tr>
          <th class='tbl' style='text-align:left;'>username</th>
<?php if ($is_admin)
  print '          <th class="tbl" style="text-align:left;"></th>';
  ?>
          <th class='tbl' style='text-align:left;'>global role</th>
          <th class='tbl' style='text-align:left;'>institutions</th>
          <th class='tbl' style='text-align:left;'>collections</th>
          <th class='tbl' style='text-align:left;'>books</th>
        </tr>
<?php foreach ($user_model->permissions_subset as $username => $details){
	$cls = $user_model->disabled($username) ? "disabled" : "enabled";
	$cls .= $user_model->global_writer($username) ? " writer" : "";
	$cls .= $user_model->global_admin($username) ? " admin" : "";
?>
		    <tr class="<?php print $cls; ?>">
          <td class='tbl'><a href='users_view_admin_details.php?target_username=<?php print $username; ?>'><?php print $username; ?></a></td>
<?php if ($is_admin && $user_model->disabled($username))
  print '            <td class="tbl"><a href="users_controller.php?action=enable_user&target_username=' . $username . '">Enable user</a></td>';
if ($is_admin && !$user_model->disabled($username))
  print '            <td class="tbl"><a href="users_controller.php?action=disable_user&target_username=' . $username . '">Disable user</a></td>';
?>
          <td class='tbl'><?php print $user_model->convert_permission($details->permissions->global_permission); ?></td>
          <td class='tbl'><?php print implode(', ', $user_model->permissions_subset->xpath($username . '/permissions/institutions/institution/institution_id'));?></td>
          <td class='tbl'><?php print implode(', ', $user_model->permissions_subset->xpath($username . '/permissions/collections/collection/collection_id'));?></td>
          <td class='tbl'><?php print implode(', ', $user_model->permissions_subset->xpath($username . '/permissions/books/book/book_id'));?></td>
        </tr>
<?php }?>
      </table>
    </fieldset>
  </body>
</html>
