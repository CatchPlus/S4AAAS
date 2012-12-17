<?php

error_reporting(-1);
require 'users_model.php';
define('view_admin_panel', 'users_view_admin_panel.php');
define('view_change_password', 'users_view_change_password.php');
define('view_admin_details', 'users_view_admin_details.php');
define('view_login', '../ui/login.php');
define('startpage', '../search/index.php');
define('view_add_user', 'users_view_add_user.php');

if ($_REQUEST['action'] == 'logout')
    logout();

session_start();
$user_model = new userModel();

// If user has no token, get token.
if (!isset($_SESSION['token'])) {
    if (isset($_REQUEST['username']) && strlen($_REQUEST['username']) && isset($_REQUEST['password']) && strlen($_REQUEST['password']))
        $_SESSION['token'] = $user_model->token($_REQUEST['username'], $_REQUEST['password']);
    else
        logout();
}
// If the token is not valid, get new token.
if (!$user_model->valid_token($_SESSION['token'])) {
    if (isset($_REQUEST['username']) && strlen($_REQUEST['username']) && isset($_REQUEST['password']) && strlen($_REQUEST['password']))
        $_SESSION['token'] = $user_model->token($_REQUEST['username'], $_REQUEST['password']);
    else
        logout();
}
// If the token is not valid, log out.
if (!$user_model->valid_token($_SESSION['token']))
    logout();



$user_model->set_actual_user($_SESSION['token']);

if ($_REQUEST['action'] == 'change_password') {
    check_request_variable('target_username');
    check_request_variable('target_password');
    check_request_variable('target_password2');
    // User must be admin or changing own password.
    if ($user_model->actual_user['global_permission'] == $user_model->admin_perm || $_REQUEST['target_username'] == $user_model->actual_user['username']) {
        if (strlen($_REQUEST['target_password']) < 4)
            fail('Password is too short.');
        if ($_REQUEST['target_password'] != $_REQUEST['target_password2'])
            fail('Second password does not match the first. Please retype.');
        $user_model->change_password($_REQUEST['target_username'], $_REQUEST['target_password']);
        $_SESSION['message'] = 'Success: Password changed';
        exit(header('Location:' . $_SESSION['location']));
    }
    else
        fail();
}


if ($_REQUEST['action'] == 'add_book') {
    check_request_variable('book_id');
    check_request_variable('book_permission');
    check_request_variable('page_from');
    check_request_variable('page_to');
    // User must be admin or having admin permission on the item.
    compare_item_permissions();
    $user_model->add_book($_REQUEST['target_username'], $_REQUEST['book_id'], $_REQUEST['book_permission'], $_REQUEST['page_from'], $_REQUEST['page_to']);
    exit(header('Location:' . $_SESSION['location']));
}

if ($_REQUEST['action'] == 'add_collection') {
    check_request_variable('collection_id');
    check_request_variable('collection_permission');
    // User must be admin or having admin permission on the item.
    compare_item_permissions();
    $user_model->add_collection($_REQUEST['target_username'], $_REQUEST['collection_id'], $_REQUEST['collection_permission']);
    exit(header('Location:' . $_SESSION['location']));
}

if ($_REQUEST['action'] == 'add_institution') {
    check_request_variable('institution_id');
    check_request_variable('institution_permission');
    compare_item_permissions();
    $user_model->add_institution($_REQUEST['target_username'], $_REQUEST['institution_id'], $_REQUEST['institution_permission']);
    exit(header('Location:' . $_SESSION['location']));
}

if ($_REQUEST['action'] == 'delete_book') {
    check_request_variable('book_id');
    // User must be admin or having admin permission on the item.
    compare_item_permissions();
    $user_model->delete_book($_REQUEST['target_username'], $_REQUEST['book_id']);
    exit(header('Location:' . $_SESSION['location']));
}

if ($_REQUEST['action'] == 'delete_collection') {
    check_request_variable('collection_id');
    // User must be admin or having admin permission on the item.
    compare_item_permissions();
    $user_model->delete_collection($_REQUEST['target_username'], $_REQUEST['collection_id']);
    exit(header('Location:' . $_SESSION['location']));
}

if ($_REQUEST['action'] == 'delete_institution') {
    check_request_variable('institution_id');
    // User must be admin or having admin permission on the item.
    compare_item_permissions();
    $user_model->delete_institution($_REQUEST['target_username'], $_REQUEST['institution_id']);
    exit(header('Location:' . $_SESSION['location']));
}


if ($_REQUEST['action'] == 'add_user') {
    check_request_variable('target_username');
    check_request_variable('target_password');
    // User must be admin or having admin permission on any item.
    if ($user_model->actual_user['global_permission'] == $user_model->admin_perm || $user_model->actual_user['admin_item']) {
        if (strlen($_REQUEST['target_password']) < 4)
            fail('Password is too short.');
        if (!$user_model->valid_username($_REQUEST['target_username']))
            fail('Target username must start with a-z or A-Z, and contain a-z or A-Z or 0-9 or _.');
        if ($user_model->existing_username($_REQUEST['target_username']))
            fail('Target username already exists.');
        if ($user_model->existing_username_deleted($_REQUEST['target_username']))
            $_SESSION['message'] = 'Note: User restored with new password.';
        if ($user_model->add_username($_REQUEST['target_username'], $_REQUEST['target_password'])) {
            if ($_SESSION['location'] == view_add_user) {
                $_SESSION['location'] = view_admin_details;
                $_SESSION['target_username'] = $_REQUEST['target_username'];
            }
            exit(header('Location:' . $_SESSION['location']));
        }
        else
            fail('$user_model->add_username');
    }
    else
        fail();
}

if ($_REQUEST['action'] == 'delete_username') {
    check_request_variable('target_username');
    // User must be admin or having admin permission on any item.
    if ($user_model->actual_user['global_permission'] == $user_model->admin_perm || $user_model->actual_user['admin_item']) {
        foreach ($user_model->get_permissions_booklevel($_REQUEST['target_username']) as $book_id => $details) {
            if (
                    array_key_exists($book_id, $user_model->actual_user['permissions_booklevel']) == false
                    || $user_model->actual_user['permissions_booklevel'][$book_id]['book_permission'] != $user_model->admin_perm
                    || $user_model->actual_user['permissions_booklevel'][$book_id]['page_from'] < $details['page_from']
                    || $user_model->actual_user['permissions_booklevel'][$book_id]['page_to'] < $details['page_to']
            )
                fail();
        }
        if ($_REQUEST['target_username'] == $user_model->actual_user['username'])
            fail('Deleting your own account is not permitted.');
        $user_model->delete_username($_REQUEST['target_username']);
        if ($_SESSION['location'] == view_admin_details) {
            $_SESSION['location'] = view_admin_panel;
            exit(header('Location:' . $_SESSION['location']));
        }
        else
            exit(header('Location:' . $_SESSION['location']));
    }
    else
        fail();
}

if ($_REQUEST['action'] == 'global_permission') {
    check_request_variable('target_username');
    check_request_variable('global_permission');
    if ($user_model->actual_user['global_permission'] == $user_model->admin_perm) {
        $user_model->change_global_permission($_REQUEST['target_username'], $_REQUEST['global_permission']);
        exit(header('Location:' . $_SESSION['location']));
    }
    else
        fail();
}

if ($_REQUEST['action'] == 'disable_user') {
    check_request_variable('target_username');
    if ($user_model->actual_user['username'] == $_REQUEST['target_username'])
        fail("Cannot disable yourself.");
    else if ($user_model->actual_user['global_permission'] != $user_model->admin_perm)
        fail();
    else {
        $user_model->disable($_REQUEST['target_username']);
        exit(header('Location:' . $_SESSION['location']));
    }
}

if ($_REQUEST['action'] == 'enable_user') {
    check_request_variable('target_username');
    if ($user_model->actual_user['global_permission'] != $user_model->admin_perm)
        fail();
    else {
        $user_model->enable($_REQUEST['target_username']);
        exit(header('Location:' . $_SESSION['location']));
    }
}

// No specific action, redirect
// If user is admin goto admin panel.
if ($user_model->actual_user['is_admin'])
    exit(header('Location:' . view_admin_panel));
// If user has admin items goto admin panel.
if ($user_model->actual_user['admin_item'])
    exit(header('Location:' . view_admin_panel));
// If user is no admin and has no admin items, goto change password panel.
exit(header('Location:' . view_change_password));

function compare_item_permissions() {
    check_request_variable('target_username');
    global $user_model;
    if ($user_model->actual_user['global_permission'] != $user_model->admin_perm) {
        if ($user_model->actual_user['global_permission'] != $user_model->admin_perm && $_REQUEST['target_username'] == $user_model->actual_user['username'])
            fail('No permission to add, change or delete your own items.');
        foreach (target_item_permissions() as $k => $v) {
            if (!array_key_exists($k, $user_model->actual_user['permissions_booklevel']))
                fail('You do not own: "' . $k . '"');
            if ($user_model->admin_perm != $user_model->actual_user['permissions_booklevel'][$k]['book_permission'])
                fail('You do not have admin permission on this book.\nBook id:\t\t"' . $k . '".');
            if ($v['page_from'] < $user_model->actual_user['permissions_booklevel'][$k]['page_from'])
                fail('The "page from" can not be lower than your "page from". \nBook id:\t\t"' . $k . '".\nYour value:\t"' . $user_model->actual_user['permissions_booklevel'][$k]['page_from'] . '".\nTarget value:\t"' . $v['page_from'] . '".');
            if ($v['page_to'] > $user_model->actual_user['permissions_booklevel'][$k]['page_to'])
                fail('The "page to" can not be higher than your "page to". \nBook id:\t\t"' . $k . '".\nYour value:\t"' . $user_model->actual_user['permissions_booklevel'][$k]['page_to'] . '".\nTarget value:\t"' . $v['page_to'] . '".');
        }
    }
    return true;
}

function target_item_permissions() {
    global $user_model;
    $target_item_permissions = array();
    if (isset($_REQUEST['book_id']) && strlen($_REQUEST['book_id'])) {
        $col = $user_model->query('SELECT USERBOOK.PERMISSIONS FROM USERBOOK JOIN USERS ON USERBOOK.USER_ID = USERS.ID JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERBOOK.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $_REQUEST['target_username'] . '\' AND BOOKS.MONK_DIR = \'' . $_REQUEST['book_id'] . '\'', true);
        if ($_REQUEST['action'] == 'add_book') {
            $target_item_permissions[$_REQUEST['book_id']] = array(
                'book_permission' => $_REQUEST['book_permission'],
                'page_from' => 1,
                'page_to' => 99999,
            );
        } elseif ($_REQUEST['action'] == 'delete_book') {
            $target_item_permissions[$_REQUEST['book_id']] = array(
                'book_permission' => $col[0],
                'page_from' => 1,
                'page_to' => 99999,
            );
        }
    }
    if (isset($_REQUEST['collection_id']) && strlen($_REQUEST['collection_id'])) {
        $col = $user_model->query('SELECT USERCOL.PERMISSIONS FROM USERCOL JOIN USERS ON USERCOL.USER_ID = USERS.ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID WHERE USERCOL.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $_REQUEST['target_username'] . '\' AND COLLECTIONS.MONK_ID = \'' . $_REQUEST['collection_id'] . '\'', true);
        foreach ($user_model->query('SELECT BOOKS.MONK_DIR BOOKS FROM COLLECTIONS JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE COLLECTIONS.MONK_ID = \'' . $_REQUEST['collection_id'] . '\'', true) as $book_id) {
            if ($_REQUEST['action'] == 'add_collection') {
                $target_item_permissions[$book_id] = array(
                    'book_permission' => $_REQUEST['collection_permission'],
                    'page_from' => 1,
                    'page_to' => 99999,
                );
            } elseif ($_REQUEST['action'] == 'delete_collection') {
                $target_item_permissions[$book_id] = array(
                    'book_permission' => $col[0],
                    'page_from' => 1,
                    'page_to' => 99999,
                );
            }
        }
    }
    if (isset($_REQUEST['institution_id']) && strlen($_REQUEST['institution_id'])) {
        $col = $user_model->query('SELECT USERINST.PERMISSIONS FROM USERINST JOIN USERS ON USERINST.USER_ID = USERS.ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID WHERE USERINST.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $_REQUEST['target_username'] . '\' AND INSTITUTIONS.MONK_ID = \'' . $_REQUEST['institution_id'] . '\'', true);
        foreach ($user_model->query('SELECT BOOKS.MONK_DIR BOOKS FROM INSTITUTIONS JOIN COLLECTIONS ON INSTITUTIONS.ID = COLLECTIONS.INSTITUTION_ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE INSTITUTIONS.MONK_ID = \'' . $_REQUEST['institution_id'] . '\'', true) as $book_id) {
            if ($_REQUEST['action'] == 'add_institution') {
                $target_item_permissions[$book_id] = array(
                    'book_permission' => $_REQUEST['institution_permission'],
                    'page_from' => 1,
                    'page_to' => 99999,
                );
            } elseif ($_REQUEST['action'] == 'delete_institution') {
                $target_item_permissions[$book_id] = array(
                    'book_permission' => $col[0],
                    'page_from' => 1,
                    'page_to' => 99999,
                );
            }
        }
    }
    return $target_item_permissions;
}

function logout() {
    if (isset($_COOKIE[session_name()]))
        setcookie(session_name(), '', time() - 3600, '/');
    $_SESSION = array();
    session_destroy();
    exit(header('Location:' . startpage));
}

function fail($str = 'No permission.') {
    $_SESSION['message'] = 'Failed: ' . $str;
    if (isset($_SESSION['location']))
        exit(header('Location:' . $_SESSION['location']));
    exit(header('Location:' . view_login));
}

function check_request_variable($s) {
    if (!isset($_REQUEST[$s]) || !strlen($_REQUEST[$s]))
        fail($s . ' not set.');
}

?>
