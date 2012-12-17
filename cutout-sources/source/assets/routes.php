<?php	// Filename: assets/routes.php

// Main route
$app->get('/', function () use ($app)
{
	$app->render('main.html', array(
		'debug' => $app->config('debug'),
		'url' => CFG_APP_URL,
		'nonce' => $app->config('debug') ? 'q='.time() : '',
		'locale' => CFG_APP_LOCALE,
		'title' => I18N_TITLE,
	));
});

// Upload route
$app->post('/upload/', function () use ($app)
{
	$env = $app->environment();
	$log = $app->getLog();

	try
	{
		// request cutout handle
		$rest_response = \Httpful\Request::get(CFG_REST_CLIENT.'/init_cutout/'.$_SERVER['REMOTE_ADDR'])
			->expectsXml()
			->send();

		// verify response
		if ($rest_response->body->status == 'OK')
		{
			$_SESSION['cutout_handle'] = (string) $rest_response->body->cutout_handle;
		}
		else
		{
			echo json_encode(array('success' => false));
			return;
		}
	}
	catch (Exception $e)
	{
		// log error
		$log->error('file: '.__FILE__
				.', line: '.__LINE__
				.', message: '.$e->getMessage()
		);
		echo json_encode(array('success' => false));
		return;
	}

	if (isset($_SESSION['cutout_handle']))
	{
		try
		{
			$source = $_FILES['filename']['tmp_name'];
			$target = CFG_PATH_DATA.'/'.$_SESSION['cutout_handle'].'/'.$_FILES['filename']['name'];

			// create a temporary storage
			@mkdir(CFG_PATH_DATA.'/'.$_SESSION['cutout_handle']);
			move_uploaded_file($source, $target);

			// upload scan
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, CFG_REST_CLIENT.'/upload_scan/'.$_SESSION['cutout_handle']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array(
				'file' => '@'.$target,
			));

			$curl_response = new SimpleXMLElement(curl_exec($ch));
			curl_close($ch);

			// remove uploaded file
			@unlink($target);

			// verify response
			if ($curl_response->status == 'OK')
			{
				echo json_encode(array('success' => true));
				return;
			}
			elseif ($curl_response->status)
			{
				echo json_encode(array('success' => false, 'status' => (string) $curl_response->status));
				return;
			}
		}
		catch (Exception $e)
		{
			// log error
			$log->error('file: '.__FILE__
					.', line: '.__LINE__
					.', message: '.$e->getMessage()
			);
			echo json_encode(array('success' => false));
			return;
		}
	}

});

// Rotate route
$app->get('/rotate/:angle', $mw_validateCutoutHandle(), function ($angle) use ($app)
{
	// $filename = CFG_PATH_DATA.'/'.$_SESSION['cutout_handle'].'/lowres.jpg';

	if (!is_int(intval($angle)))
	{
		echo json_encode(array('success' => false));
		return;
	}

	try
	{
		// send rotate image request
		$rest_response = \Httpful\Request::get(CFG_REST_CLIENT.'/render_scan/'.$_SESSION['cutout_handle'].'/'.$angle)
			->send();

		// verify response
		if ($rest_response)
		{
			$res = $app->response();
			$res['Content-Type'] = 'image/jpeg';

			echo $rest_response->body;
			return;
		}
		else
		{
			echo json_encode(array('success' => false));
			return;
		}
	}
	catch (Exception $e)
	{
		// log error
		$log->error('file: '.__FILE__
			.', line: '.__LINE__
			.', message: '.$e->getMessage()
		);
		echo json_encode(array('success' => false));
		return;
	}
});

// Retrieve model
$app->get('/model/', $mw_validateCutoutHandle(), function () use ($app)
{
	echo json_encode($_SESSION['model']);
});

// Store model
$app->post('/model/', $mw_validateCutoutHandle(), function () use ($app)
{
	$_SESSION['model'] = $_POST['model'];
});

// Start cutout process route
$app->get('/cutout/', $mw_validateCutoutHandle(), function () use ($app)
{
	$env = $app->environment();
	$log = $app->getLog();

	$model = $_SESSION['model'];
	$factor = $model['size']['real']['width'] / $model['size']['screen']['width'];
	
	$angle = $model['angle'];
	$x1 = floor($factor * $model['cropArea']['x1']);
	$y1 = floor($factor * $model['cropArea']['y1']);
	$x2 = floor($factor * $model['cropArea']['x2']);
	$y2 = floor($factor * $model['cropArea']['y2']);

	try
	{
		// send request
		$url = sprintf(CFG_REST_CLIENT.'/start_process_cutout/'.$_SESSION['cutout_handle'].'/%s/%s,%s/%s,%s',
			$angle, $x1, $y1, $x2, $y2
		);
		$rest_response = \Httpful\Request::get($url)
			->expectsXml()
			->send();

		// verify response
		if ($rest_response->body->status == 'OK')
		{
			echo json_encode(array('success' => true));
			return;
		}
		else
		{
			echo json_encode(array('success' => false));
			return;
		}
	}
	catch (Exception $e)
	{
		// log error
		$log->error('file: '.__FILE__
				.', line: '.__LINE__
				.', message: '.$e->getMessage()
		);
		echo json_encode(array('success' => false));
		return;
	}
});

// Check process cutout route
$app->get('/check/', $mw_validateCutoutHandle(), function () use ($app) {

	$env = $app->environment();
	$log = $app->getLog();
	$time = time();

	while ((time() - $time) < 30)
	{
		try
		{
			// send request
			$rest_response = \Httpful\Request::get(CFG_REST_CLIENT.'/check_process_cutout/'.$_SESSION['cutout_handle'])
				->expectsXml()
				->send();

			// verify response
			if ($rest_response->body->status == 'DONE')
			{
				echo json_encode(array('success' => true, 'xml' => $rest_response->body));
				return;
			}
			elseif ($rest_response->body->status == 'BUSY')
			{
				continue;
			}
			else
			{
				echo json_encode(array('success' => false));
				return;
			}
		}
		catch (Exception $e)
		{
			// log error
			$log->error('file: '.__FILE__
					.', line: '.__LINE__
					.', message: '.$e->getMessage()
			);
			echo json_encode(array('success' => false));
			return;
		}

		usleep(25000);
	}

});

// Get cutout route
$app->get('/render/', $mw_validateCutoutHandle(), function () use ($app)
{
	$env = $app->environment();
	$log = $app->getLog();

	try
	{
		// send process cutout request
		$rest_response = \Httpful\Request::get(CFG_REST_CLIENT.'/process_cutout/'.$_SESSION['cutout_handle'])
			->send();

		// verify response
		if ($rest_response)
		{
			$res = $app->response();
			$res['Content-Type'] = 'image/jpeg';

			echo $rest_response->body;
			return;
		}
		else
		{
			echo json_encode(array('success' => false));
			return;
		}
	}
	catch (Exception $e)
	{
		// log error
		$log->error('file: '.__FILE__
			.', line: '.__LINE__
			.', message: '.$e->getMessage()
		);
		echo json_encode(array('success' => false));
		return;
	}
});

// Download ZIP route
$app->get('/zip/', $mw_validateCutoutHandle(), function () use ($app)
{
	$env = $app->environment();
	$log = $app->getLog();

	try
	{
		// send process cutout request
		$rest_response = \Httpful\Request::get(CFG_REST_CLIENT.'/retrieve_cutout/'.$_SESSION['cutout_handle'].'?linestrip=y&format=png')
			->send();

		// verify response
		if ($rest_response)
		{
			$filename = $_SESSION['cutout_handle'].'.zip';

			$res = $app->response();
			$res['Content-Type'] = 'application/zip';
			$res['Content-Disposition'] = 'attachment; filename="'.$filename.'"';

			echo $rest_response->body;
			return;
		}
		else
		{
			echo json_encode(array('success' => false));
			return;
		}
	}
	catch (Exception $e)
	{
		// log error
		$log->error('file: '.__FILE__
			.', line: '.__LINE__
			.', message: '.$e->getMessage()
		);
		echo json_encode(array('success' => false));
		return;
	}
});

// Download RDF route
$app->get('/rdf/', $mw_validateCutoutHandle(), function () use ($app)
{
	$env = $app->environment();
	$log = $app->getLog();

	try
	{
		// send process cutout request
		$rest_response = \Httpful\Request::get(CFG_REST_CLIENT.'/generate_rdf/'.$_SESSION['cutout_handle'])
			->send();

		// verify response
		if ($rest_response)
		{
			$filename = $_SESSION['cutout_handle'].'.xml';

			$res = $app->response();
			$res['Content-Type'] = 'text/xml';
			$res['Content-Disposition'] = 'attachment; filename="'.$filename.'"';

			echo $rest_response->body;
			return;
		}
		else
		{
			echo json_encode(array('success' => false));
			return;
		}
	}
	catch (Exception $e)
	{
		// log error
		$log->error('file: '.__FILE__
			.', line: '.__LINE__
			.', message: '.$e->getMessage()
		);
		echo json_encode(array('success' => false));
		return;
	}
});

