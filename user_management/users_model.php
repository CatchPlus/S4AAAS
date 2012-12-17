<?php

define('appini_path', '/var/www/s4aaas.target-imedia.nl/rest/application/configs/application.ini');
define('rest_login', 'http://s4aaas.target-imedia.nl/rest/login');
define('makemonkpw', '/var/www/s4aaas.target-imedia.nl/bin');

class userModel {

    public $dbConn;
    public $admin_perm;
    public $actual_user;
    public $full_hierarchical_books;

    function __construct() {
        $appini = parse_ini_file(appini_path);
        $this->dbConn = mysql_connect($appini['resources.db.params.host'], $appini['resources.db.params.username'], $appini['resources.db.params.password']) or exit('No database connection.');
        mysql_select_db($appini['resources.db.params.dbname'], $this->dbConn) or exit(mysql_error());
        $this->admin_perm = max(array_keys($this->convert_permission(0)));
    }

    function __destruct() {
        mysql_close($this->dbConn);
    }

    function makemonkpw($password) {
        exec(makemonkpw . '/makemonkpw -enc \'' . escapeshellarg($password) . '\'', $monkpw);
        if (isset($monkpw[0]))
            return $monkpw[0];
        else
            return $password;
    }

    function token($username, $password) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rest_login . '/' . $username);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'password=' . $password);
        $sxe = simplexml_load_string(curl_exec($ch));
        return (string) $sxe->authentication_token;
    }

    function valid_token($token) {
        $col = $this->query('SELECT COUNT(*) FROM AUTHTOKENS WHERE TOKEN = \'' . $token . '\' AND VALID_UNTIL > NOW()', true);
        if ($col[0] == 0)
            return false;
        return true;
    }

    function stmnt($sql) {
        mysql_query($sql, $this->dbConn) or exit(mysql_error());
    }

    function query($sql, $column = false) {
        $s = array();
        if ($q = mysql_query($sql, $this->dbConn) or exit(mysql_error()))
            while ($r = mysql_fetch_array($q))
                $column ? $s[] = $r[0] : $s[] = $r;
        return $s;
    }

    function set_actual_user($token) {
        $set = $this->query('SELECT USERS.ID, USERS.MONK_ID, USERS.PERMISSIONS FROM AUTHTOKENS JOIN USERS ON AUTHTOKENS.USER_ID = USERS.ID WHERE TOKEN = \'' . $token . '\'');
        $this->actual_user['id'] = $set[0]['ID'];
        $this->actual_user['username'] = $set[0]['MONK_ID'];
        $this->actual_user['global_permission'] = $set[0]['PERMISSIONS'];
        $this->actual_user['global_permission_name'] = $this->convert_permission($set[0]['PERMISSIONS']);
        $this->actual_user['permissions_booklevel'] = $this->get_permissions_booklevel($set[0]['MONK_ID']);
        $this->actual_user['admin_item'] = false;
        foreach ($this->actual_user['permissions_booklevel'] as $v)
            if ($this->admin_perm == $v['book_permission'])
                $this->actual_user['admin_item'] = true;
        $this->actual_user['is_admin'] = false;
        if ($this->actual_user['global_permission'] == $this->admin_perm)
            $this->actual_user['is_admin'] = true;
    }

    function valid_username($username) {
        if (strlen($username) && preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $username) && substr(strtolower($username), 0, 3) != 'xml')
            return true;
        return false;
    }

    function existing_username($username) {
        $col = $this->query('SELECT COUNT(*) FROM USERS WHERE MONK_ID = \'' . $username . '\' AND DELETED = \'NO\'', true);
        if ($col[0] == 0)
            return false;
        return true;
    }

    function existing_username_deleted($username) {
        $col = $this->query('SELECT COUNT(*) FROM USERS WHERE MONK_ID = \'' . $username . '\' AND DELETED = \'YES\'', true);
        if ($col[0] == 0)
            return false;
        return true;
    }

    function add_username($target_username, $target_password) {
        if (strlen($target_password) && $this->valid_username($target_username)) {
            $col = $this->query('SELECT COUNT(*) FROM USERS WHERE MONK_ID = \'' . $target_username . '\'', true);
            if ($col[0] == 0) {
                $col = $this->query('SELECT MAX(ID) FROM USERS', true);
                $id = ((int) $col[0]) + 1;
                $this->stmnt('INSERT INTO USERS (ID, MONK_ID, PASSWORD, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DISABLED, DELETED) VALUES (' . $id . ', \'' . $target_username . '\', \'' . $this->makemonkpw($target_password) . '\', 1, ' . $this->actual_user['id'] . ', NOW(), NOW(), \'no\', \'no\')');
            } else {
                $this->stmnt('UPDATE USERS SET DELETED = \'no\', PASSWORD = \'' . $this->makemonkpw($target_password) . '\', TIMESTAMP_CHANGE = NOW() WHERE MONK_ID = \'' . $target_username . '\'');
            }
            return true;
        }
        return false;
    }

    function delete_username($target_username) {
        $this->stmnt('UPDATE USERS SET DELETED = \'YES\', TIMESTAMP_CHANGE = NOW() WHERE MONK_ID = \'' . $target_username . '\'');
    }

    function change_password($target_username, $target_password) {
        $this->stmnt('UPDATE USERS SET PASSWORD = \'' . $this->makemonkpw($target_password) . '\', TIMESTAMP_CHANGE = NOW() WHERE MONK_ID = \'' . $target_username . '\'');
    }

    function change_global_permission($target_username, $global_permission) {
        $this->stmnt('UPDATE USERS SET PERMISSIONS = \'' . $global_permission . '\', TIMESTAMP_CHANGE = NOW() WHERE MONK_ID = \'' . $target_username . '\'');
    }

    function add_book($target_username, $book_id, $book_permission, $page_from, $page_to) {
        $col = $this->query('SELECT COUNT(*) FROM USERBOOK JOIN USERS ON USERBOOK.USER_ID = USERS.ID JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERS.MONK_ID = \'' . $target_username . '\' AND BOOKS.MONK_DIR = \'' . $book_id . '\'', true);
        if ($col[0] == 0) {
            list($user_id) = $this->query('SELECT ID FROM USERS WHERE MONK_ID = \'' . $target_username . '\'', true);
            list($book_id) = $this->query('SELECT ID FROM BOOKS WHERE MONK_DIR = \'' . $book_id . '\'', true);
            $this->stmnt('INSERT INTO USERBOOK (USER_ID, BOOK_ID, PERMISSIONS, PAGE_FROM, PAGE_TO, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES (' . $user_id . ', ' . $book_id . ', ' . $book_permission . ', ' . $page_from . ', ' . $page_to . ', ' . $this->actual_user['id'] . ', NOW(), NOW(), \'no\')');
        } else {
            $this->stmnt('UPDATE USERBOOK JOIN USERS ON USERBOOK.USER_ID = USERS.ID JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID SET USERBOOK.PERMISSIONS=\'' . $book_permission . '\', USERBOOK.PAGE_FROM=\'' . $page_from . '\', USERBOOK.PAGE_TO=\'' . $page_to . '\', USERBOOK.DELETED = \'NO\', USERBOOK.TIMESTAMP_CHANGE = NOW() WHERE USERS.MONK_ID = \'' . $target_username . '\' AND BOOKS.MONK_DIR = \'' . $book_id . '\'');
        }
    }

    function add_collection($target_username, $collection_id, $collection_permission) {
        $col = $this->query('SELECT COUNT(*) FROM USERCOL JOIN USERS ON USERCOL.USER_ID = USERS.ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID WHERE USERS.MONK_ID = \'' . $target_username . '\' AND COLLECTIONS.MONK_ID = \'' . $collection_id . '\'', true);
        if ($col[0] == 0) {
            list($user_id) = $this->query('SELECT ID FROM USERS WHERE MONK_ID = \'' . $target_username . '\'', true);
            list($collection_id) = $this->query('SELECT ID FROM COLLECTIONS WHERE MONK_ID = \'' . $collection_id . '\'', true);
            $this->stmnt('INSERT INTO USERCOL (USER_ID, COLLECTION_ID, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES (' . $user_id . ', ' . $collection_id . ', ' . $collection_permission . ', ' . $this->actual_user['id'] . ', NOW(), NOW(), \'no\')');
        } else {
            $this->stmnt('UPDATE USERCOL JOIN USERS ON USERCOL.USER_ID = USERS.ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID SET USERCOL.PERMISSIONS=\'' . $collection_permission . '\', USERCOL.DELETED = \'NO\', USERCOL.TIMESTAMP_CHANGE = NOW() WHERE USERS.MONK_ID = \'' . $target_username . '\' AND COLLECTIONS.MONK_ID = \'' . $collection_id . '\'');
        }
    }

    function add_institution($target_username, $institution_id, $institution_permission) {
        $col = $this->query('SELECT COUNT(*) FROM USERINST JOIN USERS ON USERINST.USER_ID = USERS.ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID WHERE USERS.MONK_ID = \'' . $target_username . '\' AND INSTITUTIONS.MONK_ID = \'' . $institution_id . '\'', true);
        if ($col[0] == 0) {
            list($user_id) = $this->query('SELECT ID FROM USERS WHERE MONK_ID = \'' . $target_username . '\'', true);
            list($institution_id) = $this->query('SELECT ID FROM INSTITUTIONS WHERE MONK_ID = \'' . $institution_id . '\'', true);
            $this->stmnt('INSERT INTO USERINST (USER_ID, INSTITUTION_ID, PERMISSIONS, BYUSER_ID, TIMESTAMP, TIMESTAMP_CHANGE, DELETED) VALUES (' . $user_id . ', ' . $institution_id . ', ' . $institution_permission . ', ' . $this->actual_user['id'] . ', NOW(), NOW(), \'no\')');
        } else {
            $this->stmnt('UPDATE USERINST JOIN USERS ON USERINST.USER_ID = USERS.ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID SET USERINST.PERMISSIONS=\'' . $institution_permission . '\', USERBOOK.DELETED = \'NO\', USERBOOK.TIMESTAMP_CHANGE = NOW() WHERE USERS.MONK_ID = \'' . $target_username . '\' AND INSTITUTIONS.MONK_ID = \'' . $institution_id . '\'');
        }
    }

    function delete_book($target_username, $book_id) {
        list($uid) = $this->query('SELECT ID FROM USERS WHERE MONK_ID = \'' . $target_username . '\'', true);
        list($bid) = $this->query('SELECT ID FROM BOOKS WHERE MONK_DIR = \'' . $book_id . '\'', true);
        $this->stmnt('UPDATE USERBOOK SET DELETED = \'YES\', TIMESTAMP_CHANGE = NOW() WHERE USER_ID=\'' . $uid . '\' AND BOOK_ID=\'' . $bid . '\'');
    }

    function delete_collection($target_username, $collection_id) {
        list($user_id) = $this->query('SELECT ID FROM USERS WHERE MONK_ID = \'' . $target_username . '\'', true);
        list($collection_id) = $this->query('SELECT ID FROM COLLECTIONS WHERE MONK_ID = \'' . $collection_id . '\'', true);
        $this->stmnt('UPDATE USERCOL SET DELETED = \'YES\', TIMESTAMP_CHANGE = NOW() WHERE USER_ID=\'' . $user_id . '\' AND COLLECTION_ID=\'' . $collection_id . '\'');
    }

    function delete_institution($target_username, $institution_id) {
        list($user_id) = $this->query('SELECT ID FROM USERS WHERE MONK_ID = \'' . $target_username . '\'', true);
        list($institution_id) = $this->query('SELECT ID FROM INSTITUTIONS WHERE MONK_ID = \'' . $institution_id . '\'', true);
        $this->stmnt('UPDATE USERINST SET DELETED = \'YES\', TIMESTAMP_CHANGE = NOW() WHERE USER_ID=\'' . $user_id . '\' AND INSTITUTION_ID=\'' . $institution_id . '\'');
    }

    function get_permissions_booklevel($username) {
        $books = array();
        // For global admin return all books with full permissions
        if ($this->permission($username) == $this->admin_perm) {
            $set = $this->query('SELECT MONK_DIR FROM BOOKS');
            foreach ($set as $row) {
                $books[$row[0]] = array(
                    'book_permission' => $this->admin_perm,
                    'page_from' => 1,
                    'page_to' => 99999,
                );
            }
            return $books;
        }
        // Get books
        $set = $this->query('SELECT BOOKS.MONK_DIR, USERBOOK.PERMISSIONS, USERBOOK.PAGE_FROM, USERBOOK.PAGE_TO FROM USERBOOK JOIN USERS ON USERBOOK.USER_ID = USERS.ID JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERBOOK.DELETED = \'NO\' AND USERS.MONK_ID =  \'' . $username . '\'');
        foreach ($set as $row) {
            $books[$row[0]] = array(
                'book_permission' => $row[1],
                'page_from' => $row[2],
                'page_to' => $row[3],
            );
        }
        // Get collections
        $set = $this->query('SELECT BOOKS.MONK_DIR, USERCOL.PERMISSIONS FROM USERCOL JOIN USERS ON USERCOL.USER_ID = USERS.ID JOIN BOOKS ON USERCOL.COLLECTION_ID = BOOKS.COLLECTION_ID WHERE USERCOL.DELETED = \'NO\' AND USERS.MONK_ID = \'' . $username . '\'');
        foreach ($set as $row) {
            $books[$row[0]] = array(
                'book_permission' => $row[1],
                'page_from' => 1,
                'page_to' => 99999,
            );
        }
        // Get institutions
        $set = $this->query('SELECT BOOKS.MONK_DIR, USERINST.PERMISSIONS FROM USERINST JOIN USERS ON USERINST.USER_ID = USERS.ID JOIN COLLECTIONS ON USERINST.INSTITUTION_ID = COLLECTIONS.INSTITUTION_ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE USERINST.DELETED = \'NO\' AND USERS.MONK_ID = \'' . $username . '\'');
        foreach ($set as $row) {
            $books[$row[0]] = array(
                'book_permission' => $row[1],
                'page_from' => 1,
                'page_to' => 99999,
            );
        }
        return $books;
    }

    function user_books($username) {
        return $this->query('SELECT BOOKS.MONK_DIR, USERBOOK.PERMISSIONS, USERBOOK.PAGE_FROM, USERBOOK.PAGE_TO FROM USERS JOIN USERBOOK ON USERS.ID = USERBOOK.USER_ID JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERBOOK.DELETED = \'NO\' AND USERS.MONK_ID = \'' . $username . '\'');
    }

    function user_collections($username) {
        return $this->query('SELECT COLLECTIONS.MONK_ID, USERCOL.PERMISSIONS FROM USERS JOIN USERCOL ON USERS.ID = USERCOL.USER_ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID WHERE USERCOL.DELETED = \'NO\' AND USERS.MONK_ID = \'' . $username . '\'');
    }

    function user_institutions($username) {
        return $this->query('SELECT INSTITUTIONS.MONK_ID, USERINST.PERMISSIONS FROM USERS JOIN USERINST ON USERS.ID = USERINST.USER_ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID WHERE USERINST.DELETED = \'NO\' AND USERS.MONK_ID = \'' . $username . '\'');
    }

    function outside_range_ids() {
        $this->actual_user['outside_range_ids'] = array(0 => 'NO');
        if (!$this->actual_user['is_admin']) {
            $set = $this->query('SELECT ID, MONK_ID FROM USERS');
            foreach ($set as $row)
                foreach ($this->get_permissions_booklevel($row[1]) as $book_id => $details)
                    if (array_key_exists($book_id, $this->actual_user['permissions_booklevel']) == false)
                        $this->actual_user['outside_range_ids'][$row[0]] = $row[1];
        }
    }

    function hierarchical_books($username) {
        $hierarchical_books = array();
        $books_selection = array();
        if ($this->actual_user['is_admin'])
            $books_actual_user = $this->query('SELECT BOOKS.ID FROM BOOKS', true);
        else
            $books_actual_user = $this->query('SELECT BOOKS.ID FROM USERS JOIN USERINST ON USERS.ID = USERINST.USER_ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID JOIN COLLECTIONS ON INSTITUTIONS.ID = COLLECTIONS.INSTITUTION_ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE USERS.DELETED = \'NO\' AND USERINST.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $this->actual_user['username'] . '\' UNION SELECT BOOKS.ID FROM USERS JOIN USERCOL ON USERS.ID = USERCOL.USER_ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE USERS.DELETED = \'NO\' AND USERCOL.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $this->actual_user['username'] . '\' UNION SELECT BOOKS.ID FROM USERS JOIN USERBOOK ON USERS.ID = USERBOOK.USER_ID JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERS.DELETED = \'NO\' AND USERBOOK.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $this->actual_user['username'] . '\'', true);
        $books_target_user = $this->query('SELECT BOOKS.ID FROM USERS JOIN USERINST ON USERS.ID = USERINST.USER_ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID JOIN COLLECTIONS ON INSTITUTIONS.ID = COLLECTIONS.INSTITUTION_ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE USERS.DELETED = \'NO\' AND USERINST.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $username . '\' UNION SELECT BOOKS.ID FROM USERS JOIN USERCOL ON USERS.ID = USERCOL.USER_ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID JOIN BOOKS ON COLLECTIONS.ID = BOOKS.COLLECTION_ID WHERE USERS.DELETED = \'NO\' AND USERCOL.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $username . '\' UNION SELECT BOOKS.ID FROM USERS JOIN USERBOOK ON USERS.ID = USERBOOK.USER_ID JOIN BOOKS ON USERBOOK.BOOK_ID = BOOKS.ID WHERE USERS.DELETED = \'NO\' AND USERBOOK.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $username . '\'', true);
        foreach ($books_actual_user as $id)
            if (!in_array($id, $books_target_user))
                $books_selection[] = $id;
        if (count($books_selection) > 0) {
            $set = $this->query('SELECT BOOKS.MONK_DIR BOOK, COLLECTIONS.MONK_ID COLLECTION, INSTITUTIONS.MONK_ID INSTITUTION FROM BOOKS JOIN COLLECTIONS ON BOOKS.COLLECTION_ID = COLLECTIONS.ID JOIN INSTITUTIONS ON COLLECTIONS.INSTITUTION_ID = INSTITUTIONS.ID WHERE BOOKS.ID IN (' . implode(',', $books_selection) . ')');
            foreach ($set as $row)
                $hierarchical_books[$row['INSTITUTION']][$row['COLLECTION']][$row['BOOK']] = '';
        }
        return $hierarchical_books;
    }

    function hierarchical_collections($username) {
        $hierarchical_collections = array();
        $collections_selection = array();
        if ($this->actual_user['is_admin'])
            $collections_actual_user = $this->query('SELECT COLLECTIONS.ID FROM COLLECTIONS', true);
        else
            $collections_actual_user = $this->query('SELECT COLLECTIONS.ID FROM USERS JOIN USERINST ON USERS.ID = USERINST.USER_ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID JOIN COLLECTIONS ON INSTITUTIONS.ID = COLLECTIONS.INSTITUTION_ID WHERE USERS.DELETED = \'NO\' AND USERINST.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $this->actual_user['username'] . '\' UNION SELECT COLLECTIONS.ID FROM USERS JOIN USERCOL ON USERS.ID = USERCOL.USER_ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID WHERE USERS.DELETED = \'NO\' AND USERCOL.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $this->actual_user['username'] . '\'', true);
        $collections_target_user = $this->query('SELECT COLLECTIONS.ID FROM USERS JOIN USERINST ON USERS.ID = USERINST.USER_ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID JOIN COLLECTIONS ON INSTITUTIONS.ID = COLLECTIONS.INSTITUTION_ID WHERE USERS.DELETED = \'NO\' AND USERINST.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $username . '\' UNION SELECT COLLECTIONS.ID FROM USERS JOIN USERCOL ON USERS.ID = USERCOL.USER_ID JOIN COLLECTIONS ON USERCOL.COLLECTION_ID = COLLECTIONS.ID WHERE USERS.DELETED = \'NO\' AND USERCOL.DELETED= \'NO\' AND USERS.MONK_ID = \'' . $username . '\'', true);
        foreach ($collections_actual_user as $id)
            if (!in_array($id, $collections_target_user))
                $collections_selection[] = $id;
        if (count($collections_selection) > 0) {
            $set = $this->query('SELECT COLLECTIONS.MONK_ID COLLECTION, INSTITUTIONS.MONK_ID INSTITUTION FROM COLLECTIONS JOIN INSTITUTIONS ON COLLECTIONS.INSTITUTION_ID = INSTITUTIONS.ID WHERE COLLECTIONS.ID IN (' . implode(',', $collections_selection) . ')');
            foreach ($set as $row)
                $hierarchical_collections[$row['INSTITUTION']][$row['COLLECTION']] = '';
        }
        return $hierarchical_collections;
    }

    function hierarchical_institutions($username) {
        $institutions = array();
        $institutions_selection = array();
        if ($this->actual_user['is_admin'])
            $institutions_actual_user = $this->query('SELECT INSTITUTIONS.ID FROM INSTITUTIONS', true);
        else
            $institutions_actual_user = $this->query('SELECT INSTITUTIONS.ID FROM USERINST JOIN USERS ON USERINST.USER_ID = USERS.ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID WHERE USERS.MONK_ID = \'' . $this->actual_user['username'] . '\'', true);
        $institutions_target_user = $this->query('SELECT INSTITUTIONS.ID FROM USERINST JOIN USERS ON USERINST.USER_ID = USERS.ID JOIN INSTITUTIONS ON USERINST.INSTITUTION_ID = INSTITUTIONS.ID WHERE USERS.MONK_ID = \'' . $username . '\'', true);
        foreach ($institutions_actual_user as $id)
            if (!in_array($id, $institutions_target_user))
                $institutions_selection[] = $id;
        if (count($institutions_selection) > 0)
            $institutions = $this->query('SELECT INSTITUTIONS.MONK_ID INSTITUTION FROM INSTITUTIONS WHERE INSTITUTIONS.ID IN (' . implode(',', $institutions_selection) . ')', true);
        return $institutions;
    }

    ////////////////////////////////////////////////////////////////////////////
    // Disabling a user means that the user can still login, but can't annotate.
    // It is implemented by moving the permission file to 
    // '$username.disabled.xml', and creating a "guest" xml-file at 
    // '$username.xml', i.e., with only a global permission of 1 ("guest").
    function disable($target_username) {
        $this->stmnt('UPDATE USERS SET DISABLED=\'yes\', TIMESTAMP_CHANGE = NOW() WHERE MONK_ID = \'' . $target_username . '\'');
    }

    function enable($target_username) {
        $this->stmnt('UPDATE USERS SET DISABLED=\'no\', TIMESTAMP_CHANGE = NOW() WHERE MONK_ID = \'' . $target_username . '\'');
    }

    function global_admin($username) {
        $set = $this->query('SELECT PERMISSIONS FROM USERS WHERE DELETED = \'NO\' AND DISABLED = \'NO\' AND MONK_ID=\'' . $username . '\'');
        if (isset($set[0][0]) && $set[0][0] == $this->admin_perm)
            return true;
        return false;
    }

    function global_writer($username) {
        $set = $this->query('SELECT PERMISSIONS FROM USERS WHERE DELETED = \'NO\' AND DISABLED = \'NO\' AND MONK_ID=\'' . $username . '\'');
        if (isset($set[0][0]) && $set[0][0] >= 7)
            return true;
        return false;
    }

    function permission($username) {
        $set = $this->query('SELECT PERMISSIONS FROM USERS WHERE MONK_ID =\'' . $username . '\'');
        return $set[0][0];
    }

    function id($username) {
        $set = $this->query('SELECT ID FROM USERS WHERE MONK_ID =\'' . $username . '\'');
        return $set[0][0];
    }

    function convert_permission($key) {
        $key = (string) $key;
        $array = array(
            63 => 'Global admin',
            31 => 'Ingest admin',
            15 => 'Transcription admin',
            7 => 'Transcriber',
            3 => 'Trainee',
            1 => 'Guest'
        );
        if (array_key_exists($key, $array))
            return $array[$key];
        elseif ($key == 0)
            return $array;
        else
            return $array[1];
    }

}

?>
