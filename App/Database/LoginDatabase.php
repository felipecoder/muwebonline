<?php

namespace App\Database;

use App\Database\Connection;
use App\Models\LoginModel;
use PDO;
use PDOException;

class LoginDatabase extends Connection
{

	private $db;

	function __construct()
	{
		parent::__construct();

		$this->db  = $this->pdo;
	}

	public function login(LoginModel $model)
	{
		$username = $model->getUsername();

		try {
			$data = $this->db->prepare("SELECT * FROM MEMB_INFO WHERE memb___id = :memb___id");
			$data->execute(array(':memb___id' => $username));

			$row = $data->fetch(PDO::FETCH_ASSOC);

			return $row;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}
}
