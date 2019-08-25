<?php

namespace App\Middlewares;

use App\Views\ViewMessages;

class UserMiddleware
{

  function __invoke($request, $response, $next)
  {
    $route       = $request->getAttribute('route');
    $routeName   = $route->getName();
    $groups      = $route->getGroups();
    $methods     = $route->getMethods();
    $arguments   = $route->getArguments();

    $publicRoutesArray = array(
      'login',
      'register',
      'forget'
    );

    if (!isset($_SESSION['loggedinuser']) && !in_array($routeName, $publicRoutesArray)) {
      $messages = new ViewMessages();

      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Você não está logado'
      );

      $messages->addMessage('response', $return);

      $response = $response->withRedirect("/login");
    } elseif (isset($_SESSION['loggedinuser']) && in_array($routeName, $publicRoutesArray)) {
      $messages = new ViewMessages();

      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Você já está logado'
      );

      $messages->addMessage('response', $return);
      $response = $response->withRedirect("/dashboard");
    } else {
      $response = $next($request, $response);
    }

    return $response;
  }
}
