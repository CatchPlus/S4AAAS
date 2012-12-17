<?php

class db {

    public $dbConn;

    function __construct() {
        $appini = parse_ini_file(appini_path);
        $this->dbConn = mysql_connect($appini['resources.db.params.host'], $appini['resources.db.params.username'], $appini['resources.db.params.password']) or exit('No database connection.');
        mysql_select_db($appini['resources.db.params.dbname'], $this->dbConn) or exit(mysql_error());
    }

    function stmnt($sql) {
        
        print "$sql<br><br>\n\n";
        
        mysql_query($sql, $this->dbConn) or exit(mysql_error());
    }

    function query($sql, $column = false) {
        
        print "$sql<br><br>\n\n";
        
        $s = array();
        if ($q = mysql_query($sql, $this->dbConn) or exit(mysql_error()))
            while ($r = mysql_fetch_array($q))
                $column ? $s[] = $r[0] : $s[] = $r;
        return $s;
    }
    
    function q1($sql){
        $r = $this->query($sql);
        return $r[0][0];
    }

}

?>
