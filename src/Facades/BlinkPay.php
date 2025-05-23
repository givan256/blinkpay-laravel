<?php

namespace BlinkPay\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use BlinkPay\Laravel\BlinkPayGateway;

/**
 * @method static mixed processPayment(array $orderData)
 * @method static mixed processCreditCardPayment(array $data)
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