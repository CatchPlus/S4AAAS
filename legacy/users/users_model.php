<?php

define('xml_path', '/target/gpfs2/monk/UserPreferences/.permissions/%s.xml');
define('users', '/target/gpfs2/monk/.monk.passwd');

class userModel
{

  public $permissions;
  public $memory;
  public $xmlWriter;
  public $admin_perm;

  function __construct()
  {
    $this->read();
    $this->admin_perm = max(array_keys($this->convert_permission(0)));
  }

  function read()
  {

    $xml = '<users>';
    foreach (explode("\n", $this->fileGetContents(users)) as $line)
    {
      @list($username, $password) = explode("\t", $line);
      if (strlen($username) && strlen($password))
      {
        $xml .= '<' . $username . '>';
        $xml .= '<password>' . $password . '</password>';
        $xml .= $this->validXmlString($this->delPrefixSuffixSpaceXmlValues($this->delVersionFormatXml($this->fileGetContents(sprintf(xml_path, $username)))));
        $xml .= '</' . $username . '>';
      }
    }
    $xml .= '</users>';
    $this->permissions = simplexml_load_string($xml);
    $this->permissions_subset = simplexml_load_string($xml);
  }

  function write()
  {
    foreach ($this->permissions as $k => $v)
    {
      $dom_permissions = new DOMDocument;
      $dom_permissions->preserveWhiteSpace = false;
      $dom_permissions->loadXML($this->addPrefixSuffixSpaceXmlValues($v->permissions->asXML()));
      $dom_permissions->formatOutput = true;
      if (@file_put_contents(sprintf(xml_path, $k), $this->delVersionFormatXml($dom_permissions->saveXML())))
        chmod(sprintf(xml_path, $k), 0660);
      else
        return false;
    }
    $users_output = '';
    foreach ($this->permissions as $k => $v)
      $users_output .= $k . "\t" . $v->password . "\n";
    if (strlen($users_output) != 0)
    {
      if (@file_put_contents(users, $users_output))
        chmod(users, 0660);
      else
        return false;
    }
    return true;
  }

  function validXmlString($xml)
  {
    if (@simplexml_load_string($xml))
      return $xml;
    return '';
  }

  function fileGetContents($filename)
  {
    if (@$contents = file_get_contents($filename))
      return $contents;
    return '';
  }

  function delVersionFormatXml($xml)
  {
    $xml = preg_replace('/<\?xml.*\?>\n/', '', $xml);
    return preg_replace('/<\?xml.*\?>/', '', $xml);
  }

  function addPrefixSuffixSpaceXmlValues($xml)
  {
    return str_replace('>', '> ', str_replace('<', ' <', $xml));
  }

  function delPrefixSuffixSpaceXmlValues($xml)
  {
    return str_replace('> ', '>', str_replace(' <', '<', $xml));
  }

  function valid($username, $password)
  {
    if ($this->permissions->$username->password == $this->makemonkpw($password))
      return true;
    return false;
  }

  function makemonkpw($password)
  {
    exec('/target/gpfs2/monk/bin/makemonkpw -enc \'' . escapeshellarg($password) . '\'', $monkpw);
    if (isset($monkpw[0]))
      return $monkpw[0];
    else
      return $password;
  }

  function valid_username($username)
  {
    if (strlen($username) && preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $username) && substr(strtolower($username), 0, 3) != 'xml')
      return true;
    return false;
  }

  function add_username($target_username, $target_password)
  {
    if (!isset($this->permissions->$target_username) && strlen($target_password) && $this->valid_username($target_username))
    {
      $this->permissions->$target_username->password = $this->makemonkpw($target_password);
      $this->permissions->$target_username->permissions->global_permission = '1';
      return true;
    }
    return false;
  }

  function delete_username($target_username)
  {
    if (isset($this->permissions->$target_username))
    {
      unset($this->permissions->$target_username);
      if (is_file(sprintf(xml_path, $target_username)))
        unlink(sprintf(xml_path, $target_username));
      return true;
    }
    return false;
  }

  function change_password($target_username, $target_password)
  {
    if (isset($this->permissions->$target_username) && strlen($target_password))
    {
      $this->permissions->$target_username->password = $this->makemonkpw($target_password);
      return true;
    }
    return false;
  }

  function change_global_permission($target_username, $global_permission)
  {
    if (isset($this->permissions->$target_username->permissions->global_permission))
    {
      $this->permissions->$target_username->permissions->global_permission = $global_permission;
      return true;
    }
    return false;
  }

  function add_book($target_username, $book_id, $book_permission, $page_from, $page_to)
  {
    if (isset($this->permissions->$target_username))
    {
      foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/books/book/book_id') as $k => $v)
      {
        if ($v == $book_id)
        {
          $this->permissions->$target_username->permissions->books->book[$k]->book_permission = $book_permission;
          $this->permissions->$target_username->permissions->books->book[$k]->page_from = $page_from;
          $this->permissions->$target_username->permissions->books->book[$k]->page_to = $page_to;
          return true;
        }
      }
      $this->permissions->$target_username->permissions->books->book[]->book_id = $book_id;
      $count = count($this->permissions->$target_username->permissions->books->book) - 1;
      $this->permissions->$target_username->permissions->books->book[$count]->book_permission = $book_permission;
      $this->permissions->$target_username->permissions->books->book[$count]->page_from = $page_from;
      $this->permissions->$target_username->permissions->books->book[$count]->page_to = $page_to;
      return true;
    }
    return false;
  }

  function add_collection($target_username, $collection_id, $collection_permission)
  {
    if (isset($this->permissions->$target_username))
    {
      foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/collections/collection/collection_id') as $k => $v)
      {
        if ($v == $collection_id)
        {
          $this->permissions->$target_username->permissions->collections->collection[$k]->collection_permission = $collection_permission;
          return true;
        }
      }
      $this->permissions->$target_username->permissions->collections->collection[]->collection_id = $collection_id;
      $count = count($this->permissions->$target_username->permissions->collections->collection) - 1;
      $this->permissions->$target_username->permissions->collections->collection[$count]->collection_permission = $collection_permission;
      return true;
    }
    return false;
  }

  function add_institution($target_username, $institution_id, $institution_permission)
  {
    if (isset($this->permissions->$target_username))
    {
      foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/institutions/institution/institution_id') as $k => $v)
      {
        if ($v == $institution_id)
        {
          $this->permissions->$target_username->permissions->institutions->institution[$k]->institution_permission = $institution_permission;
          return true;
        }
      }
      $this->permissions->$target_username->permissions->institutions->institution[]->institution_id = $institution_id;
      $count = count($this->permissions->$target_username->permissions->institutions->institution) - 1;
      $this->permissions->$target_username->permissions->institutions->institution[$count]->institution_permission = $institution_permission;
      return true;
    }
    return false;
  }

  function delete_institution($target_username, $institution_id)
  {
    foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/institutions/institution/institution_id') as $k => $v)
    {
      if ($v == $institution_id)
      {
        unset($this->permissions->$target_username->permissions->institutions->institution[$k]);
        return true;
      }
    }
    return false;
  }

  function delete_collection($target_username, $collection_id)
  {
    foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/collections/collection/collection_id') as $k => $v)
    {
      if ($v == $collection_id)
      {
        unset($this->permissions->$target_username->permissions->collections->collection[$k]);
        return true;
      }
    }
    return false;
  }

  function delete_book($target_username, $book_id)
  {
    foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/books/book/book_id') as $k => $v)
    {
      if ($v == $book_id)
      {
        unset($this->permissions->$target_username->permissions->books->book[$k]);
        return true;
      }
    }
    return false;
  }

  function institution2books($target_username, $institution, $books_array = array())
  {
    foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/institutions/institution/institution_id') as $k => $institution)
      foreach (institutions() as $bookdir => $institution1)
        if ($institution1 == (string) $institution)
          $books_array[$bookdir] = array(
              'book_permission' => (int) $this->permissions->$target_username->permissions->institutions->institution->$k->institution_permission,
              'page_from' => 1,
              'page_to' => 99999,
          );
    return $books_array;
  }

  function institution2books1($institution)
  {
    $books_array = array();
    foreach (institutions() as $bookdir => $institution1)
      if ($institution1 == (string) $institution)
        $books_array[] = $bookdir;
    return $books_array;
  }

  function collection2books($target_username, $collection, $books_array = array())
  {
    foreach ($this->permissions->xpath('/users/' . $target_username . '/permissions/collections/collection/collection_id') as $k => $collection)
      foreach (collections() as $bookdir => $collection1)
        if ($collection1 == (string) $collection)
          $books_array[$bookdir] = array(
              'book_permission' => (int) $this->permissions->$target_username->permissions->collections->collection->$k->collection_permission,
              'page_from' => 1,
              'page_to' => 99999,
          );
    return $books_array;
  }

  function collection2books1($collection)
  {
    $books_array = array();
    foreach (collections() as $bookdir => $collection1)
      if ($collection1 == (string) $collection)
        $books_array[] = $bookdir;
    return $books_array;
  }

  function get_permissions_booklevel($username)
  {
    $books = array();
    // For global admin return all books with full permissions
    if ($this->permissions->$username->permissions->global_permission == $this->admin_perm)
    {
      foreach (bookdirlist() as $bookdir)
        $books[$bookdir] = array(
            'book_permission' => $this->admin_perm,
            'page_from' => 1,
            'page_to' => 99999,
        );
      return $books;
    }
    // Get institutions
    foreach ($this->permissions->xpath('/users/' . $username . '/permissions/institutions/institution/institution_id') as $institution)
      foreach ($this->institution2books($username, $institution) as $k => $v)
        $books[$k] = $v;
    // Get collections
    foreach ($this->permissions->xpath('/users/' . $username . '/permissions/collections/collection/collection_id') as $collection)
      foreach ($this->collection2books($username, $collection) as $k => $v)
        $books[$k] = $v;
    // Get books
    foreach ($this->permissions->xpath('/users/' . $username . '/permissions/books/book/book_id') as $k => $book)
      $books[(string) $book] = array(
          'book_permission' => (int) $this->permissions->$username->permissions->books->book->$k->book_permission,
          'page_from' => (int) $this->permissions->$username->permissions->books->book->$k->page_from,
          'page_to' => (int) $this->permissions->$username->permissions->books->book->$k->page_to,
      );
    return $books;
  }

  function make_subset($username)
  {
    if ($this->admin_perm != (int) $this->permissions->$username->permissions->global_permission)
    {
      $actual_user_permissions_booklevel = $this->get_permissions_booklevel($username);
      foreach (array_keys((array) $this->permissions) as $username1)
      {
        foreach ($this->get_permissions_booklevel($username1) as $book_id => $details)
        {
          if (
                  array_key_exists($book_id, $actual_user_permissions_booklevel) == false
                  || $actual_user_permissions_booklevel[$book_id]['book_permission'] != $this->admin_perm
                  || $actual_user_permissions_booklevel[$book_id]['page_from'] < $details['page_from']
                  || $actual_user_permissions_booklevel[$book_id]['page_to'] < $details['page_to']
          )
            unset($this->permissions_subset->$username1);
        }
      }
    }
  }

  function full_hierarchical_books()
  {
    $hierarchical = array();
    foreach (institutions() as $bookdir => $institution)
      foreach (collections() as $bookdir1 => $collection)
        if ($bookdir == $bookdir1)
          $hierarchical[$institution][$collection][$bookdir] = '';
    return $hierarchical;
  }

  function hierarchical_books($username)
  {
    $hierarchical = array();
    $permissions_booklevel_user = $this->get_permissions_booklevel($username);
    foreach ($this->full_hierarchical_books() as $institution => $collections)
      foreach ($collections as $collection => $books)
        foreach ($books as $bookdir => $d)
          if (
                  array_key_exists($bookdir, $permissions_booklevel_user)
                  && $permissions_booklevel_user[$bookdir]['book_permission'] == $this->admin_perm
          )
            $hierarchical[$institution][$collection][$bookdir] = '';
    return $hierarchical;
  }

  function hierarchical_collections($username)
  {
    $hierarchical = array();
    $permissions_booklevel_user = $this->get_permissions_booklevel($username);
    foreach ($this->full_hierarchical_books() as $institution => $collections)
      foreach (array_keys($collections) as $collection)
      {
        $permission = true;
        foreach ($this->collection2books1($collection) as $bookdir)
          if (
                  !array_key_exists($bookdir, $permissions_booklevel_user)
                  || $permissions_booklevel_user[$bookdir]['book_permission'] != $this->admin_perm
          )
          {
            $permission = false;
            break;
          }
        if ($permission)
          $hierarchical[$institution][$collection] = '';
      }
    return $hierarchical;
  }

  function institutions($username)
  {
    $user_institutions = array();
    $permissions_booklevel_user = $this->get_permissions_booklevel($username);
    foreach (array_values(array_unique(institutions())) as $institution)
    {
      $permission = true;
      foreach ($this->institution2books1($institution) as $bookdir)
        if (
                !array_key_exists($bookdir, $permissions_booklevel_user)
                || $permissions_booklevel_user[$bookdir]['book_permission'] != $this->admin_perm
        )
        {
          $permission = false;
          break;
        }
      if ($permission)
        $user_institutions[] = $institution;
    }
    return $user_institutions;
  }

  ////////////////////////////////////////////////////////////////////////////
  // Disabling a user means that the user can still login, but can't annotate.
  // It is implemented by moving the permission file to 
  // '$username.disabled.xml', and creating a "guest" xml-file at 
  // '$username.xml', i.e., with only a global permission of 1 ("guest").
  function disable($target_username)
  {
    $xml_file = sprintf(xml_path, $target_username);
    $xml_file_disabled = sprintf(xml_path, $target_username . ".disabled");
    rename($xml_file, $xml_file_disabled);
    unset($this->permissions->$target_username->permissions);
    $this->permissions->$target_username->permissions->global_permission = '1';
  }

  function enable($target_username)
  {
    $xml_file = sprintf(xml_path, $target_username);
    $xml_file_disabled = sprintf(xml_path, $target_username . ".disabled");
    rename($xml_file_disabled, $xml_file);
    unset($this->permissions->$target_username->permissions);
    $this->read();
  }

  function disabled($target_username)
  {
    $xml_file_disabled = sprintf(xml_path, $target_username . ".disabled");
    return file_exists($xml_file_disabled);
  }

  function global_admin($username)
  {
    return $this->permissions->$username->permissions->global_permission == $this->admin_perm;
  }

  function global_writer($username)
  {
    return $this->permissions->$username->permissions->global_permission >= 7;
  }

  function convert_permission($key)
  {
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
