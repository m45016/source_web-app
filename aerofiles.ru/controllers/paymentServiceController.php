<?php

require_once "{$_SERVER['DOCUMENT_ROOT']}/models/PaymentServiceModel.php";

class PaymentServiceController{

  private PaymentServiceModel $service;

  public function __construct(){
    $this->service = new PaymentServiceModel();
  }

  public function sendPayment(array $data):bool{
    return $this->service->sendPayment($data);
  }
}

?>