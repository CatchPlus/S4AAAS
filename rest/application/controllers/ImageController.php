<?php

class ImageController extends S4AAAS_Controller_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function retrieveAction()
    {
		$userHasRight = $this->userHasRight();
		if (!$userHasRight)
		{
			$this->view->status = "AUTHENTICATION FAILURE";
			return;
		}

		$institutionId = $this->getRequest()->getParam('institution');
		$collectionId = $this->getRequest()->getParam('collection');
		$bookId = $this->getRequest()->getParam('book');
		$pageNo = $this->getRequest()->getParam('page');
		$lineNo = $this->getRequest()->getParam('line');
					
		if (!$institutionId || !$collectionId || !$bookId || !$pageNo)
		{
			$this->view->status = "NOT ENOUGH PARAMS SPECIFIED";
			return;
		}
		
		$institution = S4AAAS_Model_Institution::fetchByMonkId($institutionId);
		$collection = S4AAAS_Model_Collection::fetchByMonkId($collectionId);
		$book = S4AAAS_Model_Book::fetchByMonkDir($bookId);
		
		if(!$book)
		{
			$this->view->status = "BOOK NOT FOUND";
			return;
		}
		$page = $book->getPage($pageNo);

		if (!$page)
		{
			$this->view->status = "PAGE NOT FOUND";
			return;
		}
		if (!$collection )
		{
			$this->view->status = "COLLECTION NOT FOUND";
			return;
		}
		if (!$institution)
		{
			$this->view->status = "INSTITUTION NOT FOUND";
			return;
		}
		
		if ($lineNo)
		{
			$line = S4AAAS_Model_Line::getLineFromPage($page, $lineNo);
			if (!$line)
			{
				$this->view->status = "LINE NOT FOUND";
				return;
			}

			$image = $this->getLineImage($line);
			if ($image)
			{
				$this->view->status = "OK";
				header('Content-Type: image/jpeg');
				readfile($image);
			}
			else
			{
				$this->view->status = "IMAGE NOT AVAILABLE";
			}
			
		}
		else
		{
			$image = $this->getPageImage($page, $book);
			if ($image)
			{
				$this->view->status = "OK";
				header('Content-Type: image/jpeg');
				readfile($image);
			}
			else
			{
				$this->view->status = "IMAGE NOT AVAILABLE";
			}
		}
				
    }

    public function retrieveUnauthAction()
    {
		$imageId = $this->getRequest()->getParam('image-id');
		
		if (!$imageId || ($imageId == ''))
		{
			$this->view->status = "NO IMAGE ID GIVEN";
			return;
		}

		$image = $this->getImageFromId($imageId);
		if ($image)
		{
			$this->view->status = "OK";
			header('Content-Type: image/jpeg');
			readfile($image);
		}
		else
		{
			if (!isset($this->view->status) || ($this->view->status == ""))
			{
				$this->view->status = "UNABLE TO RETRIEVE IMAGE";
			}
		}
		


    }
	
	private function getLineImage($line)
	{
		// if needed, render the image and put in linestrips cache
		if (!$line->getImageRendered())
		{
			$this->renderLineImage($line);
			$line->setImageRendered(true);
			$line->update();
		}
		
		$imagePath = $this->getLineImagePath();
		$imagePath .= '/'.$line->getId();
		$imagePath .= '.jpg';
		if (!file_exists($imagePath))
		{
			return null;
		}
		return $imagePath;
	}
	
	private function getPageImage($page, $book= null)
	{
		if (!$book)
		{
			$book = $page->getBook();
		}
		
		// add leading zeroes to page no.
		$pageNo = $page->getPageNo();
		while (strlen($pageNo) < 4)
		{
			$pageNo = '0'.$pageNo;
		}
		
		$imagePath = $this->getPageImagePath();
		$imagePath .= '/'.$book->getBookDir();
		$imagePath .= '/'.$pageNo;
		$imagePath .= '.jpg';
		
		if (!file_exists($imagePath))
		{
			return null;
		}
		return $imagePath;
	}

	private function getImageFromId($imageId)
	{
		$imageLookup = S4AAAS_Model_ImageLookup::fetchByImageId($imageId);
		
		if (!$imageLookup)
		{
			$this->view->status = "IMAGE LOOKUP ENTRIE NOT FOUND FOR id=$imageId";
			return null;
		}
				
		$type = $imageLookup->getType();
		if ($type == 'PAGE')
		{
			$page = S4AAAS_Model_Page::fetchById($imageLookup->getObjectId());
			if (!$page)
			{
				$this->view->status = "PAGE FOR IMAGE NOT FOUND";
				return null;
			}
			return $this->getPageImage($page);
		}
		elseif ($type == 'LINE')
		{
			$line = S4AAAS_Model_Line::fetchById($imageLookup->getObjectId());
			if (!$line)
			{
				$this->view->status = "LINE FOR IMAGE NOT FOUND";
				return null;
			}
			return $this->getLineImage($line);
		}
		else
		{
			$this->view->status = "NO LINE OR PAGE IMAGE";
			return null;
		}
	}
	
	private function renderLineImage($line)
	{
		$page = $line->getPage();
		if (!$page)
		{
			throw new Exception("Page not found");
		}
		$origWidth = $page->getOrigWidth();
		$yTop = round((800/$origWidth)*$line->getYTop());
		$yBot = round((800/$origWidth)*$line->getYBot());
		$pageImage = $this->getPageImage($page);
		if (!$pageImage)
		{
			return false;
		}
		$destPath = $this->getLineImagePath();
		$destPath .= '/'.$line->getId();
		$destPath .= '.jpg';
		$imageCutoutPath = $this->getImageCutoutPath();
		$command = $imageCutoutPath.' '.$pageImage.':'.$yTop.':'.$yBot.':'.$destPath;
		
//		echo "Command: ".$command;

		$return_var = 0;
		$output = array();
		exec($command, $output, $return_var);
		
//		echo "returned: $return_var";
		
		return true;
	}
	
	public function rendertestAction()
	{
		$line = S4AAAS_Model_Line::fetchById(1);
		$token = $line->createImageToken();
		$imageId = $token->getImageId();
		$image = $this->getImageFromId($imageId);
		header('Content-Type: image/jpeg');
		readfile($image);
	}

}



