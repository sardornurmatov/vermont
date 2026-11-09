<?php

return [
    'name' => env('APP_NAME', 'Vermontshop'), 'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', true), 'url' => env('APP_URL', 'http://127.0.0.1:8000'),
    'timezone' => 'Asia/Tashkent', 'locale' => env('APP_LOCALE', 'uz'),
    'fallback_locale' => 'uz', 'faker_locale' => 'uz_UZ',
    'key' => env('APP_KEY'), 'cipher' => 'AES-256-CBC',
    'maintenance' => ['driver' => env('APP_MAINTENANCE_DRIVER', 'file'), 'store' => env('APP_MAINTENANCE_STORE', 'database')],
];
