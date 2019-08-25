<?php

namespace App\Controllers;

use App\Database\PagesDatabase;
use App\Views\View;
use Slim\Http\Response;

class PagesController
{

  public function getPages()
  {
    //classes
    $data = new PagesDatabase();
    return $data->getPages();
  }

  public function getPage(View $view, Response $response, $link)
  {
    //classes
    $data = new PagesDatabase();

    //variables
    $page_data = $data->getPageInfo($link);

    $array = array(
      'title_page' => $page_data['title'],
      'page_data'  => $page_data,
    );

    return $view->getRender($array, 'pages', $response);
  }
}
