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
} 