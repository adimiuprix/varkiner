<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TradeOrderController;

Route::match(['post', 'get'], '/stop-order', [TradeOrderController::class, 'stopOrder'])->name('stop-order');