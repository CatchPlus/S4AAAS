<?php

class CutoutController extends S4AAAS_Controller_Abstract
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function initiateAction()
    {
		$this->view->status = "UNKNOWN ERROR";
        $ipAddress = $this->getRequest()->getParam('ip-address');
		
		//generate token
		$tokenHandle = new S4AAAS_Model_CutoutToken();
		$tokenHandle->setIpaddress($ipAddress);
		$tokenHandle->insert();
		
		//create directory
		$cutoutDataPath = Zend_Registry::get('cutoutDataPath');
		$token = $tokenHandle->getToken();
		if (!mkdir($cutoutDataPath.'/'.$token))
		{
			$this->status = "ERROR UNABLE TO CREATE DIRECTORY FOR GIVEN CUTOUT HANDLE";
			return;
		}
		
		// setup the view
		$this->view->cutoutHandle = $tokenHandle;
		$this->view->status = "OK";
		
    }

    public function uploadscanAction()
    {
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NO CUTOUT HANDLE GIVEN";
			return;
		}
		
		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLE NOT FOUND IN DB";
			return;
		}
		
		
		if ($this->_request->isPost())
		{
			if (!file_exists(Zend_Registry::get('cutoutDataPath').'/'.$token))
			{
				mkdir(Zend_Registry::get('cutoutDataPath').'/'.$token);
			}

			$adapter = new Zend_File_Transfer_Adapter_Http();
			$fileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'original';
			$adapter->addFilter('Rename', array('target' => $fileName, 'overwrite'=> true));

			if (!$adapter->receive()) {
				$messages = $adapter->getMessages();
				echo implode("\n", $messages);
				$this->view->status = "ERROR COULD NOT RECEIVE IMAGE FROM ADAPTER";
				return;
			}
			
			//get the file extension
			$extension = $this->getExtension($fileName);
			
			if (!$extension)
			{
				$this->view->status = "FILE IS NOT AN IMAGE";
				return;
			}
			
			
			$this->view->format = $extension;
			
			// create 800px-width presentation image
			$presentationFileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/presentation.jpg';
			shell_exec('convert '.$fileName.' -resize 800 '.$presentationFileName);
	    }
		else
		{
			$this->view->status = "ERROR NO POST DATA";
			return;
		}
		
		$this->view->status = "OK";
	}
	
    public function renderscanAction()
    {
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NOT CUTOUT HANDLE GIVEN";
			return;
		}
		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLE NOT FOUND IN DB";
			return;
		}
		
		$angle = $this->getRequest()->getParam('angle');
		if (!isset($angle) || $angle === false)
		{
			$this->view->status = "ERROR NO ANGLE GIVEN";
			return;
		}
		
		// rotate the image
		$fileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'presentation.jpg';

		$rotatedFileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'rotated.jpg';
		shell_exec('convert '.$fileName.' -rotate "'.$angle.'" '.$rotatedFileName);
		
		// return the image
		header('Content-Type: image/jpeg');
		readfile($rotatedFileName);
    }

    public function startprocessAction()
    {
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NO CUTOUT HANDLE GIVEN";
			return;
		}
		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLEN NOT FOUND IN DB";
			return;
		}
		
		$angle = $this->getRequest()->getParam('angle');
		if (!isset($angle) || $angle === false)
		{
			$this->view->status = "ERROR NO ANGLE GIVEN";
			return;
		}
		$pos1 = $this->getRequest()->getParam('pos1');
		if (!$pos1)
		{
			$this->view->status = "ERROR POSITION 1 NOT GIVEN";
			return;
		}
		$pos2 = $this->getRequest()->getParam('pos2');
		if (!$pos2)
		{
			$this->view->status = "ERROR POSITION 2 NOT GIVEN";
			return;
		}
		
		$pos1Parts = explode(',', $pos1);
		$pos2Parts = explode(',', $pos2);
		if (!isset($pos1Parts[0]) || !isset($pos1Parts[1]) || !isset($pos2Parts[0]) || !isset($pos2Parts[1]))
		{
			$this->view->status = "ERROR POSITIONS HAVE WRONG FORMAT?";
			return;
		}
		
		// opdate the cutouttoken entry
		$tokenHandler->setAngle($angle);
		$tokenHandler->setX1($pos1Parts[0]);
		$tokenHandler->setY1($pos1Parts[1]);
		$tokenHandler->setX2($pos2Parts[0]);
		$tokenHandler->setY2($pos2Parts[1]);
		$tokenHandler->setStatus('BUSY');
		$tokenHandler->update();
		
		$x = $pos1Parts[0];
		$y = $pos1Parts[1];
		$width = $pos2Parts[0] - $pos1Parts[0];
		$height = $pos2Parts[1] - $pos1Parts[1];
		
		// start cutout process
		$fileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'original';
		$originalRotatedFileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'original_rotated.jpg';
		$linecutsPath = Zend_Registry::get('cutoutDataPath').'/'.$token;
		$croppedFileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'cutout.jpg';
		
		//get width of original rotated		
		shell_exec('convert  -rotate "'.$angle.'"'." $fileName $originalRotatedFileName");
		$result = shell_exec('identify '.$fileName);
		$parts = explode(' ', $result);
		$dimensions = explode('x', $parts[2]);
		$originalWidth = $dimensions[0];
		$tokenHandler->setOrigWidth($originalWidth);
		$ratio = $originalWidth/800;
		$origX = $x*$ratio;
		$origY = $y*$ratio;
		$origW = $width*$ratio;
		$origH = $height*$ratio;
		
		// make cutout
		shell_exec("convert $originalRotatedFileName -crop $origW"."x"."$origH+$origX+$origY +repage +adjoin $croppedFileName");
		
		// delete old linestrips images
		$fileNames = scandir($linecutsPath);
		foreach ($fileNames as $fileName)
		{
			if (strrpos($fileName, 'linestrip_') === 0)
			{
				unlink($linecutsPath.'/'.$fileName);
			}
		}
		
		
		if (!$this->getMonkLines($croppedFileName, $linecutsPath, $token))
		{
			$this->view->status = "ERROR NO MONK LINES EXTRACTED";
			return;
		}
		
		
		
		// update token status
		$tokenHandler->setStatus('DONE');
		$tokenHandler->update();
		
		//get linestrips
		$fileNames = array();
		if (file_exists($linecutsPath."/Lines/cutout/"))
		{
			$fileNames = scandir($linecutsPath."/Lines/cutout/");
			$yPos = 1;
		}
		// clear linestrip table for this token
		$tokenHandler->clearLineStrips();
		
		// put linestrip into database
		foreach ($fileNames as $fileName)
		{
			if (strrpos($fileName, 'cutout-line-') === 0)
			{
				$coordsString = substr(substr($fileName, 16), 0, -4);
				$coordsStringParts = explode('-', $coordsString);
				$y1Parts = explode('=', $coordsStringParts[0]);
				$y2Parts = explode('=', $coordsStringParts[1]);
				
				$linestrip = new S4AAAS_Model_CutoutLine();
				$linestrip->setY1($y1Parts[1]);
				$linestrip->setY2($y2Parts[1]);
				$linestrip->setCutouttokenId($tokenHandler->getId());
				$lineId = substr($fileName, 12, 3);
				$linestrip->setLineId($lineId);
				$linestrip->insert();
			}
		}

		$this->view->status = "OK";
    }
	
	private function getMonkLines($fileName, $linecutsPath, $token)
	{
		$zipFileName = $linecutsPath.'/'.'cutout_'.$token.'_tmp.zip';
		$monkUrl = 'http://www.ai.rug.nl/~jpoosten/cgi-bin/brieven.py';
		$command = "curl --form file=@$fileName $monkUrl -o $zipFileName";// --silent";
		shell_exec($command);
		$command = "unzip $zipFileName -d $linecutsPath";
		shell_exec($command);
//		echo $command;
//		$output = array();
//		$status = 0;
//		$result = exec($command, $output, $status);
//		print_r($output);
//		echo $status;
//		echo $result;
		
		
		$zip = zip_open($zipFileName);
		if (is_resource($zip))
		{
			zip_close($zip);
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	private function getDimensions($fileName)
	{
		$result = shell_exec('identify '.$fileName);
		$parts = explode(' ', $result);
		return explode('x', $parts[2]);
	}
	
	private function getExtension($fileName)
	{
		$result = shell_exec('identify '.$fileName);
		if (!$result)
		{
			return null;
		}
		$parts = explode(' ', $result);
		return $parts[1];
	}

    public function checkprocessAction()
    {
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NO CUTOUT HANDLE GIVEN";
			return;
		}

		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLE NOT FOUND IN DB";
			return;
		}

		$this->checkStatus($this->view, $tokenHandler);
    }

    public function renderAction()
    {
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NOT CUTOUT HANDLE GIVEN";
			return;
		}
		
		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLE NOT FOUND IN DB";
			return;
		}
		
        $lineId = $this->getRequest()->getParam('strip_id');
		if (!$lineId)
		{
			$fileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'cutout.jpg';
			$lowresFileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'cutout_small.jpg';
//			if (!file_exists($lowresFileName))
//			{
				shell_exec("convert $fileName -resize 800 $lowresFileName");
//			}
			
			// return the image
			header('Content-Type: image/jpeg');
			readfile($lowresFileName);
		}
		else
		{
			$line = S4AAAS_Model_CutoutLine::fetchByTokenIdAndLineId($tokenHandler->getId(), $lineId);
			if (!$line)
			{
				$this->view->status = "LINE NOT FOUND";
				return;
			}
			$lineId = $this->addZeroes($lineId);
			$fileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'linestrip_'.$lineId;
			$lowresFileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'linestrip_'.$lineId.'_small.jpg';
			if (!file_exists($lowresFileName))
			{
				shell_exec("convert $fileName -resize 800 $lowresFileName");
			}

			// return the image
			header('Content-Type: image/jpeg');
			readfile($lowresFileName);
		}
		$this->view->status = "OK";
    }
	
	public function origimageAction()
	{
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NO CUTOUT HANDLE GIVEN";
			return;
		}
		
		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLE NOT FOUND IN DB";
			return;
		}
		$fileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'presentation.jpg';

		// return the image
		header('Content-Type: image/jpeg');
		readfile($fileName);
	}
	
	private function addZeroes($lineId)
	{
		while (strlen($lineId) < 3)
		{
			$lineId = '0'.$lineId;
		}
		
		return $lineId;
	}
	
	public function retrieveAction()
    {
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NO CUTOUT HANDLE GIVEN";
			return;
		}
		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLE NOT FOUND IN DB";
			return;
		}
		
		$returnLineStrips = false;
        $linestripYN = $this->getRequest()->getParam('linestrip');
		if (strtolower($linestripYN) == 'y')
		{
			$returnLineStrips = true;
		}
			
		$linecutsPath = Zend_Registry::get('cutoutDataPath').'/'.$token;
		
		$zipFileName = $linecutsPath.'/'.'cutout_'.$token.'.zip';
		$zip = new ZipArchive();
		if($zip->open($zipFileName, ZIPARCHIVE::OVERWRITE) !== true)
		{
			return false;
		}

		if (!file_exists($linecutsPath.'/original'))
		{
			$this->view->status = "ORIGINAL NOT FOUND";
			return;
		}
		
        $extension = $this->getRequest()->getParam('format');		

		
		if (!$extension || ($extension == ""))
		{
			$extension = $this->getExtension($linecutsPath.'/original');
//			$extension = 'jpg';
		}
		
//		$extension = 'jpg';

		shell_exec("convert $linecutsPath/original $linecutsPath/original.$extension");
		if (file_exists("$linecutsPath/original.$extension"))
		{
			$zip->addFile($linecutsPath.'/original.'.$extension, 'original.'.$extension);
		}
		
		if (file_exists($linecutsPath.'/cutout.jpg'))
		{

			shell_exec("convert $linecutsPath/cutout.jpg $linecutsPath/cutout.$extension");
			if (file_exists($linecutsPath.'/cutout.'.$extension))
			{
				$zip->addFile($linecutsPath.'/cutout.'.$extension, 'cutout.'.$extension);
			}
		}

		if ($returnLineStrips)
		{
			$fileNames = scandir($linecutsPath. '/Lines/cutout/');
			foreach ($fileNames as $fileName)
			{
				if (strrpos($fileName, 'cutout-line-') === 0)
				{
					$command = "convert $linecutsPath/Lines/cutout/$fileName $linecutsPath/$fileName.$extension";
					shell_exec($command);
					if (file_exists($linecutsPath.'/'.$fileName.'.'.$extension))
					{
						$zip->addFile($linecutsPath.'/'.$fileName.'.'.$extension, $fileName.'.'.$extension);
					}
				}
			}
		}

		//close the zip
		$zip->close();
		
		if (!file_exists($zipFileName))
		{
			$this->view->status = "ERROR ZIP FILE NOT FOUND";
			return;
		}
		
		$this->view->status = "OK";
		
		while(ob_get_level())
		{
			ob_end_clean();
		}

		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=cutout_$token.zip");
		header("Content-Length: " . filesize($zipFileName));

		readfile($zipFileName);
    }
	
	public function generaterdfAction()
	{
        $token = $this->getRequest()->getParam('cutout_handle');
		if (!$token)
		{
			$this->view->status = "ERROR NO CUTOUT HANDLE GIVEN";
			return;
		}
		$tokenHandler = S4AAAS_Model_CutoutToken::fetchByToken($token);
		if (!$tokenHandler)
		{
			$this->view->status = "ERROR CUTOUT HANDLE NOT FOUND IN DB";
			return;
		}

		$view = new Zend_View();
		$view->setScriptPath(APPLICATION_PATH . '/views/scripts/cutout/');
		$result = $this->checkStatus($view, $tokenHandler, false);
		
		$html = $view->render('checkprocess.phtml');
		
		$fileName = Zend_Registry::get('cutoutDataPath').'/'.$token.'/'.'checkprocess.xml';
		file_put_contents($fileName , $html);
		
		$this->view->status = $result;

		$this->view->rdf = $this->getRdfFromXml($fileName);

	}
	
	private function getRdfFromXml($xmlFileName)
	{
		// read the rdf data using Hennie's program
		$rdfProgramPath = Zend_Registry::get('cutoutRdfPath');
//		$result = -1;
//		$output = array();
		$command = "java -jar $rdfProgramPath/Navis2OpenAnnotation.jar --inputfile=$xmlFileName --linestrips";
//		$rdf = exec($command, $output, $result);
		$rdf = shell_exec($command);
		return $rdf;
	}
	
	private function checkStatus($view, $tokenHandler, $scaled=true)
	{
		$status = $tokenHandler->getStatus();
		if ($status == "BUSY")
		{
			$view->status = "BUSY";
			return $status;
		}
		
		if ($status == "DONE")
		{
			$originalWidth = $tokenHandler->getOrigWidth();
			
			$ratio = $originalWidth/800;
			$this->view->scaled = $scaled;
			
			$fileName = Zend_Registry::get('cutoutDataPath').'/'.$tokenHandler->getToken().'/'.'original';
			$result = shell_exec('identify '.$fileName);
			$parts = explode(' ', $result);
			$dimensions = explode('x', $parts[2]);
			$view->width = $scaled ? ceil($dimensions[0]*$ratio) : $dimensions[0];
			$view->height = $scaled ? ceil($dimensions[1]*$ratio) : $dimensions[1];
			$view->angle = $tokenHandler->getAngle();
			$view->x1 = $scaled ? $tokenHandler->getX1() : ceil($tokenHandler->getX1()*$ratio);
			$view->x2 = $scaled ? $tokenHandler->getX2() : ceil($tokenHandler->getX2()*$ratio);
			$view->y1 = $scaled ? $tokenHandler->getY1() : ceil($tokenHandler->getY1()*$ratio);
			$view->y2 = $scaled ? $tokenHandler->getY2() : ceil($tokenHandler->getY2()*$ratio);
			$view->serverName = Zend_Registry::get('servername');
			$view->token = $tokenHandler->getToken();
			
			$lineStrips = S4AAAS_Model_CutoutLine::fetchFromToken($tokenHandler->getId());
			$view->lineStrips = $lineStrips;
			
			// calculate ratio for y1-y2 for linestrips
			if ($scaled)
			{	
				$fileName = Zend_Registry::get('cutoutDataPath').'/'.$tokenHandler->getToken().'/'.'cutout.jpg';
				$result = shell_exec('identify '.$fileName);
				$parts = explode(' ', $result);
				$dimensions = explode('x', $parts[2]);
				$this->view->ratio = $dimensions[0]/800;
			}
			
			$view->status = "DONE";
			return $status;
		}
		
		if ($status == "UNPROCESSED")
		{
			$view->status = "ERROR";
			$view->errorMessage = "PROCESS YET NOT STARTED";
			return $status;
		}
		
		return $status;
	}

}