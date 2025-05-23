<?php

namespace BlinkPay\Laravel;

class BlinkPayService
{
    protected $config;
    protected $username;
    protected $password;
    protected $apiUrl;
    protected $bankingApiUrl;
    protected $merchantId;
    protected $merchantPassword;
    protected $defaultExchangeRate;
    protected $exchangeRateKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->apiUrl = $config['api_url'];
        $this->bankingApiUrl = $config['banking_api_url'];
        $this->merchantId = $config['merchant_id'];
        $this->merchantPassword = $config['merchant_password'];
        $this->defaultExchangeRate = $config['default_exchange_rate'];
        $this->exchangeRateKey = $config['exchange_rate_key'];
    }

    public function makePayment($phoneNumber, $amount, $description)
    {
        $params = [
            "username" => $this->username,
            "password" => $this->password,
            "api" => "depositmobilemoney",
            "msisdn" => $phoneNumber,
            "amount" => (int)$amount,
            "narrative" => $description,
            "reference" => $description,
        ];

        $response = $this->makeRequest($this->apiUrl, $params);
        $jsonDecoded = json_decode($response, true);
        
        if (!empty($jsonDecoded) && !$jsonDecoded['error']) {
            if (isset($jsonDecoded['status']) && $jsonDecoded['status'] == "PENDING") {
                $reference = $jsonDecoded['reference_code'];
                $status = $this->checkStatus($reference);
                
                while ($status == "PENDING") {
                    $status = $this->checkStatus($reference);
                    sleep(5);
                }
                
                return ['status' => $status, 'message' => $response];
            }
        }
        
        return ['status' => 'FAILED', 'message' => $response];
    }

    public function checkStatus($reference)
    {
        $params = [
            "username" => $this->username,
            "password" => $this->password,
            "api" => "checktransactionstatus",
            "reference_code" => $reference,
        ];

        $response = $this->makeRequest($this->apiUrl, $params);
        $jsonDecoded = json_decode($response, true);
        
        if (!empty($jsonDecoded) && !$jsonDecoded['error'] && isset($jsonDecoded['status'])) {
            return $jsonDecoded['status'];
        }
        
        return "FAILED";
    }

    public function getForex()
    {
        $exchangeRate = $this->defaultExchangeRate;
        
        try {
            $url = 'https://openexchangerates.org/api/latest.json?app_id=' . $this->exchangeRateKey . '&symbols=UGX';
            $contents = file_get_contents($url);
            $resp = json_decode($contents);
            $exchangeRate = $resp->rates->UGX;
        } catch (\Exception $e) {
            // Log error if needed
        }
        
        return $exchangeRate;
    }

    public function validateMsisdn($msisdn)
    {
        $billingNumber = '';
        $local = preg_match('/(^7|3)([0-9]{8})/', $msisdn);
        $local2 = preg_match('/(^0)(7|3)([0-9]{8})/', $msisdn);

        if ($local) {
            $billingNumber = "256" . $msisdn;
        } elseif ($local2) {
            $billingNumber = preg_replace('/(^0)/', '256', $msisdn);
        } else {
            $billingNumber = $msisdn;
        }

        $international = preg_match('/(^0|256||)(7|3)([0-9]{8})/', $billingNumber);

        return $international ? $billingNumber : null;
    }

    protected function makeRequest($url, $data)
    {
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            throw new \Exception('Failed to make request to BlinkPay API');
        }

        return json_decode($result, true);
    }

    protected function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If number starts with 0, replace with 256
        if (substr($phone, 0, 1) === '0') {
            $phone = '256' . substr($phone, 1);
        }
        
        // If number starts with +, remove it
        if (substr($phone, 0, 1) === '+') {
            $phone = substr($phone, 1);
        }
        
        return $phone;
    }

    public function processPayment($orderData)
    {
        if (!isset($orderData['phone_number'])) {
            throw new \Exception('Phone number is required for mobile money payment');
        }

        $data = [
            'username' => $this->username,
            'password' => $this->password,
            'merchant_id' => $this->merchantId,
            'merchant_password' => $this->merchantPassword,
            'phone_number' => $this->formatPhoneNumber($orderData['phone_number']),
            'amount' => $orderData['amount'],
            'narration' => $orderData['narration'] ?? 'Payment',
            'exchange_rate' => $this->defaultExchangeRate
        ];

        return $this->makeRequest($this->apiUrl, $data);
    }

    public function processCreditCardPayment($data)
    {
        $requestData = [
            'username' => $this->username,
            'password' => $this->password,
            'merchant_id' => $this->merchantId,
            'merchant_password' => $this->merchantPassword,
            'amount' => $data['amount'],
            'email' => $data['email'],
            'name' => $data['name'],
            'phone_number' => isset($data['phone_number']) ? $this->formatPhoneNumber($data['phone_number']) : '',
            'narration' => $data['narration'] ?? 'Payment',
            'cancel_redirect_url' => $data['cancel_redirect_url'],
            'success_redirect_url' => $data['success_redirect_url'],
            'status_notification_url' => $data['status_notification_url'],
            'exchange_rate' => $this->defaultExchangeRate
        ];

        return $this->makeRequest($this->bankingApiUrl, $requestData);
    }

    public function validateCreditCard($cardNumber)
    {
        // Remove any non-digit characters
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        // Check if the card number is valid using Luhn algorithm
        $sum = 0;
        $length = strlen($cardNumber);
        $parity = $length % 2;
        
        for ($i = 0; $i < $length; $i++) {
            $digit = $cardNumber[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        
        return ($sum % 10 == 0);
    }

    public function getCardType($cardNumber)
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        $patterns = [
            'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'mastercard' => '/^5[1-5][0-9]{14}$/',
            'amex' => '/^3[47][0-9]{13}$/',
            'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
        ];
        
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $cardNumber)) {
                return $type;
            }
        }
        
        return 'unknown';
    }
} 