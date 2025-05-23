<?php

namespace BlinkPay\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use BlinkPay\Laravel\BlinkPayGateway;

/**
 * @method static mixed mobileMoney(array $data) Process a mobile money payment
 * @method static mixed processPayment(array $orderData) Process a payment
 * @method static mixed processCreditCardPayment(array $data) Process a credit card payment
 * 
 * @see \BlinkPay\Laravel\BlinkPayGateway
 */
class BlinkPay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BlinkPayGateway::class;
    }
} 