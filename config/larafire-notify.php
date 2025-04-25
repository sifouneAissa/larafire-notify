<?php

use App\Models\Notification;

return [
    'resource' => null,
    'model' => Notification::class,
    'user_model' => null,
    'FIREBASE_CREDENTIALS' => env('FIREBASE_CREDENTIALS'),
    'api_domain_name' => 'api.'.env('APP_DOMAIN_NAME', 'localhost'),
    'per_page' => 15,
    'fcm_token_param' => 'registration_id',
    'guard' => 'api'
];