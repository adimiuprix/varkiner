<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PairController;

Route::get('/', [PairController::class, 'index'])->name('pairform');

Route::post('/edit-pair', [PairController::class, 'editPair'])->name('editpair');

Route::post('/pm2/start',   [PairController::class, 'pm2Start'])->name('pm2.start');
Route::post('/pm2/stop',    [PairController::class, 'pm2Stop'])->name('pm2.stop');
Route::post('/pm2/restart', [PairController::class, 'pm2Restart'])->name('pm2.restart');
