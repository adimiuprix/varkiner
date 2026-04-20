<?php

use App\Http\Controllers\TradeOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/trade-order', [TradeOrderController::class, 'openTradeOrder'])->name('trade-order.open');
