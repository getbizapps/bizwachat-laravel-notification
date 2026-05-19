<?php

return [
    'base_url' => env('BIZWACHAT_BASE_URL', 'https://bizwachat.com'),

    'subdomain' => env('BIZWACHAT_SUBDOMAIN'),

    'api_token' => env('BIZWACHAT_API_TOKEN'),

    'timeout' => (int) env('BIZWACHAT_TIMEOUT', 30),

    'connect_timeout' => (int) env('BIZWACHAT_CONNECT_TIMEOUT', 10),

    'throw' => (bool) env('BIZWACHAT_THROW_EXCEPTIONS', true),

    'log_channel' => env('BIZWACHAT_LOG_CHANNEL', 'stack'),

    'route' => [
        'phone_fields' => ['phone', 'phone_number', 'mobile'],
        'subdomain_field' => 'bizwachat_subdomain',
        'from_phone_number_id_field' => 'bizwachat_from_phone_number_id',
    ],
];