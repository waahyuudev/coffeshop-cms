<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
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

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'add']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('logout', [AuthController::class, 'logout']);
    });
});

Route::group(['prefix' => 'product'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('list', [ProductController::class, 'index']);
        Route::post('update', [ProductController::class, 'update']);
        Route::post('add', [ProductController::class, 'store']);
        Route::delete('delete', [ProductController::class, 'destroy']);
    });
});

Route::group(['prefix' => 'order'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('list', [OrderController::class, 'index']);
        Route::post('add', [OrderController::class, 'store']);
        Route::delete('delete', [OrderController::class, 'destroy']);
        Route::get('detail', [OrderController::class, 'orderDetail']);
        Route::put('mark-status', [OrderController::class, 'markStatus']);
    });
});

Route::group(['prefix' => 'customer'], function () {
    Route::get('menu-list', [ProductController::class, 'index']);
    Route::post('submit-order', [OrderController::class, 'store']);
});
