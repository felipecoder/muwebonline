<?php

namespace MWOPay\Core;

use MWOPay\Core\Curl;

class Withdrawals extends Curl
{
  const WITHDRAWALS_ENDPOINT = '/withdrawals/show';
  const CREATE_ENDPOINT      = '/withdrawals/create';

  function __construct($credentials)
  {
    parent::__construct($credentials);
  }

  public function show($data)
  {
    return $this->execute($data, "GET", self::WITHDRAWALS_ENDPOINT);
  }

  public function create($data)
  {
    return $this->execute($data, "POST", self::CREATE_ENDPOINT);
  }
}
