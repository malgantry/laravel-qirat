<?php

namespace App\Http\Controllers;

use App\Services\AiClient;
use Illuminate\Http\JsonResponse;

class AiStatusController extends Controller
{
    public function __construct(private readonly AiClient $aiClient)
    {
    }

    /**
     * Return AI service health passthrough.
     */
    public function health(): JsonResponse
    {
        try {
            $data = $this->aiClient->health();
            return response()->json([
                'enabled' => (bool) config('ai.enabled', true),
                'base_url' => config('ai.base_url'),
                'health' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'enabled' => (bool) config('ai.enabled', true),
                'base_url' => config('ai.base_url'),
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Return AI model accuracy if available from metadata.
     */
    public function accuracy(): JsonResponse
    {
        if (!config('ai.enabled', true)) {
            return response()->json([
                'enabled' => false,
                'base_url' => config('ai.base_url'),
                'accuracy' => null,
                'message' => 'AI is disabled via configuration.',
            ]);
        }

        try {
            $metadata = $this->aiClient->metadata();
            $accuracy = null;
            // Try common locations
            if (isset($metadata['metrics']['accuracy'])) {
                $accuracy = $metadata['metrics']['accuracy'];
            } elseif (isset($metadata['accuracy'])) {
                $accuracy = $metadata['accuracy'];
            }

            return response()->json([
                'enabled' => true,
                'base_url' => config('ai.base_url'),
                'accuracy' => $accuracy,
                'metadata' => $metadata,
                'message' => $accuracy === null ? 'Accuracy not provided by AI service metadata.' : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'enabled' => true,
                'base_url' => config('ai.base_url'),
                'accuracy' => null,
                'error' => $e->getMessage(),
            ], 502);
        }
    }
}
