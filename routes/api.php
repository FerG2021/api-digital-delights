<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\MarkController;


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

    // Promotions
    Route::get('/promotions/{id}', [PromotionController::class,'index']);
    Route::post('/promotion/{id}', [PromotionController::class,'create']);
    Route::post('/promotion/update/{id}', [PromotionController::class,'update']);
    Route::post('/promotion/delete/{id}', [PromotionController::class,'destroy']);

        // Catalogue
        Route::get('/promotions/catalogue/{id}', [PromotionController::class,'getAll']);

    // Marks
    Route::get('/marks/{id}', [MarkController::class,'index']);
    Route::post('/mark/{id}', [MarkController::class,'create']);
    Route::post('/mark/update/{id}', [MarkController::class,'update']);
    Route::post('/mark/delete/{id}', [MarkController::class,'destroy']);

    // My-account
    Route::put('/my-account/update/{id}', [AccountController::class,'update']);

});
