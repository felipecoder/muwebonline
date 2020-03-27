<?php

namespace App\Controllers;

use App\Database\NewsDatabase;
use App\Views\View;
use Slim\Http\Response;

class NewsController
{

  public function getNew(View $view, Response $response, $id = NULL)
  {
    //classes
    $data = new NewsDatabase();

    //Variables
    $new_data = $data->getNewInfo($id);

    if (empty($id)) {
      return $response->withRedirect(getenv("DIR"));
      exit();
    } elseif (empty($new_data)) {
      return $response->withRedirect(getenv("DIR"));
      exit();
    }

    $array = array(
      'title_page' => 'Notícia',
      'new_data'   => $new_data,
    );

    return $view->getRender($array, 'news', $response);
  }
}
