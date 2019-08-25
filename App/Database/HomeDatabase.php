<?php

namespace App\Database;

use App\Database\Connection;
use PDO;
use PDOException;

class HomeDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getNews()
  {
    try {
      $data = $this->db->prepare("SELECT TOP 5 * FROM mwo_news ORDER BY ID DESC");
      $data->execute();

      $row = $data->fetchAll(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
