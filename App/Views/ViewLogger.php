<?php

namespace App\Views;

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler as StreamHandler;

class ViewLogger
{
  private $logger;
  function __construct($log)
  {
    $patch        = getenv('DIRLOGS');
    $logger       = new Logger('log');
    $file_handler = new StreamHandler("{$patch}{$log}.log");
    $logger->pushHandler($file_handler);

    $this->logger = $logger;
  }

  public function addLoggerInfo($subject, $values)
  {
    $this->logger->addInfo("{$subject}", $values);
  }

  public function addLoggerWarning($subject, $values)
  {
    $this->logger->addWarning("{$subject}", $values);
  }
}
