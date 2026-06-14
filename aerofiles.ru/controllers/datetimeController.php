<?php

require_once "{$_SERVER['DOCUMENT_ROOT']}/models/DateTimeModel.php";

class DateTimeController{
  private DateTimeModel $DT;

  public function __construct(){
    $this->DT = new DateTimeModel();
  }

  public function now(){
    return $this->DT::Now();
  }

  public function isPaymentTariff(?string $dateTo): bool{
    if(is_null($dateTo)){
      return false;
    }
    return $this->DT->isPaymentTariff($dateTo);
  }

  public function setDateTime(string $datetime):void{
    $this->DT->setDateTime($datetime);
  }

  public function modify(string $string):void{
    $this->DT->modify($string);
  }

  public function getDateTime():?string{
    return $this->DT->getDateTime();
  }

}

?>
