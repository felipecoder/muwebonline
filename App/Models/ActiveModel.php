<?php

namespace App\Models;

class ActiveModel
{
  /**
   * @var string
   */
  private $ipaddress;

  /**
   * Get the value of ipaddress
   *
   * @return  string
   */
  public function getIpaddress()
  {
    return $this->ipaddress;
  }

  /**
   * Set the value of ipaddress
   *
   * @param  string  $ipaddress
   *
   * @return  self
   */
  public function setIpaddress($ipaddress)
  {
    $this->ipaddress = $ipaddress;

    return $this;
  }
}
