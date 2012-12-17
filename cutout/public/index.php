<?php

// Load configuration
require '../config/config.php';

// Start session
session_name(CFG_SESSION_NAME);
session_set_cookie_params(CFG_SESSION_TIMEOUT);
session_cache_limiter('nocache');
session_start();

// Load dependencies
require '../vendor/autoload.php';

// Load dictionary
include '../assets/dictionary.php';

// Prepare app
$app = new \Slim\Slim(array(
	'mode' => CFG_APP_MODE,
	'templates.path' => CFG_PATH_TEMPLATES,
));

// Define modes
include '../assets/modes.php';

// Define hooks
include '../assets/hooks.php';

// Define middleware
include '../assets/middleware.php';

// Define routes
include '../assets/routes.php';

// Define views
include '../assets/views.php';

// Start app
$app->run();