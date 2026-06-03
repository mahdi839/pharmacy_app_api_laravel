<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'apiIndex']);
Route::post('/orders', [OrderController::class, 'apiStore']);
