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

    public function testValidateCreditCardWithValidNumber()
    {
        $result = $this->service->validateCreditCard('4532015112830366');
        $this->assertTrue($result);
    }

    public function testValidateCreditCardWithInvalidNumber()
    {
        $result = $this->service->validateCreditCard('4532015112830367');
        $this->assertFalse($result);
    }

    public function testGetCardType()
    {
        $this->assertEquals('visa', $this->service->getCardType('4532015112830366'));
        $this->assertEquals('mastercard', $this->service->getCardType('5555555555554444'));
        $this->assertEquals('amex', $this->service->getCardType('378282246310005'));
        $this->assertEquals('discover', $this->service->getCardType('6011111111111117'));
        $this->assertEquals('unknown', $this->service->getCardType('1234567890123456'));
    }

    public function testProcessCreditCardPayment()
    {
        $data = [
            'amount' => 1000,
            'currency' => 'UGX',
            'narration' => 'Test payment',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'phone_number' => '256700000000',
            'cancel_redirect_url' => 'https://example.com/cancel',
            'success_redirect_url' => 'https://example.com/success',
            'status_notification_url' => 'https://example.com/notify'
        ];

        $result = $this->service->processCreditCardPayment($data);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('request_id', $result);
        $this->assertArrayHasKey('merchant_id', $result);
    }

    public function testProcessCreditCardPaymentWithMissingConfig()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Online Banking is not configured!');

        $data = [
            'amount' => 1000,
            'email' => 'test@example.com'
        ];

        $this->service->processCreditCardPayment($data);
    }
} 