<?php

use App\Controllers\HomeController;
use App\Models\HomeModel;
use App\Views\View;

$app->get("/", function ($request, $response, $args) {
	//Classes
	$crontroller = new HomeController();
	$model       = new HomeModel();
	$view        = new View();

	return $crontroller->getHome($model, $view, $response);
});
