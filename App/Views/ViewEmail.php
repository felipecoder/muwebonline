<?php

namespace App\Views;

use App\Database\DefaultDatabase;
use function src\slim;

class ViewEmail
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
    $template        = $config_template[2]['value'];
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

  public function getRender($values, $template)
  {
    //Classes
    $data       = new DefaultDatabase();

    //Variables
    $config_details = $data->getConfig('details');
    $config_details = json_decode($config_details, true);

    $values_default = array(
      'site_link'       => getenv('SITE_LINK'),
      'link_dir'        => getenv('DIR'),
      'link_images'     => getenv('DIRIMG'),
      'title_site'      => $config_details[0]['value'],
      'server_name'     => $config_details[2]['value'],
      'server_slogan'   => $config_details[3]['value'],
      'server_version'  => $config_details[4]['value'],
      'server_drop'     => $config_details[5]['value'],
      'server_xp'       => $config_details[6]['value'],
      'server_bugbless' => $config_details[7]['value'],
    );

    $parameters = array_merge($values_default, $values);

    return $this->view->fetch("{$template}.html", $parameters);
  }
}
