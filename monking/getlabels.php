<?php
// HEY RIK! DE SELECT-QUERIES HEB IK NOG NIET GETEST :) DAT JE HET WEET!

/** Handles a Institution, in the sense that it is inserted in the database if it did not already exist and returns an database based id */
function handleInstitution($monkId) {
	$select_query = 'SELECT * FROM INSTITUTIONS WHERE MONK_ID=\'' . $MonkId . '\'';
}

/** Handles a Collection, in the sense that it is inserted in the database if it did not already exist and returns an database based id */
function handleCollection($monkId) {
	$select_query = 'SELECT * FROM COLLECTIONS WHERE MONK_ID=\'' . $MonkId . '\'';

}

/** Handles a book, in the sense that it is inserted in the database if it did not already exist and returns an database based id */
function handleBook($monkId) {
	$select_query = 'SELECT * FROM BOOKS WHERE MONK_ID=\'' . $MonkId . '\'';

}

/** Check if a page is already in the database */
function pageInDatabase($db, $pageNavisId)
{
	$query = "SELECT * FROM PAGES WHERE NAVIS_ID = $pageNavisId";

	$result = $db->query($query);
	if (count($result) > 0)
	{
		return true;
	}

	return false;
}

/** Check if a line is already in the database */
function lineInDatabase($db, $pageId, $lineNo)
{
	$query = "SELECT * FROM PAGES WHERE PAGE_ID = $pageId AND LINE_NO = $lineNo";

	$result = $db->query($query);
	if (count($result) > 0)
	{
		return true;
	}

	return false;
}


function testFunction($db)
{
	$query = "SELECT * FROM PAGES WHERE BOOK_ID = 1 LIMIT 10";

	$result = $db->query($query);

	foreach($result as $row)
	{
		print_r($row);
	}
}



$db = new PDO('mysql:host=127.0.0.1;dbname=s4aaas', 's4aaas', '49PH9HhHd6buvjSE'); 



$filename = "index.lis";
$iterations = 10;
$institution_monk_id_to_uuid = array();
$collection_monk_id_to_uuid = array();
$book_monk_id_to_uuid = array();
$page = null;
$lastPageNavisId = '';

$linesAdded = 0; //number of lines added to db
$pagesAdded = 0; //number of pages added to db

$fp = fopen($filename, 'r');
$count = 0;
while ($line = fgetcsv($fp, 0, "\t")) {
	$firstPartSplitted = explode(' ', $line[0]);

	$FULL_NAME = $firstPartSplitted[0]; // full name (including page, line etc.)
	$NAVIS_ID = $firstPartSplitted[1]; // e.g. navis-NL-blalbla
	$PAGE = $line[1]; // page in the book
	$LINE = $line[2]; // line of the page
	$Y_TOP = $line[3];
	$Y_BOT = $line[4];
	$pageNavisId = $NAVIS_ID.'_'.$PAGE;

	// insert page into db if not already inserted
	if (!($lastPageNavisId == $pageNavisId) && !pageInDatabase($db, $pageNavisId))
	{
		$bookMonkId = substr($pageNavisId, 0, -5);
		$query = "SELECT * FROM BOOKS WHERE NAVIS_ID = $bookMonkId";
		$result = $db->query($query);
		if (count($result) > 0)
		{
			$book = $result[0];
			$bookId = $book['ID'];
			$page = array(
				'BOOK_ID' => $book['ID'],
				'NAVIS_ID' => $pageNavisId,
				'PAGE_NO' => $PAGE,
				'ORIG_WIDTH' => 0,
				'TRANSCRIPT' => ''
			);

			$sql = " 
				INSERT INTO PAGES (BOOK_ID, NAVIS_ID, PAGE_NO, ORIG_WIDTH, TRANSCRIPT) 
				VALUES ('$bookId', '$pageNavisId', '$PAGE', 0, '') 
				"; 

			$results = $db->exec($sql); 
			$page['ID'] = $db->lastInsertId();

			$pagesAdded++;
			$lastPageNavisId = $pageNavisId;
		}
		else
		{
			throw new Exception("Book not found (navis_id = $bookMonkId)");
		}		
	}


	if (!lineInDatabase($db, $page['ID'], $LINE))
	{

		// insert line into db

		$pageId = $page['ID'];

		$data = array(
			'PAGE_ID' => $pageId,
			'LINE_NO' => $LINE,
			'Y_TOP' => $Y_TOP,
			'Y_BOT' => $Y_BOT,
			'TRANSCRIPT' => ''
		);

		$sql = " 
			INSERT INTO LINES (PAGE_ID, LINE_NO, Y_TOP, Y_BOT, TRANSCRIPT) 
			VALUES ('$pageId', '$LINE', '$Y_TOP', '$Y_BOT', '') 
			"; 

		$results = $db->exec($sql); 

		$linesAdded++;
	}

	$count += 1;
	if ($count >= $iterations) break;
}

fclose($fp);

echo "$pagesAdded pages added.\n$linesAdded lines added.";

?>