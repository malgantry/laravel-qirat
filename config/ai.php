<?php

return [
    'base_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001'),
    'timeout' => env('AI_SERVICE_TIMEOUT', 10),
    'enabled' => env('AI_ENABLED', true),
    'api_key' => env('AI_API_KEY'),
    'model_dir' => env('AI_MODEL_DIR', base_path('qirat_ai_api/models')),
];