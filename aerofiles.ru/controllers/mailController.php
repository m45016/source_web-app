<?php

require_once "{$_SERVER['DOCUMENT_ROOT']}/models/MailModel.php";

class MailController{

  public MailModel $model;

  public function __construct(string $email_user, string $password, string $name_user){
    $this->model = new MailModel($email_user, $password, $name_user);
  }

  public function sendResetPassword(string $email, int $code):array{
    return $this->model->sendResetPassword($email, $code);
  }

}

?>