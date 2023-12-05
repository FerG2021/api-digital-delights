<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AccountController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['web']], function () {
    // LOGIN
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    // Categories
    Route::get('/categories/{id}', [CategoryController::class,'index']);
    Route::post('/category/{id}', [CategoryController::class,'create']);
    Route::post('/category/update/{id}', [CategoryController::class,'update']);
    Route::post('/category/delete/{id}', [CategoryController::class,'destroy']);

        // Catalogue
        Route::get('/categories/catalogue/{id}', [CategoryController::class,'getAll']);


    // Products
    Route::get('/products/{id}', [ProductController::class,'index']);
    Route::post('/product/{id}', [ProductController::class,'create']);
    Route::post('/product/update/{id}', [ProductController::class,'update']);
    Route::post('/product/delete/{id}', [ProductController::class,'destroy']);

        // Catalogue
        Route::get('/products/catalogue/{id}', [ProductController::class,'getAll']);

    // My-account
    Route::put('/my-account/update/{id}', [AccountController::class,'update']);

});
