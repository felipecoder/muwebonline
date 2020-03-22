<?php

namespace MWOPay;

use MWOPay\Core\Payment;
use MWOPay\Core\Transactions;
use MWOPay\Core\Users;
use MWOPay\Core\Withdrawals;

class MWOPay
{
  /**
   * Get the value of payment
   *
   * @return  MWOPay\Core\Payment;
   */
  private $payment;

  /**
   * Get the value of transactions
   *
   * @return  MWOPay\Core\Transactions;
   */
  private $transactions;

  /**
   * Get the value of users
   *
   * @return  MWOPay\Core\Users;
   */
  private $users;

  /**
   * Get the value of withdrawals
   *
   * @return  MWOPay\Core\Withdrawals;
   */
  private $withdrawals;

  /**
   * @var array MWOPay credentials
   */
  private $credentials;

  public function __construct($email, $token)
  {
    $this->credentials = [
      'email' => $email,
      'token' => $token
    ];

    $this->payment      = new Payment($this->credentials);
    $this->transactions = new Transactions($this->credentials);
    $this->users        = new Users($this->credentials);
    $this->withdrawals  = new Withdrawals($this->credentials);
  }

  /**
   * Get the value of payment
   *
   * @return  MWOPay\Core\Payment;
   */
  public function getPayment()
  {
    return $this->payment;
  }

  /**
   * Get get the value of transactions
   *
   * @return  MWOPay\Core\Transactions;
   */
  public function getTransactions()
  {
    return $this->transactions;
  }

  /**
   * Get get the value of users
   *
   * @return  MWOPay\Core\Users;
   */
  public function getUsers()
  {
    return $this->users;
  }

  /**
   * Get get the value of withdrawals
   *
   * @return  MWOPay\Core\Withdrawals;
   */
  public function getWithdrawals()
  {
    return $this->withdrawals;
  }
}
