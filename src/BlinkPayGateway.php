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
    

    public function processCreditCardPayment(array $data)
    {
        try {
            $result = $this->service->processCreditCardPayment($data);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
} 