<?php

namespace App\Database;

use App\Database\Connection;
use PDO;
use PDOException;

class PagesDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getPages()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_pages");
      $data->execute();

      $row = $data->fetchAll(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getPageInfo($link)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_pages WHERE link = :link");
      $data->execute(array(':link' => $link));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
