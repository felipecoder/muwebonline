<?php

namespace App\Controllers;

use App\Database\NewPasswordDatabase;
use App\Models\NewPasswordModel;
use App\Views\View;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use Slim\Http\Response;

class NewPasswordController
{

  public function getNewPassword(NewPasswordModel $model, View $view, Response $response, $username = NULL, $token = NULL)
  {
    $array = array(
      'title_page' => 'Nova Senha',
      'username'   => $username,
      'token'      => $token
    );

    return $view->getRender($array, 'newpassword', $response);
  }

  public function postNewPassword(NewPasswordModel $model, Response $response, $post, $username = NULL, $token = NULL)
  {
    //Classes
    $data     = new NewPasswordDatabase();
    $logger   = new ViewLogger('newpassword');
    $messages = new ViewMessages();

    //variables
    $config_muserver = $data->getConfig('muserver');
    $config_muserver = json_decode($config_muserver, true);

    if (empty($username) or empty($token)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error no link utilize o enviado no seu email'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "login");
      exit();
    } elseif (empty($post['password']) or empty($post['repassword'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);

      return $response->withRedirect(getenv("DIR") . "newpassword/{$username}/{$token}");
      exit();
    } elseif (strlen($post['password']) < 4 or strlen($post['password']) > 10) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'A senha é muita curta ou muito grande'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "newpassword/{$username}/{$token}");
    } elseif (strlen($post['repassword']) < 4 or strlen($post['repassword']) > 10) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'A confirmação de senha é muita curta ou muito grande'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "newpassword/{$username}/{$token}");
    } elseif ($post['password'] != $post['repassword']) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'As senhas não conferem'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "newpassword/{$username}/{$token}");
    }

    $getuser = $data->getUser($username, $token);

    if (empty($getuser)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Usuário ou token inválidos, utilize o link enviado no seu email'
      );

      $messages->addMessage('response', $return);

      return $response->withRedirect(getenv("DIR") . "login");
      exit();
    }

    if ($config_muserver[0]['value'] == 'true') {
      $updatepassword = $data->updatePasswordMD5($post['repassword'], $username);
    } else {
      $updatepassword = $data->updatePassword($post['repassword'], $username);
    }

    if ($updatepassword == 'OK') {
      $return = array(
        'error'   => false,
        'success' => true,
        'message' => 'Senha Alterada com sucesso'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $username,
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Alterou sua senha'
      );

      $logger->addLoggerInfo("NewPassword", $values);

      return $response->withRedirect(getenv("DIR") . "login");
      exit();
    } else {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Não foi possível solicitar alterar sua senha, tente novamente'
      );

      $values = array(
        'username'  => $username,
        'ipaddress' => $model->getIpaddress(),
        'message'   => $updatepassword
      );

      $logger->addLoggerWarning("Error NewPassword", $values);

      return $response->withRedirect(getenv("DIR") . "newpassword/{$username}/{$token}");
      exit();
    }
  }
}
