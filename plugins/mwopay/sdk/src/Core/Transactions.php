<?php

namespace MWOPay\Core;

use MWOPay\Core\Curl;

class Transactions extends Curl
{
  const TRANSACTIONS_ENDPOINT = '/transactions/show';

  function __construct($credentials)
  {
    parent::__construct($credentials);
  }

  public function show($data)
  {
    return $this->execute($data, "GET", self::TRANSACTIONS_ENDPOINT);
  }
}
