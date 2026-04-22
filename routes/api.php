<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TradeOrderController;

Route::post('/trade-order', [TradeOrderController::class, 'openTradeOrder'])->name('trade-order.open');