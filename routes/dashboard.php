<?php

use App\Controllers\DashboardController;
use App\Middlewares\AccesPageMiddeware;
use App\Middlewares\UserMiddleware;
use App\Models\DashboardModel;
use App\Views\View;

$app->group("/dashboard", function ($app) {
  //Index Redirect
  $app->redirect("", getenv("DIR") . "dashboard/home", 301);

  //Home
  $app->get("/home", function ($request, $response, $args) {
    //Classes
    $crontroller = new DashboardController();
    $model       = new DashboardModel();
    $view        = new View();

    $model->setUsername($_SESSION['usernameuser']);

    return $crontroller->getHome($model, $view, $response);
  })->setName('dashboardhome');

  //Vips
  $app->map(['GET', 'POST'], "/vips[/{page}[/{id:[0-9]+}]]", function ($request, $response, $args) {
    //Classes
    $crontroller = new DashboardController();
    $model       = new DashboardModel();
    $view        = new View();

    //Variables
    $model->setUsername($_SESSION['usernameuser']);

    if ($request->isPost()) {
      $data      = $_POST;
      $ipaddress = $request->getServerParam('REMOTE_ADDR');

      $model->setIpaddress($ipaddress);

      return $crontroller->postVips($model, $response, $data, $args['page'], $args['id']);
    } else {
      return $crontroller->getVips($model, $view, $response);
    }
  })->setName('dashboardvips');

  //Coins
  $app->map(['GET', 'POST'], "/coins[/{page}[/{id:[0-9]+}]]", function ($request, $response, $args) {
    //Classes
    $crontroller = new DashboardController();
    $model       = new DashboardModel();
    $view        = new View();

    //Variables
    $model->setUsername($_SESSION['usernameuser']);

    if ($request->isPost()) {
      $data      = $_POST;
      $ipaddress = $request->getServerParam('REMOTE_ADDR');

      $model->setIpaddress($ipaddress);

      return $crontroller->postCoins($model, $response, $data, $args['page'], $args['id']);
    } else {
      return $crontroller->getCoins($model, $view, $response);
    }
  })->setName('dashboardcoins');

  //Settings
  $app->map(['GET', 'POST'], "/settings[/{page}]", function ($request, $response, $args) {
    //Classes
    $crontroller = new DashboardController();
    $model       = new DashboardModel();
    $view        = new View();

    //Variables
    $model->setUsername($_SESSION['usernameuser']);

    if ($request->isPost()) {
      $data      = $_POST;
      $ipaddress = $request->getServerParam('REMOTE_ADDR');

      $model->setIpaddress($ipaddress);

      return $crontroller->postSettings($model, $response, $data, $args['page']);
    } else {
      return $crontroller->getSettings($model, $view, $response);
    }
  })->setName('dashboardsettings');

  $app->map(['GET', 'POST'], "/tickets[/{page}[/{id:[0-9]+}]]", function ($request, $response, $args) {
    //Classes
    $crontroller = new DashboardController();
    $model       = new DashboardModel();
    $view        = new View();

    //Variables
    $model->setUsername($_SESSION['usernameuser']);
    $page = (empty($args['page'])) ? NULL : $args['page'];
    $id   = (empty($args['id'])) ? NULL : $args['id'];

    if ($request->isPost()) {
      $data      = $_POST;
      $files     = $request->getUploadedFiles();
      $ipaddress = $request->getServerParam('REMOTE_ADDR');

      $model->setIpaddress($ipaddress);

      return $crontroller->postTickets($model, $response, $data, $files, $args['page']);
    } else {
      return $crontroller->getTickets($model, $view, $response, $page, $id);
    }
  })->setName('dashboardtickets');

  $app->group("/characters", function ($app) {
    $app->redirect('', '/dashboard/home', 301);
    $app->redirect('/', '/dashboard/home', 301);

    $app->map(['GET', 'POST'], "/reset", function ($request, $response, $args) {
      //Classes
      $crontroller = new DashboardController();
      $model       = new DashboardModel();
      $view        = new View();

      //Variables
      $model->setUsername($_SESSION['usernameuser']);

      if ($request->isPost()) {
        $data      = $_POST;
        $ipaddress = $request->getServerParam('REMOTE_ADDR');

        $model->setIpaddress($ipaddress);

        return $crontroller->postResets($model, $response, $data);
      } else {
        return $crontroller->getResets($model, $view, $response);
      }
    })->setName('charactersreset');

    $app->map(['GET', 'POST'], "/masterreset", function ($request, $response, $args) {
      //Classes
      $crontroller = new DashboardController();
      $model       = new DashboardModel();
      $view        = new View();

      //Variables
      $model->setUsername($_SESSION['usernameuser']);

      if ($request->isPost()) {
        $data      = $_POST;
        $ipaddress = $request->getServerParam('REMOTE_ADDR');

        $model->setIpaddress($ipaddress);

        return $crontroller->postMasterResets($model, $response, $data);
      } else {
        return $crontroller->getMasterResets($model, $view, $response);
      }
    })->setName('charactersmasterreset');

    $app->map(['GET', 'POST'], "/cleanpk", function ($request, $response, $args) {
      //Classes
      $crontroller = new DashboardController();
      $model       = new DashboardModel();
      $view        = new View();

      //Variables
      $model->setUsername($_SESSION['usernameuser']);

      if ($request->isPost()) {
        $data      = $_POST;
        $ipaddress = $request->getServerParam('REMOTE_ADDR');

        $model->setIpaddress($ipaddress);

        return $crontroller->postCleanPK($model, $response, $data);
      } else {
        return $crontroller->getCleanPK($model, $view, $response);
      }
    })->setName('characterscleanpk');

    $app->map(['GET', 'POST'], "/changenick", function ($request, $response, $args) {
      //Classes
      $crontroller = new DashboardController();
      $model       = new DashboardModel();
      $view        = new View();

      //Variables
      $model->setUsername($_SESSION['usernameuser']);

      if ($request->isPost()) {
        $data      = $_POST;
        $ipaddress = $request->getServerParam('REMOTE_ADDR');

        $model->setIpaddress($ipaddress);

        return $crontroller->postChangeNick($model, $response, $data);
      } else {
        return $crontroller->getChangeNick($model, $view, $response);
      }
    })->setName('characterschangenick');

    $app->map(['GET', 'POST'], "/changeclass", function ($request, $response, $args) {
      //Classes
      $crontroller = new DashboardController();
      $model       = new DashboardModel();
      $view        = new View();

      //Variables
      $model->setUsername($_SESSION['usernameuser']);

      if ($request->isPost()) {
        $data      = $_POST;
        $ipaddress = $request->getServerParam('REMOTE_ADDR');

        $model->setIpaddress($ipaddress);

        return $crontroller->postChangeClass($model, $response, $data);
      } else {
        return $crontroller->getChangeClass($model, $view, $response);
      }
    })->setName('characterschangeclass');

    $app->map(['GET', 'POST'], "/changeimage", function ($request, $response, $args) {
      //Classes
      $crontroller = new DashboardController();
      $model       = new DashboardModel();
      $view        = new View();

      //Variables
      $model->setUsername($_SESSION['usernameuser']);

      if ($request->isPost()) {
        $data      = $_POST;
        $files     = $request->getUploadedFiles();
        $ipaddress = $request->getServerParam('REMOTE_ADDR');

        $model->setIpaddress($ipaddress);

        return $crontroller->postChangeImage($model, $response, $data, $files);
      } else {
        return $crontroller->getChangeImage($model, $view, $response);
      }
    })->setName('characterschangeimage');
  });

  //No Vip
  $app->get("/no-vip", function ($request, $response, $args) {
    //Classes
    $crontroller = new DashboardController();
    $model       = new DashboardModel();
    $view        = new View();

    return $crontroller->getNoVip($model, $view, $response);
  })->setName('novip');

  //Blocked
  $app->get("/blocked", function ($request, $response, $args) {
    //Classes
    $crontroller = new DashboardController();
    $model       = new DashboardModel();
    $view        = new View();

    return $crontroller->getBlocked($model, $view, $response);
  })->setName('blocked');
})->add(new AccesPageMiddeware())->add(new UserMiddleware());
