<?php

namespace MWOItems\Core;

use PDO;

class Database
{
  protected $pdo;

  public function __construct($connection)
  {
    $driver = $connection['MSSQL_DRIVER'];
    $host   = $connection['MSSQL_HOST'];
    $port   = $connection['MSSQL_PORT'];
    $user   = $connection['MSSQL_USER'];
    $pass   = $connection['MSSQL_PASS'];
    $dbname = $connection['MSSQL_DBNAME'];

    switch ($driver) {
      case 'odbc':
        $dsn = "odbc:Driver={SQL Native Client};Server={$host};Port={$port};Database={$dbname}; Uid={$user};Pwd={$pass};";
        $pdo = new PDO($dsn);
        break;
      case 'sqlsrv':
        $dsn = "{$driver}:server={$host},{$port};Database={$dbname}";
        $pdo = new PDO($dsn, $user, $pass);
        break;
      case 'dblib':
        $dsn = "{$driver}:host={$host}:{$port};dbname={$dbname}";
        $pdo = new PDO($dsn, $user, $pass);
        break;

      default:
        $dsn = "{$driver}:server={$host},{$port};Database={$dbname}";
        $pdo = new PDO($dsn, $user, $pass);
        break;
    }

    $this->pdo = $pdo;
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $this->pdo;
  }

  public function getSerial()
  {
    try {
      $data = $this->pdo->prepare("exec WZ_GetItemSerial");
      $data->execute();

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public function getancient($section, $index)
  {
    try {
      $data = $this->pdo->prepare("SELECT * FROM mwo_items_ancients WHERE section = :section AND index = :index");
      $data->execute(array(
        ":section" => $section,
        ":index"   => $index,
      ));

      $row = $data->fetch(PDO::FETCH_ASSOC);

      return $row;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}
