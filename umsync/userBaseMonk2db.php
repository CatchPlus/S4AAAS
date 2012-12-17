<?php

$userManagementMonk->read();
$db->stmnt('INSERT INTO MONK_SYNCHRONISED (TIMESTAMP) VALUES (NOW())');
$iId = mysql_insert_id();
$values = array();
foreach ($userManagementMonk->permissions as $u => $d) {
    $disabled = (strtolower($d->disabled) == 'true' ? 'yes' : 'no');
    $values['user'][] = "('$u', '$d->password', {$d->permissions->global_permission}, '$disabled', $iId)";
    if (isset($d->permissions->books->book))
        foreach ($d->permissions->books->book as $book)
            if (count($book->book_id) != 0)
                $values['book'][] = '(\'' . $u . '\', (SELECT ID FROM BOOKS WHERE MONK_DIR = \'' . (string) $book->book_id . '\'), ' . (string) $book->book_permission . ', ' . (string) $book->page_from . ', ' . (string) $book->page_to . ', ' . $iId . ')';
    if (isset($d->permissions->collections->collection))
        foreach ($d->permissions->collections->collection as $collection)
            if (count($collection->collection_id) != 0)
                $values['col'][] = '(\'' . $u . '\', (SELECT ID FROM COLLECTIONS WHERE MONK_ID = \'' . (string) $collection->collection_id . '\'), ' . (string) $collection->collection_permission . ', ' . $iId . ')';
    if (isset($d->permissions->institutions->institution))
        foreach ($d->permissions->institutions->institution as $institution)
            if (count($institution->institution_id) != 0)
                $values['inst'][] = '(\'' . $u . '\', (SELECT ID FROM INSTITUTIONS WHERE MONK_ID = \'' . (string) $institution->institution_id . '\'), ' . (string) $institution->institution_permission . ', ' . $iId . ')';
}
if (isset($values['user']))
    $db->stmnt('INSERT INTO MONK_USERS (MONK_ID, PASSWORD, PERMISSIONS, DISABLED, TIMESTAMP_ID) VALUES ' . implode(',', $values['user']));
if (isset($values['book']))
    $db->stmnt('INSERT INTO MONK_USERBOOK (MONK_ID, BOOK_ID, PERMISSIONS, PAGE_FROM, PAGE_TO, TIMESTAMP_ID) VALUES ' . implode(',', $values['book']));
if (isset($values['col']))
    $db->stmnt('INSERT INTO MONK_USERCOL (MONK_ID, COLLECTION_ID, PERMISSIONS, TIMESTAMP_ID) VALUES ' . implode(',', $values['col']));
if (isset($values['inst']))
    $db->stmnt('INSERT INTO MONK_USERINST (MONK_ID, INSTITUTION_ID, PERMISSIONS, TIMESTAMP_ID) VALUES ' . implode(',', $values['inst']));
?>
