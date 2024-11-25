<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\URLController;
use Laravel\Sanctum\Http\Controllers\SanctumController;

Route::middleware('auth:api')->post('/shorten', [URLController::class, 'shorten']);
Route::middleware('auth:api')->get('/urls', [URLController::class, 'urls']);
Route::middleware('auth:api')->delete('/url/{id}', [URLController::class, 'destroy']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});