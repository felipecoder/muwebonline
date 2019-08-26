<?php

namespace App\Database;

use App\Database\Connection;
use PDO;
use PDOException;

class NewsDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getNewInfo($ID)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_news WHERE ID = :ID");
      $data->execute(array(':ID' => $ID));

      $rows = $data->fetch(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
