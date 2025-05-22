<?php

namespace BlinkPay\Laravel;

class BlinkPayGateway
{
    protected $service;
    protected $convertToUGX;
    
    public function __construct(BlinkPayService $service, bool $convertToUGX = false)
    {
        $this->service = $service;
        $this->convertToUGX = $convertToUGX;
    }
    
    public function processPayment($orderData)
    {
        $amount = $orderData['amount'];
        $phoneNumber = $this->service->validateMsisdn($orderData['phone_number']);
        
        if (!$phoneNumber) {
            throw new \Exception('Invalid phone number format');
        }
        
        if ($this->convertToUGX) {
            $exchangeRate = $this->service->getForex();
            $amount = $exchangeRate * $amount;
        }
        
        $description = "Order #{$orderData['order_id']} - Amount: {$orderData['currency']}{$orderData['amount']}";
        
        return $this->service->makePayment($phoneNumber, $amount, $description);
    }
    

    public function processCreditCardPayment($orderData)
    {
        if (!$this->service->validateCreditCard($orderData['card_number'])) {
            throw new \Exception('Invalid credit card number');
        }

        $cardType = $this->service->getCardType($orderData['card_number']);
        if ($cardType === 'unknown') {
            throw new \Exception('Unsupported card type');
        }

        $amount = $orderData['amount'];
        if ($this->convertToUGX) {
            $exchangeRate = $this->service->getForex();
            $amount = $exchangeRate * $amount;
        }

        $description = "Order #{$orderData['order_id']} - Amount: {$orderData['currency']}{$orderData['amount']}";
        
        return $this->service->processCreditCardPayment($orderData, $amount, $description);
    }
} 