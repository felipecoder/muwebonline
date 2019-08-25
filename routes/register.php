<?php

use App\Controllers\RegisterController;
use App\Middlewares\UserMiddleware;
use App\Models\RegisterModel;
use App\Views\View;

$app->map(['GET', 'POST'], "/register", function ($request, $response, $args) {

  //Classes
  $controller  = new RegisterController();
  $model       = new RegisterModel();
  $view        = new View();

  if ($request->isPost()) {
    $data      = $_POST;
    $ipaddress = $request->getServerParam('REMOTE_ADDR');

    return $controller->postRegister($model, $view, $response, $data, $ipaddress);
  } else {

    return $controller->getRegister($model, $view, $response);
  }
})->setName('register')->add(new UserMiddleware());
