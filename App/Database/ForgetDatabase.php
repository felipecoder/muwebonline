<?php

namespace App\Database;

use App\Database\Connection;
use PDO;
use PDOException;

class ForgetDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getUser($memb___id)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM MEMB_INFO WHERE memb___id = :memb___id");
      $data->execute(array(
        'memb___id' => $memb___id,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updateToken($mwo_token, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET mwo_token = :mwo_token WHERE memb___id = :memb___id");
      $data->execute(array(
        ':mwo_token' => $mwo_token,
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
