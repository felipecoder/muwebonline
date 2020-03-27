<?php

namespace App\Controllers;

use App\Database\DashboardDatabase;
use App\Models\DashboardModel;
use App\Views\View;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use DateTime;

class DashboardController
{

  public function getHome(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data  = $data->getUser($model);
    $vips       = $data->getVipsConfigs();
    $characters = $data->getCharacters($home_data['memb___id']);

    if (empty($vips)) {
      $user_vip = NULL;
    } else {
      foreach ($vips as $key => $value) {
        if ($home_data[$value['column_level']] == 0) {
          $user_vip = array(
            'name' => 'Free',
            'days' => NULL,
          );
        } elseif ($home_data[$value['column_level']] == $value['level']) {
          $user_vip = array(
            'name' => $value['name'],
            'days' => $home_data[$value['column_days']],
          );
        } elseif ($home_data[$value['column_level']] > $value['level']) {
          $user_vip = array(
            'name' => 'Free',
            'days' => NULL,
          );
        }
      }
    }

    if (empty($characters)) {
      $user_characters = NULL;
    } else {
      $user_characters = $characters;
    }

    $array = array(
      'title_page'      => 'Painel',
      'home_data'       => $home_data,
      'user_vip'        => $user_vip,
      'user_characters' => $user_characters,
    );

    return $view->getRender($array, 'dashboard-home', $response);
  }

  public function getVips(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data  = $data->getUser($model);
    $vips       = $data->getVipsConfigs();

    if (empty($vips)) {
      $vips_return = NULL;
    } else {
      foreach ($vips as $key => $value) {
        $vips_return[] = array(
          'ID'     => $value['ID'],
          'name'   => $value['name'],
          'prices' => explode(',', $value['prices']),
          'days'   => explode(',', $value['days']),
        );
      }
    }

    $array = array(
      'title_page' => 'Comprar Vips',
      'home_data'  => $home_data,
      'vips'       => $vips_return,
    );

    return $view->getRender($array, 'dashboard-vips', $response);
  }

  public function postVips(DashboardModel $model, Response $response, $post, $page, $id)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('vips');
    $messages = new ViewMessages();

    //Variables
    $home_data       = $data->getUser($model);
    $config_muserver = $data->getConfig('muserver');
    $config_muserver = json_decode($config_muserver, true);

    if (empty($page) or $page != 'buys' or empty($id)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error na compra tente novamente'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Teve um erro na hora de comprar do vip ou tentaram burlar o sistema'
      );

      $logger->addLoggerWarning("Error Vips", $values);
      return $response->withRedirect(getenv("DIR") . "dashboard/vips");
      exit();
    }

    if (empty($post['days'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Você deve selecionar os dias'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/vips");
      exit();
    }

    $vip = $data->getVipInfo($id);

    if ($home_data[$vip['column_level']] != $vip['level'] && $home_data[$vip['column_level']] > 0) {

      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Você deve esperar os dia de vip acabar para comprar o ' . $vip['name']
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/vips");
      exit();
    }

    $days   = $vip['days'];
    $days   = explode(",", $days);
    $prices = $vip['prices'];
    $prices = explode(",", $prices);

    for ($i = 0; $i < count($days); $i++) {
      if ($days[$i] == $post['days']) {
        $vip_days  = $days[$i];
        $vip_price = $prices[$i];
      }
    }

    if ($home_data['mwo_credits'] < $vip_price) {

      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Você não tem créditos suficiente'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/vips");
      exit();
    }

    if ($home_data[$vip['column_level']] == $vip['level']) {
      $update_credits = $data->removeCredits($vip_price, $home_data['memb___id']);

      if ($update_credits == 'OK') {
        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => "Removido $vip_price créditos da conta de " . $home_data['memb___id'] . ""
        );

        $logger->addLoggerInfo("Credits", $values);

        $update_vip = $data->updateVip($vip['database'], $vip['table'], $vip['column_level'], $vip['column_days'], $home_data['memb___id'], $vip['level'], $vip_days, $config_muserver[3]['value'], $home_data[$vip['column_days']]);

        if ($update_vip == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Vip adiquirido com sucesso'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Comprou  ' . $vip_days . ' dias de ' . $vip['name'] . ' por ' . $vip_price . ' créditos'
          );

          $logger->addLoggerInfo("Vips", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/vips");
          exit();
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error ao comprar vip tente novamente mais tarde'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $update_vip
          );

          $logger->addLoggerWarning("Error Vips", $values);

          $add_credits = $data->acrescentCredits($vip_price, $home_data['memb___id']);
          if ($add_credits == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => "Devolvido $vip_price créditos devido a error do vip"
            );

            $logger->addLoggerInfo("Credits", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $add_credits
            );

            $logger->addLoggerWarning("Error Credits", $values);
          }
          return $response->withRedirect(getenv("DIR") . "dashboard/vips");
          exit();
        }
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Error ao comprar vip tente novamente mais tarde'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $update_credits
        );

        $logger->addLoggerWarning("Error Credits", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/vips");
        exit();
      }
    } else {
      $update_credits = $data->removeCredits($vip_price, $home_data['memb___id']);

      if ($update_credits == 'OK') {
        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => "Removido $vip_price créditos da conta de " . $home_data['memb___id'] . ""
        );

        $logger->addLoggerInfo("Credits", $values);

        $update_vip = $data->buyVip($vip['database'], $vip['table'], $vip['column_level'], $vip['column_days'], $home_data['memb___id'], $vip['level'], $vip_days, $config_muserver[3]['value']);

        if ($update_vip == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Vip adiquirido com sucesso'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Comprou  ' . $vip_days . ' dias de ' . $vip['name'] . ' por ' . $vip_price . ' créditos'
          );

          $logger->addLoggerInfo("Vips", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/vips");
          exit();
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error ao comprar vip tente novamente mais tarde'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $update_vip
          );

          $logger->addLoggerWarning("Error Vips", $values);

          $add_credits = $data->acrescentCredits($vip_price, $home_data['memb___id']);
          if ($add_credits == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => "Devolvido $vip_price créditos devido a error do vip"
            );

            $logger->addLoggerInfo("Credits", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $add_credits
            );

            $logger->addLoggerWarning("Error Credits", $values);
          }
          return $response->withRedirect(getenv("DIR") . "dashboard/vips");
          exit();
        }
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Error ao comprar vip tente novamente mais tarde'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $update_credits
        );

        $logger->addLoggerWarning("Error Credits", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/vips");
        exit();
      }
    }
  }

  public function getCoins(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data = $data->getUser($model);
    $coins     = $data->getCoinsConfigs();

    $array = array(
      'title_page' => 'Comprar Moedas',
      'home_data'  => $home_data,
      'coins'      => $coins,
    );

    return $view->getRender($array, 'dashboard-coins', $response);
  }

  public function postCoins(DashboardModel $model, Response $response, $post, $page, $id)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('coins');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (empty($page) or $page != 'buys' or empty($id)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error na compra tente novamente'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Teve um erro na hora de comprar da moeda ou tentaram burlar o sistema'
      );

      $logger->addLoggerWarning("Error Coins", $values);
      return $response->withRedirect(getenv("DIR") . "dashboard/coins");
      exit();
    }

    if (empty($post['credits']) or $post['credits'] < 1) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Você deve colocar a quantidade de créditos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/coins");
      exit();
    }

    if ($home_data['mwo_credits'] < $post['credits']) {

      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Você não tem créditos suficiente'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/coins");
      exit();
    }

    $coin           = $data->getCoinInfo($id);
    $update_credits = $data->removeCredits($post['credits'], $home_data['memb___id']);
    $price          = $coin['price'] * $post['credits'];

    if ($update_credits == 'OK') {
      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => "Removido " . $post['credits'] . " créditos da conta de " . $home_data['memb___id'] . ""
      );

      $logger->addLoggerInfo("Credits", $values);

      $update_coin = $data->updateCoin($coin['database'], $coin['table'], $coin['column'], $price, $home_data['memb___id']);

      if ($update_coin == 'OK') {
        $return = array(
          'error'   => false,
          'success' => true,
          'message' => '' . $price . ' ' . $coin['name'] . ' adiquiridos com sucesso'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => 'Comprou  ' . $price . ' ' . $coin['name'] . ' com ' . $post['credits'] . ' créditos'
        );

        $logger->addLoggerInfo("Coins", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/coins");
        exit();
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Error ao comprar ' . $coin['name'] . ' tente novamente mais tarde'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $update_coin
        );

        $logger->addLoggerWarning("Error Coins", $values);

        $add_credits = $data->acrescentCredits($post['credits'], $home_data['memb___id']);
        if ($add_credits == 'OK') {
          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => "Devolvido " . $post['credits'] . " créditos devido a error da moeda"
          );

          $logger->addLoggerInfo("Credits", $values);
        } else {
          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $add_credits
          );

          $logger->addLoggerWarning("Error Credits", $values);
        }
        return $response->withRedirect(getenv("DIR") . "dashboard/coins");
        exit();
      }
    } else {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error ao comprar ' . $coin['name'] . ' tente novamente mais tarde'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => $update_credits
      );

      $logger->addLoggerWarning("Error Credits", $values);
      return $response->withRedirect(getenv("DIR") . "dashboard/coins");
      exit();
    }
  }

  public function getSettings(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $settings_data     = $data->getUser($model);
    $settings_customer = $data->getCustomer($model->getUsername());

    $array = array(
      'title_page'        => 'Configurações',
      'settings_data'     => $settings_data,
      'settings_customer' => $settings_customer,
    );

    return $view->getRender($array, 'dashboard-settings', $response);
  }

  public function postSettings(DashboardModel $model, Response $response, $post, $page)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('settings');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (empty($page)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error na hora de editar suas configurações, tente novamente'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Teve um erro na hora de editar a configuração ou tentaram burlar o sistema'
      );

      $logger->addLoggerWarning("Error Settings", $values);
      return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      exit();
    }

    if ($page == 'password') {
      if (empty($post['currentpassword']) or empty($post['newpassword']) or empty($post['confimpassword'])) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Preencha todos os campos'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        exit();
      } elseif ($post['currentpassword'] != $home_data['memb__pwd']) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'A senha atual está incorreta'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      } elseif (strlen($post['newpassword']) < 4 or strlen($post['newpassword']) > 10) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'A nova senha é muita curta ou muito grande'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      } elseif (strlen($post['confimpassword']) < 4 or strlen($post['confimpassword']) > 10) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'A confirmação de senha é muita curta ou muito grande'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      } elseif ($post['newpassword'] != $post['confimpassword']) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'As senhas não conferem'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      } else {
        $update_password = $data->updatePassword($post['newpassword'], $home_data['memb___id']);
        if ($update_password == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Senha alterada com sucesso'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Alterou a senha'
          );

          $logger->addLoggerInfo("Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error ao alterar a senha, tente novamente'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $update_password
          );

          $logger->addLoggerWarning("Error Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        }
      }
    } elseif ($page == 'data') {
      if (empty($post['memb_name'])) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Preencha o campo nome'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        exit();
      } elseif ($post['cur_password'] != $home_data['memb__pwd']) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'A senha atual está incorreta'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      } else {
        $update_data = $data->updateData($post, $home_data['memb___id']);
        if ($update_data == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Dados alterado com sucesso'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Alterou os dados'
          );

          $logger->addLoggerInfo("Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error ao alterar os dados, tente novamente'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $update_data
          );

          $logger->addLoggerWarning("Error Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        }
      }
    } elseif ($page == 'personalid') {
      if (empty($post['personalid'])) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Preencha o Personal ID'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        exit();
      } elseif (is_numeric($post['personalid']) == false) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'O Personal ID deve ser somente números'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        exit();
      } elseif (strlen($post['personalid']) != 7) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'O Personal ID deve conter 7 caracteres'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        exit();
      } elseif ($post['cur_password'] != $home_data['memb__pwd']) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'A senha atual está incorreta'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      } else {
        $update_data = $data->updatePersonalID($post, $home_data['memb___id']);
        if ($update_data == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Personal ID alterado com sucesso'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Alterou o personal id'
          );

          $logger->addLoggerInfo("Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error ao alterar o personal id, tente novamente'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $update_data
          );

          $logger->addLoggerWarning("Error Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        }
      }
    } elseif ($page == 'customer') {
      if (empty($post['name']) or empty($post['email']) or empty($post['cpf']) or empty($post['street']) or empty($post['number']) or empty($post['district']) or empty($post['city']) or empty($post['state']) or empty($post['postalcode'])) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Preencha todos os campos'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        exit();
      } elseif ($post['cur_password'] != $home_data['memb__pwd']) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'A senha atual está incorreta'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      } else {
        $check_customer = $data->getCustomer($home_data['memb___id']);
        if (isset($check_customer['ID'])) {
          $update_customer = $data->updateCustomer($post, $home_data['memb___id']);
        } else {
          $update_customer = $data->createCustomer($post, $home_data['memb___id']);
        }
        if ($update_customer == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Dados de compra alterado com sucesso'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Alterou os dados de compra'
          );

          $logger->addLoggerInfo("Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error ao alterar os dados de compra, tente novamente'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $update_customer
          );

          $logger->addLoggerWarning("Error Settings", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/settings");
        }
      }
    } else {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error na hora de editar suas configurações, tente novamente'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Teve um erro na hora de editar a configuração ou tentaram burlar o sistema'
      );

      $logger->addLoggerWarning("Error Settings", $values);
      return $response->withRedirect(getenv("DIR") . "dashboard/settings");
      exit();
    }
  }

  public function getTickets(DashboardModel $model, View $view, Response $response, $page, $id)
  {
    //Classes
    $data     = new DashboardDatabase();
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (!empty($page) and $page == 'create') {
      $array = array(
        'title_page' => 'Criar Ticket',
      );

      return $view->getRender($array, 'dashboard-ticketscreate', $response);
    } elseif (!empty($page) and $page == 'view') {
      if (empty($id)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Error ao visualizar o ticket, tente novamente'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/tickets");
        exit();
      }

      $ticket_data = $data->getTicketInfo($id);

      if (empty($ticket_data)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Error ao visualizar o ticket, tente novamente'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/tickets");
        exit();
      } elseif ($ticket_data['username'] != $home_data['memb___id']) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Esse ticket não pertence a você'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/tickets");
        exit();
      } else {
        $ticket_answer = $data->getTicketAnswer($id);

        $array = array(
          'title_page'    => 'Ver Ticket',
          'ticket_data'   => $ticket_data,
          'ticket_answer' => $ticket_answer,
        );

        return $view->getRender($array, 'dashboard-ticketsview', $response);
      }
    } else {
      $array = array(
        'title_page' => 'Tickets',
        'tickets'  => $data->getTickets($home_data['memb___id']),
      );

      return $view->getRender($array, 'dashboard-tickets', $response);
    }
  }

  public function postTickets(DashboardModel $model, Response $response, $post, $files, $page)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('tickets');
    $messages = new ViewMessages();

    //Variables
    $home_data    = $data->getUser($model);
    $patch_upload = getenv('DIRIMG');
    $patch_images = getenv('DIRECTORY_ROOT') . $patch_upload . "tickets";

    if (empty($page)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error ao criar o ticket, tente novamente'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Teve um erro na hora de criar o ticket ou tentaram burlar o sistema'
      );

      $logger->addLoggerWarning("Error Settings", $values);
      return $response->withRedirect(getenv("DIR") . "dashboard/tickets/create");
      exit();
    }

    if ($page == 'create') {
      if (empty($post['subject']) or empty($post['message'])) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Preencha todos os campos'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/tickets/create");
        exit();
      } else {
        if (empty($files['mwo_image'])) {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Preencha todos os campos'
          );

          $messages->addMessage('response', $return);
          return $response->withRedirect(getenv("DIR") . "dashboard/tickets/create");
          exit();
        }

        $image = $files['mwo_image'];
        if ($image->getError() === UPLOAD_ERR_OK) {
          $imagename = $this->moveUploadedFile($patch_images, $image, strtolower(preg_replace('/\s*/', '', $home_data['memb___id'])));
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error upload de imagem tente novamente'
          );

          $messages->addMessage('response', $return);
          return $response->withRedirect(getenv("DIR") . "dashboard/tickets/create");
          exit();
        }
        $create_ticket = $data->createTicket($post, $imagename, $home_data['memb___id']);
        if ($create_ticket == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Ticket criado com sucesso'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Criou um ticket'
          );

          $logger->addLoggerInfo("Tickets", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/tickets");
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Error ao criar ticket, tente novamente'
          );

          $messages->addMessage('response', $return);

          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $create_ticket
          );

          $logger->addLoggerWarning("Error Tickets", $values);
          return $response->withRedirect(getenv("DIR") . "dashboard/tickets/create");
        }
      }
    } else {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error ao criar o ticket, tente novamente'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $model->getUsername(),
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Teve um erro na hora de criar o ticket ou tentaram burlar o sistema'
      );

      $logger->addLoggerWarning("Error Settings", $values);
      return $response->withRedirect(getenv("DIR") . "dashboard/tickets/create");
      exit();
    }
  }

  public function getResets(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data       = $data->getUser($model);
    $characters_list = $data->getCharacters($home_data['memb___id']);

    $array = array(
      'title_page'      => 'Reset',
      'characters_list' => $characters_list,
    );

    return $view->getRender($array, 'characters-reset', $response);
  }

  public function postResets(DashboardModel $model, Response $response, $post)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('resets');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (empty($post['character'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      exit();
    } else {
      $config_columns   = $data->getConfig('columns');
      $config_columns   = json_decode($config_columns, true);
      $config_resets    = $data->getConfig('reset');
      $config_resets    = json_decode($config_resets, true);
      $config_class     = $data->getConfig('classcodes');
      $config_class     = json_decode($config_class, true);
      $charater_details = $data->getCharacter($config_columns[0]['value'], $home_data['memb___id'], $post['character']);
      $vips             = $data->getVipsConfigs();

      if (empty($charater_details)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Personagem não encontrado'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      }

      if (empty($vips)) {
        $vip_level = 0;
      } else {
        foreach ($vips as $key => $value) {
          if ($home_data[$value['column_level']] == $value['level']) {
            $vip_level = $value['level'];
            break;
          } else {
            $vip_level = 0;
          }
        }
      }

      foreach ($config_class as $key => $value) {
        if ($charater_details['Class'] == $value['value']) {
          $class_character = $value['name'];
          break;
        } else {
          $class_character = 'DW';
        }
      }

      $LIMIT_RESETS = $config_resets[0]['value'];
      $LIMIT_RESETS = explode(',', $LIMIT_RESETS);
      $LIMIT_RESETS = $LIMIT_RESETS[$vip_level];

      $LEVEL_RESETS = $config_resets[1]['value'];
      $LEVEL_RESETS = explode(',', $LEVEL_RESETS);
      $LEVEL_RESETS = $LEVEL_RESETS[$vip_level];

      $LEVEL_AFTER = $config_resets[2]['value'];
      $LEVEL_AFTER = explode(',', $LEVEL_AFTER);
      $LEVEL_AFTER = $LEVEL_AFTER[$vip_level];

      $ZEN_REQUIRE = $config_resets[3]['value'];
      $ZEN_REQUIRE = explode(',', $ZEN_REQUIRE);
      $ZEN_REQUIRE = $LEVEL_RESETS[$vip_level];

      $POINTS = $config_resets[4]['value'];
      $POINTS = explode(',', $POINTS);
      $POINTS = $POINTS[$vip_level];

      $CLEAR_ITENS = $config_resets[5]['value'];
      $CLEAR_ITENS = explode(',', $CLEAR_ITENS);
      $CLEAR_ITENS = $CLEAR_ITENS[$vip_level];

      $CLEAR_MAGICS = $config_resets[6]['value'];
      $CLEAR_MAGICS = explode(',', $CLEAR_MAGICS);
      $CLEAR_MAGICS = $CLEAR_MAGICS[$vip_level];

      $CLEAR_QUESTS = $config_resets[7]['value'];
      $CLEAR_QUESTS = explode(',', $CLEAR_QUESTS);
      $CLEAR_QUESTS = $CLEAR_QUESTS[$vip_level];

      $connectstat = $data->checkOnlineAccount($home_data['memb___id']);
      if ($connectstat == 1) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você deve deslogar para resetar'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      } elseif ($charater_details[$config_columns[0]['value']] >= $LIMIT_RESETS and $LIMIT_RESETS != 0) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você já alcançou o limite de resets'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      } elseif ($charater_details['cLevel'] < $LEVEL_RESETS) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem level necessário para resetar'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      } elseif ($charater_details['Money'] < $ZEN_REQUIRE) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem zen necessário para resetar'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      }

      $resetarray = array(
        'cLevel'       => $LEVEL_AFTER,
        'MapNumber'    => ($class_character == 'FE' or $class_character == 'ME' or $class_character == 'HE') ? 3 : 0,
        'MapPosX'      => ($class_character == 'FE' or $class_character == 'ME' or $class_character == 'HE') ? 174 : 125,
        'MapPosY'      => ($class_character == 'FE' or $class_character == 'ME' or $class_character == 'HE') ? 111 : 125,
        'Money'        => $ZEN_REQUIRE,
        'LevelUpPoint' => $POINTS * $charater_details[$config_columns[0]['value']],
      );

      $resetcharacter = $data->resetCharacter($config_columns, $resetarray, $post['character']);
      if ($resetcharacter == 'OK') {
        $return = array(
          'error'   => false,
          'success' => true,
          'message' => 'Personagem resetado com sucesso'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => 'Resetou o personagem ' . $post['character'] . ''
        );

        $logger->addLoggerInfo("Reset", $values);

        if ($CLEAR_ITENS == 'true') {
          $clearitens = $data->clearItens($post['character']);
          if ($clearitens == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Resetou os itens do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("Clear Itens", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $clearitens
            );

            $logger->addLoggerWarning("Error Clear Itens", $values);
          }
        }

        if ($CLEAR_MAGICS == 'true') {
          $clearmagics = $data->clearItens($post['character']);
          if ($clearmagics == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Resetou as magias do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("Clear Magics", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $clearmagics
            );

            $logger->addLoggerWarning("Error Clear Magics", $values);
          }
        }

        if ($CLEAR_QUESTS == 'true') {
          $clearquests = $data->clearQuests($this->resetClassCode($charater_details['Class']), $post['character']);
          if ($clearquests == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Resetou as quests do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("Clear Quests", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $clearquests
            );

            $logger->addLoggerWarning("Error Clear Quests", $values);
          }
        }

        return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Não foi possível resetar o personagem, tente novamente'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $resetcharacter
        );

        $logger->addLoggerWarning("Error Reset", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/reset");
      }
    }
  }

  public function getMasterResets(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data       = $data->getUser($model);
    $characters_list = $data->getCharacters($home_data['memb___id']);

    $array = array(
      'title_page'      => 'Master Reset',
      'characters_list' => $characters_list,
    );

    return $view->getRender($array, 'characters-masterreset', $response);
  }

  public function postMasterResets(DashboardModel $model, Response $response, $post)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('masterresets');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (empty($post['character'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      exit();
    } else {
      $config_columns   = $data->getConfig('columns');
      $config_columns   = json_decode($config_columns, true);
      $config_resets    = $data->getConfig('masterreset');
      $config_resets    = json_decode($config_resets, true);
      $config_class     = $data->getConfig('classcodes');
      $config_class     = json_decode($config_class, true);
      $charater_details = $data->getCharacter($config_columns[0]['value'], $home_data['memb___id'], $post['character']);
      $vips             = $data->getVipsConfigs();

      if (empty($charater_details)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Personagem não encontrado'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      }

      if (empty($vips)) {
        $vip_level = 0;
      } else {
        foreach ($vips as $key => $value) {
          if ($home_data[$value['column_level']] == $value['level']) {
            $vip_level = $value['level'];
            break;
          } else {
            $vip_level = 0;
          }
        }
      }

      foreach ($config_class as $key => $value) {
        if ($charater_details['Class'] == $value['value']) {
          $class_character = $value['name'];
          break;
        } else {
          $class_character = 'DW';
        }
      }

      $LIMIT_RESETS = $config_resets[0]['value'];
      $LIMIT_RESETS = explode(',', $LIMIT_RESETS);
      $LIMIT_RESETS = $LIMIT_RESETS[$vip_level];

      $LEVEL_RESETS = $config_resets[1]['value'];
      $LEVEL_RESETS = explode(',', $LEVEL_RESETS);
      $LEVEL_RESETS = $LEVEL_RESETS[$vip_level];

      $LEVEL_AFTER = $config_resets[2]['value'];
      $LEVEL_AFTER = explode(',', $LEVEL_AFTER);
      $LEVEL_AFTER = $LEVEL_AFTER[$vip_level];

      $ZEN_REQUIRE = $config_resets[3]['value'];
      $ZEN_REQUIRE = explode(',', $ZEN_REQUIRE);
      $ZEN_REQUIRE = $LEVEL_RESETS[$vip_level];

      $POINTS = $config_resets[4]['value'];
      $POINTS = explode(',', $POINTS);
      $POINTS = $POINTS[$vip_level];

      $CLEAR_ITENS = $config_resets[5]['value'];
      $CLEAR_ITENS = explode(',', $CLEAR_ITENS);
      $CLEAR_ITENS = $CLEAR_ITENS[$vip_level];

      $CLEAR_MAGICS = $config_resets[6]['value'];
      $CLEAR_MAGICS = explode(',', $CLEAR_MAGICS);
      $CLEAR_MAGICS = $CLEAR_MAGICS[$vip_level];

      $CLEAR_QUESTS = $config_resets[7]['value'];
      $CLEAR_QUESTS = explode(',', $CLEAR_QUESTS);
      $CLEAR_QUESTS = $CLEAR_QUESTS[$vip_level];

      $REQUIRE_RESETS = $config_resets[8]['value'];
      $REQUIRE_RESETS = explode(',', $REQUIRE_RESETS);
      $REQUIRE_RESETS = $REQUIRE_RESETS[$vip_level];

      $connectstat = $data->checkOnlineAccount($home_data['memb___id']);
      if ($connectstat == 1) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você deve deslogar para master reset'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      } elseif ($charater_details[$config_columns[0]['value']] >= $LIMIT_RESETS and $LIMIT_RESETS != 0) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você já alcançou o limite de resets'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      } elseif ($charater_details['cLevel'] < $LEVEL_RESETS) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem level necessário para master reset'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      } elseif ($charater_details['Money'] < $ZEN_REQUIRE) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem zen necessário para master reset'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      } elseif ($charater_details[$config_columns[0]['value']] < $REQUIRE_RESETS) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem resets necessário para master reset'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      }

      $resetarray = array(
        'RequireResets' => $REQUIRE_RESETS,
        'cLevel'        => $LEVEL_AFTER,
        'MapNumber'     => ($class_character == 'FE' or $class_character == 'ME' or $class_character == 'HE') ? 3 : 0,
        'MapPosX'       => ($class_character == 'FE' or $class_character == 'ME' or $class_character == 'HE') ? 174 : 125,
        'MapPosY'       => ($class_character == 'FE' or $class_character == 'ME' or $class_character == 'HE') ? 111 : 125,
        'Money'         => $ZEN_REQUIRE,
        'LevelUpPoint'  => $POINTS * $charater_details[$config_columns[0]['value']],
      );

      $resetcharacter = $data->masterresetCharacter($config_columns, $resetarray, $post['character']);
      if ($resetcharacter == 'OK') {
        $return = array(
          'error'   => false,
          'success' => true,
          'message' => 'Personagem master resetado com sucesso'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => 'Master Resetou o personagem ' . $post['character'] . ''
        );

        $logger->addLoggerInfo("Master Reset", $values);

        if ($CLEAR_ITENS == 'true') {
          $clearitens = $data->clearItens($post['character']);
          if ($clearitens == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Master Resetou os itens do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("Clear Itens", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $clearitens
            );

            $logger->addLoggerWarning("Error Clear Itens", $values);
          }
        }

        if ($CLEAR_MAGICS == 'true') {
          $clearmagics = $data->clearItens($post['character']);
          if ($clearmagics == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Master Resetou as magias do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("Clear Magics", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $clearmagics
            );

            $logger->addLoggerWarning("Error Clear Magics", $values);
          }
        }

        if ($CLEAR_QUESTS == 'true') {
          $clearquests = $data->clearQuests($this->resetClassCode($charater_details['Class']), $post['character']);
          if ($clearquests == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Master Resetou as quests do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("Clear Quests", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $clearquests
            );

            $logger->addLoggerWarning("Error Clear Quests", $values);
          }
        }

        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Não foi possível master resetar o personagem, tente novamente'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $resetcharacter
        );

        $logger->addLoggerWarning("Error Master Reset", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/masterreset");
      }
    }
  }

  public function resetClassCode($class)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $config_class = $data->getConfig('classcodes');
    $config_class = json_decode($config_class, true);

    foreach ($config_class as $key => $value) {
      if ($class == $value['value']) {
        $class_character = $value['name'];
        break;
      } else {
        $class_character = 'DW';
      }
    }

    if ($class_character == 'DW' or $class_character == 'SM' or $class_character == 'GM') {
      return $config_class[0]['value'];
    } elseif ($class_character == 'DK' or $class_character == 'BK' or $class_character == 'BM') {
      return $config_class[3]['value'];
    } elseif ($class_character == 'FE' or $class_character == 'ME' or $class_character == 'HE') {
      return $config_class[6]['value'];
    } elseif ($class_character == 'MG' or $class_character == 'DMM') {
      return $config_class[9]['value'];
    } elseif ($class_character == 'DL' or $class_character == 'LE') {
      return $config_class[11]['value'];
    } elseif ($class_character == 'SU' or $class_character == 'BS' or $class_character == 'DMS') {
      return $config_class[13]['value'];
    } elseif ($class_character == 'RF' or $class_character == 'FM') {
      return $config_class[16]['value'];
    } else {
      return $config_class[0]['value'];
    }
  }

  public function getCleanPK(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data       = $data->getUser($model);
    $characters_list = $data->getCharacters($home_data['memb___id']);

    $array = array(
      'title_page'      => 'Limpar PK',
      'characters_list' => $characters_list,
    );

    return $view->getRender($array, 'characters-cleanpk', $response);
  }

  public function postCleanPK(DashboardModel $model, Response $response, $post)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('cleanpk');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (empty($post['character'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/characters/cleanpk");
      exit();
    } else {
      $config_columns   = $data->getConfig('columns');
      $config_columns   = json_decode($config_columns, true);
      $config_cleanpk   = $data->getConfig('cleanpk');
      $config_cleanpk   = json_decode($config_cleanpk, true);
      $config_class     = $data->getConfig('classcodes');
      $config_class     = json_decode($config_class, true);
      $charater_details = $data->getCharacter($config_columns[0]['value'], $home_data['memb___id'], $post['character']);
      $vips             = $data->getVipsConfigs();

      if (empty($charater_details)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Personagem não encontrado'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/cleanpk");
      }

      if (empty($vips)) {
        $vip_level = 0;
      } else {
        foreach ($vips as $key => $value) {
          if ($home_data[$value['column_level']] == $value['level']) {
            $vip_level = $value['level'];
            break;
          } else {
            $vip_level = 0;
          }
        }
      }

      foreach ($config_class as $key => $value) {
        if ($charater_details['Class'] == $value['value']) {
          $class_character = $value['name'];
          break;
        } else {
          $class_character = 'DW';
        }
      }

      $CLEAN_MODE = $config_cleanpk[0]['value'];
      if ($CLEAN_MODE == 2) {
        $PRICEZEN = $config_cleanpk[1]['value'];
        $PRICEZEN = explode(',', $PRICEZEN);
        $PRICEZEN = $PRICEZEN[$vip_level] * $charater_details['PKCount'];
      } else {
        $PRICEZEN = $config_cleanpk[1]['value'];
        $PRICEZEN = explode(',', $PRICEZEN);
        $PRICEZEN = $PRICEZEN[$vip_level];
      }

      $connectstat = $data->checkOnlineAccount($home_data['memb___id']);
      if ($connectstat == 1) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você deve deslogar para limpar pk'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/cleanpk");
      } elseif ($charater_details['Money'] < $PRICEZEN) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem zen necessário para limpar pk'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/cleanpk");
      }

      $cleanpk = $data->cleanPK($PRICEZEN, $post['character']);
      if ($cleanpk == 'OK') {
        $return = array(
          'error'   => false,
          'success' => true,
          'message' => 'PK limpo com sucesso'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => 'Limpou o pk do personagem ' . $post['character'] . ''
        );

        $logger->addLoggerInfo("Clean PK", $values);

        return $response->withRedirect(getenv("DIR") . "dashboard/characters/cleanpk");
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Não foi possível limpar o pk do personagem, tente novamente'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $cleanpk
        );

        $logger->addLoggerWarning("Error Clean PK", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/cleanpk");
      }
    }
  }

  public function getChangeNick(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data       = $data->getUser($model);
    $characters_list = $data->getCharacters($home_data['memb___id']);

    $array = array(
      'title_page'      => 'Alterar Nome',
      'characters_list' => $characters_list,
    );

    return $view->getRender($array, 'characters-changenick', $response);
  }

  public function postChangeNick(DashboardModel $model, Response $response, $post)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('changenick');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (empty($post['character']) or empty($post['nick'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      exit();
    } else {
      $config_columns    = $data->getConfig('columns');
      $config_columns    = json_decode($config_columns, true);
      $config_changenick = $data->getConfig('changenick');
      $config_changenick = json_decode($config_changenick, true);
      $config_muserver   = $data->getConfig('muserver');
      $config_muserver   = json_decode($config_muserver, true);
      $charater_details  = $data->getCharacter($config_columns[0]['value'], $home_data['memb___id'], $post['character']);
      $check_nick        = $data->getCharacterNick($post['nick']);
      $check_guild       = $data->getCharacterGuild($post['character']);
      $vips              = $data->getVipsConfigs();

      if (empty($charater_details)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Personagem não encontrado'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      }

      if (empty($vips)) {
        $vip_level = 0;
      } else {
        foreach ($vips as $key => $value) {
          if ($home_data[$value['column_level']] == $value['level']) {
            $vip_level = $value['level'];
            break;
          } else {
            $vip_level = 0;
          }
        }
      }

      $PRICEZEN      = $config_changenick[0]['value'];
      $PRICEZEN      = explode(',', $PRICEZEN);
      $PRICEZEN      = $PRICEZEN[$vip_level];
      $BLOCKED_NAMES = $config_changenick[1]['value'];
      $BLOCKED_NAMES = explode(',', $BLOCKED_NAMES);
      $connectstat   = $data->checkOnlineAccount($home_data['memb___id']);

      foreach ($BLOCKED_NAMES as $word) {
        if (strpos(strtoupper($post['nick']), $word) !== false) {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Você não pode usar o nome ' . $word . ''
          );

          $messages->addMessage('response', $return);
          return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
          exit();
        }
      }

      if ($connectstat == 1) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você deve deslogar para alterar nome'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      } elseif ($charater_details['Money'] < $PRICEZEN) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem zen necessário para alterar nome'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      } elseif (!empty($check_nick)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Já existe um personagem com esse nome'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      } elseif (!empty($check_guild)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você deve sair da guild para mudar nome'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      } elseif ($charater_details['CtlCode'] == 1) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você está bloqueado e não pode mudar nick'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      }

      $changenick = $data->changeNick($PRICEZEN, $post['nick'], $post['character']);
      if ($changenick == 'OK') {
        $return = array(
          'error'   => false,
          'success' => true,
          'message' => 'Nome alterado com sucesso'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => 'Alterou o nome do personagem ' . $post['character'] . ' para ' . $post['nick'] . ''
        );

        $logger->addLoggerInfo("Change Nick", $values);

        $accountcharacter = $data->getAccountCharacter($home_data['memb___id']);

        if ($accountcharacter['GameID1'] == $post['character']) $slot = "GameID1";
        if ($accountcharacter['GameID2'] == $post['character']) $slot = "GameID2";
        if ($accountcharacter['GameID3'] == $post['character']) $slot = "GameID3";
        if ($accountcharacter['GameID4'] == $post['character']) $slot = "GameID4";
        if ($accountcharacter['GameID5'] == $post['character']) $slot = "GameID5";

        $editaccountcharacter = $data->editAccountCharacter($slot, $post['nick'], $home_data['memb___id']);
        if ($editaccountcharacter == 'OK') {
          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => 'Alterou o nome do personagem ' . $post['character'] . ' para ' . $post['nick'] . ''
          );

          $logger->addLoggerInfo("AccountCharacter", $values);
        } else {
          $values = array(
            'username'  => $model->getUsername(),
            'ipaddress' => $model->getIpaddress(),
            'message'   => $editaccountcharacter
          );

          $logger->addLoggerWarning("Error AccountCharacter", $values);
        }

        if ($config_muserver[2]['value'] >= 5 and $config_muserver[3]['value'] == 1) {
          $deletemasterskill = $data->deleteMasterSkillTree($post['character']);
          if ($deletemasterskill == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Alterou o nome do personagem ' . $post['character'] . ' para ' . $post['nick'] . ''
            );

            $logger->addLoggerInfo("MasterSkillTree", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $deletemasterskill
            );

            $logger->addLoggerWarning("Error MasterSkillTree", $values);
          }
        }

        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Não foi possível alterar o nome do personagem, tente novamente'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $changenick
        );

        $logger->addLoggerWarning("Error Change Nick", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changenick");
      }
    }
  }

  public function getChangeClass(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data       = $data->getUser($model);
    $characters_list = $data->getCharacters($home_data['memb___id']);
    $config_class    = $data->getConfig('classcodes');
    $config_class    = json_decode($config_class, true);
    $config_muserver = $data->getConfig('muserver');
    $config_muserver = json_decode($config_muserver, true);

    $array = array(
      'title_page'      => 'Alterar Classe',
      'characters_list' => $characters_list,
      'listclass'       => $this->listClass($config_muserver[2]['value'], $config_class),
    );

    return $view->getRender($array, 'characters-changeclass', $response);
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

  public function postChangeClass(DashboardModel $model, Response $response, $post)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('changeclass');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);

    if (empty($post['character']) or empty($post['class'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeclass");
      exit();
    } else {
      $config_columns     = $data->getConfig('columns');
      $config_columns     = json_decode($config_columns, true);
      $config_changeclass = $data->getConfig('changeclass');
      $config_changeclass = json_decode($config_changeclass, true);
      $config_muserver    = $data->getConfig('muserver');
      $config_muserver    = json_decode($config_muserver, true);
      $charater_details   = $data->getCharacter($config_columns[0]['value'], $home_data['memb___id'], $post['character']);
      $vips               = $data->getVipsConfigs();

      if (empty($charater_details)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Personagem não encontrado'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeclass");
      }

      if (empty($vips)) {
        $vip_level = 0;
      } else {
        foreach ($vips as $key => $value) {
          if ($home_data[$value['column_level']] == $value['level']) {
            $vip_level = $value['level'];
            break;
          } else {
            $vip_level = 0;
          }
        }
      }

      $PRICEZEN     = $config_changeclass[0]['value'];
      $PRICEZEN     = explode(',', $PRICEZEN);
      $PRICEZEN     = $PRICEZEN[$vip_level];
      $RESET_QUESTS = $config_changeclass[1]['value'];
      $RESET_SKILLS = $config_changeclass[2]['value'];
      $connectstat  = $data->checkOnlineAccount($home_data['memb___id']);

      if ($connectstat == 1) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você deve deslogar para alterar nome'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeclass");
      } elseif ($charater_details['Money'] < $PRICEZEN) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem zen necessário para alterar classe'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeclass");
      }

      $changeclass = $data->changeClass($PRICEZEN, $post['class'], $post['character']);
      if ($changeclass == 'OK') {
        $return = array(
          'error'   => false,
          'success' => true,
          'message' => 'Classe alterada com sucesso'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => 'Alterou a classe do personagem ' . $post['character'] . ''
        );

        $logger->addLoggerInfo("Change Classe", $values);

        if ($config_muserver[2]['value'] >= 5 and $config_muserver[3]['value'] == 1) {
          $deletemasterskill = $data->deleteMasterSkillTree($post['character']);
          if ($deletemasterskill == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Alterou a classe do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("MasterSkillTree", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $deletemasterskill
            );

            $logger->addLoggerWarning("Error MasterSkillTree", $values);
          }
        }

        if ($RESET_QUESTS == 'true') {
          $resetquests = $data->resetQuets($post['character']);
          if ($resetquests == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Alterou a classe do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("ResetQuest", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $resetquests
            );

            $logger->addLoggerWarning("Error ResetQuest", $values);
          }
        }

        if ($RESET_SKILLS == 'true') {
          $resetskills = $data->resetSkills($this->listSkills($config_muserver[2]['value']), $post['character']);
          if ($resetskills == 'OK') {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => 'Alterou a classe do personagem ' . $post['character'] . ''
            );

            $logger->addLoggerInfo("ResetSkill", $values);
          } else {
            $values = array(
              'username'  => $model->getUsername(),
              'ipaddress' => $model->getIpaddress(),
              'message'   => $resetskills
            );

            $logger->addLoggerWarning("Error ResetSkill", $values);
          }
        }

        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeclass");
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Não foi possível alterar o nome do personagem, tente novamente'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $changeclass
        );

        $logger->addLoggerWarning("Error Change Nick", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeclass");
      }
    }
  }

  public function listSkills($version)
  {
    switch ($version) {
      case 0: //Season 1 ou Abaixo - Sem DL
        $skill = 60;
        break;
      case 1: //Season 1 ou Abaixo
        $skill = 60;
        break;
      case 2: //Season 2
        $skill = 180;
        break;
      case 3: //Season 3 Episodio 1
        $skill = 180;
        break;
      case 4: //Season 4
        $skill = 180;
        break;
      case 5:
      case 6: //Season 6
        $skill = 180;
        break;
    }

    return $skill;
  }

  public function getChangeImage(DashboardModel $model, View $view, Response $response)
  {
    //Classes
    $data = new DashboardDatabase();

    //Variables
    $home_data       = $data->getUser($model);
    $characters_list = $data->getCharacters($home_data['memb___id']);

    $array = array(
      'title_page'      => 'Alterar Imagem',
      'characters_list' => $characters_list,
    );

    return $view->getRender($array, 'characters-image', $response);
  }

  public function postChangeImage(DashboardModel $model, Response $response, $post, $files)
  {
    //Classes
    $data     = new DashboardDatabase();
    $logger   = new ViewLogger('changeimage');
    $messages = new ViewMessages();

    //Variables
    $home_data = $data->getUser($model);
    $patch_upload = getenv('DIRIMG');
    $patch_images = getenv('DIRECTORY_ROOT') . $patch_upload . "users";

    if (empty($post['character']) or empty($files['mwo_image'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeimage");
      exit();
    } else {
      $config_columns     = $data->getConfig('columns');
      $config_columns     = json_decode($config_columns, true);
      $config_changeimage = $data->getConfig('changeclass');
      $config_changeimage = json_decode($config_changeimage, true);
      $config_muserver    = $data->getConfig('muserver');
      $config_muserver    = json_decode($config_muserver, true);
      $charater_details   = $data->getCharacter($config_columns[0]['value'], $home_data['memb___id'], $post['character']);
      $vips               = $data->getVipsConfigs();

      if (empty($charater_details)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Personagem não encontrado'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeimage");
      }

      if (empty($vips)) {
        $vip_level = 0;
      } else {
        foreach ($vips as $key => $value) {
          if ($home_data[$value['column_level']] == $value['level']) {
            $vip_level = $value['level'];
            break;
          } else {
            $vip_level = 0;
          }
        }
      }

      $PRICEZEN = $config_changeimage[0]['value'];
      $PRICEZEN = explode(',', $PRICEZEN);
      $PRICEZEN = $PRICEZEN[$vip_level];

      if ($charater_details['Money'] < $PRICEZEN) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Você não tem zen necessário para alterar imagem'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeimage");
      }

      $image = $files['mwo_image'];
      if ($image->getError() === UPLOAD_ERR_OK) {
        $imagename = $this->moveUploadedFile($patch_images, $image, $post['character']);
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Error upload de imagem tente novamente'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeimage");
        exit();
      }

      $changeimage = $data->changeImage($PRICEZEN, $imagename, $post['character']);
      if ($changeimage == 'OK') {
        $return = array(
          'error'   => false,
          'success' => true,
          'message' => 'Imagem alterada com sucesso'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => 'Alterou a imagem do personagem ' . $post['character'] . ''
        );

        $logger->addLoggerInfo("Change Image", $values);

        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeimage");
      } else {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Não foi possível alterar a imagem do personagem, tente novamente'
        );

        $messages->addMessage('response', $return);

        $values = array(
          'username'  => $model->getUsername(),
          'ipaddress' => $model->getIpaddress(),
          'message'   => $changeimage
        );

        $logger->addLoggerWarning("Error Change Image", $values);
        return $response->withRedirect(getenv("DIR") . "dashboard/characters/changeimage");
      }
    }
  }

  public function getNoVip(DashboardModel $model, View $view, Response $response)
  {

    $array = array(
      'title_page' => 'Negado',
    );

    return $view->getRender($array, 'dashboard-novip', $response);
  }

  public function getBlocked(DashboardModel $model, View $view, Response $response)
  {

    $array = array(
      'title_page' => 'Bloqueado',
    );

    return $view->getRender($array, 'dashboard-blocked', $response);
  }

  public function moveUploadedFile($directory, UploadedFile $uploadedFile, $name)
  {
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(rand());
    $filename = sprintf('%s-%s.%0.8s', $name, $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
  }
}
