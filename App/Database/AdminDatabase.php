<?php

namespace App\Database;

use App\Database\Connection;
use App\Models\AdminModel;
use PDO;
use PDOException;

class AdminDatabase extends Connection
{

	private $db;

	function __construct()
	{
		parent::__construct();

		$this->db  = $this->pdo;
	}

	public function login(AdminModel $model)
	{
		$username = $model->getUsername();

		try {
			$data = $this->db->prepare("SELECT * FROM mwo_accesspanel WHERE username = :username");
			$data->execute(array(':username' => $username));

			$row = $data->fetch(PDO::FETCH_ASSOC);

			return $row;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getUser($username)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_accesspanel WHERE username = :username");
			$data->execute(array(':username' => $username));

			$row = $data->fetch(PDO::FETCH_ASSOC);

			return $row;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getAccounts()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM MEMB_INFO");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getAccountInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM MEMB_INFO WHERE memb_guid = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editAccount($post, $memb_guid)
	{
		try {
			$data = $this->db->prepare("UPDATE MEMB_INFO SET memb_name = :memb_name, memb__pwd = :memb__pwd, mail_addr = :mail_addr, tel__numb = :tel__numb, mwo_credits = :mwo_credits WHERE memb_guid = :memb_guid");
			$data->execute(array(
				':memb_name'   => $post['memb_name'],
				':memb__pwd'   => $post['memb__pwd'],
				':mail_addr'   => $post['mail_addr'],
				':tel__numb'   => (empty($post['tel__numb'])) ? NULL : $post['tel__numb'],
				':mwo_credits' => $post['mwo_credits'],
				':memb_guid'   => $memb_guid,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteAccount($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM MEMB_INFO WHERE memb_guid = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteAccountCharacter($account)
	{
		try {
			$data = $this->db->prepare("DELETE FROM AccountCharacter WHERE Id = :account");
			$data->execute(array(':account' => $account));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteWarehouse($account)
	{
		try {
			$data = $this->db->prepare("DELETE FROM warehouse WHERE AccountID = :account");
			$data->execute(array(':account' => $account));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteExtWarehouse($account)
	{
		try {
			$data = $this->db->prepare("DELETE FROM ExtWarehouse WHERE AccountID = :account");
			$data->execute(array(':account' => $account));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getCharactersAccount($account)
	{
		try {
			$data = $this->db->prepare("SELECT Name FROM Character WHERE AccountID = :account");
			$data->execute(array(':account' => $account));

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteCharacter($Name)
	{
		try {
			$data = $this->db->prepare("DELETE FROM Character WHERE Name = :Name");
			$data->execute(array(':Name' => $Name));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getGuildMaster($Name)
	{
		try {
			$data = $this->db->prepare("SELECT G_Name FROM Guild WHERE G_Master = :Name");
			$data->execute(array(':Name' => $Name));

			$row = $data->fetch(PDO::FETCH_ASSOC);

			return $row;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteGuildMember($Name)
	{
		try {
			$data = $this->db->prepare("DELETE FROM GuildMember WHERE Name = :Name");
			$data->execute(array(':Name' => $Name));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteGuild($Name)
	{
		try {
			$data = $this->db->prepare("DELETE FROM Guild WHERE G_Name = :Name");
			$data->execute(array(':Name' => $Name));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteGuildMembers($Name)
	{
		try {
			$data = $this->db->prepare("DELETE FROM GuildMember WHERE G_Name = :Name");
			$data->execute(array(':Name' => $Name));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getCharacters()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM Character");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getCharacterInfo($Name)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM Character WHERE Name = :Name");
			$data->execute(array(':Name' => $Name));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editCharacter($post, $columnresets, $name)
	{
		try {
			$data = $this->db->prepare("UPDATE Character SET Class = :Class, cLevel = :cLevel, LevelUpPoint = :LevelUpPoint, Experience = :Experience, Strength = :Strength, Dexterity = :Dexterity, Vitality = :Vitality, Energy = :Energy, Leadership = :Leadership, Money = :Money, MapNumber = :MapNumber, MapPosX = :MapPosX, MapPosY = :MapPosY, PkCount = :PkCount, PkLevel = :PkLevel, PkTime = :PkTime, CtlCode = :CtlCode, {$columnresets} = :Resets WHERE Name = :Name");
			$data->execute(array(
				':Class'        => $post['Class'],
				':cLevel'       => $post['cLevel'],
				':LevelUpPoint' => $post['LevelUpPoint'],
				':Experience'   => $post['Experience'],
				':Strength'     => $post['Strength'],
				':Dexterity'    => $post['Dexterity'],
				':Vitality'     => $post['Vitality'],
				':Energy'       => $post['Energy'],
				':Leadership'   => $post['Leadership'],
				':Money'        => $post['Money'],
				':MapNumber'    => $post['MapNumber'],
				':MapPosX'      => $post['MapPosX'],
				':MapPosY'      => $post['MapPosY'],
				':PkCount'      => $post['PkCount'],
				':PkLevel'      => $post['PkLevel'],
				':PkTime'       => $post['PkTime'],
				':CtlCode'      => $post['CtlCode'],
				':Resets'       => $post['Resets'],
				':Name'         => $name,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getMenus()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_menus");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getMenusParentID($parentid)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_menus WHERE parentid = :parentid");
			$data->execute(array(':parentid' => $parentid));

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getMenuInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_menus WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertMenu($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_menus (name, label, link, parentid, status) VALUES (:name, :label, :link, :parentid, :status)");
			$data->execute(array(
				':name'     => $post['name'],
				':label'    => (empty($post['label'])) ? NULL : $post['label'],
				':link'     => $post['link'],
				':parentid' => $post['parentid'],
				':status'   => $post['status'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editMenu($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_menus SET name = :name, label = :label, link = :link, parentid = :parentid, status = :status WHERE ID = :ID");
			$data->execute(array(
				':name'     => $post['name'],
				':label'    => (empty($post['label'])) ? NULL : $post['label'],
				':link'     => $post['link'],
				':parentid' => $post['parentid'],
				':status'   => $post['status'],
				':ID'       => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteMenu($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_menus WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getConfigs()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_configs");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

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

	public function getConfigInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_configs WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editConfig($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_configs SET data = :data WHERE ID = :ID");
			$data->execute(array(
				':data'     => $post,
				':ID'       => $ID,
			));

			return 'OK';
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

	public function getRankingInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_rankings WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertRanking($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_rankings ([name], [database], [table], [column], [custom]) VALUES (:name, :database, :table, :column, :custom)");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':custom'   => (empty($post['custom'])) ? NULL : $post['custom'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editRanking($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_rankings SET [name] = :name, [database] = :database, [table] = :table, [column] = :column, [custom] = :custom WHERE ID = :ID");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':custom'   => (empty($post['custom'])) ? NULL : $post['custom'],
				':ID'       => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteRanking($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_rankings WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getNews()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_news");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getNewInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_news WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertNew($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_news ([title], [content], [image], [tag]) VALUES (:title, :content, :image, :tag)");
			$data->execute(array(
				':title'   => $post['title'],
				':content' => $post['content'],
				':image'   => (empty($post['image'])) ? NULL : $post['image'],
				':tag'     => (empty($post['tag'])) ? NULL : $post['tag'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editNew($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_news SET [title] = :title, [content] = :content, [image] = :image, [tag] = :tag WHERE ID = :ID");
			$data->execute(array(
				':title'   => $post['title'],
				':content' => $post['content'],
				':image'   => (empty($post['image'])) ? NULL : $post['image'],
				':tag'     => (empty($post['tag'])) ? NULL : $post['tag'],
				':ID'      => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteNew($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_news WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getPages()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_pages");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getPageInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_pages WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertPage($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_pages ([title], [link], [content]) VALUES (:title, :link, :content)");
			$data->execute(array(
				':title'   => $post['title'],
				':link'    => $post['link'],
				':content' => $post['content'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editPage($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_pages SET [title] = :title, [link] = :link, [content] = :content WHERE ID = :ID");
			$data->execute(array(
				':title'   => $post['title'],
				':link'    => $post['link'],
				':content' => $post['content'],
				':ID'      => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deletePage($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_pages WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
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

	public function getEventInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_events WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertEvent($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_events ([name], [time]) VALUES (:name, :time)");
			$data->execute(array(
				':name' => $post['name'],
				':time' => $post['time'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editEvent($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_events SET [name] = :name, [time] = :time WHERE ID = :ID");
			$data->execute(array(
				':name' => $post['name'],
				':time' => $post['time'],
				':ID'   => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteEvent($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_events WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getCoins()
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

	public function insertCoin($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_coins ([name], [database], [table], [column], [price]) VALUES (:name, :database, :table, :column, :price)");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':price'    => $post['price'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editCoin($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_coins SET [name] = :name, [database] = :database, [table] = :table, [column] = :column, [price] = :price WHERE ID = :ID");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':price'    => $post['price'],
				':ID'       => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteCoin($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_coins WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getVips()
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

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertVip($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_vips ([name], [database], [table], [column_level], [column_days], [level], [prices], [days]) VALUES (:name, :database, :table, :column_level, :column_days, :level, :prices, :days)");
			$data->execute(array(
				':name'         => $post['name'],
				':database'     => $post['database'],
				':table'        => $post['table'],
				':column_level' => $post['column_level'],
				':column_days'  => $post['column_days'],
				':level'        => $post['level'],
				':prices'       => $post['prices'],
				':days'         => $post['days'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editVip($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_vips SET [name] = :name, [database] = :database, [table] = :table, [column_level] = :column_level, [column_days] = :column_days, [level] = :level, [prices] = :prices, [days] = :days WHERE ID = :ID");
			$data->execute(array(
				':name'         => $post['name'],
				':database'     => $post['database'],
				':table'        => $post['table'],
				':column_level' => $post['column_level'],
				':column_days'  => $post['column_days'],
				':level'        => $post['level'],
				':prices'       => $post['prices'],
				':days'         => $post['days'],
				':ID'           => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteVips($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_vips WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getAccessPages()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_accesspages");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getAccessPageInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_accesspages WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertAccessPage($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_accesspages ([name], [access], [blocked]) VALUES (:name, :access, :blocked)");
			$data->execute(array(
				':name'    => $post['name'],
				':access'  => $post['access'],
				':blocked' => $post['blocked'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editAccessPage($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_accesspages SET [name] = :name, [access] = :access, [blocked] = :blocked WHERE ID = :ID");
			$data->execute(array(
				':name'    => $post['name'],
				':access'  => $post['access'],
				':blocked' => $post['blocked'],
				':ID'      => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteAccessPage($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_accesspages WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
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

	public function getSlideInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_slides WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertSlide($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_slides (name, label, link, image, status) VALUES (:name, :label, :link, :image, :status)");
			$data->execute(array(
				':name'   => $post['name'],
				':label'  => (empty($post['label'])) ? NULL : $post['label'],
				':link'   => $post['link'],
				':image'  => $post['image'],
				':status' => $post['status'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editSlide($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_slides SET name = :name, label = :label, link = :link, image = :image, status = :status WHERE ID = :ID");
			$data->execute(array(
				':name'   => $post['name'],
				':label'  => (empty($post['label'])) ? NULL : $post['label'],
				':link'   => $post['link'],
				':image'  => $post['image'],
				':status' => $post['status'],
				':ID'     => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteSlide($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_slides WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
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

	public function editKingOfMu($post)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_kingofmu SET [database] = :database, [table] = :table, [mode] = :mode, [custom] = :custom, [orderby] = :orderby, [character] = :character, [wins] = :wins");
			$data->execute(array(
				':database'  => $post['database'],
				':table'     => $post['table'],
				':mode'      => (empty($post['mode'])) ? 'auto' : $post['mode'],
				':custom'    => (empty($post['custom'])) ? NULL : $post['custom'],
				':orderby'   => (empty($post['orderby'])) ? NULL : $post['orderby'],
				':character' => (empty($post['character'])) ? NULL : $post['character'],
				':wins'      => (empty($post['wins'])) ? NULL : $post['wins'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}
}
