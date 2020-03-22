<?php

namespace App\Controllers;

use App\Database\AdminDatabase;
use App\Models\AdminModel;
use App\Views\ViewAdmin;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use MWOItems\Items;
use MWOPay\MWOPay;
use Slim\Http\Response;
use VisualAppeal\AutoUpdate;

class AdminController
{

	public function getHome(AdminModel $model, ViewAdmin $view, Response $response)
	{
		//Classes
		$data     = new AdminDatabase();

		//Variables	
		$config_apimwopay = $data->getConfig('apimwopay');
		$config_apimwopay = json_decode($config_apimwopay, true);
		$email            = $config_apimwopay[0]['value'];
		$token            = $config_apimwopay[1]['value'];
		/*$mwopay           = new MWOPay($email, $token);

		$arrayjson = array(
			'Users' => array(
				'ipaddress' => $model->getIpaddress(),
			)
		);

		$json = json_encode($arrayjson);
		$user_credits = $mwopay->getUsers()->credits($json);
		$user_credits = json_decode($user_credits, true);*/

		$array = array(
			'title_page'       => 'Dashboard',
			//'user_credits'     => $user_credits['credits'],
			'total_onlines'    => $data->getTotalOnline(),
			'total_accounts'   => count($data->getAccounts()),
			'total_characters' => count($data->getCharacters()),
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
		} elseif ($ipaddress != $login['ipaddress'] && getenv('USE_IP_LOGIN') == 'true') {
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

	public function getAccessPanel(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$accountspanel = $data->getAccessPanel();

			$array = array(
				'title_page'    => 'Contas Painel',
				'accountspanel' => $accountspanel,
				'page_type'     => 'list',
			);

			return $view->getRender($array, 'accesspanel', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Conta Painel',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'accesspanel', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$account_data = $data->getAccessPanelInfo($id);

			if (empty($account_data)) {
				return $response->withRedirect("/{$patch_admin}/accesspanel/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Editar Conta Painel',
				'account_data' => $account_data,
				'page_type'    => 'edit',
			);

			return $view->getRender($array, 'accesspanel', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$account_data = $data->getAccessPanelInfo($id);

			if (empty($account_data)) {
				return $response->withRedirect("/{$patch_admin}/accesspanel/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Deletar Conta Painel',
				'account_data' => $account_data,
				'page_type'    => 'delete',
			);

			return $view->getRender($array, 'accesspanel', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postAccessPanel(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page == 'create') {
			if (empty($post['username']) or empty($post['password']) or empty($post['access']) or empty($post['ipaddress'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accesspanel/" . $page);
				exit();
			}
		}

		if ($page == 'edit') {
			if (empty($post['username']) or empty($post['access']) or empty($post['ipaddress'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accesspanel/edit/" . $id);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertAccessPanel($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou uma nova conta no painel'
				);

				$logger->addLoggerInfo("AcessPanel", $values);
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

				$logger->addLoggerWarning("AcessPanel", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accesspanel/list");
		} elseif ($page == 'edit') {
			$account_data = $data->getAccessPanelInfo($id);

			if (empty($account_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa conta não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accesspanel/edit/" . $id);
				exit();
			}

			$edit = $data->editAccessPanel($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou uma conta do painel'
				);

				$logger->addLoggerInfo("AcessPanel", $values);
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

				$logger->addLoggerWarning("AcessPanel", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accesspanel/edit/" . $id);
		} elseif ($page == 'delete') {
			$account_data = $data->getAccessPanelInfo($id);

			if (empty($account_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Essa conta não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accesspanel/delete/" . $id);
				exit();
			}

			$delete = $data->deleteAccessPanel($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou uma conta do painel'
				);

				$logger->addLoggerInfo("AcessPanel", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $delete
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $delete
				);

				$logger->addLoggerWarning("AcessPanel", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accesspanel/list");
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
			if (empty($post['name']) or empty($post['database']) or empty($post['table']) or empty($post['column']) or empty($post['max']) or empty($post['link'])) {
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
					'message' => $edit
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $edit
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
					'message' => $delete
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $delete
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

	public function getRankingsHome(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$rankings = $data->getRankingsHome();

			$array = array(
				'title_page'    => 'Rankings Home',
				'rankings_data' => $rankings,
				'page_type'     => 'list',
			);

			return $view->getRender($array, 'rankings-home', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Ranking Home',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'rankings-home', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$ranking_data = $data->getRankingHomeInfo($id);

			if (empty($ranking_data)) {
				return $response->withRedirect("/{$patch_admin}/rankings-home/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Editar Ranking Home',
				'ranking_data' => $ranking_data,
				'page_type'    => 'edit',
			);

			return $view->getRender($array, 'rankings-home', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin  = getenv('DIRADMIN');
			$ranking_data = $data->getRankingHomeInfo($id);

			if (empty($ranking_data)) {
				return $response->withRedirect("/{$patch_admin}/rankings-home/list");
				exit();
			}

			$array = array(
				'title_page'   => 'Deletar Ranking Home',
				'ranking_data' => $ranking_data,
				'page_type'    => 'delete',
			);

			return $view->getRender($array, 'rankings-home', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postRankingsHome(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name']) or empty($post['database']) or empty($post['table']) or empty($post['column']) or empty($post['max'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/rankings-home/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertRankingHome($post);
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

				$logger->addLoggerInfo("RankingsHome", $values);
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

				$logger->addLoggerWarning("RankingsHome", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/rankings-home/list");
		} elseif ($page == 'edit') {
			$ranking_data = $data->getRankingHomeInfo($id);

			if (empty($ranking_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse ranking não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/rankings-home/edit/" . $id);
				exit();
			}

			$edit = $data->editRankingHome($post, $id);
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

				$logger->addLoggerInfo("RankingsHome", $values);
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

				$logger->addLoggerWarning("RankingsHome", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/rankings-home/edit/" . $id);
		} elseif ($page == 'delete') {
			$ranking_data = $data->getRankingHomeInfo($id);

			if (empty($ranking_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse ranking não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/rankings-home/list");
				exit();
			}

			$delete = $data->deleteRankingHome($id);
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

				$logger->addLoggerInfo("RankingsHome", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $delete
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $delete
				);

				$logger->addLoggerWarning("RankingsHome", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/rankings-home/list");
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

	public function getUpdate(AdminModel $model, ViewAdmin $view, Response $response, $page = NULL)
	{
		//Classes
		$data = new AdminDatabase();
		//Variables
		$update = new AutoUpdate(getenv('DIRECTORY_ROOT') . getenv('DIRECTORY_SEPARATOR') . 'temp', getenv('DIRECTORY_ROOT') . getenv('DIRECTORY_SEPARATOR'), 60);
		$update->setCurrentVersion(getenv('VERSIO_MWO'));
		$update->setUpdateUrl(getenv('SERVER_UPDATE'));
		$update->setUpdateFile("update.ini");
		$update->addLogHandler(new \Monolog\Handler\StreamHandler(getenv('DIRLOGS') . 'update.log'));
		$update->setCache(new \Desarrolla2\Cache\Adapter\File(getenv('DIRECTORY_ROOT') . getenv('DIRECTORY_SEPARATOR') . 'cache/update'), 3600);

		if ($page == 'install') {
			$update->onEachUpdateFinish(function ($version) {
				$instal = __DIR__ . "../../execute.php";
				if (file_exists($instal)) {
					try {
						include $instal;
					} catch (Exception $ex) {
					}
					unlink($instal);
				}
			});

			$result = $update->update();

			if ($result === true) {
				$return = array(
					'error' => false,
					'success' => true,
					'message' => 'Atualização realizada com sucesso'
				);
			} else {
				$return = array(
					'error' => true,
					'success' => false,
					'message' => 'Error atualização: ' . $result . ''
				);
			}
		}

		$return = (!isset($return)) ? NULL : $return;

		if ($update->checkUpdate() === false) {
			$status   = 0;
			$version  = NULL;
			$versions = NULL;
		} elseif ($update->newVersionAvailable()) {
			$status   = 2;
			$version  = $update->getLatestVersion();
			$versions = array_map(function ($version) {
				return (string) $version;
			}, $update->getVersionsToUpdate());
		} else {
			$status   = 1;
			$version  = NULL;
			$versions = NULL;
		}

		$array = array(
			'title_page'     => 'Atualizações',
			'status'         => $status,
			'version'        => $version,
			'versions'       => $versions,
			'current_verion' => getenv('VERSIO_MWO'),
			'return'         => $return,
		);

		return $view->getRender($array, 'update', $response);
	}

	public function getAccessPages(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$accesspages = $data->getAccessPages();

			$array = array(
				'title_page'       => 'Acesso Páginas',
				'accesspages_data' => $accesspages,
				'page_type'        => 'list',
			);

			return $view->getRender($array, 'accesspages', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Acesso',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'accesspages', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin     = getenv('DIRADMIN');
			$accesspage_data = $data->getAccessPageInfo($id);

			if (empty($accesspage_data)) {
				return $response->withRedirect("/{$patch_admin}/accesspages/list");
				exit();
			}

			$array = array(
				'title_page'      => 'Editar Acesso',
				'accesspage_data' => $accesspage_data,
				'page_type'       => 'edit',
			);

			return $view->getRender($array, 'accesspages', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin     = getenv('DIRADMIN');
			$accesspage_data = $data->getAccessPageInfo($id);

			if (empty($accesspage_data)) {
				return $response->withRedirect("/{$patch_admin}/accesspages/list");
				exit();
			}

			$array = array(
				'title_page'      => 'Deletar Vip',
				'accesspage_data' => $accesspage_data,
				'page_type'       => 'delete',
			);

			return $view->getRender($array, 'accesspages', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postAccessPages(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accesspages/" . $page);
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertAccessPage($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou um novo acesso'
				);

				$logger->addLoggerInfo("AccessPages", $values);
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

				$logger->addLoggerWarning("AccessPages", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accesspages/list");
		} elseif ($page == 'edit') {
			$accesspage_data = $data->getAccessPageInfo($id);

			if (empty($accesspage_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse acesso não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accesspages/list");
				exit();
			}

			$edit = $data->editAccessPage($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou um acesso'
				);

				$logger->addLoggerInfo("AccessPages", $values);
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

				$logger->addLoggerWarning("AccessPages", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accesspages/edit/" . $id);
		} elseif ($page == 'delete') {
			$accesspage_data = $data->getAccessPageInfo($id);

			if (empty($accesspage_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse acesso não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/accesspages/list");
				exit();
			}

			$delete = $data->deleteAccessPage($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um acesso'
				);

				$logger->addLoggerInfo("AccessPages", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $delete
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $delete
				);

				$logger->addLoggerWarning("AccessPages", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/accesspages/list");
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

	public function getSlides(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$slides = $data->getSlides();

			$array = array(
				'title_page'  => 'Slides',
				'slides_data' => $slides,
				'page_type'   => 'list',
			);

			return $view->getRender($array, 'slides', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Slide',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'slides', $response);
		} elseif ($page == 'edit') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$slide_data   = $data->getSlideInfo($id);

			if (empty($slide_data)) {
				return $response->withRedirect("/{$patch_admin}/slides/list");
				exit();
			}

			$array = array(
				'title_page' => 'Editar Slide',
				'slide_data'  => $slide_data,
				'page_type'  => 'edit',
			);

			return $view->getRender($array, 'slides', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$slide_data   = $data->getSlideInfo($id);

			if (empty($slide_data)) {
				return $response->withRedirect("/{$patch_admin}/slides/list");
				exit();
			}

			$array = array(
				'title_page' => 'Deletar Slide',
				'slide_data' => $slide_data,
				'page_type'  => 'delete',
			);

			return $view->getRender($array, 'slides', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postSlides(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['name']) or empty($post['link']) or empty($post['image'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/slides/create");
				exit();
			}
		}

		if ($page == 'create') {
			$register = $data->insertSlide($post);
			if ($register == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou um novo slide'
				);

				$logger->addLoggerInfo("Slides", $values);
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

				$logger->addLoggerWarning("Slides", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/slides/list");
		} elseif ($page == 'edit') {
			$slide_data = $data->getSlideInfo($id);

			if (empty($slide_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse slide não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/slides/edit/" . $id);
				exit();
			}

			$edit = $data->editSlide($post, $id);
			if ($edit == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Editado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Editou um slide'
				);

				$logger->addLoggerInfo("Slides", $values);
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

				$logger->addLoggerWarning("Slides", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/slides/edit/" . $id);
		} elseif ($page == 'delete') {
			$slide_data = $data->getSlideInfo($id);

			if (empty($slide_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse slide não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/slides/edit/" . $id);
				exit();
			}

			$delete = $data->deleteSlide($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um slide'
				);

				$logger->addLoggerInfo("Slides", $values);
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

				$logger->addLoggerWarning("Slides", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/slides/list");
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

	public function getKingOfMu(AdminModel $model, ViewAdmin $view, Response $response)
	{
		//Classes
		$data = new AdminDatabase();

		//Variables
		$kingofmu_data = $data->getKingOfMu();

		$array = array(
			'title_page'   => 'Editar Rei do Mu',
			'kingofmu_data' => $kingofmu_data,
		);

		return $view->getRender($array, 'kingofmu', $response);
	}

	public function postKingOfMu(AdminModel $model, ViewAdmin $view, Response $response, $post)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($post['mode'] == 'manual') {
			if (empty($post['database']) or empty($post['table']) or empty($post['character'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/kingofmu");
				exit();
			}
		} else {
			if (empty($post['database']) or empty($post['table']) or empty($post['column']) or empty($post['custom']) or empty($post['orderby'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/kingofmu");
				exit();
			}
		}

		$edit = $data->editKingOfMu($post);
		if ($edit == 'OK') {
			$return = array(
				'error'   => false,
				'success' => true,
				'message' => 'Editado com sucesso'
			);

			$values = array(
				'username'  => $_SESSION['usernameadmin'],
				'ipaddress' => $model->getIpaddress(),
				'message'   => 'Editou o rei do mu'
			);

			$logger->addLoggerInfo("KingOfMu", $values);
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

			$logger->addLoggerWarning("KingOfMu", $values);
		}

		$messages->addMessage('response', $return);

		return $response->withRedirect("/{$patch_admin}/kingofmu");
	}

	public function getTransactions(AdminModel $model, ViewAdmin $view, Response $response, $page = NULL, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		//Variables
		$config_apimwopay = $data->getConfig('apimwopay');
		$config_apimwopay = json_decode($config_apimwopay, true);
		$email            = $config_apimwopay[0]['value'];
		$token            = $config_apimwopay[1]['value'];
		$mwopay           = new MWOPay($email, $token);
		$patch_admin      = getenv('DIRADMIN');

		if ($page == 'all') {
			$arrayjson = array(
				'Transactions' => array(
					'Type'      => 'all',
					'ID'        => NULL,
					'ipaddress' => $model->getIpaddress()
				),
			);

			$json = json_encode($arrayjson);

			$transactions = $mwopay->getTransactions()->show($json);
			$transactions = json_decode($transactions, true);
			$transactions = json_decode($transactions['transactions'], true);

			$array = array(
				'title_page'   => 'Transações',
				'transactions' => $transactions,
				'page_type'    => 'all'
			);

			return $view->getRender($array, 'transactions', $response);
		} elseif ($page == 'info') {
			if (empty($id)) {
				return $response->withRedirect("/{$patch_admin}/transactions/all");
				exit();
			}

			$arrayjson = array(
				'Transactions' => array(
					'Type'      => 'info',
					'ID'        => $id,
					'ipaddress' => $model->getIpaddress()
				),
			);

			$json = json_encode($arrayjson);

			$transaction_data = $mwopay->getTransactions()->show($json);
			$transaction_data = json_decode($transaction_data, true);
			$transaction_data = json_decode($transaction_data['transaction'], true);

			$array = array(
				'title_page'       => 'Transação ' . $id,
				'transaction_data' => $transaction_data,
				'page_type'        => 'info'
			);

			return $view->getRender($array, 'transactions', $response);
		} else {
			$arrayjson = array(
				'Transactions' => array(
					'Type'      => $page,
					'ID'        => NULL,
					'ipaddress' => $model->getIpaddress()
				),
			);

			$json = json_encode($arrayjson);

			$transactions = $mwopay->getTransactions()->show($json);
			$transactions = json_decode($transactions, true);
			$transactions = json_decode($transactions['transactions'], true);

			$array = array(
				'title_page'   => 'Transações de ' . $page,
				'transactions' => $transactions,
				'page_type'    => $page
			);

			return $view->getRender($array, 'transactions', $response);
		}
	}

	public function getWithdrawals(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		//Variables
		$config_apimwopay = $data->getConfig('apimwopay');
		$config_apimwopay = json_decode($config_apimwopay, true);
		$email            = $config_apimwopay[0]['value'];
		$token            = $config_apimwopay[1]['value'];
		$mwopay           = new MWOPay($email, $token);

		if ($page == 'list') {
			//Variables
			$arrayjson = array(
				'Withdrawals' => array(
					'Type'      => 'all',
					'ID'        => NULL,
					'ipaddress' => $model->getIpaddress()
				),
			);

			$json = json_encode($arrayjson);

			$withdrawals = $mwopay->getWithdrawals()->show($json);
			$withdrawals = json_decode($withdrawals, true);
			$withdrawals = json_decode($withdrawals['withdrawals'], true);

			$array = array(
				'title_page'  => 'Retiradas',
				'withdrawals' => $withdrawals,
				'page_type'   => 'list',
			);

			return $view->getRender($array, 'withdrawals', $response);
		} elseif ($page == 'create') {
			$array = array(
				'title_page' => 'Criar Retiradas',
				'page_type'  => 'create',
			);

			return $view->getRender($array, 'withdrawals', $response);
		} elseif ($page == 'info') {
			//Variables
			$patch_admin = getenv('DIRADMIN');

			if (empty($id)) {
				return $response->withRedirect("/{$patch_admin}/withdrawals/list");
				exit();
			}

			$arrayjson = array(
				'Withdrawals' => array(
					'Type'      => 'info',
					'ID'        => $id,
					'ipaddress' => $model->getIpaddress()
				),
			);

			$json = json_encode($arrayjson);

			$withdrawal_data = $mwopay->getWithdrawals()->show($json);
			$withdrawal_data = json_decode($withdrawal_data, true);
			$withdrawal_data = json_decode($withdrawal_data['withdrawal'], true);

			$array = array(
				'title_page'      => 'Retirada ' . $id,
				'withdrawal_data' => $withdrawal_data,
				'page_type'       => 'info',
			);

			return $view->getRender($array, 'withdrawals', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postWithdrawals(AdminModel $model, ViewAdmin $view, Response $response, $page, $post)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin      = getenv('DIRADMIN');
		$config_apimwopay = $data->getConfig('apimwopay');
		$config_apimwopay = json_decode($config_apimwopay, true);
		$email            = $config_apimwopay[0]['value'];
		$token            = $config_apimwopay[1]['value'];
		$mwopay           = new MWOPay($email, $token);

		if (empty($post['price'])) {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Preencha todos os campos'
			);

			$messages->addMessage('response', $return);
			return $response->withRedirect("/{$patch_admin}/withdrawals/create");
			exit();
		}

		if ($page == 'create') {
			$price = number_format($post['price'], 2, '.', '');

			$arrayjson = array(
				'Withdrawals' => array(
					'Price'     => $price,
					'ipaddress' => $model->getIpaddress()
				),
			);

			$json = json_encode($arrayjson);

			$withdrawal_data = $mwopay->getWithdrawals()->create($json);
			$withdrawal_data = json_decode($withdrawal_data, true);

			if ($withdrawal_data['error'] == false) {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Cadastrado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Cadastrou uma nova retirada'
				);

				$logger->addLoggerInfo("Withdrawals", $values);
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => "Code: " . $withdrawal_data['code'] . " - Message: " . $withdrawal_data['message'],
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => "Code: " . $withdrawal_data['code'] . " - Message: " . $withdrawal_data['message'],
				);

				$logger->addLoggerWarning("Withdrawals", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/withdrawals/list");
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

	public function getTickets(AdminModel $model, ViewAdmin $view, Response $response, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$tickets = $data->getTickets();

			$array = array(
				'title_page'  => 'Tickets',
				'tickets_data' => $tickets,
				'page_type'   => 'list',
			);

			return $view->getRender($array, 'tickets', $response);
		} elseif ($page == 'answers') {
			//Variables
			$patch_admin   = getenv('DIRADMIN');
			$ticket_data   = $data->getTicketInfo($id);
			$ticket_answer = $data->getTicketAnswer($id);

			if (empty($ticket_data)) {
				return $response->withRedirect("/{$patch_admin}/tickets/list");
				exit();
			}

			$array = array(
				'title_page'    => 'Responder Ticket',
				'ticket_data'   => $ticket_data,
				'ticket_answer' => $ticket_answer,
				'page_type'     => 'answers',
			);

			return $view->getRender($array, 'tickets', $response);
		} elseif ($page == 'delete') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$ticket_data = $data->getTicketInfo($id);

			if (empty($ticket_data)) {
				return $response->withRedirect("/{$patch_admin}/tickets/list");
				exit();
			}

			$array = array(
				'title_page'  => 'Deletar Ticket',
				'ticket_data' => $ticket_data,
				'page_type'   => 'delete',
			);

			return $view->getRender($array, 'tickets', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postTickets(AdminModel $model, ViewAdmin $view, Response $response, $page, $post, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page != 'delete') {
			if (empty($post['message'])) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Preencha todos os campos'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/tickets/answers/" . $id);
				exit();
			}
		}

		if ($page == 'answers') {
			$ticket_data   = $data->getTicketInfo($id);
			$ticket_answer = $data->getTicketAnswer($id);

			if (empty($ticket_answer)) {
				$action_answer = 'create';
			} else {
				$action_answer = 'edit';
			}

			if (empty($ticket_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse ticket não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/tickets/list");
				exit();
			}
			if ($action_answer == 'create') {
				$register = $data->insertTicketAnswer($post, $_SESSION['usernameadmin'], $id);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou uma nova resposta para o ticket ' . $id
					);

					$logger->addLoggerInfo("Tickets", $values);
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

					$logger->addLoggerWarning("Tickets", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/tickets/answers/" . $id);
			} elseif ($action_answer == 'edit') {
				$edit = $data->editTicketAnswer($post, $_SESSION['usernameadmin'], $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou a resposta do ticket ' . $id
					);

					$logger->addLoggerInfo("Tickets", $values);
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

					$logger->addLoggerWarning("Tickets", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/tickets/answers/" . $id);
			}
		} elseif ($page == 'delete') {
			$ticket_data = $data->getTicketInfo($id);
			if (empty($ticket_data)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Esse ticket não existe'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/tickets/list");
				exit();
			}

			$delete = $data->deleteTicket($id);
			if ($delete == 'OK') {
				$return = array(
					'error'   => false,
					'success' => true,
					'message' => 'Deletado com sucesso'
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => 'Deletou um ticket'
				);

				$logger->addLoggerInfo("Tickets", $values);

				$delete_answer = $data->deleteTicketAnswer($id);
				if ($delete_answer == 'OK') {

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou a resposta do ticket ' . $id
					);

					$logger->addLoggerInfo("Tickets", $values);
				} else {
					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete_answer
					);

					$logger->addLoggerWarning("Tickets", $values);
				}
			} else {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => $delete
				);

				$values = array(
					'username'  => $_SESSION['usernameadmin'],
					'ipaddress' => $model->getIpaddress(),
					'message'   => $delete
				);

				$logger->addLoggerWarning("Tickets", $values);
			}

			$messages->addMessage('response', $return);

			return $response->withRedirect("/{$patch_admin}/tickets/list");
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

	public function getLogs(AdminModel $model, ViewAdmin $view, Response $response, $page, $name = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($page == 'list') {
			//Variables
			$logs = glob('logs/*.log');
			$logs = str_replace("logs/", "", $logs);

			$array = array(
				'title_page' => 'Logs',
				'logs_data'  => $logs,
				'page_type'  => 'list',
			);

			return $view->getRender($array, 'logs', $response);
		} elseif ($page == 'view') {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			$filename    = 'logs/' . $name;
			if (!file_exists($filename)) {
				return $response->withRedirect("/{$patch_admin}/logs/list");
				exit();
			}

			$handle   = fopen($filename, "rb");
			$log_data = fread($handle, filesize($filename));
			fclose($handle);

			//$log_data = readfile($filename);

			$array = array(
				'title_page' => 'Log ' . $name,
				'log_data'   => $log_data,
				'log_name'   => $name,
				'page_type'  => 'view',
			);

			return $view->getRender($array, 'logs', $response);
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postLogs(AdminModel $model, ViewAdmin $view, Response $response, $page, $name)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');

		if ($page == 'delete') {
			$filename = 'logs/' . $name;
			if (!file_exists($filename)) {
				$return = array(
					'error'   => true,
					'success' => false,
					'message' => 'Log não encontrado'
				);

				$messages->addMessage('response', $return);
				return $response->withRedirect("/{$patch_admin}/logs/list");
				exit();
			} else {
				if (unlink($filename)) {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Log: ' . $name . ' deletado com sucesso'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/logs/list");
					exit();
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Não foi possível deletar o log: ' . $name
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/logs/list");
					exit();
				}
			}
		} else {
			$return = array(
				'error'   => true,
				'success' => false,
				'message' => 'Função não encontrada'
			);

			$messages->addMessage('response', $return);
			//return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function getItems(AdminModel $model, ViewAdmin $view, Response $response, $action, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($action == 'ancients') {
			if ($page == 'list') {
				//Variables
				$ancients = $data->getItemsAncients();

				$array = array(
					'title_page'    => 'Items Ancients',
					'ancients_data' => $ancients,
					'page_type'     => 'list',
				);

				return $view->getRender($array, 'items/ancients', $response);
			} elseif ($page == 'generate') {
				$array = array(
					'title_page' => 'Gerar Item Ancient',
					'page_type'  => 'generate',
				);

				return $view->getRender($array, 'items/ancients', $response);
			} elseif ($page == 'create') {
				$array = array(
					'title_page' => 'Criar Item Ancient',
					'page_type'  => 'create',
				);

				return $view->getRender($array, 'items/ancients', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin  = getenv('DIRADMIN');
				$ancient_data = $data->getItemAncientInfo($id);

				if (empty($ancient_data)) {
					return $response->withRedirect("/{$patch_admin}/items/ancients/list");
					exit();
				}

				$array = array(
					'title_page'   => 'Editar Item Ancient',
					'ancient_data' => $ancient_data,
					'page_type'    => 'edit',
				);

				return $view->getRender($array, 'items/ancients', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin  = getenv('DIRADMIN');
				$ancient_data = $data->getItemAncientInfo($id);

				if (empty($ancient_data)) {
					return $response->withRedirect("/{$patch_admin}/items/ancients/list");
					exit();
				}

				$array = array(
					'title_page'   => 'Deletar Item Ancient',
					'ancient_data' => $ancient_data,
					'page_type'    => 'delete',
				);

				return $view->getRender($array, 'items/ancients', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} elseif ($action == 'harmonys') {
			if ($page == 'list') {
				//Variables
				$harmonys = $data->getItemsHamornys();

				$array = array(
					'title_page'    => 'Items Harmonys',
					'harmonys_data' => $harmonys,
					'page_type'     => 'list',
				);

				return $view->getRender($array, 'items/harmonys', $response);
			} elseif ($page == 'generate') {
				$array = array(
					'title_page' => 'Gerar Items Harmonys',
					'page_type'  => 'generate',
				);

				return $view->getRender($array, 'items/harmonys', $response);
			} elseif ($page == 'create') {
				$array = array(
					'title_page' => 'Criar Item Harmony',
					'page_type'  => 'create',
				);

				return $view->getRender($array, 'items/harmonys', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin  = getenv('DIRADMIN');
				$harmony_data = $data->getItemHamornyInfo($id);

				if (empty($harmony_data)) {
					return $response->withRedirect("/{$patch_admin}/items/harmonys/list");
					exit();
				}

				$array = array(
					'title_page'   => 'Editar Item Harmony',
					'harmony_data' => $harmony_data,
					'page_type'    => 'edit',
				);

				return $view->getRender($array, 'items/harmonys', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin  = getenv('DIRADMIN');
				$harmony_data = $data->getItemHamornyInfo($id);

				if (empty($harmony_data)) {
					return $response->withRedirect("/{$patch_admin}/items/harmonys/list");
					exit();
				}

				$array = array(
					'title_page'   => 'Deletar Item Harmony',
					'harmony_data' => $harmony_data,
					'page_type'    => 'delete',
				);

				return $view->getRender($array, 'items/harmonys', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} elseif ($action == 'options') {
			if ($page == 'list') {
				//Variables
				$options = $data->getItemsOptions();

				$array = array(
					'title_page'   => 'Items Options',
					'options_data' => $options,
					'page_type'    => 'list',
				);

				return $view->getRender($array, 'items/options', $response);
			} elseif ($page == 'generate') {
				$array = array(
					'title_page' => 'Gerar Items Options',
					'page_type'  => 'generate',
				);

				return $view->getRender($array, 'items/options', $response);
			} elseif ($page == 'create') {
				$array = array(
					'title_page' => 'Criar Item Option',
					'page_type'  => 'create',
				);

				return $view->getRender($array, 'items/options', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$option_data = $data->getItemOptionInfo($id);

				if (empty($option_data)) {
					return $response->withRedirect("/{$patch_admin}/items/options/list");
					exit();
				}

				$array = array(
					'title_page'  => 'Editar Item Option',
					'option_data' => $option_data,
					'page_type'   => 'edit',
				);

				return $view->getRender($array, 'items/options', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$option_data = $data->getItemOptionInfo($id);

				if (empty($option_data)) {
					return $response->withRedirect("/{$patch_admin}/items/options/list");
					exit();
				}

				$array = array(
					'title_page'  => 'Deletar Item Option',
					'option_data' => $option_data,
					'page_type'   => 'delete',
				);

				return $view->getRender($array, 'items/options', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} elseif ($action == 'sockets') {
			if ($page == 'list') {
				//Variables
				$sockets = $data->getItemsSockets();

				$array = array(
					'title_page'   => 'Items Sockets',
					'sockets_data' => $sockets,
					'page_type'    => 'list',
				);

				return $view->getRender($array, 'items/sockets', $response);
			} elseif ($page == 'generate') {
				$array = array(
					'title_page' => 'Gerar Items Sockets',
					'page_type'  => 'generate',
				);

				return $view->getRender($array, 'items/sockets', $response);
			} elseif ($page == 'create') {
				$array = array(
					'title_page' => 'Criar Item Socket',
					'page_type'  => 'create',
				);

				return $view->getRender($array, 'items/sockets', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$socket_data = $data->getItemSocketInfo($id);

				if (empty($socket_data)) {
					return $response->withRedirect("/{$patch_admin}/items/sockets/list");
					exit();
				}

				$array = array(
					'title_page'  => 'Editar Item Socket',
					'socket_data' => $socket_data,
					'page_type'   => 'edit',
				);

				return $view->getRender($array, 'items/sockets', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$socket_data = $data->getItemSocketInfo($id);

				if (empty($socket_data)) {
					return $response->withRedirect("/{$patch_admin}/items/sockets/list");
					exit();
				}

				$array = array(
					'title_page'  => 'Deletar Item Socket',
					'socket_data' => $socket_data,
					'page_type'   => 'delete',
				);

				return $view->getRender($array, 'items/sockets', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} elseif ($action == 'refines') {
			if ($page == 'list') {
				//Variables
				$refines = $data->getItemsRefines();

				$array = array(
					'title_page'   => 'Items 380',
					'refines_data' => $refines,
					'page_type'    => 'list',
				);

				return $view->getRender($array, 'items/refines', $response);
			} elseif ($page == 'generate') {
				$array = array(
					'title_page' => 'Gerar Items 380',
					'page_type'  => 'generate',
				);

				return $view->getRender($array, 'items/refines', $response);
			} elseif ($page == 'create') {
				$array = array(
					'title_page' => 'Criar Item 380',
					'page_type'  => 'create',
				);

				return $view->getRender($array, 'items/refines', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$refine_data = $data->getItemRefineInfo($id);

				if (empty($refine_data)) {
					return $response->withRedirect("/{$patch_admin}/items/refines/list");
					exit();
				}

				$array = array(
					'title_page'  => 'Editar Item 380',
					'refine_data' => $refine_data,
					'page_type'   => 'edit',
				);

				return $view->getRender($array, 'items/refines', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$refine_data = $data->getItemRefineInfo($id);

				if (empty($refine_data)) {
					return $response->withRedirect("/{$patch_admin}/items/refines/list");
					exit();
				}

				$array = array(
					'title_page'  => 'Deletar Item 380',
					'refine_data' => $refine_data,
					'page_type'   => 'delete',
				);

				return $view->getRender($array, 'items/refines', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} elseif ($action == 'skills') {
			if ($page == 'list') {
				//Variables
				$skills = $data->getItemsSkills();

				$array = array(
					'title_page'  => 'Items Skills',
					'skills_data' => $skills,
					'page_type'   => 'list',
				);

				return $view->getRender($array, 'items/skills', $response);
			} elseif ($page == 'generate') {
				$array = array(
					'title_page' => 'Gerar Items Skills',
					'page_type'  => 'generate',
				);

				return $view->getRender($array, 'items/skills', $response);
			} elseif ($page == 'create') {
				$array = array(
					'title_page' => 'Criar Item Skill',
					'page_type'  => 'create',
				);

				return $view->getRender($array, 'items/skills', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$skill_data  = $data->getItemSkillInfo($id);

				if (empty($skill_data)) {
					return $response->withRedirect("/{$patch_admin}/items/skills/list");
					exit();
				}

				$array = array(
					'title_page' => 'Editar Item Skill',
					'skill_data' => $skill_data,
					'page_type'  => 'edit',
				);

				return $view->getRender($array, 'items/skills', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$skill_data  = $data->getItemSkillInfo($id);

				if (empty($skill_data)) {
					return $response->withRedirect("/{$patch_admin}/items/skills/list");
					exit();
				}

				$array = array(
					'title_page' => 'Deletar Item Skill',
					'skill_data' => $skill_data,
					'page_type'  => 'delete',
				);

				return $view->getRender($array, 'items/skills', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postItems(AdminModel $model, ViewAdmin $view, Response $response, $action, $page, $post, $files, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');
		$connection = array(
			"MSSQL_DRIVER" => getenv('MSSQL_DRIVER'),
			"MSSQL_HOST"   => getenv('MSSQL_HOST'),
			"MSSQL_PORT"   => getenv('MSSQL_PORT'),
			"MSSQL_USER"   => getenv('MSSQL_USER'),
			"MSSQL_PASS"   => getenv('MSSQL_PASS'),
			"MSSQL_DBNAME" => getenv('MSSQL_DBNAME'),
		);

		$mwoitems = new Items($connection);
		$itemarray = array(
			"section"    => NULL,
			"index"      => NULL,
			"durability" => NULL,
			"level"      => NULL,
			"skill"      => false,
			"luck"       => false,
			"option"     => NULL,
			"refine"     => false,
			"harmony"    => array(
				"type"  => NULL,
				"level" => NULL
			),
			"excellents" => NULL,
			"sockets" => NULL
		);
		$item = $mwoitems->getItem($itemarray, 2);

		if ($action == 'ancients') {
			if ($page != 'delete' and $page != 'generate') {
				if ($post['section'] == NULL or $post['index'] == NULL or empty($post['ancient']) or empty($post['name'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/ancients/list");
					exit();
				}
			}

			if ($page == 'generate') {
				$itemtype = $files['itemtype'];
				if ($itemtype->getError() === UPLOAD_ERR_OK) {
					$fileitemtype = $itemtype->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemtype->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/ancients/generate");
					exit();
				}

				$itemoption = $files['itemoption'];
				if ($itemoption->getError() === UPLOAD_ERR_OK) {
					$fileitemoption = $itemoption->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemoption->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/ancients/generate");
					exit();
				}

				$ancients      = $mwoitems->getItemAncient($item)->generate($fileitemtype, $fileitemoption);
				$totalancients = count($ancients);
				$totalerrors   = 0;
				$totalsuccess  = 0;
				for ($i = 0; $i < $totalancients; $i++) {
					$generate = $data->insertItemAncient($ancients[$i]);
					if ($generate == 'OK') {
						$totalsuccess = $totalsuccess + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Cadastrou um novo Item Ancient'
						);

						$logger->addLoggerInfo("ItemsAncients", $values);
					} else {
						$totalerrors = $totalerrors + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $generate
						);

						$logger->addLoggerWarning("ItemsAncients", $values);
					}
				}

				if ($totalerrors > 0) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Item Ancient gerado, items com erro {$totalerrors} e {$totalsuccess} inseridos com sucesso."
					);
				} else {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => "Item Ancient gerado com sucesso, {$totalancients} inseridos com sucesso."
					);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/ancients/list");
			} elseif ($page == 'create') {
				$register = $data->insertItemAncient($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou um novo items'
					);

					$logger->addLoggerInfo("ItemsAncients", $values);
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

					$logger->addLoggerWarning("ItemsAncients", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/ancients/list");
			} elseif ($page == 'edit') {
				$ancient_data = $data->getItemAncientInfo($id);

				if (empty($ancient_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}items/ancients/edit/" . $id);
					exit();
				}

				$edit = $data->editItemAncient($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou um item'
					);

					$logger->addLoggerInfo("ItemsAncients", $values);
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

					$logger->addLoggerWarning("ItemsAncients", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/ancients/edit/" . $id);
			} elseif ($page == 'delete') {
				$ancient_data = $data->getItemAncientInfo($id);

				if (empty($ancient_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/ancients/delete/" . $id);
					exit();
				}

				$delete = $data->deleteItemAncient($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um items'
					);

					$logger->addLoggerInfo("ItemsAncients", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("ItemsAncients", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/ancients/list");
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
		} elseif ($action == 'harmonys') {
			if ($page != 'delete' and $page != 'generate') {
				if ($post['section'] == NULL or $post['index'] == NULL or empty($post['level']) or empty($post['harmonys'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/harmonys/list");
					exit();
				}
			}

			if ($page == 'generate') {
				$harmonytype = $files['harmonytype'];
				if ($harmonytype->getError() === UPLOAD_ERR_OK) {
					$fileharmonytype = $harmonytype->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$harmonytype->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/harmonys/generate");
					exit();
				}

				$harmonyoption = $files['harmonyoption'];
				if ($harmonyoption->getError() === UPLOAD_ERR_OK) {
					$fileharmonyoption = $harmonyoption->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$harmonyoption->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/harmonys/generate");
					exit();
				}

				$harmonys      = $mwoitems->getItemHarmony($item)->generate(array($fileharmonytype, $fileharmonyoption));
				$totalharmonys = count($harmonys);
				$totalerrors   = 0;
				$totalsuccess  = 0;
				for ($i = 0; $i < $totalharmonys; $i++) {
					$generate = $data->insertItemHamorny($harmonys[$i]);
					if ($generate == 'OK') {
						$totalsuccess = $totalsuccess + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Cadastrou um novo Item Harmony'
						);

						$logger->addLoggerInfo("ItemsHarmonys", $values);
					} else {
						$totalerrors = $totalerrors + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $generate
						);

						$logger->addLoggerWarning("ItemsHarmonys", $values);
					}
				}

				if ($totalerrors > 0) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Item Harmony gerado, items com erro {$totalerrors} e {$totalsuccess} inseridos com sucesso."
					);
				} else {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => "Item Harmony gerado com sucesso, {$totalharmonys} inseridos com sucesso."
					);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/harmonys/list");
			} elseif ($page == 'create') {
				$register = $data->insertItemHamorny($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou um novo item'
					);

					$logger->addLoggerInfo("ItemsHarmonys", $values);
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

					$logger->addLoggerWarning("ItemsHarmonys", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/harmonys/list");
			} elseif ($page == 'edit') {
				$harmony_data = $data->getItemHamornyInfo($id);

				if (empty($harmony_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}items/harmonys/edit/" . $id);
					exit();
				}

				$edit = $data->editItemHamorny($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou um item'
					);

					$logger->addLoggerInfo("ItemsHarmonys", $values);
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

					$logger->addLoggerWarning("ItemsHarmonys", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/harmonys/edit/" . $id);
			} elseif ($page == 'delete') {
				$harmony_data = $data->getItemHamornyInfo($id);

				if (empty($harmony_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/harmonys/delete/" . $id);
					exit();
				}

				$delete = $data->deleteItemHamorny($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um items'
					);

					$logger->addLoggerInfo("ItemsHarmonys", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("ItemsHarmonys", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/harmonys/list");
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
		} elseif ($action == 'options') {
			if ($page != 'delete' and $page != 'generate') {
				if ($post['index'] == NULL or empty($post['optionindex']) or empty($post['value']) or $post['minrange'] == NULL or $post['maxrange'] == NULL or empty($post['skill']) or empty($post['luck']) or empty($post['option']) or empty($post['newoption'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/harmonys/list");
					exit();
				}
			}

			if ($page == 'generate') {
				$itemoption = $files['itemoption'];
				if ($itemoption->getError() === UPLOAD_ERR_OK) {
					$fileitemoption = $itemoption->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemoption->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/options/generate");
					exit();
				}

				$itemoptionname = $files['itemoptionname'];
				if ($itemoptionname->getError() === UPLOAD_ERR_OK) {
					$fileitemoptionname = $itemoptionname->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemoptionname->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/options/generate");
					exit();
				}

				$options      = $mwoitems->getItemsUtil(array($fileitemoption, $fileitemoptionname))->getoptiontype();
				$totaloptions = count($options);
				$totalerrors  = 0;
				$totalsuccess = 0;
				for ($i = 0; $i < $totaloptions; $i++) {
					$generate = $data->insertItemOption($options[$i]);
					if ($generate == 'OK') {
						$totalsuccess = $totalsuccess + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Cadastrou um novo Item Option'
						);

						$logger->addLoggerInfo("ItemsOptions", $values);
					} else {
						$totalerrors = $totalerrors + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $generate
						);

						$logger->addLoggerWarning("ItemsOptions", $values);
					}
				}

				if ($totalerrors > 0) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Item Option gerado, items com erro {$totalerrors} e {$totalsuccess} inseridos com sucesso."
					);
				} else {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => "Item Option gerado com sucesso, {$totaloptions} inseridos com sucesso."
					);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/options/list");
			} elseif ($page == 'create') {
				$register = $data->insertItemOption($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou um novo item'
					);

					$logger->addLoggerInfo("ItemsOptions", $values);
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

					$logger->addLoggerWarning("ItemsOptions", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/options/list");
			} elseif ($page == 'edit') {
				$option_data = $data->getItemOptionInfo($id);

				if (empty($option_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}items/options/edit/" . $id);
					exit();
				}

				$edit = $data->editItemOption($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou um item'
					);

					$logger->addLoggerInfo("ItemsOptions", $values);
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

					$logger->addLoggerWarning("ItemsOptions", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/options/edit/" . $id);
			} elseif ($page == 'delete') {
				$option_data = $data->getItemOptionInfo($id);

				if (empty($option_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/options/delete/" . $id);
					exit();
				}

				$delete = $data->deleteItemOption($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um item'
					);

					$logger->addLoggerInfo("ItemsOptions", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("ItemsOptions", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/options/list");
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
		} elseif ($action == 'sockets') {
			if ($page != 'delete' and $page != 'generate') {
				if ($post['section'] == NULL or $post['index'] == NULL or empty($post['max']) or empty($post['sockets'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/sockets/list");
					exit();
				}
			}

			if ($page == 'generate') {
				$itemtype = $files['itemtype'];
				if ($itemtype->getError() === UPLOAD_ERR_OK) {
					$fileitemtype = $itemtype->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemtype->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/sockets/generate");
					exit();
				}

				$itemoption = $files['itemoption'];
				if ($itemoption->getError() === UPLOAD_ERR_OK) {
					$fileitemoption = $itemoption->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemoption->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/sockets/generate");
					exit();
				}

				$sockets       = $mwoitems->getItemsUtil(array($fileitemtype, $fileitemoption))->getsockettype();
				$totalasockets = count($sockets);
				$totalerrors   = 0;
				$totalsuccess  = 0;
				for ($i = 0; $i < $totalasockets; $i++) {
					$generate = $data->insertItemSocket($sockets[$i]);
					if ($generate == 'OK') {
						$totalsuccess = $totalsuccess + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Cadastrou um novo Item Socket'
						);

						$logger->addLoggerInfo("ItemsSockets", $values);
					} else {
						$totalerrors = $totalerrors + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $generate
						);

						$logger->addLoggerWarning("ItemsSockets", $values);
					}
				}

				if ($totalerrors > 0) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Item Socket gerado, items com erro {$totalerrors} e {$totalsuccess} inseridos com sucesso."
					);
				} else {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => "Item Socket gerado com sucesso, {$totalasockets} inseridos com sucesso."
					);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/sockets/list");
			} elseif ($page == 'create') {
				$register = $data->insertItemSocket($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou um novo item'
					);

					$logger->addLoggerInfo("ItemsSockets", $values);
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

					$logger->addLoggerWarning("ItemsSockets", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/sockets/list");
			} elseif ($page == 'edit') {
				$socket_data = $data->getItemSocketInfo($id);

				if (empty($socket_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}items/sockets/edit/" . $id);
					exit();
				}

				$edit = $data->editItemSocket($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou um item'
					);

					$logger->addLoggerInfo("ItemsSockets", $values);
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

					$logger->addLoggerWarning("ItemsSockets", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/sockets/edit/" . $id);
			} elseif ($page == 'delete') {
				$socket_data = $data->getItemSocketInfo($id);

				if (empty($socket_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/sockets/delete/" . $id);
					exit();
				}

				$delete = $data->deleteItemSocket($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um item'
					);

					$logger->addLoggerInfo("ItemsSockets", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("ItemsSockets", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/sockets/list");
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
		} elseif ($action == 'refines') {
			if ($page != 'delete' and $page != 'generate') {
				if ($post['section'] == NULL or $post['index'] == NULL or empty($post['options'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/refines/list");
					exit();
				}
			}

			if ($page == 'generate') {
				$itemtype = $files['itemtype'];
				if ($itemtype->getError() === UPLOAD_ERR_OK) {
					$fileitemtype = $itemtype->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemtype->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/refines/generate");
					exit();
				}

				$itemoption = $files['itemoption'];
				if ($itemoption->getError() === UPLOAD_ERR_OK) {
					$fileitemoption = $itemoption->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemoption->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/refines/generate");
					exit();
				}

				$refines      = $mwoitems->getItemsUtil(array($fileitemtype, $fileitemoption))->getrefinetype();
				$totalrefines = count($refines);
				$totalerrors  = 0;
				$totalsuccess = 0;
				for ($i = 0; $i < $totalrefines; $i++) {
					$generate = $data->insertItemRefine($refines[$i]);
					if ($generate == 'OK') {
						$totalsuccess = $totalsuccess + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Cadastrou um novo Item 380'
						);

						$logger->addLoggerInfo("Items380", $values);
					} else {
						$totalerrors = $totalerrors + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $generate
						);

						$logger->addLoggerWarning("Items380", $values);
					}
				}

				if ($totalerrors > 0) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Item 380 gerado, items com erro {$totalerrors} e {$totalsuccess} inseridos com sucesso."
					);
				} else {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => "Item 380 gerado com sucesso, {$totalrefines} inseridos com sucesso."
					);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/refines/list");
			} elseif ($page == 'create') {
				$register = $data->insertItemRefine($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou um novo item'
					);

					$logger->addLoggerInfo("Items380", $values);
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

					$logger->addLoggerWarning("Items380", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/refines/list");
			} elseif ($page == 'edit') {
				$refine_data = $data->getItemRefineInfo($id);

				if (empty($refine_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}items/refines/edit/" . $id);
					exit();
				}

				$edit = $data->editItemRefine($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou um item'
					);

					$logger->addLoggerInfo("Items380", $values);
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

					$logger->addLoggerWarning("Items380", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/refines/edit/" . $id);
			} elseif ($page == 'delete') {
				$refine_data = $data->getItemRefineInfo($id);

				if (empty($refine_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/refines/delete/" . $id);
					exit();
				}

				$delete = $data->deleteItemRefine($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um item'
					);

					$logger->addLoggerInfo("Items380", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("Items380", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/refines/list");
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
		} elseif ($action == 'skills') {
			if ($page != 'delete' and $page != 'generate') {
				if ($post['index'] == NULL or empty($post['name'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/skills/list");
					exit();
				}
			}

			if ($page == 'generate') {
				$skill = $files['skill'];
				if ($skill->getError() === UPLOAD_ERR_OK) {
					$fileskill = $skill->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$skill->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/skills/generate");
					exit();
				}

				$skills       = $mwoitems->getItemsUtil($fileskill)->getskills();
				$totalskills  = count($skills);
				$totalerrors  = 0;
				$totalsuccess = 0;
				for ($i = 0; $i < $totalskills; $i++) {
					$generate = $data->insertItemSkill($skills[$i]);
					if ($generate == 'OK') {
						$totalsuccess = $totalsuccess + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Cadastrou uma nova Skill'
						);

						$logger->addLoggerInfo("Skills", $values);
					} else {
						$totalerrors = $totalerrors + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $generate
						);

						$logger->addLoggerWarning("Skills", $values);
					}
				}

				if ($totalerrors > 0) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Skill gerado, skills com erro {$totalerrors} e {$totalsuccess} inseridos com sucesso."
					);
				} else {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => "Skill gerado com sucesso, {$totalskills} inseridos com sucesso."
					);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/skills/list");
			} elseif ($page == 'create') {
				$register = $data->insertItemSkill($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou uma nova skill'
					);

					$logger->addLoggerInfo("Skills", $values);
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

					$logger->addLoggerWarning("Skills", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/skills/list");
			} elseif ($page == 'edit') {
				$skill_data = $data->getItemSkillInfo($id);

				if (empty($skill_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}items/skills/edit/" . $id);
					exit();
				}

				$edit = $data->editItemSkill($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou uma skill'
					);

					$logger->addLoggerInfo("Skills", $values);
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

					$logger->addLoggerWarning("Skills", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/skills/edit/" . $id);
			} elseif ($page == 'delete') {
				$skill_data = $data->getItemSkillInfo($id);

				if (empty($skill_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/skills/delete/" . $id);
					exit();
				}

				$delete = $data->deleteItemSkill($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um item'
					);

					$logger->addLoggerInfo("Skills", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("Skills", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/skills/list");
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

	public function getWebShops(AdminModel $model, ViewAdmin $view, Response $response, $action, $page, $id = NULL)
	{
		//Classes
		$data = new AdminDatabase();

		if ($action == 'all') {
			if ($page == 'list') {
				//Variables
				$webshops = $data->getWebShops();

				$array = array(
					'title_page'    => 'WebShops',
					'webshops_data' => $webshops,
					'page_type'     => 'list',
				);

				return $view->getRender($array, 'webshops/all', $response);
			} elseif ($page == 'create') {
				$webshops = $data->getWebShopsParentID(0);
				$coins    = $data->getCoins();

				$array = array(
					'title_page'    => 'Criar WebShop',
					'webshops_data' => $webshops,
					'coins_data'    => $coins,
					'page_type'     => 'create',
				);

				return $view->getRender($array, 'webshops/all', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin  = getenv('DIRADMIN');
				$webshops     = $data->getWebShopsParentID(0);
				$coins        = $data->getCoins();
				$webshop_data = $data->getWebShopInfo($id);

				if (empty($webshop_data)) {
					return $response->withRedirect("/{$patch_admin}/webshops/all/list");
					exit();
				}

				$array = array(
					'title_page'    => 'Editar WebShop',
					'webshops_data' => $webshops,
					'coins_data'    => $coins,
					'webshop_data'  => $webshop_data,
					'page_type'     => 'edit',
				);

				return $view->getRender($array, 'webshops/all', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin  = getenv('DIRADMIN');
				$webshop_data = $data->getWebShopInfo($id);

				if (empty($webshop_data)) {
					return $response->withRedirect("/{$patch_admin}/webshops/all/list");
					exit();
				}

				$array = array(
					'title_page'   => 'Deletar WebShop',
					'webshop_data' => $webshop_data,
					'page_type'    => 'delete',
				);

				return $view->getRender($array, 'webshops/all', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} elseif ($action == 'categories') {
			if ($page == 'list') {
				//Variables
				$categories = $data->getCategoriesWebShops();

				$array = array(
					'title_page'      => 'Categorias WebShops',
					'categories_data' => $categories,
					'page_type'       => 'list',
				);

				return $view->getRender($array, 'webshops/categories', $response);
			} elseif ($page == 'create') {
				$categories = $data->getCategoriesWebShopsParentID(0);
				$webshops   = $data->getWebShops();

				$array = array(
					'title_page'      => 'Criar Categoria WebShop',
					'categories_data' => $categories,
					'webshops_data'   => $webshops,
					'page_type'       => 'create',
				);

				return $view->getRender($array, 'webshops/categories', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin    = getenv('DIRADMIN');
				$categories     = $data->getCategoriesWebShopsParentID(0);
				$webshops       = $data->getWebShops();
				$categorie_data = $data->getCategorieWebShopInfo($id);

				if (empty($categorie_data)) {
					return $response->withRedirect("/{$patch_admin}/webshops/categories/list");
					exit();
				}

				$array = array(
					'title_page'      => 'Editar Categoria WebShop',
					'categories_data' => $categories,
					'webshops_data'   => $webshops,
					'categorie_data'  => $categorie_data,
					'page_type'       => 'edit',
				);

				return $view->getRender($array, 'webshops/categories', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin    = getenv('DIRADMIN');
				$categorie_data = $data->getCategorieWebShopInfo($id);

				if (empty($categorie_data)) {
					return $response->withRedirect("/{$patch_admin}/webshops/categories/list");
					exit();
				}

				$array = array(
					'title_page'     => 'Deletar Categoria WebShop',
					'categorie_data' => $categorie_data,
					'page_type'      => 'delete',
				);

				return $view->getRender($array, 'webshops/categories', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} elseif ($action == 'items') {
			if ($page == 'list') {
				//Variables
				$items = $data->getItemsWebShops();
				foreach ($items as $key => $value) {
					$categoryinfo = $data->getCategorieWebShopInfo($value['categoryid']);
					$clearitems[] = array(
						'ID'           => $value['ID'],
						'categoryid'   => $value['categoryid'],
						'categoryname' => $categoryinfo['name'],
						'section'      => $value['section'],
						'index_'       => $value['index_'],
						'name'         => $value['name'],
						'durability'   => $value['durability'],
						'image'        => $value['image'],
					);
				}

				$array = array(
					'title_page' => 'Items WebShops',
					'items_data' => $clearitems,
					'page_type'  => 'list',
				);

				return $view->getRender($array, 'webshops/items', $response);
			} elseif ($page == 'generate') {
				$categories = $data->getCategoriesWebShops();

				$array = array(
					'title_page'      => 'Gerar Item WebShop',
					'categories_data' => $categories,
					'page_type'       => 'generate',
				);

				return $view->getRender($array, 'webshops/items', $response);
			} elseif ($page == 'create') {
				$categories = $data->getCategoriesWebShops();

				$array = array(
					'title_page'      => 'Criar Item WebShop',
					'categories_data' => $categories,
					'page_type'       => 'create',
				);

				return $view->getRender($array, 'webshops/items', $response);
			} elseif ($page == 'edit') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$item_data   = $data->getItemWebShopInfo($id);
				$categories  = $data->getCategoriesWebShops();

				if (empty($item_data)) {
					return $response->withRedirect("/{$patch_admin}/webshops/items/list");
					exit();
				}

				$array = array(
					'title_page'      => 'Editar Item WebShop',
					'item_data'       => $item_data,
					'categories_data' => $categories,
					'page_type'       => 'edit',
				);

				return $view->getRender($array, 'webshops/items', $response);
			} elseif ($page == 'delete') {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				$item_data   = $data->getItemWebShopInfo($id);

				if (empty($item_data)) {
					return $response->withRedirect("/{$patch_admin}/webshops/items/list");
					exit();
				}

				$array = array(
					'title_page' => 'Deletar Item WebShop',
					'item_data'  => $item_data,
					'page_type'  => 'delete',
				);

				return $view->getRender($array, 'webshops/items', $response);
			} else {
				//Variables
				$patch_admin = getenv('DIRADMIN');
				return $response->withRedirect("/{$patch_admin}/");
				exit();
			}
		} else {
			//Variables
			$patch_admin = getenv('DIRADMIN');
			return $response->withRedirect("/{$patch_admin}/");
			exit();
		}
	}

	public function postWebShops(AdminModel $model, ViewAdmin $view, Response $response, $action, $page, $post, $files, $id = NULL)
	{
		//Classes
		$data     = new AdminDatabase();
		$logger   = new ViewLogger('admin');
		$messages = new ViewMessages();

		//Variables
		$patch_admin = getenv('DIRADMIN');
		$connection = array(
			"MSSQL_DRIVER" => getenv('MSSQL_DRIVER'),
			"MSSQL_HOST"   => getenv('MSSQL_HOST'),
			"MSSQL_PORT"   => getenv('MSSQL_PORT'),
			"MSSQL_USER"   => getenv('MSSQL_USER'),
			"MSSQL_PASS"   => getenv('MSSQL_PASS'),
			"MSSQL_DBNAME" => getenv('MSSQL_DBNAME'),
		);

		$mwoitems = new Items($connection);

		if ($action == 'all') {
			if ($page != 'delete') {
				if ($post['name'] == NULL or $post['label'] == NULL or empty($post['link']) or $post['parentid'] == NULL or $post['status'] == NULL or empty($post['coin'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/all/list");
					exit();
				}
			}

			if ($page == 'create') {
				$register = $data->insertWebShop($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou um novo shop'
					);

					$logger->addLoggerInfo("WebShops", $values);
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

					$logger->addLoggerWarning("WebShops", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/webshops/all/list");
			} elseif ($page == 'edit') {
				$webshop_data = $data->getWebShopInfo($id);

				if (empty($webshop_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse shop não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/all/edit/" . $id);
					exit();
				}

				$edit = $data->editWebShop($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou um shop'
					);

					$logger->addLoggerInfo("WebShops", $values);
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

					$logger->addLoggerWarning("WebShops", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/webshops/all/edit/" . $id);
			} elseif ($page == 'delete') {
				$webshop_data = $data->getWebShopInfo($id);

				if (empty($webshop_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse shop não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/all/delete/" . $id);
					exit();
				}

				$delete = $data->deleteWebShop($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um shop'
					);

					$logger->addLoggerInfo("WebShops", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("WebShops", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/webshops/all/list");
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
		} elseif ($action == 'categories') {
			if ($page != 'delete') {
				if ($post['name'] == NULL or $post['label'] == NULL or empty($post['link']) or $post['parentid'] == NULL or empty($post['webshopid']) or $post['status'] == NULL) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/categories/list");
					exit();
				}
			}

			if ($page == 'create') {
				$register = $data->insertCategorieWebShop($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou uma nova categoria de shop'
					);

					$logger->addLoggerInfo("CategoriesWebShops", $values);
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

					$logger->addLoggerWarning("CategoriesWebShops", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/webshops/categories/list");
			} elseif ($page == 'edit') {
				$category_data = $data->getCategorieWebShopInfo($id);

				if (empty($category_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Essa categoria não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/categories/edit/" . $id);
					exit();
				}

				$edit = $data->editCategorieWebShop($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou uma categoria de shop'
					);

					$logger->addLoggerInfo("CategoriesWebShops", $values);
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

					$logger->addLoggerWarning("CategoriesWebShops", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/webshops/categories/edit/" . $id);
			} elseif ($page == 'delete') {
				$category_data = $data->getCategorieWebShopInfo($id);

				if (empty($category_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Essa categoria não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/categories/delete/" . $id);
					exit();
				}

				$delete = $data->deleteCategorieWebShop($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou uma categoria de shop'
					);

					$logger->addLoggerInfo("CategoriesWebShops", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("CategoriesWebShops", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/webshops/categories/list");
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
		} elseif ($action == 'items') {
			if ($page != 'delete' and $page != 'generate') {
				if ($post['categoryid'] == NULL or $post['section'] == NULL or empty($post['index_']) or empty($post['name']) or empty($post['durability']) or empty($post['width']) or empty($post['height']) or empty($post['skill']) or empty($post['link']) or empty($post['status']) or empty($post['price']) or empty($post['max_excellent']) or empty($post['max_sockets'])) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Preencha todos os campos'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/all/list");
					exit();
				}
			}

			if ($page == 'generate') {
				$itemfile = $files['itemfile'];
				if ($itemfile->getError() === UPLOAD_ERR_OK) {
					$fileitemfile = $itemfile->file;
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Error upload {$itemfile->getClientFilename()} tente novamente"
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/webshops/all/generate");
					exit();
				}

				$items        = $mwoitems->getItemsKOR($fileitemfile)->parse();
				$totalitems   = count($items);
				$totalerrors  = 0;
				$totalsuccess = 0;
				for ($i = 0; $i < $totalitems; $i++) {

					if (array_key_exists('durability', $items[$i])) {
						$durability = ($items[$i]['durability'] != NULL) ? $items[$i]['durability'] : 255;
					} else {
						$durability = 255;
					}

					if (array_key_exists('class', $items[$i])) {
						$classes = json_encode(array(
							'DW' => ($items[$i]['class']['dw'] == 1) ? true : false,
							'DK' => ($items[$i]['class']['dk'] == 1) ? true : false,
							'FE' => ($items[$i]['class']['fe'] == 1) ? true : false,
							'MG' => ($items[$i]['class']['mg'] == 1) ? true : false,
							'DL' => ($items[$i]['class']['dl'] == 1) ? true : false,
							'SU' => ($items[$i]['class']['su'] == 1) ? true : false,
							'RF' => ($items[$i]['class']['rf'] == 1) ? true : false,
						));
					} else {
						$classes = "all";
					}

					$clearitem = array(
						'categoryid'      => $post['categoryid'],
						'section'         => $items[$i]['section'],
						'index_'          => $items[$i]['index'],
						'name'            => $items[$i]['name'],
						'durability'      => $durability,
						'width'           => $items[$i]['width'],
						'height'          => $items[$i]['height'],
						'skill'           => $items[$i]['skill'],
						'link'            => $this->generate_url($items[$i]['name']),
						'status'          => 1,
						'price'           => 1,
						'price_level'     => 0,
						'price_option'    => 0,
						'price_skill'     => 0,
						'price_luck'      => 0,
						'price_ancient'   => 0,
						'price_harmony'   => 0,
						'price_refine'    => 0,
						'price_socket'    => 0,
						'price_excellent' => 0,
						'max_excellent'   => 0,
						'max_sockets'     => 0,
						'image'           => $items[$i]['section'] . "-" . $items[$i]['index'],
						'classes'         => $classes,

					);
					$generate = $data->insertItemWebShop($clearitem);
					if ($generate == 'OK') {
						$totalsuccess = $totalsuccess + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => 'Cadastrou um novo item'
						);

						$logger->addLoggerInfo("ItemsWebShop", $values);
					} else {
						$totalerrors = $totalerrors + 1;
						$values = array(
							'username'  => $_SESSION['usernameadmin'],
							'ipaddress' => $model->getIpaddress(),
							'message'   => $generate
						);

						$logger->addLoggerWarning("ItemsWebShop", $values);
					}
				}

				if ($totalerrors > 0) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => "Item KOR gerado, items com erro {$totalerrors} e {$totalsuccess} inseridos com sucesso."
					);
				} else {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => "Item KOR gerado com sucesso, {$totalitems} inseridos com sucesso."
					);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/webshops/items/list");
			} elseif ($page == 'create') {
				$register = $data->insertItemOption($post);
				if ($register == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Cadastrado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Cadastrou um novo item'
					);

					$logger->addLoggerInfo("ItemsOptions", $values);
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

					$logger->addLoggerWarning("ItemsOptions", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/options/list");
			} elseif ($page == 'edit') {
				$option_data = $data->getItemOptionInfo($id);

				if (empty($option_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}items/options/edit/" . $id);
					exit();
				}

				$edit = $data->editItemOption($post, $id);
				if ($edit == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Editado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Editou um item'
					);

					$logger->addLoggerInfo("ItemsOptions", $values);
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

					$logger->addLoggerWarning("ItemsOptions", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/options/edit/" . $id);
			} elseif ($page == 'delete') {
				$option_data = $data->getItemOptionInfo($id);

				if (empty($option_data)) {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => 'Esse item não existe'
					);

					$messages->addMessage('response', $return);
					return $response->withRedirect("/{$patch_admin}/items/options/delete/" . $id);
					exit();
				}

				$delete = $data->deleteItemOption($id);
				if ($delete == 'OK') {
					$return = array(
						'error'   => false,
						'success' => true,
						'message' => 'Deletado com sucesso'
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => 'Deletou um item'
					);

					$logger->addLoggerInfo("ItemsOptions", $values);
				} else {
					$return = array(
						'error'   => true,
						'success' => false,
						'message' => $delete
					);

					$values = array(
						'username'  => $_SESSION['usernameadmin'],
						'ipaddress' => $model->getIpaddress(),
						'message'   => $delete
					);

					$logger->addLoggerWarning("ItemsOptions", $values);
				}

				$messages->addMessage('response', $return);

				return $response->withRedirect("/{$patch_admin}/items/options/list");
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

	private function generate_url($string)
	{
		$key = array("+", "%27", "-",);
		$replace = array("_", "", "");
		return strtolower(str_replace($key, $replace, urlencode(utf8_encode($string))));
	}
}
