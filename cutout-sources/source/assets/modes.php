<?php	// Filename: assets/modes.php

// Development mode
$app->configureMode('development', function () use ($app) {

	// enable debugging
	$app->config(array(
		'debug' => true,
	));

	// disable logging
	$log = $app->getLog();
	$log->setEnabled(false);

});

// Production mode
$app->configureMode('production', function () use ($app) {

	// disable debugging
	$app->config(array(
		'debug' => false,
	));

	// enable logging
	$log = $app->getLog();
	$log->setEnabled(true);
	$log->setLevel(\Slim\Log::INFO);
	$log->setWriter(new \Slim\Extras\Log\DateTimeFileWriter(array(
		'path' => CFG_PATH_LOGS
	)));

});