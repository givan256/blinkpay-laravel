<?php

namespace BlinkPay\Laravel;

use Illuminate\Support\ServiceProvider;

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
            return new BlinkPayService();
        });

        $this->app->singleton(BlinkPayGateway::class, function ($app) {
            return new BlinkPayGateway(
                $app->make(BlinkPayService::class),
                config('blinkpay.convert_to_ugx', false)
            );
        });
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