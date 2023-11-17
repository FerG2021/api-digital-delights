<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;


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

// Route::get('/categoria', [CategoryController::class,'index']);
// Route::get('/categoria/{id}', [CategoryController::class,'show']);
// Route::put('/categoria/{id}', [CategoryController::class,'edit']);
// Route::delete('/categoria/{id}', [CategoryController::class,'destroy']);

Route::group(['middleware' => ['web']], function () {
    // your routes here
    // LOGIN

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    // Categories
    Route::get('/categories/{id}', [CategoryController::class,'index']);
    Route::post('/category/{id}', [CategoryController::class,'create']);
    Route::post('/category/update/{id}', [CategoryController::class,'update']);
    Route::post('/category/delete/{id}', [CategoryController::class,'destroy']);

    // Products
    Route::get('/products/{id}', [ProductController::class,'index']);
    Route::post('/product/{id}', [ProductController::class,'create']);
    Route::post('/product/update/{id}', [ProductController::class,'update']);
    Route::post('/product/delete/{id}', [ProductController::class,'destroy']);

});
