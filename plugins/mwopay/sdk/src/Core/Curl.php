<?php

namespace MWOPay\Core;

abstract class Curl
{

  /**
   * Get the value of email
   *
   * @return  string
   */
  private $email;

  /**
   * Get the value of token
   *
   * @return  string
   */
  private $token;

  public function __construct($credentials = [])
  {
    $this->email = $credentials['email'];
    $this->token = $credentials['token'];

    return $this;
  }

  public function execute($data, $method, $endpoint)
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "http://api.mwopay.net" . $endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => array(
        "Accept: */*",
        "Accept-Encoding: gzip, deflate",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Content-Type: application/json",
        "MWOEmail: " . $this->email . "",
        "MWOToken: " . $this->token . ""
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      $array = array(
        'error'   => true,
        'code'    => "001",
        'message' => $err,
      );
      return json_encode($array);
    } else {
      return $response;
    }
  }
}
