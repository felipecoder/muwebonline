<?php

use App\Controllers\NewPasswordController;
use App\Middlewares\UserMiddleware;
use App\Models\NewPasswordModel;
use App\Views\View;

$app->map(['GET', 'POST'], "/newpassword[/{username}[/{token}]]", function ($request, $response, $args) {

  //Classes
  $controller  = new NewPasswordController();
  $model       = new NewPasswordModel();
  $view        = new View();

  //variables
  $username  = (isset($args['username'])) ? $args['username'] : NULL;
  $token     = (isset($args['token'])) ? $args['token'] : NULL;

  if ($request->isPost()) {

    $data      = $_POST;
    $ipaddress = $request->getServerParam('REMOTE_ADDR');

    $model->setIpaddress($ipaddress);

    return $controller->postNewPassword($model, $response, $data, $username, $token);
  } else {

    return $controller->getNewPassword($model, $view, $response, $username, $token);
  }
})->setName('forget')->add(new UserMiddleware());
