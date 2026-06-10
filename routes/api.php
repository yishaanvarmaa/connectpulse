<?php

use App\Http\Controllers\Api\V1\ConnectionController;
use App\Http\Controllers\Api\V1\CreditsController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([AuthenticateApiKey::class])->group(function () {
    // Standard public API
    Route::post('/messages/send', [MessageController::class, 'send']);
    Route::post('/messages/bulk', [MessageController::class, 'bulk']);
    Route::get('/connection', ConnectionController::class);
    Route::get('/credits/balance', [CreditsController::class, 'balance']);

    // Legacy endpoints (backward compatible)
    Route::post('/send-message', [MessageController::class, 'send']);
    Route::post('/send-bulk', [MessageController::class, 'bulk']);
    Route::get('/status', ConnectionController::class);
    Route::get('/balance', [CreditsController::class, 'balance']);
});
