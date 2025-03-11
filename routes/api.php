<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

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
Route::middleware('auth:sanctum')->get('/v1/admin/dashboard', [DashboardController::class, 'index']);
