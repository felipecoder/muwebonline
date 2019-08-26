<?php

use App\Controllers\NewsController;
use App\Views\View;

$app->get("/news/{id:[0-9]+}", function ($request, $response, $args) {

  //Classes
  $controller  = new NewsController();
  $view        = new View();

  //Variables
  $id = (isset($args['id'])) ? $args['id'] : NULL;

  return $controller->getNew($view, $response, $id);
});
