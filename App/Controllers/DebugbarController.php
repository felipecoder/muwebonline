<?php

namespace App\Controllers;

class DebugbarController
{
  private $debugbar;
  private $app;

  function __construct($app)
  {
    $this->app      = $app;
    $this->debugbar = new \Kitchenu\Debugbar\ServiceProvider();
    $this->debugbar->register($app);

    return $this->debugbar;
  }

  public function Collectors($container)
  {
    //PDO Collector
    $pdoRead      = new \DebugBar\DataCollector\PDO\TraceablePDO(new \PDO('sqlite::memory:'));
    $pdoCollector = new \DebugBar\DataCollector\PDO\PDOCollector();
    $pdoCollector->addConnection($pdoRead);
    $container->debugbar->addCollector($pdoCollector);
  }
}
