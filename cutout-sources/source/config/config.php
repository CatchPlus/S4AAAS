<?php

/**
 * IMPORTANT!
 * No trailing slash.
 */

// Application settings
define('CFG_APP_URL', 'http://localhost/projects/target/lcs/source/public');
define('CFG_APP_MODE', 'development');	// production || development
define('CFG_APP_LOCALE', 'en');

// Session settings
define('CFG_SESSION_NAME', 'Scratch4AllAsAService');
define('CFG_SESSION_TIMEOUT', 3600);

// Path settings
define('CFG_PATH_LOGS', '../logs');
define('CFG_PATH_TEMPLATES', '../templates');
define('CFG_PATH_TWIG', '../vendor/twig/twig');
define('CFG_PATH_CACHE', '../templates/cache');
define('CFG_PATH_DATA', 'c:\dev\xampp\htdocs\projects\target\lcs\source\data');

// Rest client settings
define('CFG_REST_CLIENT', 'http://s4aaas.target-imedia.nl/rest');