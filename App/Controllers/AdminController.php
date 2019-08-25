<?php

namespace App\Controllers;

use App\Database\AdminDatabase;
use App\Models\AdminModel;
use App\Views\ViewAdmin;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use Slim\Http\Response;
use DateTime;

class AdminController
{

	public function getHome(AdminModel $model, ViewAdmin $view, Response $response)
	{
		//Classes
		$data     = new AdminDatabase();

		$array = array(
			'title_page' => 'Dashboard',
		);

		return $view->getRender($array, 'home', $response);
	}

	public function getLogin(AdminModel $model, ViewAdmin $view, Response $response)
	{

		$array = array(
			'title_page' => 'Login',
		);

		return $view->getRender($array, 'login', $response);
	}

	public function postLogin(AdminModel $model, Response $response)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$login       = $data->login($model);
		$password    = $model->getPassword();
		$ipaddress   = $model->getIpaddress();
		$patch_admin = getenv('DIRADMIN');

		if (empty($login)) {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Usuário Inválido'
			);

			$messages->addMessage('response', $return);

			$values = array(
				'username'  => $model->getUsername(),
				'ipaddress' => $model->getIpaddress(),
				'message'   => 'Usuário Inválido'
			);

			$logger->addLoggerWarning("Error Login", $values);

			return $response->withRedirect("/{$patch_admin}/login");
			exit();
		} elseif (!password_verify($password, $login['password'])) {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Senha Inválida'
			);

			$messages->addMessage('response', $return);

			$values = array(
				'username'  => $model->getUsername(),
				'ipaddress' => $model->getIpaddress(),
				'message'   => 'Senha Inválida'
			);

			$logger->addLoggerWarning("Error Login", $values);

			return $response->withRedirect("/{$patch_admin}/login");
			exit();
		} elseif ($ipaddress != $login['ipaddress']) {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Você não pode fazer login com esse computador'
			);

			$messages->addMessage('response', $return);

			$values = array(
				'username'  => $model->getUsername(),
				'ipaddress' => $model->getIpaddress(),
				'message'   => 'IP Diferente'
			);

			$logger->addLoggerWarning("Error Login", $values);

			return $response->withRedirect("/{$patch_admin}/login");
			exit();
		} else {
			$_SESSION['loggedinadmin'] = true;
			$_SESSION['usernameadmin'] = $model->getUsername();
			$_SESSION['accessadmin']   = $model->getAccess();

			$return = array(
				'error'   => false,
				'success' => true,
				'message' => 'Logado com sucesso'
			);

			$messages->addMessage('response', $return);

			$values = array(
				'username'  => $model->getUsername(),
				'ipaddress' => $model->getIpaddress(),
				'message'   => 'Fez login no sistema'
			);

			$logger->addLoggerInfo("Login", $values);

			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getLogout(Response $response)
	{
		$patch_admin = getenv('DIRADMIN');

		unset($_SESSION['loggedinadmin'], $_SESSION['usernameadmin'], $_SESSION['accessadmin']);

		return $response->withRedirect("/{$patch_admin}/logout");
	}

	public function getAccounts(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$accounts = $data->getAccounts();

			$array = array(
				'title_page'    => 'Contas',
				'accounts_data' => $accounts,
				'page_type'     => 'list',
			);

			return $view->getRender($array, 'accounts', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$account_data = $data->getAccountInfo($id);

			if (empty($account_data)) {
				return $response->withRedirect("/{$patch_admin}/accounts/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Editar Conta',
				'account_data' => $account_data,
				'page_type'    => 'edit',
			);

			return $view->getRender($array, 'accounts', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$account_data = $data->getAccountInfo($id);

			if (empty($account_data)) {
				return $response->withRedirect("/{$patch_admin}/accounts/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Deletar Conta',
				'account_data' => $account_data,
				'page_type'    => 'delete',
			);

			return $view->getRender($array, 'accounts', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postAccounts(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page == 'edit') {
			if (empty($post['memb_name']) or empty($post['memb__pwd']) or empty($post['mail_addr']) or empty($post['mwo_credits'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accounts/edit/" . $id);
				exit();
			}
			$account_data = $data->getAccountInfo($id);

			if (empty($account_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa conta não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accounts/edit/" . $id);
				exit();
			}

			$edit = $data->editAccount($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou uma conta'
				);

				$logger->addLoggerInfo("Accounts", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Não foi possivel editar a conta, tente novamente'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $edit
				);

				$logger->addLoggerWarning("Error Accounts", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accounts/edit/" . $id);
		} elseif ($page == 'delete') {
			$account_data = $data->getAccountInfo($id);

			$memb___id = $account_data['memb___id'];

			if (empty($account_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa conta não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accounts/edit/" . $id);
				exit();
			}

			$delete = $data->deleteAccount($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou uma conta'
				);

				$logger->addLoggerInfo("Accounts", $values);

				$deleteaccountcharacter = $data->deleteAccountCharacter($memb___id);
				if ($deleteaccountcharacter == 'OK') {
					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou uma conta'
					);

					$logger->addLoggerInfo("AccountCharacter", $values);
				} else {
					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $deleteaccountcharacter
					);

					$logger->addLoggerWarning("Error AccountCharacter", $values);
				}

				$deletewarehouse = $data->deleteWarehouse($memb___id);
				if ($deletewarehouse == 'OK') {
					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou uma conta'
					);

					$logger->addLoggerInfo("Warehouse", $values);
				} else {
					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $deletewarehouse
					);

					$logger->addLoggerWarning("Error Warehouse", $values);
				}

				$deleteextwarehouse = $data->deleteExtWarehouse($memb___id);
				if ($deleteextwarehouse == 'OK') {
					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou uma conta'
					);

					$logger->addLoggerInfo("ExtWarehouse", $values);
				} else {
					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $deleteextwarehouse
					);

					$logger->addLoggerWarning("Error ExtWarehouse", $values);
				}

				$getcharacters = $data->getCharactersAccount($memb___id);
				if (!empty($getcharacters)) {
					foreach ($getcharacters as $key => $value) {
						$getguildmaster = $data->getGuildMaster($value['Name']);
						if (!empty($getguildmaster)) {
							$deleteguild = $data->deleteGuild($getguildmaster['G_Name']);
							if ($deleteguild == 'OK') {
								$values = array(
									'username'  => $_SESSION['usernameadmin'],
									'ipaddress' => $model->getIpaddress(),
									'message'   => 'Deletou uma conta'
								);

								$logger->addLoggerInfo("Guild", $values);
							} else {
								$values = array(
									'username'  => $_SESSION['usernameadmin'],
									'ipaddress' => $model->getIpaddress(),
									'message'   => $deleteguild
								);

								$logger->addLoggerWarning("Error Guild", $values);
							}

							$deleteguildmembers = $data->deleteGuildMembers($getguildmaster['G_Name']);
							if ($deleteguildmembers == 'OK') {
								$values = array(
									'username'  => $_SESSION['usernameadmin'],
									'ipaddress' => $model->getIpaddress(),
									'message'   => 'Deletou uma conta'
								);

								$logger->addLoggerInfo("GuildMembers", $values);
							} else {
								$values = array(
									'username'  => $_SESSION['usernameadmin'],
									'ipaddress' => $model->getIpaddress(),
									'message'   => $deleteguildmembers
								);

								$logger->addLoggerWarning("Error GuildMembers", $values);
							}
						} else {
							$deleteguildmember = $data->deleteGuildMember($value['Name']);
							if ($deleteguildmember == 'OK') {
								$values = array(
									'username'  => $_SESSION['usernameadmin'],
									'ipaddress' => $model->getIpaddress(),
									'message'   => 'Deletou uma conta'
								);

								$logger->addLoggerInfo("GuildMember", $values);
							} else {
								$values = array(
									'username'  => $_SESSION['usernameadmin'],
									'ipaddress' => $model->getIpaddress(),
									'message'   => $deleteguildmember
								);

								$logger->addLoggerWarning("Error GuildMember", $values);
							}
						}

						$deletecharacter = $data->deleteCharacter($value['Name']);
						if ($deletecharacter == 'OK') {
							$values = array(
								'username'  => $_SESSION['usernameadmin'],
								'ipaddress' => $model->getIpaddress(),
								'message'   => 'Deletou uma conta'
							);

							$logger->addLoggerInfo("Character", $values);
						} else {
							$values = array(
								'username'  => $_SESSION['usernameadmin'],
								'ipaddress' => $model->getIpaddress(),
								'message'   => $deletecharacter
							);

							$logger->addLoggerWarning("Error Character", $values);
						}
					}
				}
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Não foi possivel deletar a conta, tente novamente'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $delete
				);

				$logger->addLoggerWarning("Error Accounts", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accounts/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getCharacters(AdminModel $model, ViewAdmin $view, Response $response, $page, $character = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$config_class   = $data->getConfig('classcodes');
			$config_class   = json_decode($config_class, true);
			$config_columns = $data->getConfig('columns');
			$config_columns = json_decode($config_columns, true);
			$characters     = $data->getCharacters();

			foreach ($characters as $key => $value) {
				foreach ($config_class as $id => $data) {
					if ($value['Class'] == $data['value']) {
						$class_character = $data['label'];
						break;
					} else {
						$class_character = 'Unknow';
					}
				}
				$characters_return[] = array(
					'AccountID' => $value['AccountID'],
					'Name'      => $value['Name'],
					'cLevel'    => $value['cLevel'],
					'Class'     => $class_character,
					'Resets'    => $value[$config_columns[0]['value']],
				);
			}

			$array = array(
				'title_page'      => 'Personagens',
				'characters_data' => $characters_return,
				'page_type'       => 'list',
			);

			return $view->getRender($array, 'characters', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin     = getenv('DIRADMIN');
			$character_data  = $data->getCharacterInfo($character);
			$config_class    = $data->getConfig('classcodes');
			$config_class    = json_decode($config_class, true);
			$config_columns  = $data->getConfig('columns');
			$config_columns  = json_decode($config_columns, true);
			$config_muserver = $data->getConfig('muserver');
			$config_muserver = json_decode($config_muserver, true);

			if (empty($character_data)) {
				return $response->withRedirect("/{$patch_admin}/characters/list");
				exit();
			}

			foreach ($config_class as $key => $value) {
				if ($character_data['Class'] == $value['value']) {
					$class_character = $value['label'];
					break;
				} else {
					$class_character = 'Unknow';
				}
			}
			$character_return = array(
				'AccountID'    => $character_data['AccountID'],
				'Name'         => $character_data['Name'],
				'cLevel'       => $character_data['cLevel'],
				'Class'        => $character_data['Class'],
				'ClassName'    => $class_character,
				'Resets'       => $character_data[$config_columns[0]['value']],
				'LevelUpPoint' => $character_data['LevelUpPoint'],
				'Experience'   => $character_data['Experience'],
				'Strength'     => $character_data['Strength'],
				'Dexterity'    => $character_data['Dexterity'],
				'Vitality'     => $character_data['Vitality'],
				'Energy'       => $character_data['Energy'],
				'Leadership'   => $character_data['Leadership'],
				'Money'        => $character_data['Money'],
				'MapNumber'    => $character_data['MapNumber'],
				'MapPosX'      => $character_data['MapPosX'],
				'MapPosY'      => $character_data['MapPosY'],
				'PkCount'      => $character_data['PkCount'],
				'PkLevel'      => $character_data['PkLevel'],
				'PkTime'       => $character_data['PkTime'],
				'CtlCode'      => $character_data['CtlCode'],
			);

			$array = array(
				'title_page'     => 'Editar Personagem',
				'character_data' => $character_return,
				'listclass'      => $this->listClass($config_muserver[2]['value'], $config_class),
				'page_type'      => 'edit',
			);

			return $view->getRender($array, 'characters', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin     = getenv('DIRADMIN');
			$character_data  = $data->getCharacterInfo($character);

			if (empty($character_data)) {
				return $response->withRedirect("/{$patch_admin}/characters/list");
				exit();
			}

			$array = array(
				'title_page'     => 'Deletar Conta',
				'character_data' => $character_data,
				'page_type'      => 'delete',
			);

			return $view->getRender($array, 'characters', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function listClass($version, $class)
	{
		switch ($version) {
			case 0: //Season 1 ou Abaixo - Sem DL
				$listClass = "<option value='" . $class[0]['value'] . "'>" . $class[0]['label'] . "</option>
				<option value='" . $class[1]['value'] . "'>" . $class[1]['label'] . "</option>
				<option value='" . $class[3]['value'] . "'>" . $class[3]['label'] . "</option>
				<option value='" . $class[4]['value'] . "'>" . $class[4]['label'] . "</option>
				<option value='" . $class[6]['value'] . "'>" . $class[6]['label'] . "</option>
				<option value='" . $class[7]['value'] . "'>" . $class[7]['label'] . "</option>
				<option value='" . $class[9]['value'] . "'>" . $class[9]['label'] . "</option>";
				break;
			case 1: //Season 1 ou Abaixo
				$listClass = "<option value='" . $class[0]['value'] . "'>" . $class[0]['label'] . "</option>
				<option value='" . $class[1]['value'] . "'>" . $class[1]['label'] . "</option>
				<option value='" . $class[3]['value'] . "'>" . $class[3]['label'] . "</option>
				<option value='" . $class[4]['value'] . "'>" . $class[4]['label'] . "</option>
				<option value='" . $class[6]['value'] . "'>" . $class[6]['label'] . "</option>
				<option value='" . $class[7]['value'] . "'>" . $class[7]['label'] . "</option>
				<option value='" . $class[9]['value'] . "'>" . $class[9]['label'] . "</option>
				<option value='" . $class[11]['value'] . "'>" . $class[11]['label'] . "</option>";
				break;
			case 2: //Season 2
				$listClass = "<option value='" . $class[0]['value'] . "'>" . $class[0]['label'] . "</option>
				<option value='" . $class[1]['value'] . "'>" . $class[1]['label'] . "</option>
				<option value='" . $class[3]['value'] . "'>" . $class[3]['label'] . "</option>
				<option value='" . $class[4]['value'] . "'>" . $class[4]['label'] . "</option>
				<option value='" . $class[6]['value'] . "'>" . $class[6]['label'] . "</option>
				<option value='" . $class[7]['value'] . "'>" . $class[7]['label'] . "</option>
				<option value='" . $class[9]['value'] . "'>" . $class[9]['label'] . "</option>
				<option value='" . $class[11]['value'] . "'>" . $class[11]['label'] . "</option>";
				break;
			case 3: //Season 3 Episodio 1
				$listClass = "<option value='" . $class[0]['value'] . "'>" . $class[0]['label'] . "</option>
				<option value='" . $class[1]['value'] . "'>" . $class[1]['label'] . "</option>
				<option value='" . $class[2]['value'] . "'>" . $class[2]['label'] . "</option>
				<option value='" . $class[3]['value'] . "'>" . $class[3]['label'] . "</option>
				<option value='" . $class[4]['value'] . "'>" . $class[4]['label'] . "</option>
				<option value='" . $class[5]['value'] . "'>" . $class[5]['label'] . "</option>
				<option value='" . $class[6]['value'] . "'>" . $class[6]['label'] . "</option>
				<option value='" . $class[7]['value'] . "'>" . $class[7]['label'] . "</option>
				<option value='" . $class[8]['value'] . "'>" . $class[8]['label'] . "</option>
				<option value='" . $class[9]['value'] . "'>" . $class[9]['label'] . "</option>
				<option value='" . $class[10]['value'] . "'>" . $class[10]['label'] . "</option>
				<option value='" . $class[11]['value'] . "'>" . $class[11]['label'] . "</option>
				<option value='" . $class[12]['value'] . "'>" . $class[12]['label'] . "</option>";
				break;
			case 4: //Season 4
				$listClass = "<option value='" . $class[0]['value'] . "'>" . $class[0]['label'] . "</option>
				<option value='" . $class[1]['value'] . "'>" . $class[1]['label'] . "</option>
				<option value='" . $class[2]['value'] . "'>" . $class[2]['label'] . "</option>
				<option value='" . $class[3]['value'] . "'>" . $class[3]['label'] . "</option>
				<option value='" . $class[4]['value'] . "'>" . $class[4]['label'] . "</option>
				<option value='" . $class[5]['value'] . "'>" . $class[5]['label'] . "</option>
				<option value='" . $class[6]['value'] . "'>" . $class[6]['label'] . "</option>
				<option value='" . $class[7]['value'] . "'>" . $class[7]['label'] . "</option>
				<option value='" . $class[8]['value'] . "'>" . $class[8]['label'] . "</option>
				<option value='" . $class[9]['value'] . "'>" . $class[9]['label'] . "</option>
				<option value='" . $class[10]['value'] . "'>" . $class[10]['label'] . "</option>
				<option value='" . $class[11]['value'] . "'>" . $class[11]['label'] . "</option>
				<option value='" . $class[12]['value'] . "'>" . $class[12]['label'] . "</option>
				<option value='" . $class[13]['value'] . "'>" . $class[13]['label'] . "</option>
				<option value='" . $class[14]['value'] . "'>" . $class[14]['label'] . "</option>
				<option value='" . $class[15]['value'] . "'>" . $class[15]['label'] . "</option>";
				break;
			case 5:
			case 6: //Season 6
				$listClass = "<option value='" . $class[0]['value'] . "'>" . $class[0]['label'] . "</option>
				<option value='" . $class[1]['value'] . "'>" . $class[1]['label'] . "</option>
				<option value='" . $class[2]['value'] . "'>" . $class[2]['label'] . "</option>
				<option value='" . $class[3]['value'] . "'>" . $class[3]['label'] . "</option>
				<option value='" . $class[4]['value'] . "'>" . $class[4]['label'] . "</option>
				<option value='" . $class[5]['value'] . "'>" . $class[5]['label'] . "</option>
				<option value='" . $class[6]['value'] . "'>" . $class[6]['label'] . "</option>
				<option value='" . $class[7]['value'] . "'>" . $class[7]['label'] . "</option>
				<option value='" . $class[8]['value'] . "'>" . $class[8]['label'] . "</option>
				<option value='" . $class[9]['value'] . "'>" . $class[9]['label'] . "</option>
				<option value='" . $class[10]['value'] . "'>" . $class[10]['label'] . "</option>
				<option value='" . $class[11]['value'] . "'>" . $class[11]['label'] . "</option>
				<option value='" . $class[12]['value'] . "'>" . $class[12]['label'] . "</option>
				<option value='" . $class[13]['value'] . "'>" . $class[13]['label'] . "</option>
				<option value='" . $class[14]['value'] . "'>" . $class[14]['label'] . "</option>
				<option value='" . $class[15]['value'] . "'>" . $class[15]['label'] . "</option>
				<option value='" . $class[16]['value'] . "'>" . $class[16]['label'] . "</option>
				<option value='" . $class[17]['value'] . "'>" . $class[17]['label'] . "</option>";
				break;
		}

		return $listClass;
	}

	public function postCharacters(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $character = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin    = getenv('DIRADMIN');
		$config_columns = $data->getConfig('columns');
		$config_columns = json_decode($config_columns, true);

		if ($page == 'edit') {
			if (empty($post['Resets'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/characters/edit/" . $character);
				exit();
			}
			$character_data  = $data->getCharacterInfo($character);

			if (empty($character_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse personagem não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/characters/edit/" . $character);
				exit();
			}

			$edit = $data->editCharacter($post, $config_columns[0]['value'], $character);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou um personagem'
				);

				$logger->addLoggerInfo("Characters", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Não foi possivel editar o personagem, tente novamente'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $edit
				);

				$logger->addLoggerWarning("Error Characters", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/characters/edit/" . $character);
		} elseif ($page == 'delete') {
			$character_data  = $data->getCharacterInfo($character);

			if (empty($character_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse personagem não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/characters/edit/" . $character);
				exit();
			}

			$delete = $data->deleteCharacter($character);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um Personagem'
				);

				$logger->addLoggerInfo("Characters", $values);

				$getguildmaster = $data->getGuildMaster($character);
				if (!empty($getguildmaster)) {
					$deleteguild = $data->deleteGuild($getguildmaster['G_Name']);
					if ($deleteguild == 'OK') {
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Deletou um Personagem'
						);

						$logger->addLoggerInfo("Guild", $values);
					} else {
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $deleteguild
						);

						$logger->addLoggerWarning("Error Guild", $values);
					}

					$deleteguildmembers = $data->deleteGuildMembers($getguildmaster['G_Name']);
					if ($deleteguildmembers == 'OK') {
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Deletou um Personagem'
						);

						$logger->addLoggerInfo("GuildMembers", $values);
					} else {
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $deleteguildmembers
						);

						$logger->addLoggerWarning("Error GuildMembers", $values);
					}
				} else {
					$deleteguildmember = $data->deleteGuildMember($character);
					if ($deleteguildmember == 'OK') {
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Deletou um Personagem'
						);

						$logger->addLoggerInfo("GuildMember", $values);
					} else {
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $deleteguildmember
						);

						$logger->addLoggerWarning("Error GuildMember", $values);
					}
				}
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Não foi possivel deletar o personagem, tente novamente'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $delete
				);

				$logger->addLoggerWarning("Error Characters", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/characters/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getMenus(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$menus = $data->getMenus();

			$array = array(
				'title_page' => 'Menus',
				'menus_data' => $menus,
				'page_type'  => 'list',
			);

			return $view->getRender($array, 'menus', $response);
		} elseif ($page == 'create') {
			//Variables
			$menus = $data->getMenusParentID(0);

			$array = array(
				'title_page' => 'Criar Menu',
				'menus_data' => $menus,
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'menus', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$menu_data   = $data->getMenuInfo($id);
			$menus       = $data->getMenusParentID(0);

			if (empty($menu_data)) {
				return $response->withRedirect("/{$patch_admin}/menus/list");
				exit();
			}

			$array = array(
				'title_page' => 'Editar Menu',
				'menus_data' => $menus,
				'menu_data'  => $menu_data,
				'page_type'  => 'edit',
			);

			return $view->getRender($array, 'menus', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$menu_data   = $data->getMenuInfo($id);
			$menus       = $data->getMenusParentID(0);

			if (empty($menu_data)) {
				return $response->withRedirect("/{$patch_admin}/menus/list");
				exit();
			}

			$array = array(
				'title_page' => 'Deletar Menu',
				'menus_data' => $menus,
				'menu_data'  => $menu_data,
				'page_type'  => 'delete',
			);

			return $view->getRender($array, 'menus', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postMenu(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name']) or empty($post['link'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/menus/create");
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertMenu($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou um novo menu'
				);

				$logger->addLoggerInfo("Menus", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Menus", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/menus/list");
		} elseif ($page == 'edit') {
			$menu_data = $data->getMenuInfo($id);

			if (empty($menu_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse menu não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/menus/edit/" . $id);
				exit();
			}

			$edit = $data->editMenu($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou um menu'
				);

				$logger->addLoggerInfo("Menus", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Menus", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/menus/edit/" . $id);
		} elseif ($page == 'delete') {
			$menu_data = $data->getMenuInfo($id);

			if (empty($menu_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse menu não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/menus/list");
				exit();
			}

			$delete = $data->deleteMenu($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um menu'
				);

				$logger->addLoggerInfo("Menus", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Menus", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/menus/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getConfigs(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$menus = $data->getConfigs();

			$array = array(
				'title_page'   => 'Configurações',
				'configs_data' => $menus,
				'page_type'    => 'list',
			);

			return $view->getRender($array, 'configs', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$config_data = $data->getConfigInfo($id);

			if (empty($config_data)) {
				return $response->withRedirect("/{$patch_admin}/configs/list");
				exit();
			}

			$config_data = array(
				'ID'   => $config_data['ID'],
				'name' => $config_data['name'],
				'type' => $config_data['type'],
				'data' => (array) json_decode($config_data['data'], true),
			);

			$array = array(
				'title_page'  => 'Editar Configuração',
				'config_data' => $config_data,
				'page_type'   => 'edit',
			);

			return $view->getRender($array, 'configs', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postConfig(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page == 'edit') {
			$config_data = $data->getConfigInfo($id);

			if (empty($config_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa configuração não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/configs/edit/" . $id);
				exit();
			}

			$count = count($post['value']);
			for ($i = 0; $i < $count; $i++) {
				$array[$i] = array(
					'name' => $post['name'][$i],
					'label' => $post['label'][$i],
					'value' => $post['value'][$i],
				);
			}

			$post = json_encode($array);

			$edit = $data->editConfig($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou uma configuração'
				);

				$logger->addLoggerInfo("Configurações", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $edit
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $edit
				);

				$logger->addLoggerWarning("Configurações", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/configs/edit/" . $id);
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getRankings(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$rankings = $data->getRankings();

			$array = array(
				'title_page'    => 'Rankings',
				'rankings_data' => $rankings,
				'page_type'     => 'list',
			);

			return $view->getRender($array, 'rankings', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Ranking',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'rankings', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$ranking_data = $data->getRankingInfo($id);

			if (empty($ranking_data)) {
				return $response->withRedirect("/{$patch_admin}/rankings/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Editar Ranking',
				'ranking_data' => $ranking_data,
				'page_type'    => 'edit',
			);

			return $view->getRender($array, 'rankings', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$ranking_data = $data->getRankingInfo($id);

			if (empty($ranking_data)) {
				return $response->withRedirect("/{$patch_admin}/rankings/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Deletar Ranking',
				'ranking_data' => $ranking_data,
				'page_type'    => 'delete',
			);

			return $view->getRender($array, 'rankings', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postRankings(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name']) or empty($post['database']) or empty($post['table']) or empty($post['column'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/rankings/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertRanking($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou um novo Ranking'
				);

				$logger->addLoggerInfo("Rankings", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Rankings", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/rankings/list");
		} elseif ($page == 'edit') {
			$ranking_data = $data->getRankingInfo($id);

			if (empty($ranking_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse ranking não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/rankings/edit/" . $id);
				exit();
			}

			$edit = $data->editRanking($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou um ranking'
				);

				$logger->addLoggerInfo("Rankings", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Rankings", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/rankings/edit/" . $id);
		} elseif ($page == 'delete') {
			$ranking_data = $data->getRankingInfo($id);

			if (empty($ranking_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse ranking não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/rankings/list");
				exit();
			}

			$delete = $data->deleteRanking($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um ranking'
				);

				$logger->addLoggerInfo("Rankings", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Rankings", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/rankings/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getNews(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$news = $data->getNews();

			$array = array(
				'title_page' => 'Notícias',
				'news_data'  => $news,
				'page_type'  => 'list',
			);

			return $view->getRender($array, 'news', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Notícia',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'news', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$new_data    = $data->getNewInfo($id);

			if (empty($new_data)) {
				return $response->withRedirect("/{$patch_admin}/news/list");
				exit();
			}

			$array = array(
				'title_page' => 'Editar Notícia',
				'new_data'   => $new_data,
				'page_type'  => 'edit',
			);

			return $view->getRender($array, 'news', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$new_data    = $data->getNewInfo($id);

			if (empty($new_data)) {
				return $response->withRedirect("/{$patch_admin}/news/list");
				exit();
			}

			$array = array(
				'title_page' => 'Deletar Notícia',
				'new_data'   => $new_data,
				'page_type'  => 'delete',
			);

			return $view->getRender($array, 'news', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postNews(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['title']) or empty($post['content'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/news/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertNew($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou uma nova notícia'
				);

				$logger->addLoggerInfo("News", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("News", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/news/list");
		} elseif ($page == 'edit') {
			$new_data = $data->getNewInfo($id);

			if (empty($new_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa notícia não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/news/list");
				exit();
			}

			$edit = $data->editNew($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou uma notícia'
				);

				$logger->addLoggerInfo("News", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("News", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/news/edit/" . $id);
		} elseif ($page == 'delete') {
			$new_data = $data->getNewInfo($id);

			if (empty($new_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa notícia não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/news/list");
				exit();
			}

			$delete = $data->deleteNew($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou uma notícia'
				);

				$logger->addLoggerInfo("News", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("News", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/news/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getPages(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$pages = $data->getPages();

			$array = array(
				'title_page' => 'Páginas',
				'pages_data' => $pages,
				'page_type'  => 'list',
			);

			return $view->getRender($array, 'pages', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Página',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'pages', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$page_data   = $data->getPageInfo($id);

			if (empty($page_data)) {
				return $response->withRedirect("/{$patch_admin}/pages/list");
				exit();
			}

			$array = array(
				'title_page' => 'Editar Página',
				'page_data'  => $page_data,
				'page_type'  => 'edit',
			);

			return $view->getRender($array, 'pages', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$page_data   = $data->getPageInfo($id);

			if (empty($page_data)) {
				return $response->withRedirect("/{$patch_admin}/pages/list");
				exit();
			}

			$array = array(
				'title_page' => 'Deletar Página',
				'page_data'  => $page_data,
				'page_type'  => 'delete',
			);

			return $view->getRender($array, 'pages', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postPages(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['title']) or empty($post['link']) or empty($post['content'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/pages/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertPage($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou uma nova página'
				);

				$logger->addLoggerInfo("Pages", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Pages", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/pages/list");
		} elseif ($page == 'edit') {
			$page_data   = $data->getPageInfo($id);

			if (empty($page_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa página não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/pages/list");
				exit();
			}

			$edit = $data->editPage($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou uma página'
				);

				$logger->addLoggerInfo("Pages", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Pages", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/pages/edit/" . $id);
		} elseif ($page == 'delete') {
			$page_data   = $data->getPageInfo($id);

			if (empty($page_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa página não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/pages/list");
				exit();
			}

			$delete = $data->deletePage($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou uma página'
				);

				$logger->addLoggerInfo("Pages", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Pages", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/pages/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getEvents(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$events = $data->getEvents();

			$array = array(
				'title_page'  => 'Eventos',
				'events_data' => $events,
				'page_type'   => 'list',
			);

			return $view->getRender($array, 'events', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Evento',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'events', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$event_data = $data->getEventInfo($id);

			if (empty($event_data)) {
				return $response->withRedirect("/{$patch_admin}/events/list");
				exit();
			}

			$array = array(
				'title_page' => 'Editar Evento',
				'event_data' => $event_data,
				'page_type'  => 'edit',
			);

			return $view->getRender($array, 'events', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$event_data = $data->getEventInfo($id);

			if (empty($event_data)) {
				return $response->withRedirect("/{$patch_admin}/events/list");
				exit();
			}

			$array = array(
				'title_page' => 'Deletar Evento',
				'event_data' => $event_data,
				'page_type'  => 'delete',
			);

			return $view->getRender($array, 'events', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postEvents(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name']) or empty($post['time'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/events/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertEvent($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou um novo evento'
				);

				$logger->addLoggerInfo("Events", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Events", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/events/list");
		} elseif ($page == 'edit') {
			$event_data = $data->getEventInfo($id);

			if (empty($event_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse evento não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/events/list");
				exit();
			}

			$edit = $data->editEvent($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou um evento'
				);

				$logger->addLoggerInfo("Events", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Events", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/events/edit/" . $id);
		} elseif ($page == 'delete') {
			$event_data = $data->getEventInfo($id);

			if (empty($event_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse evento não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/events/list");
				exit();
			}

			$delete = $data->deleteRanking($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um evento'
				);

				$logger->addLoggerInfo("Events", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Events", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/events/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getCoins(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$coins = $data->getCoins();

			$array = array(
				'title_page' => 'Moedas',
				'coins_data' => $coins,
				'page_type'  => 'list',
			);

			return $view->getRender($array, 'coins', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Moeda',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'coins', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$coin_data   = $data->getCoinInfo($id);

			if (empty($coin_data)) {
				return $response->withRedirect("/{$patch_admin}/coins/list");
				exit();
			}

			$array = array(
				'title_page' => 'Editar Moeda',
				'coin_data'  => $coin_data,
				'page_type'  => 'edit',
			);

			return $view->getRender($array, 'coins', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$coin_data   = $data->getCoinInfo($id);

			if (empty($coin_data)) {
				return $response->withRedirect("/{$patch_admin}/coins/list");
				exit();
			}

			$array = array(
				'title_page' => 'Deletar Moeda',
				'coin_data'  => $coin_data,
				'page_type'  => 'delete',
			);

			return $view->getRender($array, 'coins', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postCoins(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name']) or empty($post['database']) or empty($post['table']) or empty($post['column']) or empty($post['price'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/coins/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertCoin($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou uma nova moeda'
				);

				$logger->addLoggerInfo("Coins", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Coins", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/coins/list");
		} elseif ($page == 'edit') {
			$coin_data   = $data->getCoinInfo($id);

			if (empty($coin_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa moeda não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/coins/list");
				exit();
			}

			$edit = $data->editCoin($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou uma moeda'
				);

				$logger->addLoggerInfo("Coins", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Coins", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/coins/edit/" . $id);
		} elseif ($page == 'delete') {
			$coin_data   = $data->getCoinInfo($id);

			if (empty($coin_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa moeda não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/coins/list");
				exit();
			}

			$delete = $data->deleteCoin($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou uma moeda'
				);

				$logger->addLoggerInfo("Coins", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Coins", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/coins/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getVips(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$vips = $data->getVips();

			$array = array(
				'title_page' => 'Vips',
				'vips_data'  => $vips,
				'page_type'  => 'list',
			);

			return $view->getRender($array, 'vips', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Vip',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'vips', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$vip_data    = $data->getVipInfo($id);

			if (empty($vip_data)) {
				return $response->withRedirect("/{$patch_admin}/vips/list");
				exit();
			}

			$array = array(
				'title_page' => 'Editar Vip',
				'vip_data'   => $vip_data,
				'page_type'  => 'edit',
			);

			return $view->getRender($array, 'vips', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$vip_data    = $data->getVipInfo($id);

			if (empty($vip_data)) {
				return $response->withRedirect("/{$patch_admin}/vips/list");
				exit();
			}

			$array = array(
				'title_page' => 'Deletar Vip',
				'vip_data'   => $vip_data,
				'page_type'  => 'delete',
			);

			return $view->getRender($array, 'vips', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postVips(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name']) or empty($post['database']) or empty($post['table']) or empty($post['column_level']) or empty($post['column_days']) or empty($post['level']) or empty($post['prices']) or empty($post['days'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/vips/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertVip($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou um novo vip'
				);

				$logger->addLoggerInfo("Vips", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Vips", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/vips/list");
		} elseif ($page == 'edit') {
			$vip_data = $data->getVipInfo($id);

			if (empty($vip_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse vip não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/vips/list");
				exit();
			}

			$edit = $data->editVip($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou um vip'
				);

				$logger->addLoggerInfo("Vips", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Vips", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/vips/edit/" . $id);
		} elseif ($page == 'delete') {
			$vip_data = $data->getVipInfo($id);

			if (empty($vip_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse vip não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/vips/list");
				exit();
			}

			$delete = $data->deleteVip($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um vip'
				);

				$logger->addLoggerInfo("Vips", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $register
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $register
				);

				$logger->addLoggerWarning("Vips", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/vips/list");
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}
}
