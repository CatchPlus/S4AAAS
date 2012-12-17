<?php	// Filename: assets/views.php

\Slim\Extras\Views\Twig::$twigDirectory = CFG_PATH_TWIG;
\Slim\Extras\Views\Twig::$twigOptions = array(
	'debug' => $app->config('debug'),
	'charset' => 'utf-8',
	'cache' => realpath(CFG_PATH_CACHE),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true,
);
\Slim\Extras\Views\Twig::$twigExtensions = array(
	'Twig_Extension_Debug',
);

$twig = new \Slim\Extras\Views\Twig();
$app->view($twig);