<?php

use App\Controllers\LoginController;
use App\Middlewares\UserMiddleware;
use App\Models\LoginModel;
use App\Views\View;

$app->map(['GET', 'POST'], "/login", function ($request, $response, $args) {

  //Classes
  $controller  = new LoginController();
  $model       = new LoginModel();
  $view        = new View();

  if ($request->isPost()) {

    $data      = $_POST;
    $ipaddress = $request->getServerParam('REMOTE_ADDR');

    $model->setUsername($data['username'])
      ->setPassword($data['password'])
      ->setIpaddress($ipaddress);

    return $controller->postLogin($model, $response);
  } else {

    return $controller->getLogin($model, $view, $response);
  }
})->setName('login')->add(new UserMiddleware());
