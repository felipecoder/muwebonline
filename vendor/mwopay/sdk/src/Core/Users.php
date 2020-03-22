<?php

namespace MWOPay\Core;

use MWOPay\Core\Curl;

class Users extends Curl
{
  const CREDITS_ENDPOINT = '/users/credits';
  const UPDATE_ENDPOINT  = '/users/update';

  function __construct($credentials)
  {
    parent::__construct($credentials);
  }

  public function credits($data)
  {
    return $this->execute($data, "GET", self::CREDITS_ENDPOINT);
  }

  public function update($data)
  {
    return $this->execute($data, "POST", self::UPDATE_ENDPOINT);
  }
}
