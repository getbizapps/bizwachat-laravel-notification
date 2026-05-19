<?php

namespace BizwaChat\LaravelNotification;

use BizwaChat\LaravelNotification\Channels\BizwaChatChannel;
use BizwaChat\LaravelNotification\Http\BizwaChatClient;
use BizwaChat\LaravelNotification\Support\BizwaChatRouteResolver;
use Illuminate\Support\ServiceProvider;

class BizwaChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bizwachat-notification.php', 'bizwachat-notification');

        $this->app->singleton(BizwaChatClient::class, function () {
            return new BizwaChatClient();
        });

        $this->app->singleton(BizwaChatRouteResolver::class, function () {
            return new BizwaChatRouteResolver();
        });

        $this->app->singleton('bizwachat', function ($app) {
            return new BizwaChatManager(
                $app->make(BizwaChatClient::class),
                $app->make(BizwaChatRouteResolver::class),
            );
        });

        $this->app->alias('bizwachat', BizwaChatManager::class);
        $this->app->bind(BizwaChatChannel::class, function ($app) {
            return new BizwaChatChannel(
                $app->make(BizwaChatManager::class),
                $app->make(BizwaChatRouteResolver::class),
            );
        });
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/bizwachat-notification.php' => config_path('bizwachat-notification.php'),
        ], 'bizwachat-notification-config');
    }
}