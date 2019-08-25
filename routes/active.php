<?php

use App\Controllers\ActiveController;
use App\Models\ActiveModel;
use App\Views\View;

$app->get("/active/{username}/{token}", function ($request, $response, $args) {
  //Classes
  $crontroller = new ActiveController();
  $model       = new ActiveModel();
  $view        = new View();

  //Variables
  $username  = (isset($args['username'])) ? $args['username'] : NULL;
  $token     = (isset($args['token'])) ? $args['token'] : NULL;
  $ipaddress = $request->getServerParam('REMOTE_ADDR');

  $model->setIpaddress($ipaddress);

  return $crontroller->getActive($model, $view, $response, $username, $token);
});
