<?php

namespace App\Controllers;

use App\Database\LoginDatabase;
use App\Models\LoginModel;
use App\Views\View;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use Slim\Http\Response;

class LoginController
{

  public function getLogin(LoginModel $model, View $view, Response $response)
  {

    $array = array(
      'title_page' => 'Login',
    );

    return $view->getRender($array, 'login', $response);
  }

  public function postLogin(LoginModel $model, Response $response)
  {
    //Classes
    $data     = new LoginDatabase();
    $logger   = new ViewLogger('login');
    $messages = new ViewMessages();

    //Variables
    $login       = $data->login($model);
    $password    = $model->getPassword();

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

      return $response->withRedirect(getenv("DIR") . "login");
      exit();
    } elseif ($password != $login['memb__pwd']) {
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

      return $response->withRedirect(getenv("DIR") . "login");
      exit();
    } else {
      $_SESSION['loggedinuser'] = true;
      $_SESSION['usernameuser'] = $model->getUsername();

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

      return $response->withRedirect(getenv("DIR") . "dashboard");
      exit();
    }
  }
}
