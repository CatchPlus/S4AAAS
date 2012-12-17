<?php

error_reporting(-1);
require 'users_model.php';
require 'include/navis-id2path.php';
define('view_admin_panel', 'users_view_admin_panel.php');
define('view_change_password', 'users_view_change_password.php');
define('view_admin_details', 'users_view_admin_details.php');
define('view_login', 'users_view_login.php');
define('view_add_user', 'users_view_add_user.php');
check_request_variable('action');

if ($_REQUEST['action'] == 'logout')
  logout();

session_start();
$user_model = new userModel();

if ($_REQUEST['action'] == 'login')
{
  if (!(isset($_REQUEST['username']) && strlen($_REQUEST['username']) && isset($_REQUEST['password']) && strlen($_REQUEST['password'])))
    logout();
  if (!$user_model->valid($_REQUEST['username'], $_REQUEST['password']))
    logout();
  $_SESSION['username'] = $_REQUEST['username'];
  $_SESSION['role'] = $user_model->convert_permission($user_model->permissions->$_SESSION['username']->permissions->global_permission);
}

$actual_user_global_permission = (int) $user_model->permissions->$_SESSION['username']->permissions->global_permission;
$actual_user_permissions_booklevel = $user_model->get_permissions_booklevel($_SESSION['username']);
$actual_user_admin_item = false;
foreach ($actual_user_permissions_booklevel as $v)
  if ($user_model->admin_perm == $v['book_permission'])
    $actual_user_admin_item = true;

// Redirect login
if ($_REQUEST['action'] == 'login')
{
  // If user is admin goto admin panel.
  if ($user_model->admin_perm == $actual_user_global_permission)
    exit(header('Location:' . view_admin_panel));
  // If user has admin items goto admin panel.
  if ($actual_user_admin_item)
    exit(header('Location:' . view_admin_panel));
  // If user is no admin and has no admin items, goto change password panel.
  exit(header('Location:' . view_change_password));
}

if ($_REQUEST['action'] == 'change_password')
{
  check_request_variable('target_username');
  check_request_variable('target_password');
  check_request_variable('target_password2');
  // User must be admin or changing own password.
  if ($actual_user_global_permission == $user_model->admin_perm || $_REQUEST['target_username'] == $_SESSION['username'])
  {
    if (strlen($_REQUEST['target_password']) < 4)
      fail('Password is too short.');
    if ($_REQUEST['target_password'] != $_REQUEST['target_password2'])
      fail('Second password does not match the first. Please retype.');
    if ($user_model->change_password($_REQUEST['target_username'], $_REQUEST['target_password']))
    {
      $_SESSION['message'] = 'Success: Password changed';
      succes();
    }
    else
      fail('$user_model->change_password');
  }
  else
    fail();
}


if ($_REQUEST['action'] == 'add_book')
{
  check_request_variable('book_id');
  check_request_variable('book_permission');
  check_request_variable('page_from');
  check_request_variable('page_to');
  // User must be admin or having admin permission on the item.
  compare_item_permissions();
  if ($user_model->add_book($_REQUEST['target_username'], $_REQUEST['book_id'], $_REQUEST['book_permission'], $_REQUEST['page_from'], $_REQUEST['page_to']))
    succes();
  else
    fail('$user_model->add_book');
}

if ($_REQUEST['action'] == 'add_collection')
{
  check_request_variable('collection_id');
  check_request_variable('collection_permission');
  // User must be admin or having admin permission on the item.
  compare_item_permissions();
  if ($user_model->add_collection($_REQUEST['target_username'], $_REQUEST['collection_id'], $_REQUEST['collection_permission']))
    succes();
  else
    fail('$user_model->add_collection');
}

if ($_REQUEST['action'] == 'add_institution')
{
  check_request_variable('institution_id');
  check_request_variable('institution_permission');
  compare_item_permissions();
  if ($user_model->add_institution($_REQUEST['target_username'], $_REQUEST['institution_id'], $_REQUEST['institution_permission']))
    succes();
  else
    fail('$user_model->add_institution');
}

if ($_REQUEST['action'] == 'delete_book')
{
  check_request_variable('book_id');
  // User must be admin or having admin permission on the item.
  compare_item_permissions();
  if ($user_model->delete_book($_REQUEST['target_username'], $_REQUEST['book_id']))
    succes();
  else
    fail('$user_model->delete_book');
}

if ($_REQUEST['action'] == 'delete_collection')
{
  check_request_variable('collection_id');
  // User must be admin or having admin permission on the item.
  compare_item_permissions();
  if ($user_model->delete_collection($_REQUEST['target_username'], $_REQUEST['collection_id']))
    succes();
  else
    fail('$user_model->delete_collection');
}

if ($_REQUEST['action'] == 'delete_institution')
{
  check_request_variable('institution_id');
  // User must be admin or having admin permission on the item.
  compare_item_permissions();
  if ($user_model->delete_institution($_REQUEST['target_username'], $_REQUEST['institution_id']))
    succes();
  else
    fail('$user_model->delete_institution');
}


if ($_REQUEST['action'] == 'add_user')
{
  check_request_variable('target_username');
  check_request_variable('target_password');
  // User must be admin or having admin permission on any item.
  if ($actual_user_global_permission == $user_model->admin_perm || $actual_user_admin_item)
  {
    if (strlen($_REQUEST['target_password']) < 4)
      fail('Password is too short.');
    if (!$user_model->valid_username($_REQUEST['target_username']))
      fail('Target username must start with a-z or A-Z, and contain a-z or A-Z or 0-9 or _.');
    if (array_key_exists($_REQUEST['target_username'], $user_model->permissions))
      fail('Target username already exists.');
    if ($user_model->add_username($_REQUEST['target_username'], $_REQUEST['target_password']))
    {
      if ($_SESSION['location'] == view_add_user)
      {
        $_SESSION['location'] = view_admin_details;
        $_SESSION['target_username'] = $_REQUEST['target_username'];
      }
      succes();
    }
    else
      fail('$user_model->add_username');
  }
  else
    fail();
}

if ($_REQUEST['action'] == 'delete_username')
{
  check_request_variable('target_username');
  // User must be admin or having admin permission on any item.
  if ($actual_user_global_permission == $user_model->admin_perm || $actual_user_admin_item)
  {
    foreach ($user_model->get_permissions_booklevel($_REQUEST['target_username']) as $book_id => $details)
    {
      if (
              array_key_exists($book_id, $actual_user_permissions_booklevel) == false
              || $actual_user_permissions_booklevel[$book_id]['book_permission'] != $user_model->admin_perm
              || $actual_user_permissions_booklevel[$book_id]['page_from'] < $details['page_from']
              || $actual_user_permissions_booklevel[$book_id]['page_to'] < $details['page_to']
      )
        fail();
    }
    if ($_REQUEST['target_username'] == $_SESSION['username'])
      fail('Deleting your own account is not permitted.');

    if ($user_model->delete_username($_REQUEST['target_username']))
    {
      if ($_SESSION['location'] == view_admin_details)
      {
        $_SESSION['location'] = view_admin_panel;
        succes();
      }
      else
        succes();
    }
    else
      fail('$user_model->delete_username');
  }
  else
    fail();
}


if ($_REQUEST['action'] == 'global_permission')
{
  check_request_variable('target_username');
  check_request_variable('global_permission');
  if ($actual_user_global_permission == $user_model->admin_perm)
  {
    if ($user_model->change_global_permission($_REQUEST['target_username'], $_REQUEST['global_permission']))
      succes();
    else
      fail('$user_model->change_global_permission');
  }
  else
    fail();
}

if ($_REQUEST['action'] == 'disable_user')
{
  check_request_variable('target_username');
  if ($_SESSION['username'] == $_REQUEST['target_username'])
    fail("Cannot disable yourself.");
  else if ($actual_user_global_permission != $user_model->admin_perm)
    fail();
  else
  {
    $user_model->disable($_REQUEST['target_username']);
    succes();
  }
}

if ($_REQUEST['action'] == 'enable_user')
{
  check_request_variable('target_username');
  if ($actual_user_global_permission != $user_model->admin_perm)
    fail();
  else
  {
    $user_model->enable($_REQUEST['target_username']);
    succes();
  }
}

fail('Nothing done.');

function compare_item_permissions()
{
  check_request_variable('target_username');
  global $user_model;
  global $actual_user_global_permission;
  global $actual_user_permissions_booklevel;
  if ($actual_user_global_permission != $user_model->admin_perm)
  {
    if ($actual_user_global_permission != $user_model->admin_perm && $_REQUEST['target_username'] == $_SESSION['username'])
      fail('No permission to add, change or delete your own items.');
    foreach (target_item_permissions() as $k => $v)
    {
      if (!array_key_exists($k, $actual_user_permissions_booklevel))
        fail('You do not own: "' . $k . '"');
      if ($user_model->admin_perm != $actual_user_permissions_booklevel[$k]['book_permission'])
        fail('You do not have admin permission on this book.\nBook id:\t\t"' . $k . '".');
      if ($v['page_from'] < $actual_user_permissions_booklevel[$k]['page_from'])
        fail('The "page from" can not be lower than your "page from". \nBook id:\t\t"' . $k . '".\nYour value:\t"' . $actual_user_permissions_booklevel[$k]['page_from'] . '".\nTarget value:\t"' . $v['page_from'] . '".');
      if ($v['page_to'] > $actual_user_permissions_booklevel[$k]['page_to'])
        fail('The "page to" can not be higher than your "page to". \nBook id:\t\t"' . $k . '".\nYour value:\t"' . $actual_user_permissions_booklevel[$k]['page_to'] . '".\nTarget value:\t"' . $v['page_to'] . '".');
    }
  }
  return true;
}

function target_item_permissions()
{
  global $user_model;
  $target_item_permissions = array();
  if (isset($_REQUEST['book_id']) && strlen($_REQUEST['book_id']))
  {
    if ($_REQUEST['action'] == 'add_book')
      $target_item_permissions[$_REQUEST['book_id']] = array(
          'book_permission' => $_REQUEST['book_permission'],
          'page_from' => $_REQUEST['page_from'],
          'page_to' => $_REQUEST['page_to'],
      );
    elseif ($_REQUEST['action'] == 'delete_book')
      foreach ($user_model->permissions->xpath('/users/' . $_REQUEST['target_username'] . '/permissions/books/book') as $v)
        if ($v->book_id == $_REQUEST['book_id'])
          $target_item_permissions[$_REQUEST['book_id']] = array(
              'book_permission' => (int) $v->book_permission,
              'page_from' => (int) $v->page_from,
              'page_to' => (int) $v->page_to,
          );
    return $target_item_permissions;
  }
  if (isset($_REQUEST['collection_id']) && strlen($_REQUEST['collection_id']))
  {
    foreach (collections() as $k => $v)
      if ($v == $_REQUEST['collection_id'])
        if ($_REQUEST['action'] == 'add_collection')
          $target_item_permissions[$k] = array(
              'book_permission' => $_REQUEST['collection_permission'],
              'page_from' => 1,
              'page_to' => 99999,
          );
        elseif ($_REQUEST['action'] == 'delete_collection')
          foreach ($user_model->permissions->xpath('/users/' . $_REQUEST['target_username'] . '/permissions/collections/collection') as $v1)
            if ($v == (string) $v1->collection_id)
              $target_item_permissions[$k] = array(
                  'book_permission' => (int) $v1->collection_permission,
                  'page_from' => 1,
                  'page_to' => 99999,
              );
    return $target_item_permissions;
  }
  if (isset($_REQUEST['institution_id']) && strlen($_REQUEST['institution_id']))
  {
    foreach (institutions() as $k => $v)
      if ($v == $_REQUEST['institution_id'])
        if ($_REQUEST['action'] == 'add_institution')
          $target_item_permissions[$k] = array(
              'book_permission' => $_REQUEST['institution_permission'],
              'page_from' => 1,
              'page_to' => 99999,
          );
        elseif ($_REQUEST['action'] == 'delete_institution')
          foreach ($user_model->permissions->xpath('/users/' . $_REQUEST['target_username'] . '/permissions/institutions/institution') as $v1)
            if ($v == (string) $v1->institution_id)
              $target_item_permissions[$k] = array(
                  'book_permission' => (int) $v1->institution_permission,
                  'page_from' => 1,
                  'page_to' => 99999,
              );
    return $target_item_permissions;
  }
}

function logout()
{
  session_unset();
  exit(header('Location:' . view_login));
}

function fail($str = 'No permission.')
{
  $_SESSION['message'] = 'Failed: ' . $str;
  if (isset($_SESSION['location']))
    exit(header('Location:' . $_SESSION['location']));
  exit(header('Location:' . view_login));
}

function succes()
{
  global $user_model;
  if (!$user_model->write())
  {
    $_SESSION['location'] = view_login;
    fail('No file write permission');
  }
  exit(header('Location:' . $_SESSION['location']));
}

function check_request_variable($s)
{
  if (!isset($_REQUEST[$s]) || !strlen($_REQUEST[$s]))
    fail($s . ' not set.');
}

?>
