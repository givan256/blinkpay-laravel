<?php

namespace BlinkPay\Laravel;

use Illuminate\Support\ServiceProvider;
use BlinkPay\Laravel\Facades\BlinkPay;

class BlinkPayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/blinkpay.php', 'blinkpay'
        );

        $this->app->singleton(BlinkPayService::class, function ($app) {
            return new BlinkPayService([
                'username' => config('blinkpay.username'),
                'password' => config('blinkpay.password'),
                'api_url' => config('blinkpay.api_url'),
                'banking_api_url' => config('blinkpay.banking_api_url'),
                'merchant_id' => config('blinkpay.merchant_id'),
                'merchant_password' => config('blinkpay.merchant_password'),
                'default_exchange_rate' => config('blinkpay.default_exchange_rate'),
                'exchange_rate_key' => config('blinkpay.exchange_rate_key')
            ]);
        });

        $this->app->singleton(BlinkPayGateway::class, function ($app) {
            return new BlinkPayGateway(
                $app->make(BlinkPayService::class),
                config('blinkpay.convert_to_ugx', false)
            );
        });

        $this->app->alias(BlinkPayGateway::class, 'blinkpay');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/blinkpay.php' => config_path('blinkpay.php'),
        ], 'blinkpay-config');
    }
} 