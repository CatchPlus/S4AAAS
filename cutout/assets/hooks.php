<?php	// Filename: assets/hooks.php

// Log succesful requests
$app->hook('slim.before.dispatch', function () use ($app) {
	$env = $app->environment();
	$log = $app->getLog();

	$log->info('request method: '.$env->offsetGet('REQUEST_METHOD')
			.', path info: '.$env->offsetGet('PATH_INFO')
			.', remote addr: '.$env->offsetGet('REMOTE_ADDR')
	);
});