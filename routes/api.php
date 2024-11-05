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
use App\Http\Controllers\SupplyController;


Route::get('/current-date', [SettingController::class, 'currentDate']);


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

// Mettre a jour le type et la couleur
Route::post('/settings/color', [SettingController::class, 'updateColor'])->middleware('auth:sanctum');
Route::get('/settings/colors', [SettingController::class, 'listSettings'])->middleware('auth:sanctum');

// Supprimer le type et la couleur
Route::delete('/settings/color', [SettingController::class, 'deleteColor'])->middleware('auth:sanctum');

 // logo
Route::post('/settings/logo', [SettingController::class, 'updateLogo'])->middleware('auth:sanctum');
Route::get('/settings/logos', [SettingController::class, 'listLogos'])->middleware('auth:sanctum');
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

Route::get('/sales', [SaleController::class, 'index'])->middleware('auth:sanctum')->middleware('auth:sanctum');

//dashboard
Route::get('/sales/last-week-sales', [SaleController::class, 'lastWeekSales'])->middleware('auth:sanctum');
Route::get('/sales/week-sales', [SaleController::class, 'thisWeekSales'])->middleware('auth:sanctum');
Route::get('/sales/day-sales', [SaleController::class, 'todaySales'])->middleware('auth:sanctum');
Route::get('/sales/vente-en-cours', [SaleController::class, 'salesInProgressOrExpired'])->middleware('auth:sanctum');
Route::get('/sales/vente-pourcentage', [SaleController::class, 'getSaleStatistics'])->middleware('auth:sanctum');

Route::get('/sales/monthly/{year}', [SaleController::class, 'getMonthlySalesForYear'])->middleware('auth:sanctum');
Route::get('/sales/annual-total', [SaleController::class, 'getAnnualSalesTotal'])->middleware('auth:sanctum');


Route::get('/sales/for-one-year', [SaleController::class, 'salesForOneYear'])->middleware('auth:sanctum');
Route::get('/sales/ca-for-now', [SaleController::class, 'getCaNow'])->middleware('auth:sanctum');
Route::get('/sales/last-mounth-sale', [SaleController::class, 'salesForLastMonth'])->middleware('auth:sanctum');
Route::get('/sales/invalid', [SaleController::class, 'getCountInvalidSale'])->middleware('auth:sanctum');
Route::get('/sales/count-in-progress', [SaleController::class, 'countSalesInProgress'])->middleware('auth:sanctum');
Route::get('/sales/in-progress', [SaleController::class, 'listInProgress'])->middleware('auth:sanctum');
Route::get('/sales/ids-in-progress', [SaleController::class, 'getSalesIdsInProgress']);
Route::put('/sales/update-details', [SaleController::class, 'updateSaleDetails'])->middleware(('auth:sanctum'));
Route::get('/sales/pending', [SaleController::class, 'getAllSales']);
Route::put('/sales/update-payment', [SaleController::class, 'updatePayment']);
Route::get('/pending-sales/pdf', [SaleController::class, 'downloadPendingSalesPdf']);
Route::get('/sale/{id}/pdf', [SaleController::class, 'downloadSaleDetailPdf']);
Route::middleware('auth:sanctum')->get('sale/filtre_ventes', [SaleController::class, 'getVentes']);

Route::post('/sales/download-report', [SaleController::class, 'downloadSalesReport']);

Route::post('/validate-sale-state', [SaleController::class, 'validateSaleState']);

Route::delete('/sales/delete', [SaleController::class, 'deleteSales']);

Route::delete('/sales/{id}/clear', [SaleController::class, 'clearCurrentCart'])
    ->name('sales.clear')
    ->middleware('auth:sanctum');

// order api ressource
// Route pour ajouter des produits au panier
Route::post('/cart', [OrderController::class, 'addToCart'])->middleware('auth:sanctum');

// Route pour finaliser la commande
Route::post('/order/finalize', [OrderController::class, 'finalizeOrder'])->middleware('auth:sanctum');
Route::post('/order', [OrderController::class, 'store'])->middleware('auth:sanctum');
Route::post('/orders/{orderId}/add-products', [OrderController::class, 'addProductToOrder'])->middleware('auth:sanctum');
Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:sanctum');
Route::delete('/order/cancel/{order}', [OrderController::class, 'cancel'])->middleware('auth:sanctum');
Route::delete('/orders/{order_id}/products/remove', [OrderController::class, 'deleteProductFromOrder'])->middleware('auth:sanctum');
Route::post('/order/validation/store', [OrderController::class, 'validation'])->middleware('auth:sanctum');

// Approvisionnement
Route::post('/supply/add/{id_product}', [SupplyController::class, 'addSupply'])->middleware('auth:sanctum');
Route::get('/supplies', [SupplyController::class, 'listSupplies'])->middleware('auth:sanctum');
Route::post('/shelf-life-settings', [SupplyController::class, 'setDifferenceInYears'])->middleware('auth:sanctum');
Route::delete('/supply/{id}', [SupplyController::class, 'deleteSupply'])->middleware('auth:sanctum');




// setting ressource
Route::get('/settings', [SettingController::class, 'index'])->middleware('auth:sanctum');
Route::post('/settings', [SettingController::class, 'store'])->middleware('auth:sanctum');
// menus ressource
Route::get('/menus', [MenuController::class, 'index'])->middleware('auth:sanctum');
// roles ressource
Route::get('/roles', [RoleController::class, 'index'])->middleware('auth:sanctum');
Route::post('/roles', [RoleController::class, 'store'])->middleware('auth:sanctum');

Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('auth:sanctum');