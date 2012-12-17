<?php
error_reporting(-1);
require 'users_model.php';
session_start();
if (!isset($_SESSION['token']))
    exit(header('Location:users_controller.php'));
$_SESSION['location'] = 'users_view_admin_panel.php';
$user_model = new userModel();
$user_model->set_actual_user($_SESSION['token']);
$user_model->outside_range_ids();
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
<?php require 'menu.php';?>   
    <script type='text/javascript'>
      var message = '<?php print $_SESSION['message']; $_SESSION['message'] = ''?>';
      if (message.length != 0)
        alert(message);
    </script>    
    <h1>Admin panel</h1>
    <label>logged in as: "<?php print $user_model->actual_user['username']; ?>", with role: "<?php print $user_model->actual_user['global_permission_name']; ?>"</label>
    <br>
    <a href='users_controller.php?action=logout'>logout</a> | 
    <a href='users_view_add_user.php'>add user</a>
    <br>
    <fieldset style='float: left;'>
      <legend>users</legend>
      <table class='tbl'>
        <tr>
          <th class='tbl' style='text-align:left;'>username</th>
<?php if ($user_model->actual_user['is_admin'])
  print '          <th class=\'tbl\' style=\'text-align:left;\'></th>'."\n";
  ?>
          <th class='tbl' style='text-align:left;'>global role</th>
          <th class='tbl' style='text-align:left;'>institutions</th>
          <th class='tbl' style='text-align:left;'>collections</th>
          <th class='tbl' style='text-align:left;'>books</th>
        </tr>
<?php 
  $user_model->outside_range_ids();
  foreach ($user_model->query('SELECT U.MONK_ID, U.PERMISSIONS, U.DISABLED, (SELECT GROUP_CONCAT(BOOKS.MONK_ID) FROM USERBOOK JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERBOOK.DELETED = \'NO\' AND USERBOOK.USER_ID = U.ID) BOOKS, (SELECT GROUP_CONCAT(COLLECTIONS.MONK_ID) FROM USERCOL JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID WHERE USERCOL.DELETED = \'NO\' AND USERCOL.USER_ID = U.ID) COLLECTIONS, (SELECT GROUP_CONCAT(INSTITUTIONS.MONK_ID) FROM USERINST JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID WHERE USERINST.DELETED = \'NO\' AND USERINST.USER_ID = U.ID) INSTITUTIONS FROM USERS U WHERE U.DELETED = \'NO\' AND U.ID NOT IN (' . implode(',',array_keys($user_model->actual_user['outside_range_ids'])) . ') GROUP BY U.ID') as $row){
    $cls = strtoupper($row['DISABLED']) == 'YES' ? "disabled" : "enabled";
    $cls .= $user_model->global_writer($row['MONK_ID']) ? " writer" : "";
    $cls .= $user_model->global_admin($row['MONK_ID']) ? " admin" : "";
?>
        <tr class="<?php print $cls; ?>">
          <td class='tbl'><a href='users_view_admin_details.php?target_username=<?php print $row['MONK_ID']; ?>'><?php print $row['MONK_ID']; ?></a></td>
<?php if ($user_model->actual_user['is_admin'] && strtoupper($row['DISABLED']) == 'YES')
  print '          <td class="tbl"><a href="users_controller.php?action=enable_user&target_username=' . $row['MONK_ID'] . '">Enable user</a></td>'."\n";
if ($user_model->actual_user['is_admin'] && strtoupper($row['DISABLED']) == 'NO')
  print '          <td class="tbl"><a href="users_controller.php?action=disable_user&target_username=' . $row['MONK_ID'] . '">Disable user</a></td>'."\n";
?>
          <td class='tbl'><?php print $user_model->convert_permission($row['PERMISSIONS']); ?></td>
          <td class='tbl'><?php print $row['INSTITUTIONS'];?></td>
          <td class='tbl'><?php print $row['COLLECTIONS'];?></td>
          <td class='tbl'><?php print $row['BOOKS'];?></td>
        </tr>
<?php }?>
      </table>
    </fieldset>
  </body>
</html>
