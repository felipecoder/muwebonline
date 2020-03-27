<?php

namespace App\Database;

use PDO;
use PDOException;

class RegisterDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function register($post, $username)
  {
    try {
      $data = $this->db->prepare("INSERT INTO MEMB_INFO (memb___id,memb__pwd,memb_name,sno__numb,post_code,addr_info,addr_deta,mail_addr,phon_numb,job__code,appl_days,modi_days,out__days,true_days,mail_chek,bloc_code,ctl1_code) 
      VALUES (:username, :password, :nick,:personalid,NULL,NULL,NULL,:email,NULL,'1','2003-11-23','2003-11-23','2003-11-23','2003-11-23','0','0','1')");
      $data->execute(array(
        ':username'   => $username,
        ':password'   => $post['password'],
        ':nick'       => $post['nick'],
        ':personalid' => '111111' . $post['personalid'],
        ':email'      => $post['email']
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function checkUsername($username)
  {
    try {
      $data = $this->db->prepare("SELECT memb___id FROM MEMB_INFO WHERE memb___id = :memb___id");
      $data->execute(array(':memb___id' => $username));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      $check = (!empty($row['memb___id'])) ? true : false;

      return $check;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function checkEmail($email)
  {
    try {
      $data = $this->db->prepare("SELECT mail_addr FROM MEMB_INFO WHERE mail_addr = :mail_addr");
      $data->execute(array(':mail_addr' => $email));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      $check = (!empty($row['mail_addr'])) ? true : false;

      return $check;
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

  public function updateToken($token, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET mwo_token = :mwo_token WHERE memb___id = :memb___id");
      $data->execute(array(
        ':mwo_token' => $token,
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updateMailCheck($memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET mail_chek = 1 WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getVipsConfigs()
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_vips");
      $data->execute();

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getVipInfo($ID)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_vips WHERE ID = :ID");
      $data->execute(array(':ID' => $ID));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function insertVip($database, $table, $column_level, $column_days, $memb___id, $level, $days, $type)
  {
    switch ($type) {
      case 0:
        $datetime = strtotime("+ " . $days . " days");
        break;

      case 1:
        $datetime = "DATEADD(day, " . $days . ", getdate())";
        break;

      case 2:
        $datetime = $days;
        break;

      default:
        $datetime = "DATEADD(day, " . $days . ", getdate())";
        break;
    }

    try {
      $data = $this->db->prepare("UPDATE $database.dbo.$table SET $column_level = :level, $column_days = :days WHERE memb___id = :memb___id");
      $data->execute(array(
        ':level'     => $level,
        ':days'      => $datetime,
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updateCredits($credits, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET mwo_credits = :credits WHERE memb___id = :memb___id");
      $data->execute(array(
        ':credits'   => $credits,
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

  public function inserVirCurrInfo($username, $nick)
  {
    try {
      $data = $this->db->prepare("INSERT INTO VI_CURR_INFO (ends_days, chek_code, used_time, memb___id, memb_name, memb_guid, sno__numb, Bill_Section, Bill_value, Bill_Hour, Surplus_Point, Surplus_Minute, Increase_Days )  VALUES (:ends_days, 1 , 1234, :username, :nick, 1, '7', '6', '3', '6', '6', :data, '0')");
      $data->execute(array(
        ':ends_days' => date("Y"),
        ':username'  => $username,
        ':nick'      => $nick,
        ':data'      => (new \DateTime())->format('Y-m-d H:g:i'),
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
