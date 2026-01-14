<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ai.base_url'), '/');
        $this->timeout = (int) config('ai.timeout', 10);
    }

    /**
     * @param float[] $features
     * @return array{predictions: array<int,float>, top_class: int|null}
     */
    public function predict(array $features): array
    {
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) {
            $headers['X-API-KEY'] = $apiKey;
        }
        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders($headers)
            ->post("{$this->baseUrl}/predict", ['features' => array_values($features)]);

        if ($response->failed()) {
            $message = $response->json('detail') ?? $response->body();
            throw new RuntimeException('AI service error: ' . $message);
        }

        $data = $response->json();

        return [
            'predictions' => $data['predictions'] ?? [],
            'top_class' => $data['top_class'] ?? null,
        ];
    }

    /**
     * Fetch AI service health info.
     * @return array
     */
    public function health(): array
    {
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) {
            $headers['X-API-KEY'] = $apiKey;
        }

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders($headers)
            ->get("{$this->baseUrl}/health");

        if ($response->failed()) {
            $message = $response->json('detail') ?? $response->body();
            throw new RuntimeException('AI service error: ' . $message);
        }

        return $response->json() ?? [];
    }

    /**
     * Fetch AI service metadata (optional).
     * Looks for fields like `model`, `version`, `metrics.accuracy` or `accuracy`.
     * @return array
     */
    public function metadata(): array
    {
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) {
            $headers['X-API-KEY'] = $apiKey;
        }

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders($headers)
            ->get("{$this->baseUrl}/metadata");

        if ($response->failed()) {
            $message = $response->json('detail') ?? $response->body();
            throw new RuntimeException('AI service error: ' . $message);
        }

        return $response->json() ?? [];
    }
}