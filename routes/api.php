<?php

use App\Http\Controllers\Api\V1\Admin\UserDashboardController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route::get('/test', function (Request $request) {
//     return ["email"=>"samira@gmail.com","password"=>"password"];
// });

Route::post('/v1/admin/register', [AuthController::class, 'register']);
Route::post('/v1/admin/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/v1/admin/logout', [AuthController::class, 'logout']);

//dashboard
Route::middleware(['role:super_admin', 'auth:sanctum'])->group(function () {
    Route::get('/v1/admin/dashboard', [UserDashboardController::class, 'index']);
});
//catgories
Route::middleware(['role:super_admin', 'auth:sanctum'])->group(function () {
    Route::get('/v1/admin/categories', [CategoryController::class, 'index']);
    Route::post('/v1/admin/categories', [CategoryController::class, 'store']);
    Route::put('/v1/admin/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/v1/admin/categories/{id}', [CategoryController::class, 'destroy']);
});

//products

Route::middleware(['auth:sanctum', 'role:product_manager|super_admin'])->group(function () {
    Route::get('/v1/admin/products', [ProductController::class, 'index']);
    Route::post('/v1/admin/products', [ProductController::class, 'store']);
    Route::put('/v1/admin/products/{id}', [ProductController::class, 'update']);
    Route::get('/v1/admin/products/{id}', [ProductController::class, 'show']);
    Route::delete('/v1/admin/products/{id}', [ProductController::class, 'destroy']);
});

//users
Route::middleware(['role:super_admin', 'auth:sanctum'])->group(function () {
    Route::get('/v1/admin/users', [UsersController::class, 'index']);
    Route::post('/v1/admin/users', [UsersController::class, 'store']);
    Route::delete('/v1/admin/users/{id}', [UsersController::class, 'destroy']);
    Route::put('/v1/admin/users/{id}', [UsersController::class, 'update']);
});