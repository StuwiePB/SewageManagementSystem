<?php

namespace App\Providers;

use App\Contracts\SnsPublisher;
use App\Services\Sns\AwsSnsPublisher;
use App\Services\Sns\LogSnsPublisher;
use Aws\Sns\SnsClient;
use Illuminate\Support\ServiceProvider;

class SnsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SnsClient::class, function () {
            $config = [
                'version' => 'latest',
                'region' => config('sns.region'),
            ];

            if (config('sns.key') && config('sns.secret')) {
                $config['credentials'] = [
                    'key' => config('sns.key'),
                    'secret' => config('sns.secret'),
                ];
            }

            return new SnsClient($config);
        });

        $this->app->singleton(SnsPublisher::class, function ($app) {
            if (config('sns.enabled')) {
                return $app->make(AwsSnsPublisher::class);
            }

            return $app->make(LogSnsPublisher::class);
        });
    }
}
