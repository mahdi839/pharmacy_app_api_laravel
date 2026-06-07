<?php

use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\HomeSliderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::get('/customer/me', [CustomerAuthController::class, 'me']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
Route::get('/customer/orders', [CustomerAuthController::class, 'orders']);
Route::get('/home-sliders', [HomeSliderController::class, 'apiIndex']);
Route::get('/products', [ProductController::class, 'apiIndex']);
Route::post('/orders', [OrderController::class, 'apiStore']);
