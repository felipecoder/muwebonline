<?php

namespace App\Controllers;

use App\Database\DefaultDatabase;
use App\Views\ViewEmail;
use App\Views\ViewLogger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailController
{

  public function sendEmail($to, $subject, $array, $template)
  {
    //Classes
    $data   = new DefaultDatabase();
    $view   = new ViewEmail();
    $logger = new ViewLogger('email');
    $mail   = new PHPMailer(true);

    //variables
    $body         = $view->getRender($array, $template);
    $config_email = $data->getConfig('email');
    $config_email = json_decode($config_email, true);

    try {
      //Server settings
      $mail->isSMTP();
      $mail->Host       = $config_email[0]['value'];
      $mail->SMTPAuth   = $config_email[1]['value'];
      $mail->Username   = $config_email[2]['value'];
      $mail->Password   = $config_email[3]['value'];
      $mail->SMTPSecure = $config_email[4]['value'];
      $mail->Port       = $config_email[5]['value'];
      $mail->CharSet    = $config_email[6]['value'];

      //Recipients
      $mail->setFrom($config_email[2]['value']);
      $mail->addAddress($to);

      // Content
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $body;
      $mail->AltBody = $subject;

      $mail->send();
      $logger->addLoggerInfo('Email enviado', array('assunto' => $subject, 'para' => $to));
    } catch (Exception $e) {
      $logger->addLoggerWarning('Message could not be sent. Mailer Error:', array('error' => $mail->ErrorInfo));
    }
  }
}
