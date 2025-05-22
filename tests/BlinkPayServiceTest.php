<?php

namespace BlinkPay\Laravel\Tests;

use BlinkPay\Laravel\BlinkPayService;
use PHPUnit\Framework\TestCase;

class BlinkPayServiceTest extends TestCase
{
    protected $config;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->config = [
            'username' => 'test_username',
            'password' => 'test_password',
            'api_url' => 'https://api.test.com',
            'dollar_rate' => 3700,
            'openexchangerates_key' => 'test_key'
        ];
        
        $this->service = new BlinkPayService($this->config);
    }

    public function testValidateMsisdnWithLocalNumber()
    {
        $result = $this->service->validateMsisdn('712345678');
        $this->assertEquals('256712345678', $result);
    }

    public function testValidateMsisdnWithLocalNumberStartingWithZero()
    {
        $result = $this->service->validateMsisdn('0712345678');
        $this->assertEquals('256712345678', $result);
    }

    public function testValidateMsisdnWithInternationalNumber()
    {
        $result = $this->service->validateMsisdn('256712345678');
        $this->assertEquals('256712345678', $result);
    }

    public function testValidateMsisdnWithInvalidNumber()
    {
        $result = $this->service->validateMsisdn('123456789');
        $this->assertNull($result);
    }

    public function testGetForexReturnsDefaultRateWhenApiFails()
    {
        $result = $this->service->getForex();
        $this->assertEquals($this->config['dollar_rate'], $result);
    }
} 