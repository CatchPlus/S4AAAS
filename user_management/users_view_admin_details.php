<?php
error_reporting(-1);
require 'users_model.php';
session_start();
if (!isset($_SESSION['token']))
    exit(header('Location:users_controller.php'));
$_SESSION['location'] = 'users_view_admin_details.php';
$user_model = new userModel();
$user_model->set_actual_user($_SESSION['token']);
if (!isset($_SESSION['message']))
    $_SESSION['message'] = '';
if (isset($_REQUEST['target_username']))
    $_SESSION['target_username'] = $_REQUEST['target_username'];
?>
<html>
  <head>
    <title>User details</title>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
    <link rel='stylesheet' type='text/css' href='style.css'/>
  </head>
  <body>
<?php require 'menu.php';?>
    <script type='text/javascript'>
      var message = '<?php print $_SESSION['message']; $_SESSION['message'] = ''?>';
      if (message.length != 0)
        alert(message);
      
      function toggle_enable_add_items()
      {
        if(document.getElementById('select_add_book').value == 'Choose book')
        {
          document.getElementById('select_book_permission').disabled=true
          document.getElementById('input_page_from').disabled=true
          document.getElementById('input_page_to').disabled=true
          document.getElementById('button_add_book').disabled=true
        }
        else
        {
          document.getElementById('select_book_permission').disabled=false
          document.getElementById('input_page_from').disabled=false
          document.getElementById('input_page_to').disabled=false
          document.getElementById('button_add_book').disabled=false
        }
        if(document.getElementById('select_add_collection').value == 'Choose collection')
        {
          document.getElementById('select_collection_permission').disabled=true
          document.getElementById('button_add_collection').disabled=true
        }
        else
        {
          document.getElementById('select_collection_permission').disabled=false
          document.getElementById('button_add_collection').disabled=false
        }
        if(document.getElementById('select_add_institution').value == 'Choose institution')
        {
          document.getElementById('select_institution_permission').disabled=true
          document.getElementById('button_add_institution').disabled=true
        }
        else
        {
          document.getElementById('select_institution_permission').disabled=false
          document.getElementById('button_add_institution').disabled=false
        }          
      }
      
      function delete_user()
      {
        if (confirm('Delete "<?php print $_SESSION['target_username']; ?>"?') == true)
          location.href='users_controller.php?action=delete_username&target_username=<?php print $_SESSION['target_username']; ?>'
      }
    </script>

    <h1>Details user: "<?php print $_SESSION['target_username']; ?>"</h1>
    <label>logged in as: "<?php print $user_model->actual_user['username']; ?>", with role: "<?php print $user_model->actual_user['global_permission_name']; ?>"</label>
    <br>
    <a href='users_view_admin_panel.php'>home</a> | 
    <a href='users_controller.php?action=logout'>logout</a> |
    <a href="#" onClick="javascript:delete_user()">delete: <?php print $_SESSION['target_username'] ?>"</a>
    <br>

    <!--GLOBAL PERMISSION-->    
    <fieldset style='float: left;'>
      <legend><h3>Global permission</h3></legend>
      <form action='users_controller.php' method='GET'>
        <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
        
        
        
        
<?php $set = $user_model->query('SELECT PERMISSIONS, DISABLED FROM USERS WHERE MONK_ID = \'' . $_SESSION['target_username'] . '\'');
if (strtoupper($set[0]['DISABLED']) == 'NO'){?>
        <input type='hidden' name='action' value='disable_user' />
		<input type="submit" value="Disable user" <?php if (!$user_model->actual_user['is_admin']) print 'disabled' ?>/>
<?php } else { ?>
        <input type='hidden' name='action' value='enable_user'/>
		<input type="submit" value="Enable user" <?php if (!$user_model->actual_user['is_admin']) print 'disabled' ?>/>
<?php } ?>
	  </form>
<?php if (strtoupper($set[0]['DISABLED']) == 'NO'){?>
      <form action='users_controller.php' method='GET'>
        <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
        <input type='hidden' name='action' value='global_permission'/>
        <select name='global_permission' <?php if (!$user_model->actual_user['is_admin']) print 'disabled' ?>>          
<?php
  foreach ($user_model->convert_permission(0) as $perm => $perm_desc)
  {
    $selected = '';
    if ($perm == $set[0]['PERMISSIONS'])
      $selected = 'selected';
    print '          <option ' . $selected . ' value="' . $perm . '">'.$perm_desc.'</option>'."\n";
  }
?>  
        </select>
        <input type='submit' value='Change' <?php if (!$user_model->actual_user['is_admin']) print 'disabled' ?>/>
      </form>
<?php } ?>
    </fieldset>
    <div style='width: 0px; clear: both;'></div>      

    <!--CHANGE PASSWORD-->
    <fieldset style='float: left;'>
      <legend><h3>Change password</h3></legend>
      <b style='width: 150px; float: left;'>new password</b>
      <b style='width: 150px; float: left;'>retype new password</b>
      <div style='width: 0px; clear: both;'></div>
      <form action='users_controller.php' method='GET'>
        <input type='hidden' name='action' value='change_password'/>
        <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
        <input style='width: 150px; float: left;' title='new password' type='password' name='target_password' <?php if (!$user_model->actual_user['is_admin'] && $_SESSION['target_username'] != $user_model->actual_user['username']) print 'disabled' ?>/>
        <input style='width: 150px; float: left;' title='retype new password' type='password' name='target_password2' <?php if (!$user_model->actual_user['is_admin'] && $_SESSION['target_username'] != $user_model->actual_user['username']) print 'disabled' ?>/>
        <input type='submit' value='Change' <?php if (!$user_model->actual_user['is_admin'] && $_SESSION['target_username'] != $user_model->actual_user['username']) print 'disabled' ?>/>
      </form>
    </fieldset>
    <div style='width: 0px; clear: both;'></div>

<?php 
if (strtoupper($set[0]['DISABLED']) == 'NO')
{
?>
    
    <!--BOOKS-->
    <fieldset style='width: 900px; float: left;'>
      <legend><h3>Books</h3></legend>
      <i style='width: 350px; float: left;'>book</i>
      <i style='width: 150px; float: left;'>permission</i>
      <i style='width: 100px; float: left;'>page from</i>
      <i style='width: 100px; float: left;'>page to</i>
      <i style='width: 100px; float: left;'></i>
      <div style='width: 0px; clear: both;'></div>  
      <form action='users_controller.php' method='GET'>
        <select style='width: 350px; float: left; background-color: ghostwhite;' id='select_add_book' name='book_id' onChange='toggle_enable_add_items()'>
          <option><b>Choose book</b></option>
<?php
foreach ($user_model->hierarchical_books($_SESSION['target_username']) as $institution => $collections)
  {
    print '          <optgroup label="' . $institution . '">' . "\n";
    foreach ($collections as $collection => $books)
    {
      print '            <optgroup label="&nbsp;&nbsp;' . $collection . '">' . "\n";
      foreach ($books as $book => $dummy)
        print '              <option  value="' . $book . '">' . $book . '</option>' . "\n";
      print '            </optgroup>' . "\n";
    }
    print '          </optgroup>' . "\n";
  }
?>
        </select>
        <select id="select_book_permission" disabled="disabled" style='width: 150px; float: left; background-color: ghostwhite;' name='book_permission'>
<?php
  foreach ($user_model->convert_permission(0) as $perm => $perm_desc)
    print '          <option value="' . $perm . '">' . $perm_desc . '</option>'."\n";
?>
        </select>      
        <input id="input_page_from" disabled="disabled" style='width: 100px; float: left; background-color: ghostwhite;' type='input' name='page_from' value='1'/>
        <input id="input_page_to" disabled="disabled" style='width: 100px; float: left; background-color: ghostwhite;' type='input' name='page_to' value='99999'/>
        <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
        <input type='hidden' name='action' value='add_book'/>
        <input id="button_add_book" disabled="disabled" style='width: 100px; float: left;' type='submit' value='Add'/>
        <div style='width: 0px; clear: both;'></div>
      </form>
      <br>
<?php
  foreach ($user_model->user_books($_SESSION['target_username']) as $book_details)
  {
?>
      <form action='users_controller.php' method='GET'>
        <input type='hidden' name='book_id' value='<?php print $book_details['MONK_DIR']; ?>'/>
        <label style='width: 350px; float: left;'><?php print $book_details['MONK_DIR']; ?> <a href='users_controller.php?action=delete_book&book_id=<?php print $book_details['MONK_DIR']; ?>&target_username=<?php print $_SESSION['target_username']; ?>'>delete</a></label>
        <select style='width: 150px; float: left;' name='book_permission'>
<?php
  foreach ($user_model->convert_permission(0) as $perm => $perm_desc)
  {
    $selected = '';
    if ($perm == $book_details['PERMISSIONS'])
      $selected = 'selected';
    print '          <option ' . $selected . ' value="' . $perm . '">' . $perm_desc . '</option>'."\n";
  }
?>
        </select>
        <input style='width: 100px; float: left;' name='page_from' value='<?php print $book_details['PAGE_FROM']; ?>'/>
        <input style='width: 100px; float: left;' name='page_to' value='<?php print $book_details['PAGE_TO']; ?>'/>
        <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
        <input type='hidden' name='action' value='add_book'/>
        <input style='width: 100px; float: left;' type='submit' value='Change'/>
      </form>
<?php
  }
?>
    </fieldset>

<p style='font-size: 95%; clear: both; padding-top: 15px; width=500px;'>
Please note that write permissions in monk are only considered at book or 
global level.<br>
&ldquo;Global admin&rdquo; at collection or institution level means only the 
ability to manage users.
</p>

    <div style='width: 0px; clear: both;'></div>

    <!--COLLECTIONS-->
    <fieldset style='width: 700px; float: left;'>
      <legend><h3>Collections</h3></legend>
      <i style='width: 350px; float: left;'>collection</i>
      <i style='width: 150px; float: left;'>permission</i>
      <i style='width: 100px; float: left;'></i>
      <div style='width: 0px; clear: both;'></div>  
      <form action='users_controller.php' method='GET'>
        <select style='width: 350px; float: left; background-color: ghostwhite;' id='select_add_collection' name='collection_id' onChange='toggle_enable_add_items()'>
          <option><b>Choose collection</b></option>
<?php
foreach ($user_model->hierarchical_collections($_SESSION['target_username']) as $institution => $collections)
  {
    print '          <optgroup label="' . $institution . '">' . "\n";
    foreach ($collections as $collection => $d)
      print '            <option  value="' . $collection . '">' . $collection . '</option>' . "\n";
    print '          </optgroup>' . "\n";
  }
?>              
        </select>
        <select id="select_collection_permission" disabled="disabled" style='width: 150px; float: left; background-color: ghostwhite;' name='collection_permission'>
<?php
  foreach ($user_model->convert_permission(0) as $perm => $perm_desc)
  {
    if ($perm == 7 || $perm == 15) continue;
    print '          <option value="' . $perm . '">' . $perm_desc . '</option>'."\n";
  }
?>
        </select>      
        <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
        <input type='hidden' name='action' value='add_collection'/>
        <input id="button_add_collection" disabled="disabled" style='width: 100px; float: left;' type='submit' value='Add'/>
        <div style='width: 0px; clear: both;'></div>
      </form>
      <br>
<?php
  foreach ($user_model->user_collections($_SESSION['target_username']) as $collection_details)
  {
?>
      <form action='users_controller.php' method='GET'>
      <input type='hidden' name='collection_id' value='<?php print $collection_details['MONK_ID']; ?>'/>
      <label style='width: 350px; float: left;'><?php print $collection_details['MONK_ID']; ?> <a href='users_controller.php?action=delete_collection&collection_id=<?php print $collection_details['MONK_ID']; ?>&target_username=<?php print $_SESSION['target_username']; ?>'>delete</a></label>
      <select style='width: 150px; float: left;' name='collection_permission'>
<?php
  foreach ($user_model->convert_permission(0) as $perm => $perm_desc)
  {
    if ($perm == 7 || $perm == 15) continue;
    $selected = '';
    if ($perm == $collection_details['PERMISSIONS'])
      $selected = 'selected';
    print '        <option ' . $selected . ' value="'.$perm.'">'.$perm_desc.'</option>'."\n";
  }
?>
      </select>
      <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
      <input type='hidden' name='action' value='add_collection'/>
      <input style="width: 100px; float: left;" type="submit" value="Change"/>        
      </form>        
<?php
  }
?>
    </fieldset>
    <div style='width: 0px; clear: both;'></div>
    
    
    <!--INSTITUTIONS-->
    <fieldset style='width: 700px; float: left;'>
      <legend><h3>Institutions</h3></legend>
      <i style='width: 350px; float: left;'>institution</i>
      <i style='width: 150px; float: left;'>permission</i>
      <i style='width: 100px; float: left;'></i>
      <div style='width: 0px; clear: both;'></div>  
      <form action='users_controller.php' method='GET'>
        <select style='width: 350px; float: left; background-color: ghostwhite;' id='select_add_institution' name='institution_id' onChange='toggle_enable_add_items()'>
          <option><b>Choose institution</b></option>
<?php
  foreach ($user_model->hierarchical_institutions($_SESSION['target_username']) as $institution)
    print '          <option onclick="enable_add_institution()" value="' . $institution . '">' . $institution . '</option>' . "\n";
?>              
        </select>
        <select id="select_institution_permission" disabled="disabled" style='width: 150px; float: left; background-color: ghostwhite;' name='institution_permission'>
<?php
  foreach ($user_model->convert_permission(0) as $perm => $perm_desc)
  {
    if ($perm == 7 || $perm == 15) continue;
    print '          <option value="' . $perm . '">' . $perm_desc . '</option>'."\n";
  }
?>
        </select>      
        <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
        <input type='hidden' name='action' value='add_institution'/>
        <input id="button_add_institution" disabled="disabled" style='width: 100px; float: left;' type='submit' value='Add'/>
        <div style='width: 0px; clear: both;'></div>
      </form>
      <br>
<?php
    foreach ($user_model->user_institutions($_SESSION['target_username']) as $institutions_details)
  {
?>
      <form action='users_controller.php' method='GET'>
      <input type='hidden' name='institution_id' value='<?php print $institutions_details['MONK_ID']; ?>'/>
      <label style='width: 350px; float: left;'><?php print $institutions_details['MONK_ID']; ?> <a href='users_controller.php?action=delete_institution&institution_id=<?php print $institutions_details['MONK_ID']; ?>&target_username=<?php print $_SESSION['target_username']; ?>'>delete</a></label>
      <select style='width: 150px; float: left;' name='institution_permission'>
<?php
  foreach ($user_model->convert_permission(0) as $perm => $perm_desc)
  {
    if ($perm == 7 || $perm == 15) continue;
    $selected = '';
    if ($perm == $institutions_details['PERMISSIONS'])
      $selected = 'selected';
    print '        <option ' . $selected . ' value="' . $perm . '">' . $perm_desc . '</option>'."\n";
  }
?>
      </select>
      <input type='hidden' name='target_username' value='<?php print $_SESSION['target_username']; ?>'/>
      <input type='hidden' name='action' value='add_institution'/>
      <input style="width: 100px; float: left;" type="submit" value="Change"/>        
      </form>        
<?php
  }
?>
    </fieldset>
    <div style='width: 0px; clear: both;'></div>
<?php 
} // end of 'if user is not disabled' statement
?>
    
  </body>
</html>
