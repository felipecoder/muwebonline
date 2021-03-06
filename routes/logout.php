<?php

use App\Middlewares\UserMiddleware;

$app->get("/logout", function ($request, $response, $args) {
  //Classes
  unset($_SESSION['loggedinuser'], $_SESSION['usernameuser']);

  return $response->withRedirect(getenv("DIR") . "login");
})->add(new UserMiddleware());
