<?php

namespace MWOPay\Core;

use MWOPay\Core\Curl;

class Payment extends Curl
{
  const CARD_ENDPOINT    = '/payment/card';
  const BILLET_ENDPOINT  = '/payment/billet';

  function __construct($credentials)
  {
    parent::__construct($credentials);
  }

  public function card($data)
  {
    return $this->execute($data, "POST", self::CARD_ENDPOINT);
  }

  public function billet($data)
  {
    return $this->execute($data, "POST", self::BILLET_ENDPOINT);
  }
}
