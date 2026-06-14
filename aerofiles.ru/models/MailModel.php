<?php

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/phpmailer/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailExcention;

class MailModel
{

  private string $email_user;
  private string $password;
  private string $name_user;
  private PHPMailer $mail;


  public function __construct(string $email_user, string $password, string $name_user)
  {
    $this->email_user = $email_user;
    $this->password = $password;
    $this->name_user = $name_user;
    $this->mail = new PHPMailer(true);
    $this->mail->CharSet = 'UTF-8';
  }

  public function sendResetPassword(string $email, int $code): array
  {
    try {

      $this->mail->isSMTP();
      $this->mail->Host = 'smtp.gmail.com';
      $this->mail->SMTPAuth = true;
      $this->mail->Username = $this->email_user;
      $this->mail->Password = $this->password;
      $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $this->mail->Port = 587;

      $this->mail->setFrom($this->email_user, $this->name_user);
      $this->mail->addAddress($email);
      $this->mail->isHTML();
      $this->mail->Subject = 'Сброс пароля для аккаунта Aerofiles';
      $this->mail->Body = "  <div style=\"background-color: black;
                                padding: 30px;
                                color: white;
                                font-family: system-ui;
                                font-size: 18px;\">
                                <div>Ваш код для сброса пароля</div><br>
                                <div style=\"padding: 18px;
                                text-align: center;
                                font-size: 30px;
                                font-weight: 600;
                                border-radius: 10px;
                                background-color: #20263A;\">{$code}</div><br>
                                <div>
                                  Никому его не сообщайте.
                                </div><br>
                                <div>
                                  Если вы не запрашивали код, проигнорируйте данное сообщение.
                                </div>
                              </div>";

      $this->mail->AltBody = "Ваш код для сброса пароля\n
                            {$code}\n
                            Никому его не сообщайте.\n
                            Если вы не запрашивали код, проигнорируйте данное сообщение.";

      $isSend = $this->mail->send();
      
      return ['success'=>$isSend,'error'=>null];
    
    } catch (MailExcention $e) {
      return ['success'=>false,'error'=>$e->getMessage()];
    }
  }
}
