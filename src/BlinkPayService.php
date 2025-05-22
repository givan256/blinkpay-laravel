<?php

namespace BlinkPay\Laravel;

class BlinkPayService
{
    protected $config;
    protected $username;
    protected $password;
    protected $apiUrl;
    protected $exchangeRate;
    protected $openExchangeRatesKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->apiUrl = $config['api_url'];
        $this->exchangeRate = $config['dollar_rate'];
        $this->openExchangeRatesKey = $config['openexchangerates_key'];
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
        $exchangeRate = $this->exchangeRate;
        
        try {
            $url = 'https://openexchangerates.org/api/latest.json?app_id=' . $this->openExchangeRatesKey . '&symbols=UGX';
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
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        return $result !== false ? $result : '';
    }

    public function processCreditCardPayment(array $data)
    {
        $merchantId = config('blinkpay.merchant_id');
        $merchantPassword = config('blinkpay.merchant_password');
        $apiUrl = config('blinkpay.banking_api_url');
        
        if (!$merchantId || !$merchantPassword || !$apiUrl) {
            throw new \Exception('Online Banking is not configured!');
        }

        $amount = (int)$data['amount'];
        $currencyCode = $data['currency'] ?? 'UGX';
        $narration = $data['narration'];
        $emailAddress = $data['email'];
        $names = $data['name'] ?? '';
        $phoneNumber = $data['phone_number'] ?? '';
        $cancelRedirectUrl = $data['cancel_redirect_url'];
        $successRedirectUrl = $data['success_redirect_url'];
        $statusNotificationUrl = $data['status_notification_url'];

        // Generate request ID using SHA1 hash
        $concatenation = $merchantId . '|' . $amount . '|' . $currencyCode . '|' . 
                        $narration . '|' . $names . '|' . $phoneNumber . '|' . 
                        $emailAddress . '|' . $cancelRedirectUrl . '|' . 
                        $successRedirectUrl . '|' . $statusNotificationUrl . '|' . 
                        $merchantPassword;
        
        $requestId = sha1($concatenation);

        // Prepare request parameters
        $params = [
            'currency_code' => $currencyCode,
            'amount' => $amount,
            'narration' => $narration,
            'names' => $names,
            'phone_number' => $phoneNumber,
            'email_address' => $emailAddress,
            'cancel_redirect_url' => $cancelRedirectUrl,
            'success_redirect_url' => $successRedirectUrl,
            'status_notification_url' => $statusNotificationUrl,
            'merchant_id' => $merchantId,
            'request_id' => $requestId
        ];

        // Make API request
        $response = $this->makeRequest($apiUrl, $params, 'POST');
        
        return $response;
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