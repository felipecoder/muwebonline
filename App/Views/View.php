<?php

namespace App\Views;

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

		//Variables
		$menus          = $data->getMenus(0);
		$total_onlines  = $data->getTotalOnline();
		$staff          = $data->getStaff();
		$user_logged_in = (isset($_SESSION['loggedinuser'])) ? true : false;
		$messages       = $messages->getMessages();
		$user           = (isset($_SESSION['usernameuser'])) ? $data->getUser($_SESSION['usernameuser']) : NULL;
		$config_details = $data->getConfig('details');
		$config_details = json_decode($config_details, true);
		$config_captcha = $data->getConfig('captcha');
		$config_captcha = json_decode($config_captcha, true);
		$config_class   = $data->getConfig('classcodes');
		$config_class   = json_decode($config_class, true);
		$social_link    = $data->getConfig('sociallinks');
		$social_link    = json_decode($social_link, true);
		$rankings       = $data->getRankings();
		$events         = $data->getEvents();
		$coins          = $data->getCoinsConfigs();
		$slides         = $data->getSlides();
		$getkingofmu    = $data->getKingOfMu();

		if (isset($messages['response'])) {
			$return = $messages['response'];
		} else {
			$return = NULL;
		}

		if (empty($menus)) {
			$menus_return = NULL;
		} else {
			foreach ($menus as $key => $value) {
				$menus_return[] = array(
					'ID'       => $value['ID'],
					'name'     => $value['name'],
					'level'    => $value['label'],
					'link'     => $value['link'],
					'parentid' => $value['parentid'],
					'status'   => $value['status'],
					'children' => $data->getMenus($value['ID'])
				);
			}
		}

		if (empty($rankings)) {
			$rankings_return = NULL;
		} else {
			foreach ($rankings as $key => $value) {
				$rankings_return[] = array(
					'ID'      => $value['ID'],
					'name'    => $value['name'],
					'column'  => $value['column'],
					'ranking' => $data->createRanking($value['database'], $value['table'], $value['column'], $value['max'], $value['custom'])
				);
			}
		}

		if (empty($events)) {
			$events_return = NULL;
		} else {
			foreach ($events as $key => $value) {
				$events_return[] = array(
					0 => $value['name'],
					1 => explode(",", $value['time']),
				);
			}
		}

		if (isset($_SESSION['usernameuser'])) {
			if (empty($coins)) {
				$coins_return = NULL;
			} else {
				foreach ($coins as $key => $value) {
					$coins_return[] = array(
						'ID'     => $value['ID'],
						'name'   => $value['name'],
						'column' => $value['column'],
						'value'  => $data->getCoinsUser($value['database'], $value['table'], $value['column'], $user['memb___id'])
					);
				}
			}
		} else {
			$coins_return = NULL;
		}

		if ($getkingofmu['mode'] == 'manual') {
			$kingofmu = $data->getCharacterKingManual($getkingofmu['database'], $getkingofmu['table'], $getkingofmu['character']);
			foreach ($config_class as $key => $value) {
				if ($kingofmu['Class'] == $value['value']) {
					$class_character = $value['label'];
					break;
				} else {
					$class_character = 'Unknow';
				}
			}

			$wins = array('wins' => $getkingofmu['wins']);
			$classname = array('classname' => $class_character);
			$kingofmu = array_merge($kingofmu, $wins, $classname);
		} else {
			$kingofmu = $data->getCharacterKingAuto($getkingofmu['database'], $getkingofmu['table'], $getkingofmu['custom'], $getkingofmu['orderby']);
			foreach ($config_class as $key => $value) {
				if ($kingofmu['Class'] == $value['value']) {
					$class_character = $value['label'];
					break;
				} else {
					$class_character = 'Unknow';
				}
			}

			$wins = array('wins' => $getkingofmu['wins']);
			$classname = array('classname' => $class_character);
			$kingofmu = array_merge($kingofmu, $wins, $classname);
		}

		$values_default = array(
			'link_site'       => getenv('SITE_LINK'),
			'link_dir'        => getenv('DIR'),
			'link_images'     => getenv('DIRIMG'),
			'title_site'      => $config_details[0]['value'],
			'server_name'     => $config_details[2]['value'],
			'server_slogan'   => $config_details[3]['value'],
			'server_version'  => $config_details[4]['value'],
			'server_drop'     => $config_details[5]['value'],
			'server_xp'       => $config_details[6]['value'],
			'server_bugbless' => $config_details[7]['value'],
			'menus'           => $menus_return,
			'user_logged_in'  => $user_logged_in,
			'total_onlines'   => $total_onlines,
			'staff'           => $staff,
			'recaptcha_site'  => $config_captcha[1]['value'],
			'userdata'        => $user,
			'rankings'        => $rankings_return,
			'coins_user'      => $coins_return,
			'events_json'     => json_encode($events_return),
			'events_array'    => $events_return,
			'facebook_link'   => $social_link[0]['value'],
			'twitter_link'    => $social_link[1]['value'],
			'instagram_link'  => $social_link[2]['value'],
			'discord_link'    => $social_link[3]['value'],
			'youtube_link'    => $social_link[4]['value'],
			'whatsapp_link'   => $social_link[5]['value'],
			'teamspeak_link'  => $social_link[6]['value'],
			'slides'          => $slides,
			'kingofmu'        => $kingofmu,
			'return'          => $return,
		);

		$parameters = array_merge($values_default, $values);

		return $this->view->render($response, "{$template}.html", $parameters);
	}
}
