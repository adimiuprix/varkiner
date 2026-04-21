<?php

use App\Http\Controllers\TradeOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/trade-order', [TradeOrderController::class, 'openTradeOrder'])
        ->name('trade-order.open');
});
