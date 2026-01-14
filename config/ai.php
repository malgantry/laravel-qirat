<?php

return [
    'base_url' => env('AI_SERVICE_URL', 'http://localhost:8001'),
    'timeout' => env('AI_SERVICE_TIMEOUT', 10),
    'enabled' => env('AI_ENABLED', true),
    'api_key' => env('AI_API_KEY'),
];