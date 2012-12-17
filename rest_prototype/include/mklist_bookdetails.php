<?php

// <book directory>:<book id>:<book institution>:<book collection>
require 'navis-id2path.php';
$institutions = institutions();
foreach (collections() as $k => $v)
  print $k . ':' . str_replace('_%04d-line-%03d', '', bookdir2linepattern($k)) . ':' . $institutions[$k] . ':' . $v . "\n";
?>
