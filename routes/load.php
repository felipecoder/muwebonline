<?php

use function src\slim;
use App\Controllers\DebugbarController;
use App\Views\View;

//Variables
$app            = new \Slim\App(slim());
$container      = $app->getContainer();
$debug_bar      = getenv('DEBUG_BAR');
$display_errors = getenv('DISPLAY_ERRORS');

/*
	Php Debug Bar
*/
if ($debug_bar == 'true') {
	$debugbar = new DebugbarController($app);
	$debugbar->Collectors($container);
}

/*
	Whoops PHP
*/
if ($display_errors == 'true') {
	$whoopsGuard = new \Zeuxisoo\Whoops\Provider\Slim\WhoopsGuard();
	$whoopsGuard->setApp($app);
	$whoopsGuard->setRequest($container['request']);
	$whoopsGuard->install();
} else {
	error_reporting(0);
	@ini_set('display_errors', 0);
}


/*
	All File Routers Inlude
*/
include 'active.php';
include 'admin.php';
include 'dashboard.php';
include 'forget.php';
include 'home.php';
//include 'items.php';
include 'login.php';
include 'logos.php';
include 'logout.php';
include 'newpassword.php';
include 'news.php';
include 'pages.php';
include 'rankings.php';
include 'register.php';

/*
	Error 404
*/
$container['notFoundHandler'] = function ($container) {
	return function ($request, $response) use ($container) {
		$view = new View();

		$array = array(
			'title_page' => 'Error 404',
		);

		return $view->getRender($array, '404', $response);
	};
};

/*
	Error 500
*/
/*$container['errorHandler'] = function ($container) {
	return function ($request, $response) use ($container) {
		$view = new View();

		$array = array(
			'title_page' => 'Error 500',
		);

		return $view->getRender($array, '500', $response);
	};
};*/

/*
	Error PHP return Error 500
*/
/*$container['phpErrorHandler'] = function ($container) {
	return $container['errorHandler'];
};*/

$app->run();
