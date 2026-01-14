<?php

use App\Http\Controllers\AiPredictController;
use App\Http\Controllers\AiStatusController;
use Illuminate\Support\Facades\Route;

Route::post('/ai/predict', AiPredictController::class);
Route::get('/ai/health', [AiStatusController::class, 'health']);
Route::get('/ai/accuracy', [AiStatusController::class, 'accuracy']);
