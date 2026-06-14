<?php

class PaymentServiceError extends Exception{
  public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
  {
    return parent::__construct($message, $code, $previous);
  }
}

?>