<?php

namespace App\Database;

use App\Database\Connection;
use PDO;
use PDOException;

class NewPasswordDatabase extends Connection
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

  public function updatePassword($password, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET memb__pwd = :memb__pwd WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb__pwd' => $password,
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updatePasswordMD5($password, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET memb__pwd = dbo.MWO_hashmd5('{$memb___id}', '{$password}') WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getConfig($type)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_configs WHERE type = :type");
      $data->execute(array(':type' => $type));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row['data'];
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
