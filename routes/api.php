<?php

use App\Http\Controllers\ApiCommerceController;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/products', [ApiController::class, 'products'])->middleware([ResolveTenant::class, 'throttle:60,1']);
    Route::get('/orders', [ApiController::class, 'orders'])->middleware(['auth:sanctum', ResolveTenant::class, 'abilities:orders:read', 'throttle:60,1']);
    Route::post('/tokens', [ApiCommerceController::class, 'token'])->middleware([ResolveTenant::class, 'throttle:5,1']);
    Route::middleware(['auth:sanctum', ResolveTenant::class, 'throttle:60,1'])->group(function () {
        $controller = ApiCommerceController::class;
        Route::delete('/tokens/current', [$controller, 'revoke']);
        Route::get('/cart', [$controller, 'cart'])->middleware('abilities:cart:write');
        Route::post('/cart/items', [$controller, 'add'])->middleware('abilities:cart:write');
        Route::patch('/cart/items/{item}', [$controller, 'update'])->middleware('abilities:cart:write');
        Route::post('/checkout', [$controller, 'checkout'])->middleware('abilities:orders:write');
        Route::get('/orders/{number}', [$controller, 'order'])->middleware('abilities:orders:read');
        Route::post('/orders/{number}/sandbox-payment', [$controller, 'pay'])->middleware('abilities:orders:write');
        Route::post('/orders/{number}/cancel', [$controller, 'cancel'])->middleware('abilities:orders:write');
        Route::post('/order-items/{item}/returns', [$controller, 'returnItem'])->middleware('abilities:returns:write');
    });
    Route::post('/webhooks/sandbox', [ApiController::class, 'webhook'])->middleware([ResolveTenant::class, 'throttle:60,1']);
});
