<?php

$userManagementMonk->read();


// When there is no previous synchronisation, only completly new users (and items when availible) will be added.
if ($firstSync) {
    foreach ($db->query('SELECT U.* FROM USERS U WHERE U.MONK_ID NOT IN (SELECT MONK_ID FROM MONK_USERS)') as $s4aaasUserRec) {
        $userManagementMonk->add_username($s4aaasUserRec['MONK_ID'], $s4aaasUserRec['PASSWORD']);
        if (strtolower($s4aaasUserRec['DISABLED']) == 'yes')
            $userManagementMonk->permissions->{$s4aaasUserRec['MONK_ID']}->disabled = 'true';
        else
            $userManagementMonk->permissions->{$s4aaasUserRec['MONK_ID']}->disabled = 'false';
        $userManagementMonk->change_password($s4aaasUserRec['MONK_ID'], $s4aaasUserRec['PASSWORD']);
        $userManagementMonk->change_global_permission($s4aaasUserRec['MONK_ID'], $s4aaasUserRec['PERMISSIONS']);
        if (strtolower($s4aaasUserRec['DELETED']) == 'yes')
            $userManagementMonk->delete_username($s4aaasUserRec['MONK_ID']);
        
        foreach ($db->query('SELECT U.MONK_ID U_MONK_ID, B.MONK_DIR B_MONK_DIR, UB.PERMISSIONS UB_PERMISSIONS, UB.PAGE_FROM UB_PAGE_FROM, UB.PAGE_TO UB_PAGE_TO, UB.DELETED UB_DELETED FROM USERBOOK UB JOIN USERS U ON UB.USER_ID = U.ID JOIN BOOKS B ON UB.BOOK_ID = B.ID WHERE U.MONK_ID = \'' . $s4aaasUserRec['MONK_ID'] . '\'') as $s4aaasBookRec) {
            if (strtolower($s4aaasBookRec['UB_DELETED']) == 'yes')
                $userManagementMonk->delete_book($s4aaasBookRec['U_MONK_ID'], $s4aaasBookRec['B_MONK_DIR']);
            else
                $userManagementMonk->add_book($s4aaasBookRec['U_MONK_ID'], $s4aaasBookRec['B_MONK_DIR'], $s4aaasBookRec['UB_PERMISSIONS'], $s4aaasBookRec['UB_PAGE_FROM'], $s4aaasBookRec['UB_PAGE_TO']);
        }
        
        foreach ($db->query('SELECT U.MONK_ID U_MONK_ID, C.MONK_ID C_MONK_ID, UC.PERMISSIONS UC_PERMISSIONS, UC.DELETED UC_DELETED FROM USERCOL UC JOIN USERS U ON UC.USER_ID = U.ID JOIN COLLECTIONS C ON UC.COLLECTION_ID = C.ID WHERE U.MONK_ID = \'' . $s4aaasUserRec['MONK_ID'] . '\'') as $s4aaasColRec) {
            if (strtolower($s4aaasColRec['UC_DELETED']) == 'yes')
                $userManagementMonk->delete_collection($s4aaasColRec['U_MONK_ID'], $s4aaasColRec['C_MONK_ID']);
            else
                $userManagementMonk->add_collection($s4aaasColRec['U_MONK_ID'], $s4aaasColRec['C_MONK_ID'], $s4aaasColRec['UC_PERMISSIONS']);
        }
        
        foreach ($db->query('SELECT U.MONK_ID U_MONK_ID, I.MONK_ID I_MONK_ID, UI.PERMISSIONS UI_PERMISSIONS, UI.DELETED UI_DELETED FROM USERINST UI JOIN USERS U ON UI.USER_ID = U.ID JOIN INSTITUTIONS I ON UI.INSTITUTION_ID = I.ID WHERE U.MONK_ID = \'' . $s4aaasUserRec['MONK_ID'] . '\'') as $s4aaasInstRec) {
            if (strtolower($s4aaasInstRec['UI_DELETED']) == 'yes')
                $userManagementMonk->delete_institution($s4aaasInstRec['U_MONK_ID'], $s4aaasInstRec['I_MONK_ID']);
            else
                $userManagementMonk->add_institution($s4aaasInstRec['U_MONK_ID'], $s4aaasInstRec['I_MONK_ID'], $s4aaasInstRec['UI_PERMISSIONS']);
        }            

    }




// Normal synchronisation.    
} else {
    $qscui = $db->query('
  SELECT 
  U.MONK_ID                 U_MONK_ID
  ,U.PASSWORD                U_PASSWORD
  ,U.PERMISSIONS             U_PERMISSIONS
  ,U.TIMESTAMP_CHANGE        U_TIMESTAMP_CHANGE
  ,U.DISABLED                U_DISABLED
  ,U.DELETED                 U_DELETED
  ,B.MONK_DIR                B_MONK_DIR
  ,UB.PERMISSIONS            UB_PERMISSIONS
  ,UB.PAGE_FROM              UB_PAGE_FROM
  ,UB.PAGE_TO                UB_PAGE_TO
  ,UB.TIMESTAMP_CHANGE       UB_TIMESTAMP_CHANGE
  ,UB.DELETED                UB_DELETED
  ,C.MONK_ID                 C_MONK_ID
  ,UC.PERMISSIONS            UC_PERMISSIONS
  ,UC.TIMESTAMP_CHANGE       UC_TIMESTAMP_CHANGE
  ,UC.DELETED                UC_DELETED
  ,I.MONK_ID                 I_MONK_ID
  ,UI.PERMISSIONS            UI_PERMISSIONS
  ,UI.TIMESTAMP_CHANGE       UI_TIMESTAMP_CHANGE
  ,UI.DELETED                UI_DELETED
FROM 
  USERS U
  LEFT JOIN USERBOOK UB ON UB.USER_ID = U.ID
  LEFT JOIN BOOKS B ON UB.BOOK_ID = B.ID
  LEFT JOIN USERCOL  UC ON UC.USER_ID  = U.ID
  LEFT JOIN COLLECTIONS C ON UC.COLLECTION_ID = C.ID
  LEFT JOIN USERINST UI ON UI.USER_ID = U.ID
  LEFT JOIN INSTITUTIONS I ON UI.INSTITUTION_ID = I.ID
WHERE 
  (U.TIMESTAMP_CHANGE > \'' . $dto_monkPreviousTs->format(dtFormat) . '\' AND U.TIMESTAMP_CHANGE < \'' . $dto_monkActualTs->format(dtFormat) . '\')
  OR (UB.TIMESTAMP_CHANGE > \'' . $dto_monkPreviousTs->format(dtFormat) . '\' AND UB.TIMESTAMP_CHANGE < \'' . $dto_monkActualTs->format(dtFormat) . '\')
  OR (UC.TIMESTAMP_CHANGE > \'' . $dto_monkPreviousTs->format(dtFormat) . '\' AND UC.TIMESTAMP_CHANGE < \'' . $dto_monkActualTs->format(dtFormat) . '\')
  OR (UI.TIMESTAMP_CHANGE > \'' . $dto_monkPreviousTs->format(dtFormat) . '\' AND UI.TIMESTAMP_CHANGE < \'' . $dto_monkActualTs->format(dtFormat) . '\')
');
    foreach ($qscui as $scui) {
        $dto_u = new DateTime($scui['U_TIMESTAMP_CHANGE']);
        $dto_ub = new DateTime($scui['UB_TIMESTAMP_CHANGE']);
        $dto_uc = new DateTime($scui['UC_TIMESTAMP_CHANGE']);
        $dto_ui = new DateTime($scui['UI_TIMESTAMP_CHANGE']);

        if ($dto_u > $dto_monkPreviousTs && $dto_u < $dto_monkActualTs) {
            $userManagementMonk->add_username($scui['U_MONK_ID'], $scui['U_PASSWORD']);
            if (strtolower($scui['U_DISABLED']) == 'yes')
                $userManagementMonk->permissions->{$scui['U_MONK_ID']}->disabled = 'true';
            else
                $userManagementMonk->permissions->{$scui['U_MONK_ID']}->disabled = 'false';
            $userManagementMonk->change_password($scui['U_MONK_ID'], $scui['U_PASSWORD']);
            $userManagementMonk->change_global_permission($scui['U_MONK_ID'], $scui['U_PERMISSIONS']);
            if (strtolower($scui['U_DELETED']) == 'yes')
                $userManagementMonk->delete_username($scui['U_MONK_ID']);
        }
        if ($dto_ub > $dto_monkPreviousTs && $dto_ub < $dto_monkActualTs) {
            if (strtolower($scui['UB_DELETED']) == 'yes')
                $userManagementMonk->delete_book($scui['U_MONK_ID'], $scui['B_MONK_DIR']);
            else
                $userManagementMonk->add_book($scui['U_MONK_ID'], $scui['B_MONK_DIR'], $scui['UB_PERMISSIONS'], $scui['UB_PAGE_FROM'], $scui['UB_PAGE_TO']);
        }
        if ($dto_uc > $dto_monkPreviousTs && $dto_uc < $dto_monkActualTs) {
            if (strtolower($scui['UC_DELETED']) == 'yes')
                $userManagementMonk->delete_collection($scui['U_MONK_ID'], $scui['C_MONK_ID']);
            else
                $userManagementMonk->add_collection($scui['U_MONK_ID'], $scui['C_MONK_ID'], $scui['UC_PERMISSIONS']);
        }
        if ($dto_ui > $dto_monkPreviousTs && $dto_ui < $dto_monkActualTs) {
            if (strtolower($scui['UI_DELETED']) == 'yes')
                $userManagementMonk->delete_institution($scui['U_MONK_ID'], $scui['I_MONK_ID']);
            else
                $userManagementMonk->add_institution($scui['U_MONK_ID'], $scui['I_MONK_ID'], $scui['UI_PERMISSIONS']);
        }
    }
}
$userManagementMonk->write();
?>
