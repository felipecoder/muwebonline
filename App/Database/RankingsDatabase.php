<?php

namespace App\Database;

use App\Database\Connection;
use PDO;
use PDOException;

class RankingsDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getRankings()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_rankings");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getRankingInfo($link)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_rankings WHERE link = :link");
      $data->execute(array(':link' => $link));

      $rows = $data->fetch(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function countRanking($database, $table)
  {
    try {
      $query = "SELECT * FROM $database.dbo.$table";
      $data = $this->db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $data->execute();

      $data->rowCount();

      return  $data->rowCount();
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function createRanking($table, $column, $custom, $init = 0, $limit = 10)
  {
    $first = (($init - 1) * $limit + 1);
    $end = ($init * $limit);
    try {
      $data = $this->db->prepare("SELECT $custom, $column as score, * FROM (SELECT ROW_NUMBER() OVER (ORDER BY $column DESC) AS position, * FROM $table) $table WHERE position BETWEEN $first AND $end ORDER BY $column DESC");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
