<?php

namespace App\Http\Controllers;

use App\Services\AiClient;
use Illuminate\Http\Request;

class AiPredictController extends Controller
{
    public function __construct(private readonly AiClient $aiClient)
    {
    }

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'features' => ['required', 'array', 'min:1'],
            'features.*' => ['numeric'],
        ]);

        $result = $this->aiClient->predict($validated['features']);

        return response()->json($result);
    }
}