<?php

use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\Api\V1\SendMessageController;
use App\Http\Controllers\Api\V1\StatusController;
use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([AuthenticateApiKey::class])->group(function () {
    Route::post('/send-message', [SendMessageController::class, 'send']);
    Route::post('/send-bulk', [SendMessageController::class, 'sendBulk']);
    Route::get('/balance', BalanceController::class);
    Route::get('/status', StatusController::class);
});
