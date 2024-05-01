<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpMqtt\Client\MqttClientServiceProvider;
use Saloon\Laravel\SaloonServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
