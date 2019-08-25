<?php

namespace App\Models;

class DashboardModel
{
  /**
   * @var int
   */
  private $id;

  /**
   * @var string
   */
  private $username;

  /**
   * @var string
   */
  private $ipaddress;

  /**
   * Get the value of id
   *
   * @return  int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get the value of username
   *
   * @return  string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * Set the value of username
   *
   * @param  string  $username
   *
   * @return  self
   */
  public function setUsername($username)
  {
    $this->username = $username;

    return $this;
  }

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
