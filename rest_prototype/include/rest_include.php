<?php

function id2dir($id)
{
  if (strpos($id, 'navis-H24001_7824') === 0)
  {
    if (substr($id, 18, 4) <= 1346)
      return 'navis';
    return 'navis2';
  }
  else
  {
    $book_id = bookid(line_id($id));
    foreach (bookdirlist() as $bookdir)
      if (bookid(sprintf(bookdir2linepattern($bookdir), 000, 0000)) === $book_id)
        return $bookdir;
  }
}

function get_page_url()
{
  $page_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
  while (substr($page_url, -1) != '/')
    $page_url = substr($page_url, 0, strlen($page_url) - 1);
  return $page_url;
}

?>
