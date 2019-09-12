<?php

namespace App\Database;

use PDO;
use PDOException;

class DefaultDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getMenus($parentid)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_menus WHERE status = 1 AND parentid = :parentid");
      $data->execute(array(':parentid' => $parentid));

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getTotalOnline()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM MEMB_STAT WHERE Connectstat = 1");
      $data->execute();

      return $data->rowCount();
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getStaff()
  {
    try {
      $data = $this->db->prepare("SELECT Character.Name, MEMB_STAT.ConnectStat FROM Character JOIN MEMB_STAT ON (Character.AccountID = MEMB_STAT.memb___id) WHERE Character.CtlCode = 8 OR Character.CtlCode = 32");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getUser($username)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM MEMB_INFO WHERE memb___id = :memb___id");
      $data->execute(array(':memb___id' => $username));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
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

  public function createRanking($database, $table, $column, $custom)
  {
    try {
      $data = $this->db->prepare("SELECT TOP 5 $custom, $column as ranking FROM $database.dbo.$table ORDER BY $column DESC");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getEvents()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_events");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCoinsConfigs()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_coins");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCoinsUser($database, $table, $column, $memb___id)
  {
    try {
      $data = $this->db->prepare("SELECT $column as coins FROM $database.dbo.$table WHERE memb___id = :memb___id");
      $data->execute(array(':memb___id' => $memb___id));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row['coins'];
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getSlides()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_slides");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getKingOfMu()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_kingofmu");
      $data->execute();

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCharacterKingManual($database, $table, $name)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM $database.dbo.$table WHERE Name = :name");
      $data->execute(array(':name' => $name));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCharacterKingAuto($database, $table, $custom, $orderby)
  {
    try {
      $data = $this->db->prepare("SELECT TOP 1 $custom FROM $database.dbo.$table ORDER BY $orderby");
      $data->execute();

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
