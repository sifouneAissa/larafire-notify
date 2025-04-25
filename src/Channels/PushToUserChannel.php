<?php

namespace Sifouneaissa\LarafireNotify\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;

class PushToUserChannel
{
    public function send($notifiable, Notification $notification )
    {
        if (
            !($notifiable instanceof AnonymousNotifiable) &&
            method_exists($notifiable, 'canReceivePush') &&
            !$notifiable->canReceivePush()) {
            return null;
        }

        try {
            $message = $notification->toPush($notifiable);

            return app('firebase.messaging')->send($message);
        } catch (\Exception $err) {

            Log::error("[Notification] - Failed to send notification", [
                'error' => [
                    'code' => $err->getCode(),
                    'file' => $err->getFile(),
                    'line' => $err->getLine(),
                    'message' => $err->getMessage(),
                ],
                'error_string' => $err->__toString(),
            ]);
            return null;
        }
    }
}
