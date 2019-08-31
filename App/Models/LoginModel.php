<?php

namespace App\Models;

class LoginModel
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
  private $password;

  /**
   * @var string
   */
  private $ipaddress;

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * @param string $username
   *
   * @return self
   */
  public function setUsername($username)
  {
    $this->username = $username;

    return $this;
  }

  /**
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * @param string $password
   *
   * @return self
   */
  public function setPassword($password)
  {
    $this->password = $password;

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
