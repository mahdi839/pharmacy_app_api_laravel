<?php

use App\Http\Controllers\AppUpdateController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\HomeSliderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/app-version', [AppUpdateController::class, 'apiShow']);
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::get('/customer/me', [CustomerAuthController::class, 'me']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
Route::get('/customer/orders', [CustomerAuthController::class, 'orders']);
Route::get('/home-sliders', [HomeSliderController::class, 'apiIndex']);
Route::get('/banners', [BannerController::class, 'apiIndex']);
Route::get('/products', [ProductController::class, 'apiIndex']);
Route::get('/products/{product}', [ProductController::class, 'apiShow'])->whereNumber('product');
Route::post('/orders', [OrderController::class, 'apiStore']);

Route::middleware('throttle:30,1')->group(function (): void {
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
});

Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/notifications', [CustomerNotificationController::class, 'index']);
    Route::post('/notifications/read', [CustomerNotificationController::class, 'markRead']);
});
