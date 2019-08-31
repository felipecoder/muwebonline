<?php

namespace App\Models;

class RegisterModel
{
  /**
   * @var string
   */
  private $username;

  /**
   * @var string
   */
  private $nick;

  /**
   * @var string
   */
  private $password;

  /**
   * @var string
   */
  private $repassword;

  /**
   * @var string
   */
  private $email;

  /**
   * @var string
   */
  private $reemail;

  /**
   * @var string
   */
  private $ipaddress;

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
   * Get the value of nick
   *
   * @return  string
   */
  public function getNick()
  {
    return $this->nick;
  }

  /**
   * Set the value of nick
   *
   * @param  string  $nick
   *
   * @return  self
   */
  public function setNick($nick)
  {
    $this->nick = $nick;

    return $this;
  }

  /**
   * Get the value of password
   *
   * @return  string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * Set the value of password
   *
   * @param  string  $password
   *
   * @return  self
   */
  public function setPassword($password)
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Get the value of repassword
   *
   * @return  string
   */
  public function getRepassword()
  {
    return $this->repassword;
  }

  /**
   * Set the value of repassword
   *
   * @param  string  $repassword
   *
   * @return  self
   */
  public function setRepassword($repassword)
  {
    $this->repassword = $repassword;

    return $this;
  }

  /**
   * Get the value of email
   *
   * @return  string
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set the value of email
   *
   * @param  string  $email
   *
   * @return  self
   */
  public function setEmail($email)
  {
    $this->email = $email;

    return $this;
  }

  /**
   * Get the value of reemail
   *
   * @return  string
   */
  public function getReemail()
  {
    return $this->reemail;
  }

  /**
   * Set the value of reemail
   *
   * @param  string  $reemail
   *
   * @return  self
   */
  public function setReemail($reemail)
  {
    $this->reemail = $reemail;

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
