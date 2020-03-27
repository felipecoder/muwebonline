<?php

namespace App\Database;

use App\Database\Connection;
use App\Models\DashboardModel;
use PDO;
use PDOException;

class DashboardDatabase extends Connection
{

  private $db;

  function __construct()
  {
    parent::__construct();

    $this->db  = $this->pdo;
  }

  public function getUser(DashboardModel $model)
  {
    $memb___id = $model->getUsername();

    try {
      $data = $this->db->prepare("SELECT * FROM MEMB_INFO WHERE memb___id = :memb___id");
      $data->execute(array(':memb___id' => $memb___id));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
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

  public function getCharacters($accountid)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM Character WHERE AccountID = :AccountID");
      $data->execute(array(':AccountID' => $accountid));

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

      $rows = $data->fetch(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function removeCredits($credits, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET mwo_credits = mwo_credits - :credits WHERE memb___id = :memb___id");
      $data->execute(array(
        'credits'   => $credits,
        'memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function acrescentCredits($credits, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET mwo_credits = mwo_credits + :credits WHERE memb___id = :memb___id");
      $data->execute(array(
        'credits'   => $credits,
        'memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function buyVip($database, $table, $column_level, $column_days, $memb___id, $level, $days, $type)
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

  public function updateVip($database, $table, $column_level, $column_days, $memb___id, $level, $days, $type, $user_days)
  {
    switch ($type) {
      case 0:
        $datetime = strtotime(date("d-m-Y g:i a", $user_days) . " + " . $days . " days");
        break;

      case 1:
        $datetime = "DATEADD(day, " . $days . ", $user_days)";
        break;

      case 2:
        $datetime = $days + $user_days;
        break;

      default:
        $datetime = "DATEADD(day, " . $days . ", $user_days)";
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

  public function getCoinInfo($ID)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_coins WHERE ID = :ID");
      $data->execute(array(':ID' => $ID));

      $rows = $data->fetch(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updateCoin($database, $table, $column, $price, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE $database.dbo.$table SET $column = $column + :price WHERE memb___id = :memb___id");
      $data->execute(array(
        ':price'     => $price,
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updatePassword($memb__pwd, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET [memb__pwd] = :memb__pwd WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb__pwd' => $memb__pwd,
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updateData($post, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET [memb_name] = :memb_name, [tel__numb] = :tel__numb WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb_name' => $post['memb_name'],
        ':tel__numb' => $post['tel__numb'],
        ':memb___id' => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updatePersonalID($post, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE MEMB_INFO SET [sno__numb] = :personalid WHERE memb___id = :memb___id");
      $data->execute(array(
        ':personalid' => '111111' . $post['personalid'],
        ':memb___id'  => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCustomer($memb___id)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_customers WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb___id' => $memb___id,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function createCustomer($post, $memb___id)
  {
    try {
      $data = $this->db->prepare("INSERT INTO mwo_customers ([memb___id], [name], [email], [cpf], [street], [number], [complement], [district], [city], [state], [postalcode]) VALUES (:memb___id, :name, :email, :cpf, :street, :number, :complement, :district, :city, :state, :postalcode)");
      $data->execute(array(
        ':memb___id'  => $memb___id,
        ':name'       => $post['name'],
        ':email'      => $post['email'],
        ':cpf'        => preg_replace("/[^0-9]/", "", trim($post['cpf'])),
        ':street'     => $post['street'],
        ':number'     => preg_replace("/[^0-9]/", "", trim($post['number'])),
        ':complement' => $post['complement'],
        ':district'   => $post['district'],
        ':city'       => $post['city'],
        ':state'      => $post['state'],
        ':postalcode' => preg_replace("/[^0-9]/", "", trim($post['postalcode'])),
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function updateCustomer($post, $memb___id)
  {
    try {
      $data = $this->db->prepare("UPDATE mwo_customers SET [name] = :name, [email] = :email, [cpf] = :cpf, [street] = :street, [number] = :number, [complement] = :complement, [district] = :district, [city] = :city, [state] = :state, [postalcode] = :postalcode WHERE memb___id = :memb___id");
      $data->execute(array(
        ':name'       => $post['name'],
        ':email'      => $post['email'],
        ':cpf'        => preg_replace("/[^0-9]/", "", trim($post['cpf'])),
        ':street'     => $post['street'],
        ':number'     => preg_replace("/[^0-9]/", "", trim($post['number'])),
        ':complement' => $post['complement'],
        ':district'   => $post['district'],
        ':city'       => $post['city'],
        ':state'      => $post['state'],
        ':postalcode' => preg_replace("/[^0-9]/", "", trim($post['postalcode'])),
        ':memb___id'  => $memb___id,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getTickets($username)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_tickets WHERE username = :username");
      $data->execute(array(':username' => $username));

      $rows = $data->fetchAll(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getTicketInfo($ID)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_tickets WHERE ID = :ID");
      $data->execute(array(':ID' => $ID));

      $rows = $data->fetch(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function createTicket($post, $image, $username)
  {
    try {
      $data = $this->db->prepare("INSERT INTO mwo_tickets ([subject], [message], [username], [image]) VALUES (:subject, :message, :username, :image)");
      $data->execute(array(
        ':subject'  => $post['subject'],
        ':message'  => $post['message'],
        ':username' => $username,
        ':image'    => $image,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getTicketAnswer($ticket_id)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_tickets_answers WHERE ticket_id = :ticket_id");
      $data->execute(array(':ticket_id' => $ticket_id));

      $rows = $data->fetch(PDO::FETCH_ASSOC);

      return $rows;
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

  public function getCharacter($column_reset, $accountid, $name)
  {
    try {
      $data = $this->db->prepare("SELECT $column_reset, Class, Money, cLevel, PKCount, LevelUpPoint, Strength, Dexterity, Vitality, Energy, Leadership, CtlCode FROM Character WHERE AccountID = :AccountID AND Name = :Name");
      $data->execute(array(
        ':AccountID' => $accountid,
        ':Name'      => $name,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCharacterNick($name)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM Character WHERE Name = :Name");
      $data->execute(array(
        ':Name' => $name,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getCharacterGuild($name)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM GuildMember WHERE Name = :Name");
      $data->execute(array(
        ':Name' => $name,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function checkOnlineAccount($memb___id)
  {
    try {
      $data = $this->db->prepare("SELECT ConnectStat FROM MEMB_STAT WHERE memb___id = :memb___id");
      $data->execute(array(
        ':memb___id' => $memb___id,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function resetCharacter($columns, $post, $Name)
  {
    $reset      = $columns[0]['value'];
    $resetday   = $columns[1]['value'];
    $resetweek  = $columns[2]['value'];
    $resetmonth = $columns[3]['value'];
    try {
      $data = $this->db->prepare("UPDATE Character SET Experience = 0 , {$reset} = {$reset} + 1, {$resetday} = {$resetday} + 1, {$resetweek} = {$resetweek} + 1, {$resetmonth} = {$resetmonth} + 1, cLevel = :cLevel, MapNumber = :MapNumber, MapPosX = :MapPosX, MapPosY = :MapPosY, Money = Money - :Money, Strength = 30 , Dexterity = 30 , Energy = 30 , Vitality = 30, LeaderShip = 30, LevelUpPoint = :LevelUpPoint WHERE Name = :Name");
      $data->execute(array(
        ':cLevel'       => $post['cLevel'],
        ':MapNumber'    => $post['MapNumber'],
        ':MapPosX'      => $post['MapPosX'],
        ':MapPosY'      => $post['MapPosY'],
        ':Money'        => $post['Money'],
        ':LevelUpPoint' => $post['LevelUpPoint'],
        ':Name'         => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function masterresetCharacter($columns, $post, $Name)
  {
    $reset       = $columns[0]['value'];
    $masterreset = $columns[0]['value'];
    try {
      $data = $this->db->prepare("UPDATE Character SET Experience = 0 , {$reset} = {$reset} - :RequireResets, {$masterreset} = {$masterreset} + 1, cLevel = :cLevel, MapNumber = :MapNumber, MapPosX = :MapPosX, MapPosY = :MapPosY, Money = Money - :Money, Strength = 30 , Dexterity = 30 , Energy = 30 , Vitality = 30, LeaderShip = 30, LevelUpPoint = :LevelUpPoint WHERE Name = :Name");
      $data->execute(array(
        ':RequireResets' => $post['RequireResets'],
        ':cLevel'        => $post['cLevel'],
        ':MapNumber'     => $post['MapNumber'],
        ':MapPosX'       => $post['MapPosX'],
        ':MapPosY'       => $post['MapPosY'],
        ':Money'         => $post['Money'],
        ':LevelUpPoint'  => $post['LevelUpPoint'],
        ':Name'          => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function clearItens($Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET Inventory = NULL WHERE Name = :Name");
      $data->execute(array(
        ':Name' => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function clearMagics($Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET MagicList = NULL WHERE Name = :Name");
      $data->execute(array(
        ':Name' => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function clearQuests($Class, $Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET Quest = NULL, Class = :Class WHERE Name = :Name");
      $data->execute(array(
        ':Class' => $Class,
        ':Name'  => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function cleanPK($Money, $Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET PkCount = 0, PkLevel = 0, PkTime = 0, Money = Money - :Money WHERE Name = :Name");
      $data->execute(array(
        ':Money' => $Money,
        ':Name'  => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function changeNick($Money, $newname, $Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET Name = :newname, Money = Money - :Money WHERE Name = :Name");
      $data->execute(array(
        ':Money'   => $Money,
        ':newname' => $newname,
        ':Name'    => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getAccountCharacter($Id)
  {
    try {
      $data = $this->db->prepare("SELECT GameID1, GameID2, GameID3, GameID4, GameID5 FROM AccountCharacter WHERE Id = :Id");
      $data->execute(array(':Id' => $Id));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function editAccountCharacter($slot, $newnick, $Id)
  {
    try {
      $data = $this->db->prepare("UPDATE AccountCharacter SET {$slot} = :newnick WHERE Id = :Id");
      $data->execute(array(
        ':newnick' => $newnick,
        ':Id'      => $Id
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function deleteMasterSkillTree($Name)
  {
    try {
      $data = $this->db->prepare("DELETE FROM MasterSkillTree WHERE Name = :Name");
      $data->execute(array(
        ':Name' => $Name
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function changeClass($Money, $class, $Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET Class = :Class, Money = Money - :Money WHERE Name = :Name");
      $data->execute(array(
        ':Money' => $Money,
        ':Class' => $class,
        ':Name'  => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function resetQuets($Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET Quest = cast(REPLICATE(char(0xff),50) as varbinary(50)) WHERE Name = :Name");
      $data->execute(array(
        ':Name' => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function resetSkills($skills, $Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET MagicList = cast(REPLICATE(char(0xff),{$skills}) as varbinary({$skills})) WHERE Name = :Name");
      $data->execute(array(
        ':Name' => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getUserDefault($username)
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

  public function getAccessPageInfo($name)
  {
    try {
      $data = $this->db->prepare("SELECT * FROM mwo_accesspages WHERE name = :name");
      $data->execute(array(':name' => $name));

      $rows = $data->fetch(PDO::FETCH_ASSOC);

      return $rows;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function changeImage($Money, $mwo_image, $Name)
  {
    try {
      $data = $this->db->prepare("UPDATE Character SET mwo_image = :mwo_image, Money = Money - :Money WHERE Name = :Name");
      $data->execute(array(
        ':Money'     => $Money,
        ':mwo_image' => $mwo_image,
        ':Name'      => $Name,
      ));

      return 'OK';
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
