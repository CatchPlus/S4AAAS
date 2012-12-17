<?php
    define('appini_path', '/var/www/s4aaas.target-imedia.nl/rest/application/configs/application.ini');
    $appini = parse_ini_file(appini_path);

    // Connect to database server
    $db = new mysqli( $appini['resources.db.params.host']
                    , $appini['resources.db.params.username']
                    , $appini['resources.db.params.password']
                    , $appini['resources.db.params.dbname'])
          or die ("Unable to connect\n");

    // Create statement object
    $stmt = $db->stmt_init();

    // Create a prepared statement
    $stmt->prepare("DELETE FROM IMAGELOOKUP WHERE id=?")
          or die ("Unable to prepare delete\n");

    // Bind your variables to replace the ?s
    $stmt->bind_param('i', $id);

    // Execute the query
    $res = $db->query(
               "SELECT il1.ID
                FROM `IMAGELOOKUP` il1
                , (SELECT OBJECT_ID
                  ,       TYPE
                  ,       MAX(VALID_UNTIL) VALID_UNTIL
                  FROM  IMAGELOOKUP
                  WHERE VALID_UNTIL < sysdate()
                  GROUP BY OBJECT_ID, TYPE) il2
                WHERE il1.OBJECT_ID   = il2.OBJECT_ID
                AND   il1.TYPE        = il2.TYPE
                AND   il1.VALID_UNTIL < il2.VALID_UNTIL
                LIMIT 100
               ")
          or die ("Unable to run query\n");

    // Query number of rows in rowset
    $nrows = $res->num_rows;

    // Output
    // echo "The query returned $nrows row(s):\n\n";

    // Iteration loop, for each row in rowset
    for ($row = 0; $row < $nrows; $row++) {
        $res->data_seek($row);
        $data = $res->fetch_assoc();

        // Assigning variables from cell values
        $id = $data["ID"];

        echo "Deleting# $id \n";

        // Execute query
        $stmt->execute();
    }
    $stmt->close();

    /* free result set */
    $res->close();

    // Execute the query
    $res = $db->query(
               "SELECT li.ID
                FROM  `LINES` AS li
                WHERE NOT EXISTS
                ( SELECT 1
                  FROM `IMAGELOOKUP` AS il
                  WHERE li.ID = il.OBJECT_ID AND il.OBJECT_ID IS NULL
                )
                AND   li.IMAGE_RENDERED = 1
               ")
          or die ("Unable to run query\n");

    // Query number of rows in rowset
    $nrows = $res->num_rows;

    // Iteration loop, for each row in rowset
    for ($row = 0; $row < $nrows; $row++) {
        $res->data_seek($row);
        $data = $res->fetch_assoc();

        // Assigning variables from cell values
        $id = $data["ID"];

        echo "Deleting### $id \n";
    }
    /* free result set */
    $res->close();
?>
