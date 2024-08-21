<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LogoutUserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;


// auth user
Route::post('login', [\App\Http\Controllers\LoginUserController::class, 'login']);
// logout  user
Route::post('/logout', [LogoutUserController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// route product resource
Route::resource('product', ProductController::class)->middleware('auth:sanctum');


// pdf
Route::get('/download-product-list', [PdfController::class, 'generateProductList'])->middleware('auth:sanctum');


// Mettre à jour le type et la couleur
Route::post('/settings/color', [SettingController::class, 'updateColor'])->middleware('auth:sanctum');
Route::get('/settings/colors', [SettingController::class, 'listSettings'])->middleware('auth:sanctum');


// Supprimer le type et la couleur
Route::delete('/settings/color', [SettingController::class, 'deleteColor'])->middleware('auth:sanctum');

 // Mettre à jour le logo
Route::post('/settings/logo', [SettingController::class, 'updateLogo'])->middleware('auth:sanctum');
// routes/api.php
Route::get('/settings/logos', [SettingController::class, 'listLogos'])->middleware('auth:sanctum');

// Supprimer le logo
Route::delete('/settings/logo', [SettingController::class, 'deleteLogo'])->middleware('auth:sanctum');


// get all users
Route::get('/users', [UserController::class, 'index'])->middleware('auth:sanctum');
Route::get('/users/item', [UserController::class, 'item'])->middleware('auth:sanctum');
Route::post('/users', [UserController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('auth:sanctum');
Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::post('/add-to-cart', [CartController::class, 'addToCart'])->middleware('auth:sanctum');
// create sale api ressource
Route::post('/sale', [SaleController::class, 'store'])->middleware('auth:sanctum');
Route::post('/sales/checked-validation', [SaleController::class, 'checkValidation'])->middleware('auth:sanctum');
Route::post('/sales/payment-mode', [SaleController::class, 'addPaymentMode']);



Route::put('/sales/update-details', [SaleController::class, 'updateSaleDetails']);




Route::get('/sales', [SaleController::class, 'index'])->middleware('auth:sanctum')->middleware('auth:sanctum');
Route::get('/sales/last-week-sales', [SaleController::class, 'lastWeekSales'])->middleware('auth:sanctum');
Route::get('/sales/for-one-year', [SaleController::class, 'salesForOneYear'])->middleware('auth:sanctum');
Route::get('/sales/ca-for-now', [SaleController::class, 'getCaNow'])->middleware('auth:sanctum');
Route::get('/sales/last-mounth-sale', [SaleController::class, 'salesForLastMonth'])->middleware('auth:sanctum');
Route::get('/sales/invalid', [SaleController::class, 'getCountInvalidSale'])->middleware('auth:sanctum');
Route::get('/sales/count-in-progress', [SaleController::class, 'countSalesInProgress'])->middleware('auth:sanctum');
Route::get('/sales/in-progress', [SaleController::class, 'listInProgress'])->middleware('auth:sanctum');
Route::get('/sales/ids-in-progress', [SaleController::class, 'getSalesIdsInProgress']);

Route::post('/validate-sale-state', [SaleController::class, 'validateSaleState']);


Route::delete('/sales/{id}/clear', [SaleController::class, 'clearCurrentCart'])
    ->name('sales.clear')
    ->middleware('auth:sanctum');





// order api ressource
Route::post('/order/{id}', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::delete('/order/cancel/{product}/{order}', [OrderController::class, 'cancel']);
Route::post('/order/validation/store', [OrderController::class, 'validation']);
// setting ressource
Route::get('/settings', [SettingController::class, 'index'])->middleware('auth:sanctum');
Route::post('/settings', [SettingController::class, 'store'])->middleware('auth:sanctum');
// menus ressource
Route::get('/menus', [MenuController::class, 'index'])->middleware('auth:sanctum');
// roles ressource
Route::get('/roles', [RoleController::class, 'index'])->middleware('auth:sanctum');
Route::post('/roles', [RoleController::class, 'store'])->middleware('auth:sanctum');
