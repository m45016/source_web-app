<?php

class DateTimeModel{
  
  private ?DateTime $dt = null;

  public static function Now(){
    $datetime = new DateTime();
    return $datetime->format('Y-m-d H:i:s');
  }

  public function isPaymentTariff(string $dateTo): bool{
    if(strtotime($dateTo) > strtotime(self::Now())){
      return true;
    }
    return false;
  }

  public function setDateTime(string $datetime):void{
    $this->dt = new DateTime($datetime);
  }

  public function modify(string $string):?bool{
    if(is_null($this->dt)){
      return null;
    }
    $this->dt->modify($string);
    return true;
  }

  public function getDateTime():?string{
    if(is_null($this->dt)){
      return null;
    }
    return $this->dt->format('Y-m-d H:i:s');
  }

}

?>