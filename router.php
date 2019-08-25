<?php
$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('templates/'.TEMPLATE_SITE.'', [
        #'cache' => 'cache',
		'debug' => true
    ]);

    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$container['email'] = function ($container) {
    $email = new \Slim\Views\Twig('emails', [
        'debug' => false
    ]);

    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $email->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $email;
};

$container['admin'] = function ($container) {
    $view = new \Slim\Views\Twig('templates/adminlte', [
        #'cache' => 'cache',
        'debug' => true
    ]);

    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    return $view;
};

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('log');
    $file_handler = new \Monolog\Handler\StreamHandler('logs/user.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['loggerseller'] = function($c) {
    $logger = new \Monolog\Logger('log');
    $file_handler = new \Monolog\Handler\StreamHandler('logs/seller.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['loggercontact'] = function($c) {
    $logger = new \Monolog\Logger('log');
    $file_handler = new \Monolog\Handler\StreamHandler('logs/contact.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO( "".$db['driver'].":server=".$db['host'].";Database=".$db['dbname']."", "".$db['user']."", "".$db['pass']."");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $response->withRedirect('/404', 301);
    };
};

/*$container['errorHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $response->withRedirect('/500', 301);
    };
};

$container['phpErrorHandler'] = function ($container) {
    return $container['errorHandler'];
};*/

$app->add(new \Slim\HttpCache\Cache('public', 86400));

$app->add(function ($request, $response, $next) {
    $request = $request->withAttribute('session', $_SESSION); //add the session storage to your request as [READ-ONLY]
    return $next($request, $response);
});