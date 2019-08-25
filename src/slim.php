<?php

namespace src;

function slim()
{
	$configuration = [
		'settings' => [
			'displayErrorDetails' => getenv('DISPLAY_ERRORS'),
			'determineRouteBeforeAppMiddleware' => true,
			'debug' => true,
			'whoops.page_title' => 'MuWebOnline Error',
		],
	];

	return new \Slim\Container($configuration);
}
