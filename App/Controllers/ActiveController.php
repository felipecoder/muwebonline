<?php

namespace App\Controllers;

use App\Database\ActiveDatabase;
use App\Models\ActiveModel;
use App\Views\View;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use Slim\Http\Response;

class ActiveController
{

  public function getActive(ActiveModel $model, View $view, Response $response, $username = NULL, $token = NULL)
  {
    //classes
    $data     = new ActiveDatabase();
    $logger   = new ViewLogger('active');
    $messages = new ViewMessages();

    //variables
    if (empty($username) or empty($token)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error no link utilize o enviado no seu email'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "login");
      exit();
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

    $updatemailcheck = $data->updateMailCheck($username);
    if ($updatemailcheck == 'OK') {
      $return = array(
        'error'   => false,
        'success' => true,
        'message' => 'Conta ativada com sucesso'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $username,
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Ativou sua conta'
      );

      $logger->addLoggerInfo("Active", $values);
      return $response->withRedirect(getenv("DIR") . "login");
      exit();
    } else {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Não foi possivel ativar sua conta, tente novamente'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $username,
        'ipaddress' => $model->getIpaddress(),
        'message'   => $updatemailcheck
      );

      $logger->addLoggerWarning("Error Active", $values);
      return $response->withRedirect(getenv("DIR") . "login");
      exit();
    }
  }
}
