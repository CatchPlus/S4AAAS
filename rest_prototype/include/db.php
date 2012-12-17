<?php

class db
{

  public $conn;

  function __construct()
  {
    $_CONFIG['username'] = 's4aaas';
    $_CONFIG['password'] = '49PH9HhHd6buvjSE';
    $_CONFIG['host'] = 'THDev2.target-imedia.nl';
    $_CONFIG['database'] = 's4aaas';
    $this->conn = mysql_connect($_CONFIG['host'], $_CONFIG['username'], $_CONFIG['password']) or exit('No database connection.');
    mysql_select_db($_CONFIG['database'], $this->conn) or exit(mysql_error());
  }

  function __destruct()
  {
    mysql_close($this->conn);
  }

  function query($sql)
  {
    $set = array();
    if ($result = mysql_query($sql, $this->conn) or exit(mysql_error()))
      while ($row = mysql_fetch_array($result))
        $set[] = $row;
    return $set;
  }

  function query_column($sql)
  {
    $column = array();
    if ($result = mysql_query($sql, $this->conn) or exit(mysql_error()))
      while ($row = mysql_fetch_array($result))
        $column[] = $row[0];
    return $column;
  }

}

?>
