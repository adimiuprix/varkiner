<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PairController;

Route::get('/', [PairController::class, 'index'])->name('pairform');

Route::post('/edit-pair', [PairController::class, 'editPair'])->name('editpair');
