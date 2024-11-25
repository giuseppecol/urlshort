<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\URLController;

Route::get('/{shortCode}', [URLController::class, 'redirect']);