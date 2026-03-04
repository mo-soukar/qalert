<?php

namespace Soukar\QAlert\Providers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Soukar\QAlert\Listeners\JobFailedListener;
use Soukar\QAlert\Services\ChannelManager;
use Soukar\QAlert\Services\QAlertManager;

class QAlertServiceProvider
    extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('qalert-manager', function ($app) {
            return new QAlertManager($app->make(ChannelManager::class));
        });

    }

    public function boot() {

        Event::listen(JobFailed::class, [JobFailedListener::class, 'handle']);
        $this->mergeConfigFrom(__DIR__ . '/../Config/qalert.php', 'qalert');
        $this->publishes([
            __DIR__.'/../Config/qalert.php' => config_path('qalert.php'),
                         ],'qalert');
    }
}
