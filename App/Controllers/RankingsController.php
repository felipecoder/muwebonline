<?php

namespace App\Controllers;

use App\Database\RankingsDatabase;
use App\Views\View;
use Slim\Http\Response;

class RankingsController
{

  public function getRanking(View $view, Response $response, $link, $id)
  {
    //classes
    $data = new RankingsDatabase();

    //variables
    $rankings = $data->getRankings();

    if ($link != NULL) {
      $ranking_data = $data->getRankingInfo($link);
      if (empty($ranking_data)) {
        $error = array(
          'error'   => true,
          'success' => false,
          'message' => 'Esse ranking não existe'
        );

        $array = array(
          'title_page'    => 'Rankings',
          'rankings_page' => $rankings,
          'error'         => $error,
        );

        return $view->getRender($array, 'rankings-index', $response);
      } else {
        $page      = ($id > 0) ? $id : 1;
        $limit     = $ranking_data['max'];
        $skip      = $page * $limit;
        $count     = $data->countRanking($ranking_data['database'], $ranking_data['table']);

        $ranking_return = array(
          'ID'       => $ranking_data['ID'],
          'name'     => $ranking_data['name'],
          'database' => $ranking_data['database'],
          'table'    => $ranking_data['table'],
          'max'      => $ranking_data['max'],
          'column'   => $ranking_data['column'],
          'link'     => $ranking_data['link'],
          'data'     => $data->createRanking($ranking_data['table'], $ranking_data['column'], $skip, $ranking_data['max']),
        );

        $array = array(
          'title_page'    => 'Rankings ' . $ranking_data['name'],
          'rankings_page' => $rankings,
          'rankings_data' => $ranking_return,
          'pagination'    => [
            'needed'        => $count > $limit,
            'count'         => $count,
            'page'          => $page,
            'lastpage'      => (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit)),
            'limit'         => $limit,
          ],
        );

        /*echo "<pre>";
        print_r($ranking_return);
        exit();*/

        return $view->getRender($array, 'rankings-index', $response);
      }
    } else {
      $array = array(
        'title_page'    => 'Rankings',
        'rankings_page' => $rankings,
      );

      return $view->getRender($array, 'rankings', $response);
    }
  }
}
