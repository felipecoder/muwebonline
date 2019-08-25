<?php

namespace App\Database;

use App\Database\Connection;
use PDO;
use PDOException;

class ActiveDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getUser($memb___id, $mwo_token)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM MEMB_INFO WHERE memb___id = :memb___id AND mwo_token = :mwo_token");
      $data->execute(array(
        'memb___id' => $memb___id,
        'mwo_token' => $mwo_token,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updateMailCheck($memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET mail_chek = 1, mwo_token = NULL WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
