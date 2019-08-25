<?php

namespace App\Views;

use App\Database\AdminDatabase;
use function src\slim;

class ViewAdmin
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
		$data = new AdminDatabase();

		//Variables
		$config_template = $data->getConfig('templates');
		$config_template = json_decode($config_template, true);
		$template        = $config_template[1]['value'];
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
		$data     = new AdminDatabase();
		$messages = new ViewMessages;

		//Variables
		$messages       = $messages->getMessages();
		$user           = (isset($_SESSION['usernameadmin'])) ? $data->getUser($_SESSION['usernameadmin']) : NULL;
		$config_details = $data->getConfig('details');
		$config_details = json_decode($config_details, true);

		if (isset($messages['response'])) {
			$return = $messages['response'];
		} else {
			$return = NULL;
		}

		$values_default = array(
			'link_site'   => getenv('SITE_LINK'),
			'link_admin'  => getenv('DIRADMIN'),
			'link_dir'    => getenv('DIR'),
			'link_images' => getenv('DIRIMG'),
			'title_site'  => $config_details[0]['value'],
			'title_sigla' => $config_details[1]['value'],
			'admin_data'  => $user,
			'return'      => $return,
		);

		$parameters = array_merge($values_default, $values);

		return $this->view->render($response, "{$template}.html", $parameters);
	}
}
