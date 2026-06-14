<?php

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/omnipay/autoload.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/errors/paymentServiceError.php";

use Omnipay\Common\CreditCard;
use Omnipay\Omnipay;
use Omnipay\Common\GatewayInterface;

class PaymentServiceModel
{

  private GatewayInterface $gateway;
  private array $requiredProperties = [
    'amount',
    'currency',
    'description',
  ];

  public CreditCard $card;

  public function __construct()
  {
    $this->gateway = Omnipay::create('Dummy');
    $this->gateway->initialize(['testMode' => true]);
    $this->card = new CreditCard([
      'firstName'    => 'Example',
      'lastName'     => 'Customer',
      'number'       => '4242424242424242',
      'expiryMonth'  => '12',
      'expiryYear'   => '2030'
    ]);
  }

  public function sendPayment(array $data): bool
  {

    foreach ($this->requiredProperties as $prop) {
      if (!isset($data[$prop])) {
        throw new PaymentServiceError("Поле {$prop} обязательное");
      }
    }

    $transaction = $this->gateway->purchase([
      'card' => $this->card,
      'amount' => number_format($data['amount'], 2, '.', ''),
      'currency' => $data['currency'],
      'description' => $data['description']
    ]);

    $response = $transaction->send();

    if ($response->isSuccessful()) {
      return true;
    }

    return false;
  }
}
