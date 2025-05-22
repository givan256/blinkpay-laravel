<?php

namespace BlinkPay\Laravel\Tests;

use BlinkPay\Laravel\BlinkPayGateway;
use BlinkPay\Laravel\BlinkPayService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class BlinkPayGatewayTest extends TestCase
{
    protected $service;
    protected $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = $this->createMock(BlinkPayService::class);
        $this->gateway = new BlinkPayGateway($this->service, false);
    }

    public function testProcessPaymentWithValidData()
    {
        $orderData = [
            'order_id' => '123',
            'amount' => 100,
            'currency' => 'USD',
            'phone_number' => '712345678'
        ];

        $this->service->expects($this->once())
            ->method('validateMsisdn')
            ->with('712345678')
            ->willReturn('256712345678');

        $this->service->expects($this->once())
            ->method('makePayment')
            ->with(
                '256712345678',
                100,
                'Order #123 - Amount: USD100'
            )
            ->willReturn(['status' => 'SUCCESS', 'message' => 'Payment processed']);

        $result = $this->gateway->processPayment($orderData);
        
        $this->assertEquals('SUCCESS', $result['status']);
        $this->assertEquals('Payment processed', $result['message']);
    }

    public function testProcessPaymentWithInvalidPhoneNumber()
    {
        $orderData = [
            'order_id' => '123',
            'amount' => 100,
            'currency' => 'USD',
            'phone_number' => 'invalid'
        ];

        $this->service->expects($this->once())
            ->method('validateMsisdn')
            ->with('invalid')
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid phone number format');

        $this->gateway->processPayment($orderData);
    }

    public function testProcessPaymentWithUGXConversion()
    {
        $orderData = [
            'order_id' => '123',
            'amount' => 100,
            'currency' => 'USD',
            'phone_number' => '712345678'
        ];

        $gateway = new BlinkPayGateway($this->service, true);

        $this->service->expects($this->once())
            ->method('validateMsisdn')
            ->with('712345678')
            ->willReturn('256712345678');

        $this->service->expects($this->once())
            ->method('getForex')
            ->willReturn(3700);

        $this->service->expects($this->once())
            ->method('makePayment')
            ->with(
                '256712345678',
                370000,
                'Order #123 - Amount: USD100'
            )
            ->willReturn(['status' => 'SUCCESS', 'message' => 'Payment processed']);

        $result = $gateway->processPayment($orderData);
        
        $this->assertEquals('SUCCESS', $result['status']);
        $this->assertEquals('Payment processed', $result['message']);
    }
} 