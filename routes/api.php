<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPriceController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);

Route::get('products/{product}/prices', [ProductPriceController::class, 'index']);
Route::post('products/{product}/prices', [ProductPriceController::class, 'store']);
