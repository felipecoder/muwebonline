<?php

use App\Controllers\PagesController;
use App\Views\View;

$pages = new PagesController();
$pages = $pages->getPages();

foreach ($pages as $key => $value) {
  $app->get("" . $value['link'] . "", function ($request, $response, $args) {
    //Classes
    $crontroller = new PagesController();
    $view        = new View();

    //variables
    $uri  = $request->getUri();
    $link = $uri->getPath();

    return $crontroller->getPage($view, $response, $link);
  });
}
