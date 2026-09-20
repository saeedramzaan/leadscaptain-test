<?php

return [
    'base_url' => env('LEADSCAPTAIN_API_URL', 'https://api.leadscaptain.com'),

    'api_token' => env('LEADSCAPTAIN_API_TOKEN'),

    'timeout' => (int) env('LEADSCAPTAIN_TIMEOUT', 10),

    'retry_times' => (int) env('LEADSCAPTAIN_RETRY_TIMES', 5),

    'retry_sleep' => (int) env('LEADSCAPTAIN_RETRY_SLEEP', 200),
];