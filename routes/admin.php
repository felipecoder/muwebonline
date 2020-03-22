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

		//Variables
		$ipaddress = $request->getServerParam('REMOTE_ADDR');

		$model->setUsername($_SESSION['usernameadmin'])
			->setIpaddress($ipaddress);

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

	$app->map(['GET', 'POST'], "/accesspanel/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
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

			return $crontroller->postAccessPanel($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getAccessPanel($model, $view, $response, $args['page'], $id);
		}
	})->setName('accesspanel');

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

	$app->map(['GET', 'POST'], "/rankings-home/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
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

			return $crontroller->postRankingsHome($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getRankingsHome($model, $view, $response, $args['page'], $id);
		}
	})->setName('rankings-home');

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

	$app->map(['GET', 'POST'], "/slides/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
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

			return $crontroller->postSlides($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getSlides($model, $view, $response, $args['page'], $id);
		}
	})->setName('slides');

	$app->map(['GET', 'POST'], "/kingofmu", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');

			$model->setIpaddress($ipaddress);

			return $crontroller->postKingOfMu($model, $view, $response, $data);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			return $crontroller->getKingOfMu($model, $view, $response);
		}
	})->setName('kingofmu');

	$app->get("/transactions/{page}[/{id}]", function ($request, $response, $args) {
		//Classes
		$crontroller = new AdminController();
		$model       = new AdminModel();
		$view        = new ViewAdmin();

		//Variables
		$ipaddress = $request->getServerParam('REMOTE_ADDR');
		$page      = (isset($args['page'])) ? $args['page'] : NULL;
		$id        = (isset($args['id'])) ? $args['id'] : NULL;
		$model->setIpaddress($ipaddress);

		return $crontroller->getTransactions($model, $view, $response, $page, $id);
	})->setName('transactions');

	$app->map(['GET', 'POST'], "/withdrawals/{page}[/{id}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$ipaddress = $request->getServerParam('REMOTE_ADDR');

			$model->setIpaddress($ipaddress);

			return $crontroller->postWithdrawals($model, $view, $response, $args['page'], $data);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getWithdrawals($model, $view, $response, $args['page'], $id);
		}
	})->setName('withdrawals');

	$app->map(['GET', 'POST'], "/tickets/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
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

			return $crontroller->postTickets($model, $view, $response, $args['page'], $data, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getTickets($model, $view, $response, $args['page'], $id);
		}
	})->setName('tickets');

	$app->map(['GET', 'POST'], "/logs/{page}[/{name}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$name      = (isset($args['name'])) ? $args['name'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postLogs($model, $view, $response, $args['page'], $name);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$name = (isset($args['name'])) ? $args['name'] : NULL;

			return $crontroller->getLogs($model, $view, $response, $args['page'], $name);
		}
	})->setName('logs');

	$app->map(['GET', 'POST'], "/items/{action}/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$files     = $request->getUploadedFiles();
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postItems($model, $view, $response, $args['action'], $args['page'], $data, $files, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getItems($model, $view, $response, $args['action'], $args['page'], $id);
		}
	})->setName('items');

	$app->map(['GET', 'POST'], "/webshops/{action}/{page}[/{id:[0-9]+}]", function ($request, $response, $args) {
		if ($request->isPost()) {

			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$data      = $_POST;
			$files     = $request->getUploadedFiles();
			$ipaddress = $request->getServerParam('REMOTE_ADDR');
			$id        = (isset($args['id'])) ? $args['id'] : NULL;

			$model->setIpaddress($ipaddress);

			return $crontroller->postWebShops($model, $view, $response, $args['action'], $args['page'], $data, $files, $id);
		} else {
			//Classes
			$crontroller = new AdminController();
			$model       = new AdminModel();
			$view        = new ViewAdmin();

			//Variables
			$id = (isset($args['id'])) ? $args['id'] : NULL;

			return $crontroller->getWebShops($model, $view, $response, $args['action'], $args['page'], $id);
		}
	})->setName('webshops');

	$app->get("/logout", function ($request, $response, $args) {
		$crontroller = new AdminController();
		return $crontroller->getLogout($response);
	});
})->add(new AdminMiddleware());
