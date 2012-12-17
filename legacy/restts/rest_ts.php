<?php


error_reporting(-1);

$time['total_starttime'] = microtime(true);
require 'include/navis-id2path.php';
require 'include/rest_include.php';
require 'include/rest_metadata.php';
define('monkserver', 'http://application01.target.rug.nl/');
define('triesearch_dir', getcwd() . '/../triesearch');
define('imgcache_dir', getcwd() . '/../imgcache');
define('linecache_dir', getcwd() . '/../linecache');
define('annotated_line_file_format', monkserver . 'monk/%s/Pages/%s/Lines/Txt/%s.txt'); // bookdir, page_id, line_id
define('annotated_page_file_format', monkserver . 'monk/%s/Txt/%s.txt'); //bookdir, page_id
define('labeled_lineid_format', monkserver . 'monk/%s/Index/all-%s-labeled-line.IDs+txt'); //bookdir, bookdir
define('file_page_path', monkserver . 'monk/%s/Jpeg/%s.jpg'); // bookdir, page_id
define('file_line_path', monkserver . 'monk/%s/Pages/%s/Lines/web-grey/%s.jpg'); //bookdir, page_id, line_id
define('blacklist', '/[^a-zA-Z0-9_@=<>\-]/');
define('MONK_version', '1.0 build 2');
$imgcache_resolution = '800';
$wordzone_types_global = array('HUMAN', 'JAVA', 'RECOG', 'RECOGe', 'MINED');
$annotations_global = array('wordzone' => 'W', 'line' => 'L', 'page' => 'P');

if (!isset($_SERVER['PATH_INFO']))
  status_code(400, 'Unknown REST URL.');

$url = explode('/', strtolower($_SERVER['PATH_INFO']));
$resource = isset($url[1]) ? $url[1] : '';
$searchstr = isset($url[2]) ? $url[2] : '';

//date_default_timezone_set('Europe/Amsterdam');
//file_put_contents('log.txt', date('Y-m-d H:i:s') . ', PATH_INFO: ' . $_SERVER['PATH_INFO'] . ', QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . "\n", 8);

switch ($resource)
{
  case 'index':
    restrict_params(array('offset', 'rows', 'institutions', 'collections', 'books', 'match', 'annotations', 'wordzonetypes'));
    $results = search();
    xml_index($results);
    break;
  case 'books':
    restrict_params(array());
    xml_books(books());
    break;
  case 'suggestions':
    restrict_params(array('rows'));
    $results = suggestions();
    xml_suggestions($results);
    break;
  default:
    status_code(400, 'Unknown REST URL.');
}

function search()
{
  global $time;
  $time['linecache_time'] = 0;
  $time['topline_time'] = 0;
  $wordzone_annotations = array();
  $line_annotations = array();
  $page_annotations = array();
  $r = shellcmd();
  $shellcmd = $r['shellcmd'];
  $total = $r['total'];
  $time['trie_search_starttime'] = microtime(true);
  exec($shellcmd, $lines);
  $time['trie_search_time'] = microtime(true) - $time['trie_search_starttime'];
  foreach ($lines as $line)
  {
    $lineparts = explode("\t", $line);
    $annotation = $lineparts[0];
    $txt = $lineparts[1];
    $institution = $lineparts[2];
    $collection = $lineparts[3];
    $page = $lineparts[5];
    $line_nr = $lineparts[6];
    $wordzone_type = $lineparts[7];
    $id = $lineparts[9];
    $bookdir = id2dir($id);
    $book_id = bookid($id);
    $page_id = page_id($id);
    $shortname = shortname($id);
    if ($annotation == 'W')
    {
      $wordzone_annotations[] = array(
          'txt' => $txt,
          'institution' => $institution,
          'collection' => $collection,
          'bookdir' => $bookdir,
          'page' => $page,
          'line' => $line_nr,
          'navis_id' => $id,
          'book_id' => $book_id,
          'shortname' => $shortname,
          'file_page_path' => sprintf(file_page_path, $bookdir, page_id($id)),
          'file_line_path' => sprintf(file_line_path, $bookdir, page_id($id), line_id($id))
      );
    } elseif ($annotation == 'L')
    {
      $time['linecache_starttime'] = microtime(true);
      $annotated_line = exec('cd ' . linecache_dir . '; ./getline cache/' . page_id($id) . '.lines ' . $id . ' ' . sprintf(annotated_line_file_format, $bookdir, $page_id, $id));
      $time['linecache_time'] += (microtime(true) - $time['linecache_starttime']);
      $line_annotations[] = array(
          'txt' => $txt,
          'institution' => $institution,
          'collection' => $collection,
          'bookdir' => $bookdir,
          'page' => $page,
          'line' => $line_nr,
          'line_id' => line_id($id),
          'annotated_line' => $annotated_line,
          'book_id' => $book_id,
          'shortname' => $shortname,
          'file_page_path' => sprintf(file_page_path, $bookdir, page_id($id)),
          'file_line_path' => sprintf(file_line_path, $bookdir, page_id($id), line_id($id))
      );
    } elseif ($annotation == 'P')
    {
      $id = str_replace('-line-PAGE', '', $id);
      $page_id = page_id($id);
      $time['linecache_starttime'] = microtime(true);
      $time['linecache_time'] += (microtime(true) - $time['linecache_starttime']);
      $annotated_page = exec('cd ' . linecache_dir . '; ./getline cache/' . $page_id . '.lines ' . $page_id . ' ' . sprintf(annotated_page_file_format, $bookdir, $page_id));
      $time['topline_starttime'] = microtime(true);
      $line_id = exec('cd ' . triesearch_dir . '; ./trie-search7 --trie=index-topline.trie --key=' . $id . '-line-001 --limit=1');
      $time['topline_time'] += (microtime(true) - $time['topline_starttime']);
      $page_annotations[] = array(
          'txt' => $txt,
          'institution' => $institution,
          'collection' => $collection,
          'bookdir' => $bookdir,
          'page' => $page,
          'page_id' => $page_id,
          'line_id' => $line_id,
          'annotated_page' => $annotated_page,
          'book_id' => $book_id,
          'shortname' => $shortname,
          'file_page_path' => sprintf(file_page_path, $bookdir, $page_id),
          'file_line_path' => sprintf(file_line_path, $bookdir, $page_id, $line_id)
      );
    }
  }
  $results = array(
      'wordzone_annotations' => $wordzone_annotations,
      'line_annotations' => $line_annotations,
      'page_annotations' => $page_annotations,
      'total' => $total,
      'shellcmd' => $shellcmd
  );
  return $results;
}

function shellcmd()
{
  global $searchstr;
  global $time;
  if (!strlen($searchstr))
    status_code(400, 'Search string empty.');
  $obj_parameters = new parameters;
  $match = $obj_parameters->match();
  $offset = $obj_parameters->offset();
  $rows = $obj_parameters->rows();
  $bs = books_selection();

  if ($match == 'prefix')
  {
    $p = '(';
    $s = '*)';
  } else if ($match == 'suffix')
  {
    $p = '(*';
    $s = ')';
  } else if ($match == 'exact')
  {
    $p = '(';
    $s = ')';
  } else if ($match == 'wildcard' || $match == 'suffix')
  {
    $p = '(*';
    $s = '*)';
  }

  $shellcmd = '';
  $shellcmd .= 'cd ' . triesearch_dir . '; ';
  $shellcmd .= './trie-search7 ';
  $shellcmd .= '--trie=\'index-bylabel4.trie\' ';

  $wordzone_types = $obj_parameters->wordzone_types();
  $annotations = $obj_parameters->annotations();
  foreach ($annotations as $a)
    if ($a == 'W')
      foreach ($wordzone_types as $w)
        $shellcmd .= '--key=' . escapeshellarg('W\t' . $w . '\t' . $p . $searchstr . $s . '\t') . ' ';
    else
      $shellcmd .= '--key=' . escapeshellarg('' . $a . '\t\t' . $p . $searchstr . $s . '\t') . ' ';
  $shellcmd .= '--substring=\'index-substrings.trie\' --mergeskip=3 ';

  foreach ($bs['selections_filtered'] as $s)
    if (!($s['i'] == '*' && $s['c'] == '*' && $s['b'] == '*'))
      $shellcmd .= '--accept=\'*\t*\t*\t' . $s['i'] . '\t' . $s['c'] . '\t' . $s['b'] . '\t\' ';
  if (strpos($shellcmd, '--accept'))
    $shellcmd .= '--reject=\'*\' ';

  $time['count_starttime'] = microtime(true);
  $results['total'] = exec($shellcmd . '--count ');
  $time['count_time'] = microtime(true) - $time['count_starttime'];

  $shellcmd .= '--limit=' . $rows . ' ';
  $shellcmd .= '--offset=' . $offset . ' ';

  $shellcmd .= '| ./trie-lookup --full-index=\'index-full.txt\' ';

  $results['shellcmd'] = $shellcmd;
  return $results;
}

function xml_index($results)
{
  global $time;
  global $imgcache_resolution;
  $obj_parameters = new parameters;
  $bs = books_selection();
  $xml = new xmlWriter();
  $xml->openMemory();
  $xml->setIndent(True);
  $xml->startDocument('1.0', 'UTF-8');
  $xml->startElement('searchresults');
  $xml->writeElement('total', $results['total']);
  $xml->writeElement('send', count($results['wordzone_annotations']) + count($results['line_annotations']) + count($results['page_annotations']));
  $xml->writeElement('offset', $obj_parameters->offset());
  $xml->writeElement('rows', $obj_parameters->rows());
  $xml->writeElement('match', $obj_parameters->match());
  $xml->writeElement('annotations', implode('|', array_keys($obj_parameters->annotations())));
  $xml->writeElement('wordzone_types', implode('|', $obj_parameters->wordzone_types()));
  $xml->writeElement('temp_shellcmd_for_debug', $results['shellcmd']);
  $xml->writeElement('institutions_ids', implode('|', $bs['institutions_filtered']));
  $xml->writeElement('collections_ids', implode('|', $bs['collections_filtered']));
  $xml->writeElement('book_ids', implode('|', $bs['books_ids_filtered']));

  $time['imgcache_starttime'] = microtime(true);

  // IMGCACHE WORDZONE
  $cmd = 'cd ' . imgcache_dir . '; ./getimage factor links/ ' . $imgcache_resolution . ' ';
  foreach ($results['wordzone_annotations'] as $wordzone_annotation)
    $cmd .= $wordzone_annotation['file_line_path'] . ' cache/' . line_id($wordzone_annotation['navis_id']) . '-' . $imgcache_resolution . '.tjpg ';
  exec($cmd, $o);
  for ($i = 0; $i < count($results['wordzone_annotations']); $i++)
  {
    $file_line_path = @explode('|', $o[$i]);
    $file_page_path = @explode('|', exec('cd ' . imgcache_dir . '; ./getimage link links/ ' . $imgcache_resolution . ' ' . $results['wordzone_annotations'][$i]['file_page_path'] . ' cache/' . page_id($results['wordzone_annotations'][$i]['navis_id']) . '-' . $imgcache_resolution . '.tjpg '));
    $results['wordzone_annotations'][$i]['file_line_path_anonymized'] = isset($file_line_path[0]) && strlen($file_line_path[0]) ? get_page_url() . 'getimage.php?id=' . $file_line_path[0] : '';
    $results['wordzone_annotations'][$i]['file_line_path_anonymized_factor'] = isset($file_line_path[1]) && strlen($file_line_path[1]) ? $file_line_path[1] : '';
    $results['wordzone_annotations'][$i]['file_page_path_anonymized'] = isset($file_page_path[0]) && strlen($file_page_path[0]) ? get_page_url() . 'getimage.php?id=' . $file_page_path[0] : '';
  }

  // IMGCACHE LINE
  $cmd = 'cd ' . imgcache_dir . '; ./getimage factor links/ ' . $imgcache_resolution . ' ';
  foreach ($results['line_annotations'] as $line_annotation)
    $cmd .= $line_annotation['file_line_path'] . ' cache/' . line_id($line_annotation['line_id']) . '-' . $imgcache_resolution . '.tjpg ';
  exec($cmd, $o);
  for ($i = 0; $i < count($results['line_annotations']); $i++)
  {
    $file_line_path = @explode('|', $o[$i]);
    $file_page_path = @explode('|', exec('cd ' . imgcache_dir . '; ./getimage link links/ ' . $imgcache_resolution . ' ' . $results['line_annotations'][$i]['file_page_path'] . ' cache/' . page_id($results['line_annotations'][$i]['line_id']) . '-' . $imgcache_resolution . '.tjpg '));
    $results['line_annotations'][$i]['file_line_path_anonymized'] = isset($file_line_path[0]) && strlen($file_line_path[0]) ? get_page_url() . 'getimage.php?id=' . $file_line_path[0] : '';
    $results['line_annotations'][$i]['file_line_path_anonymized_factor'] = isset($file_line_path[1]) && strlen($file_line_path[1]) ? $file_line_path[1] : '';
    $results['line_annotations'][$i]['file_page_path_anonymized'] = isset($file_page_path[0]) && strlen($file_page_path[0]) ? get_page_url() . 'getimage.php?id=' . $file_page_path[0] : '';
  }

  // IMGCACHE PAGE
  $cmd = 'cd ' . imgcache_dir . '; ./getimage factor links/ ' . $imgcache_resolution . ' ';
  foreach ($results['page_annotations'] as $page_annotation)
    $cmd .= $page_annotation['file_line_path'] . ' cache/' . $page_annotation['line_id'] . '-' . $imgcache_resolution . '.tjpg ';
  exec($cmd, $o);
  for ($i = 0; $i < count($results['page_annotations']); $i++)
  {
    $file_line_path = @explode('|', $o[$i]);
    $file_page_path = @explode('|', exec('cd ' . imgcache_dir . '; ./getimage link links/ ' . $imgcache_resolution . ' ' . $results['page_annotations'][$i]['file_page_path'] . ' cache/' . page_id($results['page_annotations'][$i]['line_id']) . '-' . $imgcache_resolution . '.tjpg '));
    $results['page_annotations'][$i]['file_line_path_anonymized'] = isset($file_line_path[0]) && strlen($file_line_path[0]) ? get_page_url() . 'getimage.php?id=' . $file_line_path[0] : '';
    $results['page_annotations'][$i]['file_line_path_anonymized_factor'] = isset($file_line_path[1]) && strlen($file_line_path[1]) ? $file_line_path[1] : '';
    $results['page_annotations'][$i]['file_page_path_anonymized'] = isset($file_page_path[0]) && strlen($file_page_path[0]) ? get_page_url() . 'getimage.php?id=' . $file_page_path[0] : '';
  }

  $xml->startElement('time');
  $xml->writeElement('count', round($time['count_time'], 3));
  $xml->writeElement('trie_search', round($time['trie_search_time'], 3));
  $xml->writeElement('linecache', round($time['linecache_time'], 3));
  $xml->writeElement('topline', round($time['topline_time'], 3));
  $xml->writeElement('imgcache', round(microtime(true) - $time['imgcache_starttime'], 3));
  $xml->writeElement('total', round(microtime(true) - $time['total_starttime'], 3));
  $xml->endElement();

  $xml->startElement('wordzone_annotations');
  foreach ($results['wordzone_annotations'] as $wordzone_annotation)
  {
    $xml->startElement('wordzone_annotation');
    $xml->writeElement('navis_id', $wordzone_annotation['navis_id']);
    $xml->writeElement('file_page_path', $wordzone_annotation['file_page_path_anonymized']);
    $xml->writeElement('file_line_path', $wordzone_annotation['file_line_path_anonymized']);
    $xml->writeElement('file_line_path_factor', $wordzone_annotation['file_line_path_anonymized_factor']);
    $xml->writeElement('metadata', metadata('book_id2na_url', $wordzone_annotation['book_id']));
    $xml->writeElement('institution', $wordzone_annotation['institution']);
    $xml->writeElement('collection', $wordzone_annotation['collection']);
    $xml->writeElement('book_id', $wordzone_annotation['book_id']);
    $xml->writeElement('shortname', $wordzone_annotation['shortname']);
    list($kboek, $kpage, $kline) = parse_line_id($wordzone_annotation['navis_id']);
    $xml->writeElement('page', $kpage);
    $xml->writeElement('line', $kline);
    $xml->writeElement('x', parameter_value('-x=', $wordzone_annotation['navis_id']));
    $xml->writeElement('y', parameter_value('-y=', $wordzone_annotation['navis_id']));
    $xml->writeElement('w', parameter_value('-w=', $wordzone_annotation['navis_id']));
    $xml->writeElement('h', parameter_value('-h=', $wordzone_annotation['navis_id']));
    $xml->writeElement('y1', parameter_value('-y1=', $wordzone_annotation['navis_id']));
    $xml->writeElement('y2', parameter_value('-y2=', $wordzone_annotation['navis_id']));
    $xml->writeElement('txt', $wordzone_annotation['txt']);
    $xml->writeElement('source', MONK_version);
    $xml->endElement();
  }
  $xml->endElement();
  $xml->startElement('line_annotations');
  foreach ($results['line_annotations'] as $line_annotation)
  {
    $xml->startElement('line_annotation');
    $xml->writeElement('line_id', $line_annotation['line_id']);
    $xml->writeElement('file_page_path', $line_annotation['file_page_path_anonymized']);
    $xml->writeElement('file_line_path', $line_annotation['file_line_path_anonymized']);
    $xml->writeElement('file_line_path_factor', $line_annotation['file_line_path_anonymized_factor']);
    $xml->writeElement('metadata', metadata('book_id2na_url', $line_annotation['book_id']));
    $xml->writeElement('institution', $line_annotation['institution']);
    $xml->writeElement('collection', $line_annotation['collection']);
    $xml->writeElement('book_id', $line_annotation['book_id']);
    $xml->writeElement('shortname', $line_annotation['shortname']);
    list($kboek, $kpage, $kline) = parse_line_id($line_annotation['line_id']);
    $xml->writeElement('page', $kpage);
    $xml->writeElement('line', $kline);
    $xml->writeElement('y1', parameter_value('-y1=', $line_annotation['line_id']));
    $xml->writeElement('y2', parameter_value('-y2=', $line_annotation['line_id']));
    $xml->writeElement('txt', $line_annotation['txt']);
    $xml->writeElement('annotated_line', $line_annotation['annotated_line']);
    $xml->writeElement('source', MONK_version);
    $xml->endElement();
  }
  $xml->endElement();
  $xml->startElement('page_annotations');
  foreach ($results['page_annotations'] as $page_annotation)
  {
    list($kboek, $kpage, $kline) = parse_line_id($page_annotation['page_id']);
    $xml->startElement('page_annotation');
    $xml->writeElement('page_id', $page_annotation['page_id']);
    $xml->writeElement('file_page_path', $page_annotation['file_page_path_anonymized']);
    $xml->writeElement('file_line_path', $page_annotation['file_line_path_anonymized']);
    $xml->writeElement('file_line_path_factor', $page_annotation['file_line_path_anonymized_factor']);
    $xml->writeElement('metadata', metadata('book_id2na_url', $page_annotation['book_id']));
    $xml->writeElement('institution', $page_annotation['institution']);
    $xml->writeElement('collection', $page_annotation['collection']);
    $xml->writeElement('book_id', $page_annotation['book_id']);
    $xml->writeElement('shortname', $page_annotation['shortname']);
    $xml->writeElement('page', $kpage);
    $xml->writeElement('txt', $page_annotation['txt']);
    $xml->writeElement('annotated_page', $page_annotation['annotated_page']);
    $xml->writeElement('source', MONK_version);
    $xml->endElement();
  }
  $xml->endElement();
  $xml->endElement();
  header('X-Compression: gzip');
  header('Content-Encoding: gzip');
  header('Content-type: text/xml');
  exit(gzencode($xml->outputMemory(true)));
}

class parameters
{

  function offset()
  {
    $offset = isset($_GET['offset']) && ctype_digit($_GET['offset']) ? $_GET['offset'] : 0;
    return $offset;
  }

  function rows()
  {
    $max_rows = 100;
    $rows = isset($_GET['rows']) && ctype_digit($_GET['rows']) && $_GET['rows'] <= $max_rows ? $_GET['rows'] : $max_rows;
    return $rows;
  }

  function match()
  {
    $match = isset($_GET['match']) && in_array(strtolower($_GET['match']), array('prefix', 'suffix', 'exact', 'wildcard')) ? strtolower($_GET['match']) : 'prefix';
    return $match;
  }

  function wordzone_types()
  {
    global $wordzone_types_global;
    if (isset($_GET['wordzonetypes']))
    {
      $inputs = explode('|', $_GET['wordzonetypes']);
      $outputs = array();
      foreach ($wordzone_types_global as $wordzone_type)
      {
        if (in_array($wordzone_type, $inputs))
          $outputs[] = $wordzone_type;
      }
      if (count($outputs))
        return $outputs;
    }
    return array('HUMAN', 'JAVA');
  }

  function annotations()
  {
    global $annotations_global;
    $annotations = array();
    if (isset($_GET['annotations']) && strlen($_GET['annotations']))
    {
      $annotations_user = explode('|', strtolower($_GET['annotations']));
      foreach ($annotations_global as $k => $v)
        if (in_array($k, $annotations_user))
          $annotations[$k] = $v;
    }
    else
      $annotations = $annotations_global;
    if (count($annotations) == 0)
      $annotations = $annotations_global;
    return $annotations;
  }

}

function books_selection()
{
  // Get user input.
  $institutions_user = array();
  $collections_user = array();
  $books_user = array();
  if (isset($_GET['institutions']) && strlen($_GET['institutions']))
    $institutions_user = explode('|', $_GET['institutions']);
  if (isset($_GET['collections']) && strlen($_GET['collections']))
    $collections_user = explode('|', $_GET['collections']);
  if (isset($_GET['books']) && strlen($_GET['books']))
    $books_user = explode('|', $_GET['books']);

  // Make books_hierarchical_all & books_hierarchical_user.
  $books_all = books();
  $books_hierarchical_all = array();
  $books_hierarchical_user = array();
  foreach ($books_all as $book_all)
  {
    $navis_book_id = str_replace('_%04d-line-%03d', '', bookdir2linepattern($book_all['bookdir']));
    $books_hierarchical_all[$book_all['institution']][$book_all['collection']][$book_all['book_id']] = $navis_book_id;
    if (in_array($book_all['book_id'], $books_user) || in_array($book_all['shortname'], $books_user))
      $books_hierarchical_user[$book_all['institution']][$book_all['collection']][$book_all['book_id']] = $navis_book_id;
    if (in_array($book_all['collection'], $collections_user))
      $books_hierarchical_user[$book_all['institution']][$book_all['collection']] = array();
    if (in_array($book_all['institution'], $institutions_user))
      $books_hierarchical_user[$book_all['institution']] = array();
  }

  // Promote full selected groups in hierarchy.
  foreach ($books_hierarchical_user as $institution => $sub_collections)
    foreach ($sub_collections as $collection => $sub_book_ids)
      if (!count(array_diff_assoc($books_hierarchical_all[$institution][$collection], $sub_book_ids)))
        $books_hierarchical_user[$institution][$collection] = array();
  $sub_array_nocount = create_function('$as', 'foreach ($as as $a) if (count($a)) return 0; return 1;');
  foreach ($books_hierarchical_user as $institution => $sub_collections)
    if (!count(array_diff_assoc($books_hierarchical_all[$institution], $sub_collections)) && $sub_array_nocount($sub_collections))
      $books_hierarchical_user[$institution] = array();

  // Make groups and selection based on user input.
  $bs['institutions_filtered'] = array();
  $bs['collections_filtered'] = array();
  $bs['books_ids_filtered'] = array();
  $bs['selections_filtered'] = array();
  foreach ($books_hierarchical_user as $k1 => $v1)
  {
    if (!count($v1))
    {
      $bs['selections_filtered'][] = array('i' => $k1, 'c' => '*', 'b' => '*');
      $bs['institutions_filtered'][] = $k1;
    }
    foreach ($v1 as $k2 => $v2)
    {
      if (!count($v2))
      {
        $bs['selections_filtered'][] = array('i' => $k1, 'c' => $k2, 'b' => '*');
        $bs['collections_filtered'][] = $k2;
      }
      foreach ($v2 as $k3 => $v3)
      {
        $bs['selections_filtered'][] = array('i' => $k1, 'c' => $k2, 'b' => $v3);
        $bs['books_ids_filtered'][] = $k3;
      }
    }
  }

  // Handle full selection.
  if (!count(array_diff_assoc($books_hierarchical_all, $books_hierarchical_user)) && $sub_array_nocount($sub_collections))
    $bs['selections_filtered'] = array(array('i' => '*', 'c' => '*', 'b' => '*'));

  return $bs;
}

function books()
{
  $collections = collections();
  foreach (institutions() as $bookdir => $institution)
  {
    $linepattern = bookdir2linepattern($bookdir);
    $line_id = sprintf($linepattern, 0000, 000);
    if ($bookdir == 'navis')
      $shortname = 'KdK 1903, bladz. 1299-1346';
    else if ($bookdir == 'navis2')
      $shortname = 'KdK 1903, bladz. 1348-1401';
    else
      $shortname = shortname($line_id);
    $books[] = array(
        'institution' => $institution,
        'collection' => $collections[$bookdir],
        'bookdir' => $bookdir,
        'book_id' => str_replace('_%04d-line-%03d', '', substr($linepattern, strpos($linepattern, '-') + 1)),
        'shortname' => $shortname
    );
  }
  return $books;
}

function suggestions()
{
  global $searchstr;
  if (!strlen($searchstr))
    status_code(400, 'Search string empty.');
  $obj_parameters = new parameters;
  $shellcmd = '';
  $shellcmd .= 'cd ' . triesearch_dir . '; ';
  $shellcmd .= './trie-search7 ';
  $shellcmd .= '--trie=\'index-words.trie\' ';
  $shellcmd .= '--key=' . escapeshellarg($searchstr) . ' ';
  $shellcmd .= '--limit=' . $obj_parameters->rows() . ' ';
  exec($shellcmd, $lines);
  $suggestions = array();
  foreach ($lines as $line)
  {
    $lineparts = explode("\t", $line);
    $suggestions[] = @$lineparts[0];
  }
  $results = array(
      'suggestions' => $suggestions,
      'shellcmd' => $shellcmd
  );
  return $results;
}

function xml_suggestions($results)
{
  $xml = new xmlWriter();
  $xml->openMemory();
  $xml->setIndent(True);
  $xml->startDocument('1.0', 'UTF-8');
  $xml->startElement('searchresults');
  $xml->writeElement('temp_shellcmd_for_debug', $results['shellcmd']);
  $xml->startElement('searchresult');
  $xml->writeElement('suggestions', implode('|', $results['suggestions']));
  $xml->endElement();
  $xml->endElement();
  header('X-Compression: gzip');
  header('Content-Encoding: gzip');
  header('Content-type: text/xml');
  exit(gzencode($xml->outputMemory(true)));
}

function xml_books($books)
{
  $books_hierarchical_all = array();
  foreach ($books as $book)
    $books_hierarchical_all[$book['institution']][$book['collection']][$book['bookdir']] = array('book_id' => $book['book_id'], 'shortname' => $book['shortname']);
  $xml = new xmlWriter();
  $xml->openMemory();
  $xml->setIndent(True);
  $xml->startDocument('1.0', 'UTF-8');
  $xml->startElement('searchresults');
  $xml->writeElement('send', count($books));
  foreach ($books_hierarchical_all as $institution => $collections)
  {
    $xml->startElement('institution');
    $xml->writeElement('institution_id', $institution);
    $xml->writeElement('institution_hn', metadata('institution_id2hn', $institution, true));

    foreach ($collections as $collection => $bookdirs)
    {
      $xml->startElement('collection');
      $xml->writeElement('collection_id', $collection);
      $xml->writeElement('collection_hn', metadata('collection_id2hn', $collection, true));
      foreach ($bookdirs as $bookdir => $details)
      {
        $xml->startElement('book');
        $xml->writeElement('book_id', $details['book_id']);
        $xml->writeElement('book_name', $details['shortname']);
        $xml->writeElement('book_hn', metadata('book_id2hn', $details['book_id'], true));
        $xml->endElement();
      }
      $xml->endElement();
    }
    $xml->endElement();
  }
  $xml->endElement();
  header('X-Compression: gzip');
  header('Content-Encoding: gzip');
  header('Content-type: text/xml');
  exit(gzencode($xml->outputMemory(true)));
}

function parameter_value($parameter, $navis_id)
{
  $param_startpos = strpos($navis_id, $parameter) + strlen($parameter);
  $param_endpos = $param_startpos;
  while (ctype_digit(substr($navis_id, $param_endpos, 1)))
  {
    $param_endpos++;
  }
  $length = $param_endpos - $param_startpos;
  return substr($navis_id, $param_startpos, $length);
}

function get_string($string, $start, $end)
{
  $r = explode($start, $string);
  if (isset($r[1]))
    $r = explode($end, $r[1]);
  else
    $r[0] = '';
  return $r[0];
}

function restrict_params($params)
{
  foreach ($_GET as $key => $value)
  {
    if (!in_array($key, $params))
      status_code(400, 'Parameter: "' . $key . '" unknown.');
  }
}

function status_code($code = 400, $message = 'Bad Request')
{
  if ($code == 400)
  {
    header('HTTP/1.0 400 Bad Request');
    exit('<h1>400 Bad Request</h1>' . "\n" . $message);
  }
  if ($code == 501)
  {
    header('HTTP/1.0 501 Not implemented');
    exit('<h1>501 Not implemented</h1>' . "\n" . $message);
  }
}

?>