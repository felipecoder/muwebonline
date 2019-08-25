<?php

use App\Controllers\ForgetController;
use App\Middlewares\UserMiddleware;
use App\Models\ForgetModel;
use App\Views\View;

$app->map(['GET', 'POST'], "/forget", function ($request, $response, $args) {

  //Classes
  $controller  = new ForgetController();
  $model       = new ForgetModel();
  $view        = new View();

  if ($request->isPost()) {

    $data      = $_POST;
    $ipaddress = $request->getServerParam('REMOTE_ADDR');

    $model->setIpaddress($ipaddress);

    return $controller->postForget($model, $response, $data);
  } else {

    return $controller->getForget($model, $view, $response);
  }
})->setName('forget')->add(new UserMiddleware());
