<?php

namespace App\Middlewares;

use App\Views\ViewMessages;

class AdminMiddleware
{

	function __invoke($request, $response, $next)
	{
		$patch_admin = getenv("DIR") . getenv('DIRADMIN');
		$route       = $request->getAttribute('route');
		$routeName   = $route->getName();
		$groups      = $route->getGroups();
		$methods     = $route->getMethods();
		$arguments   = $route->getArguments();

		$publicRoutesArray = array(
			'login',
		);

		if (!isset($_SESSION['loggedinadmin']) && !in_array($routeName, $publicRoutesArray)) {
			$messages = new ViewMessages();

			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Você não está logado'
			);

			$messages->addMessage('response', $return);

			$response = $response->withRedirect("{$patch_admin}/login");
		} elseif (isset($_SESSION['loggedinadmin']) && in_array($routeName, $publicRoutesArray)) {
			$messages = new ViewMessages();

			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Você já está logado'
			);

			$messages->addMessage('response', $return);
			$response = $response->withRedirect("{$patch_admin}/");
		} else {
			$response = $next($request, $response);
		}

		return $response;
	}
}
