<?php

namespace NotificationChannels\Clicksend;

use ClickSend\Api\SMSApi;
use ClickSend\Configuration;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class ClicksendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(ClicksendChannel::class)
            ->needs(SMSApi::class)
            ->give(function () {
                $config = Configuration::getDefaultConfiguration()
                    ->setUsername(config('services.clicksend.username'))
                    ->setPassword(config('services.clicksend.api_key'));

                return new SMSApi(
                    new Client(),
                    $config
                );
            });

    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
