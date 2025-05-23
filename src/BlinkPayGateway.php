<?php

namespace BlinkPay\Laravel;

class BlinkPayGateway
{
    protected $service;
    protected $convertToUgx;
    
    public function __construct(BlinkPayService $service, bool $convertToUgx = false)
    {
        $this->service = $service;
        $this->convertToUgx = $convertToUgx;
    }
    
    public function mobileMoney(array $data)
    {
        return $this->processPayment($data);
    }
    
    public function processPayment(array $orderData)
    {
        // Ensure orderData is properly formatted
        $orderData = $this->validateAndFormatData($orderData);
        
        if ($this->convertToUgx) {
            $orderData['amount'] = $this->convertToUgx($orderData['amount'], $orderData['currency'] ?? 'USD');
            $orderData['currency'] = 'UGX';
        }
        
        $amount = $orderData['amount'];
        $phoneNumber = $this->service->validateMsisdn($orderData['phone_number']);
        
        if (!$phoneNumber) {
            throw new \Exception('Invalid phone number format');
        }
        
        $description = "Order #{$orderData['order_id']} - Amount: {$orderData['currency']}{$orderData['amount']}";
        
        return $this->service->makePayment($phoneNumber, $amount, $description);
    }
    
    public function processCreditCardPayment(array $data)
    {
        // Ensure data is properly formatted
        $data = $this->validateAndFormatData($data);
        
        if ($this->convertToUgx) {
            $data['amount'] = $this->convertToUgx($data['amount'], $data['currency'] ?? 'USD');
            $data['currency'] = 'UGX';
        }
        
        try {
            $result = $this->service->processCreditCardPayment($data);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    protected function convertToUgx($amount, $currency)
    {
        // If already in UGX, return as is
        if ($currency === 'UGX') {
            return $amount;
        }
        
        // Convert to UGX using the default exchange rate
        return $amount * config('blinkpay.default_exchange_rate', 3700);
    }

    protected function validateAndFormatData(array $data)
    {
        // Ensure all required fields are present
        $required = ['amount', 'order_id'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // If data contains JSON strings, decode them
        foreach ($data as $key => $value) {
            if (is_string($value) && $this->isJson($value)) {
                $data[$key] = json_decode($value, true);
            }
        }

        return $data;
    }

    protected function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
} 