<?php

namespace MWOItems\Core;

class Serial
{

  /**
   * @var string
   */
  private $_connection;


  /**
   * @var string
   */
  private $serial;

  public function __construct(Item $item)
  {
    $this->_connection = $item->getConnection();
  }

  public function generate()
  {
    $data = new Database($this->_connection);
    $serial = $data->getSerial();
    $serial = array_shift($serial);
    $serial = substr($serial, 0, 8);
    $this->setSerial($serial);
    return $serial;
  }

  /**
   * Get the value of serial
   *
   * @return  string
   */
  public function getSerial()
  {
    return $this->serial;
  }

  /**
   * Set the value of serial
   *
   * @param  string  $serial
   *
   * @return  self
   */
  public function setSerial($serial)
  {
    $this->serial = $serial;

    return $this;
  }
}
