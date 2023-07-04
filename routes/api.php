<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LogoutUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// auth user
Route::post('login', [\App\Http\Controllers\LoginUserController::class, 'login']);
// logout  user
Route::post('/logout', [LogoutUserController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// route product resource 
Route::resource('product', ProductController::class)->middleware('auth:sanctum');
// get all users
Route::get('/users', [UserController::class, 'index'])->middleware('auth:sanctum');
Route::post('/users', [UserController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('auth:sanctum');
Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::post('/add-to-cart', [CartController::class, 'addToCart'])->middleware('auth:sanctum');
// create sale api 
Route::post('/sale', [SaleController::class, 'store'])->middleware('auth:sanctum');
Route::get('/sales', [SaleController::class, 'index'])->middleware('auth:sanctum');
// order api
Route::post('/order/{id}', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::delete('/order/cancel/{product}/{order}', [OrderController::class, 'cancel']);
Route::post('/order/validation/store', [OrderController::class, 'validation']);
