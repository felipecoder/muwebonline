<?php

namespace App\Views;

use App\Controllers\DefaultController;
use App\Database\DefaultDatabase;
use function src\slim;

class View
{

	public $view;

	function __construct()
	{
		$app       = new \Slim\App(slim());
		$container = $app->getContainer();

		$template = $this->create($container);

		$this->view = $template;
	}

	private function create($container)
	{
		//Classes
		$data = new DefaultDatabase();

		//Variables
		$config_template = $data->getConfig('templates');
		$config_template = json_decode($config_template, true);
		$template        = $config_template[0]['value'];
		$cache           = $config_template[3]['value'];
		$debug           = $config_template[4]['value'];

		if ($cache == "true") {
			$twig = new \Slim\Views\Twig("templates/{$template}", [
				'cache' => "cache/{$template}",
				'debug' => $debug
			]);
		} else {
			$twig = new \Slim\Views\Twig("templates/{$template}", [
				'debug' => $debug
			]);
		}
		$router    = $container->get('router');
		$uri       = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));

		$twig->addExtension(new \Slim\Views\TwigExtension($router, $uri));

		return $twig;
	}

	public function getRender($values, $template, $response)
	{
		//Classes
		$data       = new DefaultDatabase();
		$messages   = new ViewMessages();
		$controller = new DefaultController();

		//Variables
		$user_logged_in = (isset($_SESSION['loggedinuser'])) ? true : false;
		$messages       = $messages->getMessages();
		$user           = (isset($_SESSION['usernameuser'])) ? $data->getUser($_SESSION['usernameuser']) : NULL;

		if (isset($messages['response'])) {
			$return = $messages['response'];
		} else {
			$return = NULL;
		}


		$values_default = array(
			'link_site'       => getenv('SITE_LINK'),
			'link_dir'        => getenv('DIR'),
			'link_images'     => getenv('DIRIMG'),
			'user_logged_in'  => $user_logged_in,
			'userdata'        => $user,
			'return'          => $return,
		);

		$parameters = array_merge($values_default, $values, $controller->getAll());

		//echo "<pre>";

		//print_r($controller->getAll()['castlesiege']['logo']);
		//echo '<img src="' . $controller->getAll()['castlesiege']['logo'] . '" />';
		//exit();

		return $this->view->render($response, "{$template}.html", $parameters);
	}
}
