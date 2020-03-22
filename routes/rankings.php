<?php

use App\Controllers\RankingsController;
use App\Views\View;

$app->get("/rankings[/{link}[/{id:[0-9]+}]]", function ($request, $response, $args) {

  //Classes
  $controller  = new RankingsController();
  $view        = new View();

  //Variables
  $link = (isset($args['link'])) ? $args['link'] : NULL;
  $id = (isset($args['id'])) ? $args['id'] : NULL;

  return $controller->getRanking($view, $response, $link, $id);
});
