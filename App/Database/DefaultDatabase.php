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
      $data = $this->db->prepare("SELECT count(*) FROM MEMB_STAT WHERE Connectstat = 1");
      $data->execute();

      return $data->fetchColumn();
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
      $data = $this->db->prepare("SELECT * FROM mwo_rankings_home");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function createRanking($database, $table, $column, $max, $custom)
  {
    try {
      $data = $this->db->prepare("SELECT TOP $max $custom, $column as ranking FROM $database.dbo.$table ORDER BY $column DESC");
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

  public function getCastleSiege()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_castlesiege");
      $data->execute();

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCastleSiegeManual($name)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM Guild WHERE G_Name = :name");
      $data->execute(array(':name' => $name));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getTotalMembersGuild($name)
  {
    try {
      $data = $this->db->prepare("SELECT count(*) FROM GuildMember WHERE G_Name = :name");
      $data->execute(array(':name' => $name));

      return $data->fetchColumn();
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCastleSiegeAuto()
  {
    try {
      $data = $this->db->prepare("SELECT cs.CASTLE_OCCUPY AS CASTLE_OCCUPY, cs.SIEGE_START_DATE AS SIEGE_START_DATE, cs.SIEGE_END_DATE AS SIEGE_END_DATE, cs.OWNER_GUILD AS G_Name, g.G_Master AS G_Master, g.G_Mark AS G_Mark, g.G_Score AS G_Score,(SELECT count(1) FROM GuildMember WHERE G_Name = cs.OWNER_GUILD) AS TotalMembers, c.mwo_image AS mwo_image FROM MuCastle_DATA cs LEFT JOIN Guild g ON g.G_Name = cs.OWNER_GUILD LEFT JOIN Character c on c.Name = g.G_Master");
      $data->execute();

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getImageCharacter($name)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM Character WHERE Name = :name");
      $data->execute(array(':name' => $name));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
