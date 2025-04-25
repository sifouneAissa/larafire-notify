<?php

use Sifouneaissa\LarafireNotify\Controllers\NotificationController;
use Sifouneaissa\LarafireNotify\Controllers\NotificationManagerController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('user')
            ->name('user.')
            ->group(function () {
                Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

                Route::prefix('push')
                    ->name('push.')
                    ->group(function () {
                        Route::post('subscribe', [
                            NotificationManagerController::class,
                            'subscribe',
                        ])->name('subscribe');
                        Route::post('unsubscribe', [
                            NotificationManagerController::class,
                            'unsubscribe',
                        ])->name('unsubscribe');
                    });
            });
    });

    Route::prefix('user')
        ->name('user.')
        ->group(function () {
            Route::prefix('push')
                ->name('push.')
                ->group(function () {
                    Route::post('/subscribe-to-topic', [NotificationManagerController::class, 'subscribeToTopic'])->name('subscribeToTopic');
                    Route::post('/unsubscribe-from-topic', [NotificationManagerController::class, 'unsubscribeFromTopic'])->name('unsubscribeFromTopic');
                    Route::post('/test-notification', [NotificationManagerController::class, 'testNotification'])->name('testNotification');
                });
        });
});
