<?php

namespace App\Controllers;

use App\Database\DefaultDatabase;
use App\Database\RegisterDatabase;
use App\Models\RegisterModel;
use App\Views\View;
use App\Views\ViewLogger;
use App\Views\ViewMessages;
use Slim\Http\Response;
use ReCaptcha\ReCaptcha;

class RegisterController
{

  public function getRegister(RegisterModel $model, View $view, Response $response)
  {

    $array = array(
      'title_page' => 'Cadastro',
    );

    return $view->getRender($array, 'register', $response);
  }

  public function getCaptchaSecret()
  {
    //classes
    $data = new DefaultDatabase();

    //variables
    $recaptcha = $data->getConfig('captcha');
    $recaptcha = json_decode($recaptcha, true);

    return $recaptcha[0]['value'];
  }

  public function postRegister(RegisterModel $model, View $view, Response $response, $post, $ipaddress)
  {
    //Classes
    $data      = new RegisterDatabase();
    $mail      = new EmailController();
    $logger    = new ViewLogger('register');
    $messages  = new ViewMessages();
    $recaptcha = new ReCaptcha($this->getCaptchaSecret());

    if (
      empty($post['username']) or
      empty($post['nick']) or
      empty($post['email']) or
      empty($post['reemail']) or
      empty($post['password']) or
      empty($post['repassword'])
    ) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Prencha todos os campos'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    }

    $recaptcharesponse = $post['g-recaptcha-response'];
    $recaptchacheck    = $recaptcha->setExpectedHostname(getenv('DOMAIN'))->verify($recaptcharesponse, $ipaddress);

    if (!$recaptchacheck->isSuccess()) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Error no captcha tente novamente'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    }

    //Variables
    $username        = $post['username'];
    $email           = $post['email'];
    $reemail         = $post['reemail'];
    $password        = $post['password'];
    $repassword      = $post['repassword'];
    $config_register = $data->getConfig('register');
    $config_register = json_decode($config_register, true);
    $config_muserver = $data->getConfig('muserver');
    $config_muserver = json_decode($config_muserver, true);

    if ($config_register[1]['value'] == 'true') $username = strtolower($username);

    if (!$this->isValidEmail($email)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Insira um email válido'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    } elseif ($email != $reemail) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Os emails não conferem'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    } elseif (strlen($password) < 4 or strlen($password) > 10) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'A senha é muita curta ou muito grande'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    } elseif (strlen($repassword) < 4 or strlen($repassword) > 10) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'A confirmação de senha é muita curta ou muito grande'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    } elseif ($password != $repassword) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'As senhas não conferem'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    } elseif ($data->checkUsername($username)) {
      $return = array(
        'error'   => true,
        'success' => false,
        'message' => 'Usuário já cadastrado'
      );

      $messages->addMessage('response', $return);
      return $response->withRedirect(getenv("DIR") . "register");
    } elseif ($config_register[5]['value'] == 'false') {
      if ($data->checkUsername($username)) {
        $return = array(
          'error'   => true,
          'success' => false,
          'message' => 'Email já cadastrado'
        );

        $messages->addMessage('response', $return);
        return $response->withRedirect(getenv("DIR") . "register");
      } else {
        $register = $data->register($post, $username);
        if ($register == 'OK') {
          $return = array(
            'error'   => false,
            'success' => true,
            'message' => 'Cadastrado com sucesso'
          );

          $values = array(
            'email'     => $post['email'],
            'ipaddress' => $ipaddress,
            'message'   => 'Criou uma conta'
          );

          $logger->addLoggerInfo("Register", $values);

          if ($config_register[0]['value'] == 'true') {
            $token       = md5(uniqid(rand(), true));
            $subject     = 'Confirmação de Cadastro';
            $link_action = getenv('SITE_LINK') . 'active/' . $username . '/' . $token;
            $array = array(
              'subject'     => $subject,
              'link_action' => $link_action,
              'name'        => $post['nick'],
            );

            $mail->sendEmail($email, $subject, $array, 'register');
            $data->updateToken($token, $username);
          } else {
            $data->updateMailCheck($username);
          }

          if ($config_register[2]['value'] == 'true') {
            $vips = $data->getVipsConfigs();
            foreach ($vips as $key => $value) {
              if ($value['level'] == $config_register[3]['value']) {
                $vipid = $value['ID'];
              }
            }

            if (!empty($vipid)) {
              $vip = $data->getVipInfo($vipid);
              $data->insertVip($vip['database'], $vip['table'], $vip['column_level'], $vip['column_days'], $username, $vip['level'], $config_register[4]['value'], $config_muserver[3]['value']);

              $return = array(
                'error'   => false,
                'success' => true,
                'message' => 'Cadastrado com sucesso, você foi premiado com ' . $config_register[4]['value'] . ' dia(s) de vip ' . $vip['name'] . ''
              );
            }
          }

          if ($config_register[6]['value'] == 'true') {
            $data->updateCredits($config_register[7]['value'], $username);

            $return = array(
              'error'   => false,
              'success' => true,
              'message' => 'Cadastrado com sucesso, você foi premiado com ' . $config_register[4]['value'] . ' dia(s) de vip ' . $vip['name'] . ' e com ' . $config_register[7]['value'] . ' créditos'
            );
          }

          if ($config_muserver[0]['value'] == 'true') {
            $data->updatePasswordMD5($password, $username);
          }

          if ($config_muserver[1]['value'] == 'true') {
            $data->inserVirCurrInfo($username, $post['nick']);
          }
        } else {
          $return = array(
            'error'   => true,
            'success' => false,
            'message' => 'Não foi possível criar sua conta, tente novamente'
          );

          $values = array(
            'email'     => $post['email'],
            'ipaddress' => $ipaddress,
            'message'   => $register
          );

          $logger->addLoggerWarning("Error Register", $values);
        }

        $messages->addMessage('response', $return);

        return $response->withRedirect(getenv("DIR") . "register");
      }
    }
  }

  public function isValidEmail($email)
  {
    $email = htmlspecialchars_decode($email, ENT_QUOTES);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    return true;
  }
}
