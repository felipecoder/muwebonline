<?php

namespace App\Database;

use PDO;

abstract class Connection
{
	protected $pdo;

	public function __construct()
	{
		$driver = getenv('MSSQL_DRIVER');
		$host   = getenv('MSSQL_HOST');
		$port   = getenv('MSSQL_PORT');
		$user   = getenv('MSSQL_USER');
		$pass   = getenv('MSSQL_PASS');
		$dbname = getenv('MSSQL_DBNAME');

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
				$dsn = "{$driver}:host={$host},{$port};dbname={$dbname}";
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
}
