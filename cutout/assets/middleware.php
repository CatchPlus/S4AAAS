<?php	// Filename: assets/middleware.php

/**
 * Middleware boilerplate

	$mw_myMiddleware = function (\Slim\Route $route)
	{
		return function ()
		{
			$app = \Slim\Slim::getInstance();
		};
	};

 */

// Validate cutout handle
$mw_validateCutoutHandle = function ()
{
	return function ()
	{
		if (!isset($_SESSION['cutout_handle']))
		{
			$app = \Slim\Slim::getInstance();
			$app->flash('error', ERR_VALIDATE_CUTOUT_HANDLE);
			$app->redirect('../');
		}
	};
};