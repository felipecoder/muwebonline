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

	public function getAccessPanel()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_accesspanel");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getAccessPanelInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_accesspanel WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertAccessPanel($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_accesspanel ([username], [password], [access], [ipaddress]) VALUES (:username, :password, :access, :ipaddress)");
			$data->execute(array(
				':username'  => $post['username'],
				':password'  => password_hash($post['password'], PASSWORD_DEFAULT),
				':access'    => $post['access'],
				':ipaddress' => $post['ipaddress'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editAccessPanel($post, $ID)
	{
		$account_data = $this->getAccessPanelInfo($ID);
		try {
			$data = $this->db->prepare("UPDATE mwo_accesspanel SET [username] = :username, [password] = :password, [access] = :access, [ipaddress] = :ipaddress WHERE ID = :ID");
			$data->execute(array(
				':username'  => $post['username'],
				':password'  => ($post['password'] == NULL) ? $account_data['password'] : password_hash($post['password'], PASSWORD_DEFAULT),
				':access'    => $post['access'],
				':ipaddress' => $post['ipaddress'],
				':ID'        => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteAccessPanel($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_accesspanel WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
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
			$data = $this->db->prepare("INSERT INTO mwo_rankings ([name], [database], [table], [column], [link], [max]) VALUES (:name, :database, :table, :column, :link, :max)");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':max'   		=> $post['max'],
				':link'     => $post['link'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editRanking($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_rankings SET [name] = :name, [database] = :database, [table] = :table, [column] = :column, [max] = :max, [link] = :link WHERE ID = :ID");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':max'   		=> $post['max'],
				':link'     => $post['link'],
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

	public function getRankingsHome()
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

	public function getRankingHomeInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_rankings_home WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertRankingHome($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_rankings_home ([name], [database], [table], [column], [custom], [max]) VALUES (:name, :database, :table, :column, :custom, :max)");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':max'   		=> $post['max'],
				':custom'   => (empty($post['custom'])) ? NULL : $post['custom'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editRankingHome($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_rankings_home SET [name] = :name, [database] = :database, [table] = :table, [column] = :column, [max] = :max, [custom] = :custom WHERE ID = :ID");
			$data->execute(array(
				':name'     => $post['name'],
				':database' => $post['database'],
				':table'    => $post['table'],
				':column'   => $post['column'],
				':max'   		=> $post['max'],
				':custom'   => (empty($post['custom'])) ? NULL : $post['custom'],
				':ID'       => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteRankingHome($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_rankings_home WHERE ID = :ID");
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
			$data = $this->db->prepare("UPDATE mwo_kingofmu SET [active] = :active, [database] = :database, [table] = :table, [mode] = :mode, [custom] = :custom, [orderby] = :orderby, [character] = :character, [wins] = :wins");
			$data->execute(array(
				':active'    => $post['active'],
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

	public function getTickets()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_tickets");
			$data->execute();

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

	public function deleteTicket($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_tickets WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

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

	public function insertTicketAnswer($post, $username, $ticket_id)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_tickets_answers (message, username, ticket_id) VALUES (:message, :username, :ticket_id)");
			$data->execute(array(
				':message'   => $post['message'],
				':username'  => $username,
				':ticket_id' => $ticket_id
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editTicketAnswer($post, $username, $ticket_id)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_tickets_answers SET message = :message, username = :username, date = CURRENT_TIMESTAMP WHERE ticket_id = :ticket_id");
			$data->execute(array(
				':message'   => $post['message'],
				':username'  => $username,
				':ticket_id' => $ticket_id,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteTicketAnswer($ticket_id)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_tickets_answers WHERE ticket_id = :ticket_id");
			$data->execute(array(':ticket_id' => $ticket_id));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemsAncients()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_ancients");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemAncientInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_ancients WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertItemAncient($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_items_ancients (section, index_, ancient, name) VALUES (:section, :index, :ancient, :name)");
			$data->execute(array(
				':section' => $post['section'],
				':index'   => $post['index'],
				':ancient' => $post['ancient'],
				':name'    => $post['name'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editItemAncient($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_items_ancients SET section = :section, index_ = :index, ancient = :ancient, name = :name WHERE ID = :ID");
			$data->execute(array(
				':section' => $post['section'],
				':index'   => $post['index'],
				':ancient' => $post['ancient'],
				':name'    => $post['name'],
				':ID'      => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteItemAncient($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_items_ancients WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemsHamornys()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_jewelofharmony");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemHamornyInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_jewelofharmony WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertItemHamorny($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_items_jewelofharmony (section, index_, level, harmonys) VALUES (:section, :index, :level, :harmonys)");
			$data->execute(array(
				':section'  => $post['section'],
				':index'    => $post['index'],
				':level'    => $post['level'],
				':harmonys' => $post['harmonys'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editItemHamorny($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_items_jewelofharmony SET section = :section, index_ = :index, level = :level, harmonys = :harmonys WHERE ID = :ID");
			$data->execute(array(
				':section'  => $post['section'],
				':index'    => $post['index'],
				':level'    => $post['level'],
				':harmonys' => $post['harmonys'],
				':ID'       => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteItemHamorny($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_items_jewelofharmony WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemsOptions()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_options");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemOptionInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_options WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertItemOption($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_items_options (index_, optionindex, value, minrange, maxrange, skill, luck, [option], newoption, name) VALUES (:index, :optionindex, :value, :minrange, :maxrange, :skill, :luck, :option, :newoption, :name)");
			$data->execute(array(
				':index'       => $post['index'],
				':optionindex' => $post['optionindex'],
				':value'       => $post['value'],
				':minrange'    => $post['minrange'],
				':maxrange'    => $post['maxrange'],
				':skill'       => $post['skill'],
				':luck'        => $post['luck'],
				':option'      => $post['option'],
				':newoption'   => $post['newoption'],
				':name'        => $post['name'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editItemOption($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_items_options SET index_ = :index, optionindex = :optionindex, value = :value, minrange = :minrange, maxrange = :maxrange, skill = :skill, luck = :luck, [option] = :option, newoption = :newoption, name = :name WHERE ID = :ID");
			$data->execute(array(
				':index'       => $post['index'],
				':optionindex' => $post['optionindex'],
				':value'       => $post['value'],
				':minrange'    => $post['minrange'],
				':maxrange'    => $post['maxrange'],
				':skill'       => $post['skill'],
				':luck'        => $post['luck'],
				':option'      => $post['option'],
				':newoption'   => $post['newoption'],
				':name'        => $post['name'],
				':ID'          => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteItemOption($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_items_options WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemsSockets()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_sockets");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemSocketInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_sockets WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertItemSocket($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_items_sockets (section, index_, max, sockets) VALUES (:section, :index, :max, :sockets)");
			$data->execute(array(
				':section' => $post['section'],
				':index'   => $post['index'],
				':max'     => $post['max'],
				':sockets' => $post['sockets'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editItemSocket($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_items_sockets SET section = :section, index_ = :index, max = :max, sockets = :sockets WHERE ID = :ID");
			$data->execute(array(
				':section' => $post['section'],
				':index'   => $post['index'],
				':max'     => $post['max'],
				':sockets' => $post['sockets'],
				':ID'      => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteItemSocket($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_items_sockets WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemsRefines()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_refines");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemRefineInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_items_refines WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertItemRefine($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_items_refines (section, index_, options) VALUES (:section, :index, :options)");
			$data->execute(array(
				':section' => $post['section'],
				':index'   => $post['index'],
				':options' => $post['options'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editItemRefine($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_items_refines SET section = :section, index_ = :index, options = :options WHERE ID = :ID");
			$data->execute(array(
				':section' => $post['section'],
				':index'   => $post['index'],
				':options' => $post['options'],
				':ID'      => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteItemRefine($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_items_refines WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemsSkills()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_skills_name");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemSkillInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_skills_name WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertItemSkill($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_skills_name (index_, name) VALUES (:index, :name)");
			$data->execute(array(
				':index' => $post['index'],
				':name'  => $post['name'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editItemSkill($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_skills_name SET index_ = :index, name = :name WHERE ID = :ID");
			$data->execute(array(
				':index' => $post['index'],
				':name'  => $post['name'],
				':ID'    => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteItemSkill($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_skills_name WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getWebShops()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshops");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getWebShopsParentID($parentid)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshops WHERE parentid = :parentid");
			$data->execute(array(':parentid' => $parentid));

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getWebShopInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshops WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertWebShop($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_webshops (name, label, link, parentid, status, coin) VALUES (:name, :label, :link, :parentid, :status, :coin)");
			$data->execute(array(
				':name'     => $post['name'],
				':label'    => $post['label'],
				':link'     => $post['link'],
				':parentid' => $post['parentid'],
				':status'   => $post['status'],
				':coin'     => $post['coin'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editWebShop($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_webshops SET name = :name, label = :label, link = :link, parentid = :parentid, status = :status, coin = :coin WHERE ID = :ID");
			$data->execute(array(
				':name'     => $post['name'],
				':label'    => $post['label'],
				':link'     => $post['link'],
				':parentid' => $post['parentid'],
				':status'   => $post['status'],
				':coin'     => $post['coin'],
				':ID'       => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteWebShop($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_webshops WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getCategoriesWebShops()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshop_categories");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getCategoriesWebShopsParentID($parentid)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshop_categories WHERE parentid = :parentid");
			$data->execute(array(':parentid' => $parentid));

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getCategorieWebShopInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshop_categories WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertCategorieWebShop($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_webshop_categories (name, label, link, parentid, webshopid, status) VALUES (:name, :label, :link, :parentid, :webshopid, :status)");
			$data->execute(array(
				':name'      => $post['name'],
				':label'     => $post['label'],
				':link'      => $post['link'],
				':parentid'  => $post['parentid'],
				':webshopid' => $post['webshopid'],
				':status'    => $post['status'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editCategorieWebShop($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_webshop_categories SET name = :name, label = :label, link = :link, parentid = :parentid, webshopid = :webshopid, status = :status WHERE ID = :ID");
			$data->execute(array(
				':name'      => $post['name'],
				':label'     => $post['label'],
				':link'      => $post['link'],
				':parentid'  => $post['parentid'],
				':webshopid' => $post['webshopid'],
				':status'    => $post['status'],
				':ID'       => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteCategorieWebShop($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_webshop_categories WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemsWebShops()
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshop_items");
			$data->execute();

			$rows = $data->fetchAll(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function getItemWebShopInfo($ID)
	{
		try {
			$data = $this->db->prepare("SELECT * FROM mwo_webshop_items WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			$rows = $data->fetch(PDO::FETCH_ASSOC);

			return $rows;
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function insertItemWebShop($post)
	{
		try {
			$data = $this->db->prepare("INSERT INTO mwo_webshop_items (categoryid, section, index_, name, durability, width, height, skill, link, status, price, price_level, price_option, price_skill, price_luck, price_ancient, price_harmony, price_refine, price_socket, price_excellent, max_excellent, max_sockets, image, classes) VALUES (:categoryid, :section, :index_, :name, :durability, :width, :height, :skill, :link, :status, :price, :price_level, :price_option, :price_skill, :price_luck, :price_ancient, :price_harmony, :price_refine, :price_socket, :price_excellent, :max_excellent, :max_sockets, :image, :classes)");
			$data->execute(array(
				':categoryid'      => $post['categoryid'],
				':section'         => $post['section'],
				':index_'          => $post['index_'],
				':name'            => $post['name'],
				':durability'      => $post['durability'],
				':width'           => $post['width'],
				':height'          => $post['height'],
				':skill'           => $post['skill'],
				':link'            => $post['link'],
				':status'          => $post['status'],
				':price'           => $post['price'],
				':price_level'     => $post['price_level'],
				':price_option'    => $post['price_option'],
				':price_skill'     => $post['price_skill'],
				':price_luck'      => $post['price_luck'],
				':price_ancient'   => $post['price_ancient'],
				':price_harmony'   => $post['price_harmony'],
				':price_refine'    => $post['price_refine'],
				':price_socket'    => $post['price_socket'],
				':price_excellent' => $post['price_excellent'],
				':max_excellent'   => $post['max_excellent'],
				':max_sockets'     => $post['max_sockets'],
				':image'           => $post['image'],
				':classes'         => $post['classes'],
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function editItemsWebShop($post, $ID)
	{
		try {
			$data = $this->db->prepare("UPDATE mwo_webshop_items SET categoryid = :categoryid, section = :section, index_ = :index_, name = :name, durability = :durability, width = :width, height = :height, skill = :skill, link = :link, status = :status, price = :price, price_level = :price_level, price_option = :price_option, price_skill = :price_skill, price_luck = :price_luck, price_ancient = :price_ancient, price_harmony = :price_harmony, price_refine = :price_refine, price_socket = :price_socket, price_excellent = :price_excellent, max_excellent = :max_excellent, max_sockets = :max_sockets, image = :image, classes = :classes WHERE ID = :ID");
			$data->execute(array(
				':categoryid'      => $post['categoryid'],
				':section'         => $post['section'],
				':index_'          => $post['index_'],
				':name'            => $post['name'],
				':durability'      => $post['durability'],
				':width'           => $post['width'],
				':height'          => $post['height'],
				':skill'           => $post['skill'],
				':link'            => $post['link'],
				':status'          => $post['status'],
				':price'           => $post['price'],
				':price_level'     => $post['price_level'],
				':price_option'    => $post['price_option'],
				':price_skill'     => $post['price_skill'],
				':price_luck'      => $post['price_luck'],
				':price_ancient'   => $post['price_ancient'],
				':price_harmony'   => $post['price_harmony'],
				':price_refine'    => $post['price_refine'],
				':price_socket'    => $post['price_socket'],
				':price_excellent' => $post['price_excellent'],
				':max_excellent'   => $post['max_excellent'],
				':max_sockets'     => $post['max_sockets'],
				':image'           => $post['image'],
				':classes'         => $post['classes'],
				':ID'              => $ID,
			));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}

	public function deleteItemWebShop($ID)
	{
		try {
			$data = $this->db->prepare("DELETE FROM mwo_webshop_items WHERE ID = :ID");
			$data->execute(array(':ID' => $ID));

			return 'OK';
		} catch (PDOException $e) {
			return $e->getMessage();
		}
	}
}
