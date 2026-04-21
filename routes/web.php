<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TradeOrderController;

Route::match(['post', 'get'], '/test-order', [TradeOrderController::class, 'testOrder'])->name('test-order');