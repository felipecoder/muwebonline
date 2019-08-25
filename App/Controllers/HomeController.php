<?php

namespace App\Controllers;

use App\Database\HomeDatabase;
use App\Models\HomeModel;
use App\Views\View;
use Slim\Http\Response;

class HomeController
{

	public function getHome(HomeModel $model, View $view, Response $response)
	{
		//classes
		$data = new HomeDatabase();

		//variables
		$news = $data->getNews();

		$array = array(
			'title_page' => 'Home',
			'news'       => $news,
		);

		return $view->getRender($array, 'index', $response);
	}
}
