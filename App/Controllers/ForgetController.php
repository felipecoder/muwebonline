<?php

namespace App\Controllers;

use App\Database\ForgetDatabase;
use App\Models\ForgetModel;
use App\Views\View;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use Slim\Http\Response;

class ForgetController
{

  public function getForget(ForgetModel $model, View $view, Response $response)
  {
    $array = array(
      'title_page' => 'Recuperar Senha',
    );

    return $view->getRender($array, 'forget', $response);
  }

  public function postForget(ForgetModel $model, Response $response, $post)
  {
    //Classes
    $data     = new ForgetDatabase();
    $logger   = new ViewLogger('forget');
    $messages = new ViewMessages();
    $mail     = new EmailController();

    if (empty($post['username'])) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Preencha todos os campos'
      );

      $messages->addMessage('response', $return);

      return $response->withRedirect("/forget");
      exit();
    }

    $getuser = $data->getUser($post['username']);

    if (empty($getuser)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Usuário inválido'
      );

      $messages->addMessage('response', $return);

      return $response->withRedirect("/forget");
      exit();
    }
    $token       = md5(uniqid(rand(), true));
    $updatetoken = $data->updateToken($token, $post['username']);

    if ($updatetoken == 'OK') {
      $subject     = 'Recuperação de Senha';
      $link_action = getenv('SITE_LINK') . 'newpassword/' . $post['username'] . '/' . $token;
      $array = array(
        'subject'     => $subject,
        'link_action' => $link_action,
        'name'        => $getuser['memb_name'],
      );

      $mail->sendEmail($getuser['mail_addr'], $subject, $array, 'forget');

      $return = array(
        'error'   => false,
        'success' => true,
        'message' => 'Recuperação solicitada, verifique seu email'
      );

      $messages->addMessage('response', $return);

      $values = array(
        'username'  => $post['username'],
        'ipaddress' => $model->getIpaddress(),
        'message'   => 'Pediu para recuperar a senha'
      );

      $logger->addLoggerInfo("Forget", $values);

      return $response->withRedirect("/forget");
      exit();
    } else {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Não foi possível solicitar a alteração de senha, tente novamente'
      );

      $values = array(
        'username'  => $post['username'],
        'ipaddress' => $model->getIpaddress(),
        'message'   => $updatetoken
      );

      $logger->addLoggerWarning("Error Forget", $values);

      return $response->withRedirect("/forget");
      exit();
    }
  }
}
