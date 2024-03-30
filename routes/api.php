<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\Resource\Clients\ClientsResource;
use App\Http\Controllers\Api\Resource\Orders\OrdersResource;
use App\Http\Controllers\Api\Resource\Products\ProductsResource;
use App\Http\Controllers\Api\Resource\Warehouses\ProductWarehousesResource;
use App\Http\Controllers\Api\Resource\Warehouses\WarehousesResource;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['prefix' => 'v1'], function () {
    //auth login
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        //users
        Route::post('logout', [AuthController::class, 'logout']);

        //clients
        Route::resource("clients", ClientsResource::class);

        //products
        Route::resource("products", ProductsResource::class);

        //warehouses
        Route::resource("warehouses", WarehousesResource::class);

        //product-warehouses
        Route::resource("product-warehouses", ProductWarehousesResource::class);

        //orders
        Route::resource("orders", OrdersResource::class);
    });

    Route::post('currency', [CurrencyController::class, 'currency']);

});