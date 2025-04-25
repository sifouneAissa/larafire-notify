<?php

namespace Sifouneaissa\LarafireNotify;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;
use Sifouneaissa\LarafireNotify\Helpers\FirebaseAuth;
use Sifouneaissa\LarafireNotify\Notifications\BaseNotification;
use Sifouneaissa\LarafireNotify\Repositories\NotificationRepository;
use Sifouneaissa\LarafireNotify\Repositories\NotificationRespositoryInterface;

class LarafireNotifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mapRoutes();

        $this->publishes([
            __DIR__ . '/../config/larafire-notify.php' => config_path('larafire-notify.php'),
        ], 'larafire-notify-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/larafire-notify.php', 'larafire-notify');

        $firebaseCredentials = config("larafire-notify.FIREBASE_CREDENTIALS");

        $this->app->singleton('firebase', function ($app) use ($firebaseCredentials) {
            return (new Factory)->withServiceAccount(base_path($firebaseCredentials));
        });

        $this->app->singleton('firebaseAuth', fn($app) => new FirebaseAuth);
        $this->app->singleton('baseNotification', fn($app) => new BaseNotification);

        $this->app->bind(NotificationRespositoryInterface::class, NotificationRepository::class);
    }

    protected function mapRoutes()
    {
        if (file_exists(__DIR__ . '/../routes/api.php')) {
            Route::domain(config('larafire-notify.api_domain_name'))
                ->middleware('api')
                ->group(__DIR__ . '/../routes/api.php');
        }

        // if (file_exists(__DIR__ . '/../routes/web.php')) {
        //     Route::middleware('web')
        //         ->group(__DIR__ . '/../routes/web.php');
        // }
    }
}
