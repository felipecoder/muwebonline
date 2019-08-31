<?php

use App\Controllers\AdminController;
use App\Middlewares\AdminMiddleware;
use App\Models\AdminModel;
use App\Views\ViewAdmin;

$patch_admin 	= getenv('DIRADMIN');

$app->group("/{$patch_admin}", function ($app) {
	$app->get("/", function ($request, $response, $args) {
		//Classes
		$crontroller = new AdminController();
		$model       = new AdminModel();
		$view        = new ViewAdmin();

		$model->setUsername($_SESSION['usernameadmin']);

		return $crontroller->getHome($model, $view, $response);
	})->setName('home');

	$app->map(['GET', 'POST'], "/login", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');

			$model->setUsername($data['username'])
				->setPassword($data['password'])
				->setIpaddress($ipaddress);

			return $crontroller->postLogin($model, $response);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			return $crontroller->getLogin($model, $view, $response);
		}
	})->setName('login');

	$app->map(['GET', 'POST'], "/accounts/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postAccounts($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getAccounts($model, $view, $response, $args['page'], $id);
		}
	})->setName('accounts');

	$app->map(['GET', 'POST'], "/characters/{page}[/{character}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$character = (isset($args['character'])) ? $args['character'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postCharacters($model, $view, $response, $args['page'], $data, $character);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$character = (isset($args['character'])) ? $args['character'] : NULL;

			return $crontroller->getCharacters($model, $view, $response, $args['page'], $character);
		}
	})->setName('characters');

	$app->map(['GET', 'POST'], "/menus/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postMenu($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getMenus($model, $view, $response, $args['page'], $id);
		}
	})->setName('menus');

	$app->map(['GET', 'POST'], "/configs/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postConfig($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getConfigs($model, $view, $response, $args['page'], $id);
		}
	})->setName('configs');

	$app->map(['GET', 'POST'], "/rankings/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postRankings($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getRankings($model, $view, $response, $args['page'], $id);
		}
	})->setName('rankings');

	$app->map(['GET', 'POST'], "/news/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postNews($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getNews($model, $view, $response, $args['page'], $id);
		}
	})->setName('news');

	$app->map(['GET', 'POST'], "/pages/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postPages($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getPages($model, $view, $response, $args['page'], $id);
		}
	})->setName('pages');

	$app->map(['GET', 'POST'], "/events/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postEvents($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getEvents($model, $view, $response, $args['page'], $id);
		}
	})->setName('events');

	$app->map(['GET', 'POST'], "/coins/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postCoins($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getCoins($model, $view, $response, $args['page'], $id);
		}
	})->setName('coins');

	$app->map(['GET', 'POST'], "/vips/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postVips($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getVips($model, $view, $response, $args['page'], $id);
		}
	})->setName('vips');

	$app->get("/update[/{page}]", function ($request, $response, $args) {
		//Classes
		$crontroller = new AdminController();
		$model       = new AdminModel();
		$view        = new ViewAdmin();

		//Variables
		$ipaddress = $request->getServerParam('REMOTE_ADDR');
		$page = (isset($args['page'])) ? $args['page'] : NULL;
		$model->setIpaddress($ipaddress);

		return $crontroller->getUpdate($model, $view, $response, $page);
	})->setName('update');

	$app->map(['GET', 'POST'], "/accesspages/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postAccessPages($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getAccessPages($model, $view, $response, $args['page'], $id);
		}
	})->setName('accesspages');

	$app->get("/logout", function ($request, $response, $args) {
		$crontroller = new AdminController();
		return $crontroller->getLogout($response);
	});
})->add(new AdminMiddleware());
